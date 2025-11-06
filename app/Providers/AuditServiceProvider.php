<?php

namespace App\Providers;

use App\Events\AuditEvent;
use App\Listeners\AuditEventListener;
use App\Observers\AuditModelObserver;
use App\Services\AuditService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuditService::class, function ($app) {
            return new AuditService();
        });
    }

    public function boot(): void
    {
        // Register audit event listener for custom AuditEvent classes and subclasses
        // We use a wildcard listener to catch all events, then filter for AuditEvent instances
        Event::listen('*', function (string $eventName, array $data) {
            if (isset($data[0]) && $data[0] instanceof AuditEvent) {
                app(AuditEventListener::class)->handle($data[0]);
            }
        });

        // Register model observers for models implementing AuditableInterface
        $this->registerModelObservers();

        // Register package audit listeners from config
        $this->registerPackageListeners();
    }

    protected function registerModelObservers(): void
    {
        // Skip if observer doesn't exist yet (will be created in Phase 2)
        if (!class_exists(AuditModelObserver::class)) {
            return;
        }

        $models = config('audit.models', []);
        $observer = new AuditModelObserver(app(AuditService::class));

        foreach ($models as $model) {
            if (class_exists($model)) {
                $model::observe($observer);
            }
        }
    }

    protected function registerPackageListeners(): void
    {
        $packages = config('audit.packages', []);

        foreach ($packages as $packageName => $listenerClass) {
            if (class_exists($listenerClass)) {
                // Register event listeners for package-specific events
                // This allows packages to dispatch their own events that get audited
                Event::listen(
                    "{$packageName}.*",
                    $listenerClass
                );
            }
        }
    }
}

