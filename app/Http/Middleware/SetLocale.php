<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $locale = match (true) {
            $user instanceof User => $user->preferredLocale(),
            $request->is('api/*') => $request->header('X-Locale', $request->getPreferredLanguage(['en', 'sw'])),
            default => $request->session()->get('locale', config('app.locale')),
        };

        $supportedLocales = config('app.supported_locales', ['en', 'sw']);

        if (! is_string($locale) || ! is_array($supportedLocales) || ! in_array($locale, $supportedLocales, true)) {
            $locale = (string) config('app.fallback_locale', 'en');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
