<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\Team;
use App\Policies\RolePolicy;
use App\Policies\TeamPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AfterburnerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/afterburner.php',
            'afterburner'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set the app name from Afterburner config
        config(['app.name' => config('afterburner.app_name')]);

        // Register TeamPolicy with Laravel's Gate
        Gate::policy(Team::class, TeamPolicy::class);

        // Register RolePolicy for team-based role management
        // These gates pass Team as the first argument, and optionally Role as the second
        Gate::define('createRole', function ($user, Team $team) {
            return app(RolePolicy::class)->create($user, $team);
        });
        
        Gate::define('updateRole', function ($user, Team $team, Role $role = null) {
            return app(RolePolicy::class)->update($user, $team, $role);
        });
        
        Gate::define('deleteRole', function ($user, Team $team, Role $role) {
            return app(RolePolicy::class)->delete($user, $team, $role);
        });
        
        Gate::define('viewRole', function ($user, Team $team, Role $role = null) {
            return app(RolePolicy::class)->view($user, $team, $role);
        });
        
        Gate::define('updateRoleHierarchy', function ($user, Team $team) {
            return app(RolePolicy::class)->updateHierarchy($user, $team);
        });

        // Register RedirectResponse macros for flash banner messages
        RedirectResponse::macro('banner', function ($message): RedirectResponse {
            /** @var \Illuminate\Http\RedirectResponse $this */
            return $this->with('flash', [
                'bannerStyle' => 'success',
                'banner' => $message,
            ]);
        });

        RedirectResponse::macro('warningBanner', function ($message): RedirectResponse {
            /** @var \Illuminate\Http\RedirectResponse $this */
            return $this->with('flash', [
                'bannerStyle' => 'warning',
                'banner' => $message,
            ]);
        });

        RedirectResponse::macro('dangerBanner', function ($message): RedirectResponse {
            /** @var \Illuminate\Http\RedirectResponse $this */
            return $this->with('flash', [
                'bannerStyle' => 'danger',
                'banner' => $message,
            ]);
        });
    }
}
