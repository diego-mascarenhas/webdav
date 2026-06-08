<?php

namespace App\Services;

use App\Dav\Services\DavPrincipalService;
use App\Dav\Support\DavBlob;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Sabre\VObject\Component;
use Sabre\VObject\Reader;

class VCardContactReader
{
    public function __construct(
        private DavPrincipalService $davPrincipal
    ) {}

    /**
     * @return Collection<int, array{uid: string, full_name: string, name: string, surname: string, email: ?string, phone: ?string, updated_at: ?int}>
     */
    public function forUser(User $user): Collection
    {
        $principalUri = $this->davPrincipal->principalUri($user);

        $addressbookIds = DB::table('addressbooks')
            ->where('principaluri', $principalUri)
            ->pluck('id');

        if ($addressbookIds->isEmpty()) {
            return collect();
        }

        return DB::table('cards')
            ->whereIn('addressbookid', $addressbookIds)
            ->orderBy('lastmodified', 'desc')
            ->get()
            ->map(fn ($row) => $this->parseCard($row))
            ->filter();
    }

    /**
     * @return array{uid: string, full_name: string, name: string, surname: string, email: ?string, phone: ?string, updated_at: ?int}|null
     */
    private function parseCard(object $row): ?array
    {
        $cardData = DavBlob::toString($row->carddata);

        if ($cardData === '') {
            return null;
        }

        try {
            $vCard = Reader::read($cardData);
        } catch (\Throwable) {
            return null;
        }

        $fullName = isset($vCard->FN) ? trim((string) $vCard->FN) : '';
        [$name, $surname] = $this->splitName($fullName, $vCard);

        $email = $this->firstProperty($vCard, 'EMAIL');
        $phone = $this->firstProperty($vCard, 'TEL');
        $uid = isset($vCard->UID) ? (string) $vCard->UID : $row->uri;

        return [
            'uid' => $uid,
            'full_name' => $fullName ?: trim($name.' '.$surname),
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'phone' => $phone,
            'updated_at' => $row->lastmodified ? (int) $row->lastmodified : null,
        ];
    }

    private function splitName(string $fullName, Component $vCard): array
    {
        if (isset($vCard->N)) {
            $parts = $vCard->N->getParts();
            $surname = trim((string) ($parts[0] ?? ''));
            $name = trim((string) ($parts[1] ?? ''));

            if ($name !== '' || $surname !== '') {
                return [$name, $surname];
            }
        }

        $parts = preg_split('/\s+/', $fullName, 2) ?: [];

        return [
            trim($parts[0] ?? ''),
            trim($parts[1] ?? ''),
        ];
    }

    private function firstProperty(Component $vCard, string $property): ?string
    {
        if (! isset($vCard->{$property})) {
            return null;
        }

        $value = $vCard->{$property};

        if ($value instanceof \Iterator) {
            $value = $value->current();
        }

        return $value !== null ? trim((string) $value) : null;
    }
}
