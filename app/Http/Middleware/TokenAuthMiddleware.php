<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Access\AuthorizationException;

use Closure;

class TokenAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $header = $request->header('Authorization');

        if (empty($header)) {
            throw new AuthorizationException("Missing auth header.");
        }

        $tokenParts = explode(' ', $header);

        if (count($tokenParts) < 2 || empty($tokenParts[1])) {
            throw new AuthorizationException("Invalid access token.");
        }

        $accessToken = env('ACCESS_TOKEN');

        if ($tokenParts[1] != $accessToken) {
            throw new AuthorizationException("Invalid access token.");
        }

        return $next($request);
    }
}
