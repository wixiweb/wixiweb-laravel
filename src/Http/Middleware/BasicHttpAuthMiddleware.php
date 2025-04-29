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

        $username = config('wixiweb.basic_auth.username');
        $password = config('wixiweb.basic_auth.password');

        if ($request->getUser() !== $username || $request->getPassword() !== $password) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic']);
        }

        return $next($request);
    }
}
