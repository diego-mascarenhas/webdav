<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesDavUser;
use App\Http\Controllers\Controller;
use App\Services\ICalEventWriter;
use App\Services\ICalTodoWriter;
use App\Services\VCardWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncWriteController extends Controller
{
    use ResolvesDavUser;

    public function upsertContact(Request $request, VCardWriter $writer, ?string $uid = null): JsonResponse
    {
        $user = $this->resolveDavUser($request);

        if ($user === null) {
            return response()->json(['message' => 'Query parameter "email" is required.'], 422);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'uid' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $writer->upsert($user, $validated, $uid ?? $validated['uid'] ?? null);

        return response()->json(['data' => $result], $uid === null ? 201 : 200);
    }

    public function deleteContact(Request $request, string $uid, VCardWriter $writer): JsonResponse
    {
        $user = $this->resolveDavUser($request);

        if ($user === null) {
            return response()->json(['message' => 'Query parameter "email" is required.'], 422);
        }

        if (! $writer->delete($user, $uid)) {
            return response()->json(['message' => 'Contact not found.'], 404);
        }

        return response()->json(['meta' => ['deleted' => true]]);
    }

    public function upsertEvent(Request $request, ICalEventWriter $writer, ?string $uid = null): JsonResponse
    {
        $user = $this->resolveDavUser($request);

        if ($user === null) {
            return response()->json(['message' => 'Query parameter "email" is required.'], 422);
        }

        $validated = $request->validate([
            'summary' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date'],
            'all_day' => ['nullable', 'boolean'],
            'uid' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $writer->upsert($user, $validated, $uid ?? $validated['uid'] ?? null);

        return response()->json(['data' => $result], $uid === null ? 201 : 200);
    }

    public function deleteEvent(Request $request, string $uid, ICalEventWriter $writer): JsonResponse
    {
        $user = $this->resolveDavUser($request);

        if ($user === null) {
            return response()->json(['message' => 'Query parameter "email" is required.'], 422);
        }

        if (! $writer->delete($user, $uid)) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        return response()->json(['meta' => ['deleted' => true]]);
    }

    public function upsertTask(Request $request, ICalTodoWriter $writer, ?string $uid = null): JsonResponse
    {
        $user = $this->resolveDavUser($request);

        if ($user === null) {
            return response()->json(['message' => 'Query parameter "email" is required.'], 422);
        }

        $validated = $request->validate([
            'summary' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'completed' => ['nullable', 'boolean'],
            'uid' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $writer->upsert($user, $validated, $uid ?? $validated['uid'] ?? null);

        return response()->json(['data' => $result], $uid === null ? 201 : 200);
    }

    public function deleteTask(Request $request, string $uid, ICalTodoWriter $writer): JsonResponse
    {
        $user = $this->resolveDavUser($request);

        if ($user === null) {
            return response()->json(['message' => 'Query parameter "email" is required.'], 422);
        }

        if (! $writer->delete($user, $uid)) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        return response()->json(['meta' => ['deleted' => true]]);
    }
}
