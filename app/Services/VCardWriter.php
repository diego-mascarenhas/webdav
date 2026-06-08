<?php

namespace App\Services;

use App\Dav\Support\DavBlob;
use App\Dav\Support\ResolvesDavResources;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Sabre\VObject\Component\VCard;

class VCardWriter
{
    use ResolvesDavResources;

    private const OPERATION_ADD = 1;

    private const OPERATION_MODIFY = 2;

    private const OPERATION_DELETE = 3;

    /**
     * @param  array{uid?: string, name: string, surname?: ?string, email?: ?string, phone?: ?string}  $payload
     * @return array{uid: string, updated_at: int}
     */
    public function upsert(User $user, array $payload, ?string $uid = null): array
    {
        $addressBookId = $this->defaultAddressBookId($user);

        if ($addressBookId === null) {
            throw new \RuntimeException('Default address book not found.');
        }

        $uid = $uid ?? $payload['uid'] ?? (string) Str::uuid();
        $uri = $this->cardUri($uid);
        $cardData = $this->buildVCard($uid, $payload);
        $now = time();
        $etag = $this->contentEtag($cardData);
        $size = strlen($cardData);

        $existing = DB::table('cards')
            ->where('addressbookid', $addressBookId)
            ->where('uri', $uri)
            ->first();

        if ($existing === null) {
            DB::table('cards')->insert([
                'addressbookid' => $addressBookId,
                'carddata' => DavBlob::forWrite($cardData),
                'uri' => $uri,
                'lastmodified' => $now,
                'etag' => $etag,
                'size' => $size,
            ]);
            $operation = self::OPERATION_ADD;
        } else {
            DB::table('cards')
                ->where('id', $existing->id)
                ->update([
                    'carddata' => DavBlob::forWrite($cardData),
                    'lastmodified' => $now,
                    'etag' => $etag,
                    'size' => $size,
                ]);
            $operation = self::OPERATION_MODIFY;
        }

        $syncToken = $this->bumpAddressBookSyncToken($addressBookId);
        $this->recordAddressBookChange($addressBookId, $uri, $operation, $syncToken);

        return [
            'uid' => $uid,
            'updated_at' => $now,
        ];
    }

    public function delete(User $user, string $uid): bool
    {
        $addressBookId = $this->defaultAddressBookId($user);

        if ($addressBookId === null) {
            return false;
        }

        $uri = $this->cardUri($uid);

        $deleted = DB::table('cards')
            ->where('addressbookid', $addressBookId)
            ->where('uri', $uri)
            ->delete();

        if ($deleted === 0) {
            return false;
        }

        $syncToken = $this->bumpAddressBookSyncToken($addressBookId);
        $this->recordAddressBookChange($addressBookId, $uri, self::OPERATION_DELETE, $syncToken);

        return true;
    }

    /**
     * @param  array{name: string, surname?: ?string, email?: ?string, phone?: ?string}  $payload
     */
    private function buildVCard(string $uid, array $payload): string
    {
        $name = trim($payload['name'] ?? '');
        $surname = trim($payload['surname'] ?? '');
        $fullName = trim($name.' '.$surname) ?: $name ?: 'Contact';

        $vCard = new VCard([
            'UID' => $uid,
            'FN' => $fullName,
            'N' => [$surname, $name, '', '', ''],
        ]);

        if (! empty($payload['email'])) {
            $vCard->add('EMAIL', $payload['email']);
        }

        if (! empty($payload['phone'])) {
            $vCard->add('TEL', $payload['phone']);
        }

        $vCard->add('REV', gmdate('Ymd\\THis\\Z'));

        return $vCard->serialize();
    }
}
