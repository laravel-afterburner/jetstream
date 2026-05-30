<?php

namespace App\Support;

use Illuminate\Support\Collection;

class TeamNavigation
{
    protected static array $items = [];

    /**
     * Register a team dropdown navigation item.
     *
     * @param  array  $item  Navigation item configuration
     */
    public static function register(array $item): void
    {
        self::$items[] = array_merge([
            'order' => 100,
            'placement' => 'default',
            'permission' => null,
            'active' => null,
            'route_params' => [],
        ], $item);
    }

    /**
     * Get registered team navigation items for a menu placement.
     *
     * @param  string  $placement  e.g. after-members, after-system-settings, default
     */
    public static function items(string $placement = 'default'): Collection
    {
        return collect(self::$items)
            ->filter(fn ($item) => ($item['placement'] ?? 'default') === $placement)
            ->filter(function ($item) {
                if (isset($item['permission']) && is_callable($item['permission'])) {
                    return $item['permission'](auth()->user());
                }

                return true;
            })
            ->map(function ($item) {
                if (isset($item['route_params']) && is_callable($item['route_params'])) {
                    $item['route_params'] = $item['route_params']();
                }

                return $item;
            })
            ->sortBy('order')
            ->values();
    }

    /**
     * Clear all registered team navigation items (useful for testing).
     */
    public static function clear(): void
    {
        self::$items = [];
    }
}
