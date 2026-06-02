<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NavigationActive
{
    /**
     * Determine whether the current (or browser) page matches route name pattern(s).
     *
     * During Livewire sub-requests (e.g. refresh-notifications), the HTTP route is
     * "livewire.update", not the page the user is viewing. In that case we match
     * against the Referer URL so nav highlights stay correct.
     */
    public static function routeIs(string ...$patterns): bool
    {
        $route = static::currentRoute();

        if ($route === null) {
            return false;
        }

        $name = $route->getName();

        if ($name === null) {
            return false;
        }

        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $name)) {
                return true;
            }
        }

        return false;
    }

    public static function routeParameter(string $key, mixed $default = null): mixed
    {
        return static::currentRoute()?->parameter($key, $default) ?? $default;
    }

    public static function currentRoute(): ?Route
    {
        $route = request()->route();

        if ($route !== null && ! static::isLivewireSubRequest($route)) {
            return $route;
        }

        return static::routeFromReferer();
    }

    protected static function isLivewireSubRequest(Route $route): bool
    {
        $name = $route->getName();

        if ($name !== null && (Str::is('livewire.*', $name) || Str::is('*.livewire.*', $name))) {
            return true;
        }

        return str_starts_with($route->uri(), 'livewire/');
    }

    protected static function routeFromReferer(): ?Route
    {
        $referer = request()->headers->get('referer');

        if (blank($referer)) {
            return null;
        }

        try {
            $refererRequest = Request::create($referer);

            return app('router')->getRoutes()->match($refererRequest);
        } catch (NotFoundHttpException|MethodNotAllowedHttpException) {
            return null;
        }
    }
}
