<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesDavUser;
use App\Http\Controllers\Controller;
use App\Services\ICalTodoReader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ResolvesDavUser;

    public function index(Request $request, ICalTodoReader $reader): JsonResponse
    {
        $user = $this->resolveDavUser($request);

        if ($user === null) {
            return response()->json([
                'message' => 'Query parameter "email" is required.',
            ], 422);
        }

        $tasks = $reader->forUser($user);

        return response()->json([
            'data' => $tasks->values(),
            'meta' => [
                'count' => $tasks->count(),
                'principal' => app(\App\Dav\Services\DavPrincipalService::class)->principalUri($user),
            ],
        ]);
    }
}
