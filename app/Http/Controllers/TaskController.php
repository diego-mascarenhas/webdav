<?php

namespace App\Http\Controllers;

use App\Dav\Services\DavPrincipalService;
use App\Http\Controllers\Concerns\ResolvesDavUser;
use App\Services\ICalTodoReader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    use ResolvesDavUser;

    public function index(Request $request, ICalTodoReader $reader): View|JsonResponse
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

        $tasks = $reader->forUser($user);

        if ($request->is('api/*')) {
            return response()->json([
                'data' => $tasks->values(),
                'meta' => [
                    'count' => $tasks->count(),
                    'principal' => app(DavPrincipalService::class)->principalUri($user),
                ],
            ]);
        }

        return view('tasks.index', [
            'tasks' => $tasks,
            'user' => $user,
        ]);
    }
}
