<?php

namespace App\Providers;

use App\Contracts\PushTransport;
use App\Contracts\SmsTransport;
use App\Firebase\FirebaseTokenVerifier;
use App\Firebase\KreaitFirebaseTokenVerifier;
use App\Models\User;
use App\Services\Notifications\FcmPushTransport;
use App\Services\Notifications\LogPushTransport;
use App\Services\Notifications\LogSmsTransport;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FirebaseTokenVerifier::class, KreaitFirebaseTokenVerifier::class);
        $this->app->bind(
            PushTransport::class,
            fn ($app): PushTransport => config('services.notifications.push_transport') === 'fcm'
                ? $app->make(FcmPushTransport::class)
                : $app->make(LogPushTransport::class),
        );
        $this->app->bind(SmsTransport::class, LogSmsTransport::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Gate::before(function (User $user): ?bool {
            if (! $user->is_active) {
                return false;
            }

            return null;
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
