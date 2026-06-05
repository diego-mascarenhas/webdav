<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDavApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('dav.api_token');

        if (! is_string($token) || $token === '') {
            abort(503, 'API token not configured (DAV_API_TOKEN).');
        }

        $provided = $request->bearerToken() ?? $request->header('X-Dav-Api-Token');

        if (! hash_equals($token, (string) $provided)) {
            abort(401, 'Invalid API token.');
        }

        return $next($request);
    }
}
