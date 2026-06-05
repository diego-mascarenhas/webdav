<?php

namespace App\Http\Controllers\Concerns;

use App\Auth\DavUserResolver;
use App\Models\User;
use Illuminate\Http\Request;

trait ResolvesDavUser
{
    private function resolveDavUser(Request $request): ?User
    {
        if ($request->user()) {
            return $request->user();
        }

        if ($request->is('api/*')) {
            $login = $request->query('email');

            if (! is_string($login) || $login === '') {
                return null;
            }

            return DavUserResolver::findByLogin($login);
        }

        return null;
    }
}
