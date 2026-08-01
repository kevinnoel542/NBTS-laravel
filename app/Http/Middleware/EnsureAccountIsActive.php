<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->resolveAccount($request);

        if (! $user || $user->is_active) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return new JsonResponse([
                'message' => trans('api.mobile_account_inactive'),
            ], 403);
        }

        Auth::guard((string) config('fortify.guard', 'web'))->logout();

        $request->session()->forget(['login.id', 'login.remember']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            Fortify::username() => trans('auth.inactive'),
        ]);
    }

    private function resolveAccount(Request $request): ?User
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser instanceof User) {
            return $authenticatedUser;
        }

        $challengedUserId = $request->session()->get('login.id');

        if (! is_int($challengedUserId) && ! is_string($challengedUserId)) {
            return null;
        }

        return User::find($challengedUserId);
    }
}
