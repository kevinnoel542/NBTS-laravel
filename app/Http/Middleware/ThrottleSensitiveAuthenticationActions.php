<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class ThrottleSensitiveAuthenticationActions
{
    /** @var list<string> */
    private const ROUTE_NAMES = [
        'password.email',
        'password.update',
        'password.confirm.store',
        'two-factor.enable',
        'two-factor.confirm',
        'two-factor.disable',
        'two-factor.regenerate-recovery-codes',
        'passkey.destroy',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->routeIs(...self::ROUTE_NAMES)) {
            return $next($request);
        }

        return app(ThrottleRequests::class)->handle(
            $request,
            $next,
            'sensitive-auth',
        );
    }
}
