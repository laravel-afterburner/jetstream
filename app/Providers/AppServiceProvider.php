<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laragear\WebAuthn\Events\CredentialAsserted;
use App\Listeners\UpdateWebAuthnCredentialLastUsed;

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
        Event::listen(
            CredentialAsserted::class,
            UpdateWebAuthnCredentialLastUsed::class
        );
    }
}
