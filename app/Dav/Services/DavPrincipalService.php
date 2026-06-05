<?php

namespace App\Dav\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class DavPrincipalService
{
    public function principalUri(User $user): string
    {
        return 'principals/'.$this->principalName($user);
    }

    public function principalName(User $user): string
    {
        return $user->dav_username ?? strtok($user->email, '@');
    }

    public function provision(User $user): void
    {
        $principalUri = $this->principalUri($user);
        $name = $this->principalName($user);

        DB::table('principals')->updateOrInsert(
            ['uri' => $principalUri],
            [
                'email' => $user->email,
                'displayname' => $user->name,
            ]
        );

        foreach ([
            "{$principalUri}/calendar-proxy-read",
            "{$principalUri}/calendar-proxy-write",
        ] as $proxyUri) {
            DB::table('principals')->updateOrInsert(
                ['uri' => $proxyUri],
                ['email' => null, 'displayname' => null]
            );
        }

        DB::table('addressbooks')->updateOrInsert(
            ['principaluri' => $principalUri, 'uri' => 'default'],
            [
                'displayname' => 'Contacts',
                'description' => 'Default address book',
                'synctoken' => 1,
            ]
        );

        $calendarId = DB::table('calendarinstances')
            ->where('principaluri', $principalUri)
            ->where('uri', 'default')
            ->value('calendarid');

        if ($calendarId === null) {
            $calendarId = DB::table('calendars')->insertGetId([
                'synctoken' => 1,
                'components' => 'VEVENT,VTODO',
            ]);
        }

        DB::table('calendarinstances')->updateOrInsert(
            ['principaluri' => $principalUri, 'uri' => 'default'],
            [
                'calendarid' => $calendarId,
                'access' => 1,
                'displayname' => 'Calendar',
                'description' => 'Default calendar',
                'calendarcolor' => '#3B82F6',
                'transparent' => 0,
                'share_invitestatus' => 2,
            ]
        );
    }
}
