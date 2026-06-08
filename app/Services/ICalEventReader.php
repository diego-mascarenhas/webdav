<?php

namespace App\Services;

use App\Dav\Services\DavPrincipalService;
use App\Dav\Support\DavBlob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;

class ICalEventReader
{
    public function __construct(
        private DavPrincipalService $davPrincipal
    ) {}

    /**
     * @return Collection<int, array{uid: string, summary: string, location: ?string, description: ?string, starts_at: ?string, ends_at: ?string, all_day: bool, updated_at: ?int}>
     */
    public function forUser(User $user): Collection
    {
        $principalUri = $this->davPrincipal->principalUri($user);

        $calendarIds = DB::table('calendarinstances')
            ->where('principaluri', $principalUri)
            ->pluck('calendarid');

        if ($calendarIds->isEmpty()) {
            return collect();
        }

        return DB::table('calendarobjects')
            ->whereIn('calendarid', $calendarIds)
            ->where('componenttype', 'VEVENT')
            ->orderBy('firstoccurence')
            ->get()
            ->map(fn ($row) => $this->parseEvent($row))
            ->filter()
            ->sortBy('starts_at')
            ->values();
    }

    /**
     * @return array{uid: string, summary: string, location: ?string, description: ?string, starts_at: ?string, ends_at: ?string, all_day: bool, updated_at: ?int}|null
     */
    private function parseEvent(object $row): ?array
    {
        $calendarData = DavBlob::toString($row->calendardata);

        if ($calendarData === '') {
            return null;
        }

        try {
            $calendar = Reader::read($calendarData);
        } catch (\Throwable) {
            return null;
        }

        if (! $calendar instanceof VCalendar) {
            return null;
        }

        $event = $calendar->VEVENT;

        if ($event === null) {
            return null;
        }

        $startsAt = $this->formatDateTime($event->DTSTART ?? null);
        $endsAt = $this->formatDateTime($event->DTEND ?? null);
        $allDay = isset($event->DTSTART) && (string) $event->DTSTART['VALUE'] === 'DATE';

        $description = isset($event->DESCRIPTION) ? trim((string) $event->DESCRIPTION) : null;

        return [
            'uid' => isset($event->UID) ? (string) $event->UID : $row->uri,
            'summary' => isset($event->SUMMARY) ? trim((string) $event->SUMMARY) : '(Sin título)',
            'location' => isset($event->LOCATION) ? trim((string) $event->LOCATION) : null,
            'description' => $description !== '' ? $description : null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'all_day' => $allDay,
            'updated_at' => $row->lastmodified ? (int) $row->lastmodified : null,
        ];
    }

    private function formatDateTime(mixed $property): ?string
    {
        if ($property === null) {
            return null;
        }

        try {
            $dateTime = $property->getDateTime();

            return Carbon::instance($dateTime)->timezone(config('app.timezone'))->toIso8601String();
        } catch (\Throwable) {
            return trim((string) $property) ?: null;
        }
    }
}
