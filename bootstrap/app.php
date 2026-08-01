<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureStaffAccountAccess;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ThrottleSensitiveAuthenticationActions;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
            ThrottleSensitiveAuthenticationActions::class,
        ]);
        $middleware->api(prepend: [SetLocale::class]);

        $middleware->alias([
            'active' => EnsureAccountIsActive::class,
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'staff' => EnsureStaffAccountAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
