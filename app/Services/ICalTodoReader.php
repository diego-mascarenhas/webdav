<?php

namespace App\Services;

use App\Dav\Services\DavPrincipalService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;

class ICalTodoReader
{
    public function __construct(
        private DavPrincipalService $davPrincipal
    ) {}

    /**
     * @return Collection<int, array{uid: string, summary: string, description: ?string, due_at: ?string, completed: bool, updated_at: ?int}>
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
            ->where('componenttype', 'VTODO')
            ->orderBy('lastmodified', 'desc')
            ->get()
            ->map(fn ($row) => $this->parseTodo($row))
            ->filter()
            ->values();
    }

    /**
     * @return array{uid: string, summary: string, description: ?string, due_at: ?string, completed: bool, updated_at: ?int}|null
     */
    private function parseTodo(object $row): ?array
    {
        if (empty($row->calendardata)) {
            return null;
        }

        try {
            $calendar = Reader::read($row->calendardata);
        } catch (\Throwable) {
            return null;
        }

        if (! $calendar instanceof VCalendar) {
            return null;
        }

        $todo = $calendar->VTODO;

        if ($todo === null) {
            return null;
        }

        $status = isset($todo->STATUS) ? strtoupper((string) $todo->STATUS) : null;
        $completed = $status === 'COMPLETED' || isset($todo->COMPLETED);

        $description = isset($todo->DESCRIPTION) ? trim((string) $todo->DESCRIPTION) : null;

        return [
            'uid' => isset($todo->UID) ? (string) $todo->UID : $row->uri,
            'summary' => isset($todo->SUMMARY) ? trim((string) $todo->SUMMARY) : '(Sin título)',
            'description' => $description !== '' ? $description : null,
            'due_at' => $this->formatDateTime($todo->DUE ?? null),
            'completed' => $completed,
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
