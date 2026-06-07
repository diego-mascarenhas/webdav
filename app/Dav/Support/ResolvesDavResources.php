<?php

namespace App\Dav\Support;

use App\Dav\Services\DavPrincipalService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

trait ResolvesDavResources
{
    protected function defaultAddressBookId(User $user): ?int
    {
        $principalUri = app(DavPrincipalService::class)->principalUri($user);

        $id = DB::table('addressbooks')
            ->where('principaluri', $principalUri)
            ->where('uri', 'default')
            ->value('id');

        return $id !== null ? (int) $id : null;
    }

    protected function defaultCalendarId(User $user): ?int
    {
        $principalUri = app(DavPrincipalService::class)->principalUri($user);

        $calendarId = DB::table('calendarinstances')
            ->where('principaluri', $principalUri)
            ->where('uri', 'default')
            ->value('calendarid');

        return $calendarId !== null ? (int) $calendarId : null;
    }

    protected function bumpAddressBookSyncToken(int $addressBookId): int
    {
        DB::table('addressbooks')
            ->where('id', $addressBookId)
            ->increment('synctoken');

        return (int) DB::table('addressbooks')->where('id', $addressBookId)->value('synctoken');
    }

    protected function bumpCalendarSyncToken(int $calendarId): int
    {
        DB::table('calendars')
            ->where('id', $calendarId)
            ->increment('synctoken');

        return (int) DB::table('calendars')->where('id', $calendarId)->value('synctoken');
    }

    protected function recordAddressBookChange(int $addressBookId, string $uri, int $operation, int $syncToken): void
    {
        DB::table('addressbookchanges')->insert([
            'uri' => $uri,
            'synctoken' => $syncToken,
            'addressbookid' => $addressBookId,
            'operation' => $operation,
        ]);
    }

    protected function recordCalendarChange(int $calendarId, string $uri, int $operation, int $syncToken): void
    {
        DB::table('calendarchanges')->insert([
            'uri' => $uri,
            'synctoken' => $syncToken,
            'calendarid' => $calendarId,
            'operation' => $operation,
        ]);
    }

    protected function cardUri(string $uid): string
    {
        return str_replace(['/', '\\'], '-', $uid).'.vcf';
    }

    protected function calendarObjectUri(string $uid): string
    {
        return str_replace(['/', '\\'], '-', $uid).'.ics';
    }

    /**
     * Sabre DAV stores etags in VARBINARY(32) — must fit 32 bytes (md5 hex, no quotes).
     */
    protected function contentEtag(string $data): string
    {
        return md5($data);
    }
}
