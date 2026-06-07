<?php

namespace App\Services;

use App\Dav\Support\ResolvesDavResources;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Sabre\VObject\Component\VCalendar;

class ICalTodoWriter
{
    use ResolvesDavResources;

    private const OPERATION_ADD = 1;

    private const OPERATION_MODIFY = 2;

    private const OPERATION_DELETE = 3;

    /**
     * @param  array{uid?: string, summary: string, description?: ?string, due_at?: ?string, completed?: bool}  $payload
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
        $dueAt = isset($payload['due_at']) ? Carbon::parse($payload['due_at']) : null;

        $existing = DB::table('calendarobjects')
            ->where('calendarid', $calendarId)
            ->where('uri', $uri)
            ->first();

        $row = [
            'calendardata' => $calendarData,
            'lastmodified' => $now,
            'etag' => $etag,
            'size' => $size,
            'componenttype' => 'VTODO',
            'firstoccurence' => $dueAt?->timestamp ?? $now,
            'lastoccurence' => $dueAt?->timestamp ?? $now,
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
            ->where('componenttype', 'VTODO')
            ->delete();

        if ($deleted === 0) {
            return false;
        }

        $syncToken = $this->bumpCalendarSyncToken($calendarId);
        $this->recordCalendarChange($calendarId, $uri, self::OPERATION_DELETE, $syncToken);

        return true;
    }

    /**
     * @param  array{summary: string, description?: ?string, due_at?: ?string, completed?: bool}  $payload
     */
    private function buildCalendar(string $uid, array $payload): string
    {
        $completed = (bool) ($payload['completed'] ?? false);

        $calendar = new VCalendar();
        $todo = $calendar->add('VTODO', [
            'UID' => $uid,
            'SUMMARY' => $payload['summary'],
            'DTSTAMP' => gmdate('Ymd\\THis\\Z'),
            'STATUS' => $completed ? 'COMPLETED' : 'NEEDS-ACTION',
        ]);

        if (! empty($payload['description'])) {
            $todo->DESCRIPTION = $payload['description'];
        }

        if (! empty($payload['due_at'])) {
            $dueAt = Carbon::parse($payload['due_at']);
            $todo->DUE = $dueAt->utc()->format('Ymd\\THis\\Z');
        }

        if ($completed) {
            $todo->COMPLETED = gmdate('Ymd\\THis\\Z');
        }

        return $calendar->serialize();
    }
}
