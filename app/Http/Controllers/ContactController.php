<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesDavUser;
use App\Services\VCardContactReader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    use ResolvesDavUser;

    public function index(Request $request, VCardContactReader $reader): View|JsonResponse
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

        $contacts = $reader->forUser($user);

        if ($request->is('api/*')) {
            return response()->json([
                'data' => $contacts->values(),
                'meta' => [
                    'count' => $contacts->count(),
                    'principal' => app(\App\Dav\Services\DavPrincipalService::class)->principalUri($user),
                ],
            ]);
        }

        return view('contacts.index', [
            'contacts' => $contacts,
            'user' => $user,
        ]);
    }
}
