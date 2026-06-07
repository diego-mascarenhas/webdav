<?php

namespace App\Http\Controllers\Api;

use App\Dav\Services\DavUserProvisioner;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateDavUserRequest;
use App\Http\Requests\Api\LinkDavUserRequest;
use App\Http\Requests\Api\UpdateDavUserPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request, DavUserProvisioner $provisioner): JsonResponse
    {
        $email = $request->query('email');

        if (! is_string($email) || $email === '') {
            return response()->json([
                'message' => 'Query parameter "email" is required.',
            ], 422);
        }

        $user = $provisioner->findByEmail($email);

        if ($user === null) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json([
            'data' => $provisioner->toAccountPayload($user),
        ]);
    }

    public function store(CreateDavUserRequest $request, DavUserProvisioner $provisioner): JsonResponse
    {
        $validated = $request->validated();

        $result = $provisioner->create(
            email: $validated['email'],
            name: $validated['name'],
            davUsername: $validated['dav_username'] ?? null,
            password: $validated['password'] ?? null,
        );

        return response()->json([
            'data' => $provisioner->toAccountPayload($result['user'], $result['password']),
            'meta' => [
                'created' => $result['created'],
            ],
        ], $result['created'] ? 201 : 200);
    }

    public function link(LinkDavUserRequest $request, DavUserProvisioner $provisioner): JsonResponse
    {
        $validated = $request->validated();

        $user = $provisioner->link($validated['email'], $validated['password']);

        if ($user === null) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        return response()->json([
            'data' => $provisioner->toAccountPayload($user),
            'meta' => [
                'linked' => true,
            ],
        ]);
    }

    public function updatePassword(UpdateDavUserPasswordRequest $request, DavUserProvisioner $provisioner): JsonResponse
    {
        $validated = $request->validated();

        $user = $provisioner->findByEmail($validated['email']);

        if ($user === null) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $user = $provisioner->updatePassword($user, $validated['password']);

        return response()->json([
            'data' => $provisioner->toAccountPayload($user),
            'meta' => [
                'password_updated' => true,
            ],
        ]);
    }
}
