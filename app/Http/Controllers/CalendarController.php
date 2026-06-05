<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesDavUser;
use App\Services\ICalEventReader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    use ResolvesDavUser;

    public function index(Request $request, ICalEventReader $reader): View|JsonResponse
    {
        $user = $this->resolveDavUser($request);

        if ($user === null) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Query parameter "email" is required.',
                ], 422);
            }

            abort(401);
        }

        $events = $reader->forUser($user);

        if ($request->is('api/*')) {
            return response()->json([
                'data' => $events->values(),
                'meta' => [
                    'count' => $events->count(),
                    'principal' => app(\App\Dav\Services\DavPrincipalService::class)->principalUri($user),
                ],
            ]);
        }

        return view('calendar.index', [
            'events' => $events,
            'user' => $user,
        ]);
    }
}
