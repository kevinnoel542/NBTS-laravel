<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $supportedLocales = config('app.supported_locales', ['en', 'sw']);

        abort_unless(is_array($supportedLocales) && in_array($locale, $supportedLocales, true), 404);

        $request->session()->put('locale', $locale);

        $user = $request->user();

        if ($user instanceof User && $user->locale !== $locale) {
            $user->forceFill(['locale' => $locale])->save();
        }

        return back();
    }
}
