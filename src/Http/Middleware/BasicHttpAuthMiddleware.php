<?php

namespace Wixiweb\WixiwebLaravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BasicHttpAuthMiddleware
{
    public function handle(Request $request, Closure $next,)
    {
        if (app()->isLocal()) {
            return $next($request);
        }

        if (! self::hasValidCredentials($request)) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic']);
        }

        return $next($request);
    }

    /**
     * Credentials are always rejected when the expected username or password is not configured.
     */
    public static function hasValidCredentials(Request $request): bool
    {
        $username = (string) config('wixiweb.basic_auth.username');
        $password = (string) config('wixiweb.basic_auth.password');

        if ($username === '' || $password === '') {
            return false;
        }

        return hash_equals($username, (string) $request->getUser())
            && hash_equals($password, (string) $request->getPassword());
    }
}
