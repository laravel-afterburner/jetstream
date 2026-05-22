<?php

namespace App\Providers;

use App\Listeners\AuditEmailFailedListener;
use App\Listeners\UpdateWebAuthnCredentialLastUsed;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laragear\WebAuthn\Events\CredentialAsserted;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if ($user?->isSystemAdmin()) {
                return true;
            }
        });

        Event::listen(
            CredentialAsserted::class,
            UpdateWebAuthnCredentialLastUsed::class
        );

        Event::listen(
            JobFailed::class,
            AuditEmailFailedListener::class
        );
    }
}
