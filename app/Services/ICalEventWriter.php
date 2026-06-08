<?php

namespace App\Services;

use App\Dav\Support\DavBlob;
use App\Dav\Support\ResolvesDavResources;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Sabre\VObject\Component\VCalendar;

class ICalEventWriter
{
    use ResolvesDavResources;

    private const OPERATION_ADD = 1;

    private const OPERATION_MODIFY = 2;

    private const OPERATION_DELETE = 3;

    /**
     * @param  array{uid?: string, summary: string, description?: ?string, location?: ?string, starts_at: string, ends_at?: ?string, all_day?: bool}  $payload
     * @return array{uid: string, updated_at: int}
     */
    public function upsert(User $user, array $payload, ?string $uid = null): array
    {
        $calendarId = $this->defaultCalendarId($user);

        if ($calendarId === null) {
            throw new \RuntimeException('Default calendar not found.');
        }

        $uid = $uid ?? $payload['uid'] ?? (string) Str::uuid();
        $uri = $this->calendarObjectUri($uid);
        $calendarData = $this->buildCalendar($uid, $payload);
        $now = time();
        $etag = $this->contentEtag($calendarData);
        $size = strlen($calendarData);
        $startsAt = Carbon::parse($payload['starts_at']);
        $endsAt = isset($payload['ends_at']) ? Carbon::parse($payload['ends_at']) : $startsAt->copy()->addHour();
        $allDay = (bool) ($payload['all_day'] ?? false);

        $existing = DB::table('calendarobjects')
            ->where('calendarid', $calendarId)
            ->where('uri', $uri)
            ->first();

        $row = [
            'calendardata' => DavBlob::forWrite($calendarData),
            'lastmodified' => $now,
            'etag' => $etag,
            'size' => $size,
            'componenttype' => 'VEVENT',
            'firstoccurence' => $startsAt->timestamp,
            'lastoccurence' => $endsAt->timestamp,
            'uid' => $uid,
        ];

        if ($existing === null) {
            DB::table('calendarobjects')->insert(array_merge($row, [
                'uri' => $uri,
                'calendarid' => $calendarId,
            ]));
            $operation = self::OPERATION_ADD;
        } else {
            DB::table('calendarobjects')->where('id', $existing->id)->update($row);
            $operation = self::OPERATION_MODIFY;
        }

        $syncToken = $this->bumpCalendarSyncToken($calendarId);
        $this->recordCalendarChange($calendarId, $uri, $operation, $syncToken);

        return [
            'uid' => $uid,
            'updated_at' => $now,
        ];
    }

    public function delete(User $user, string $uid): bool
    {
        $calendarId = $this->defaultCalendarId($user);

        if ($calendarId === null) {
            return false;
        }

        $uri = $this->calendarObjectUri($uid);

        $deleted = DB::table('calendarobjects')
            ->where('calendarid', $calendarId)
            ->where('uri', $uri)
            ->where('componenttype', 'VEVENT')
            ->delete();

        if ($deleted === 0) {
            return false;
        }

        $syncToken = $this->bumpCalendarSyncToken($calendarId);
        $this->recordCalendarChange($calendarId, $uri, self::OPERATION_DELETE, $syncToken);

        return true;
    }

    /**
     * @param  array{summary: string, description?: ?string, location?: ?string, starts_at: string, ends_at?: ?string, all_day?: bool}  $payload
     */
    private function buildCalendar(string $uid, array $payload): string
    {
        $allDay = (bool) ($payload['all_day'] ?? false);
        $startsAt = Carbon::parse($payload['starts_at']);
        $endsAt = isset($payload['ends_at']) ? Carbon::parse($payload['ends_at']) : $startsAt->copy()->addHour();

        $calendar = new VCalendar;
        $event = $calendar->add('VEVENT', [
            'UID' => $uid,
            'SUMMARY' => $payload['summary'],
            'DTSTAMP' => gmdate('Ymd\\THis\\Z'),
        ]);

        if ($allDay) {
            $event->DTSTART = $startsAt->format('Ymd');
            $event->DTEND = $endsAt->format('Ymd');
        } else {
            $event->DTSTART = $startsAt->utc()->format('Ymd\\THis\\Z');
            $event->DTEND = $endsAt->utc()->format('Ymd\\THis\\Z');
        }

        if (! empty($payload['description'])) {
            $event->DESCRIPTION = $payload['description'];
        }

        if (! empty($payload['location'])) {
            $event->LOCATION = $payload['location'];
        }

        return $calendar->serialize();
    }
}
