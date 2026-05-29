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
            'permission' => null,
            'active' => null,
            'route_params' => [],
        ], $item);
    }

    /**
     * Get all registered team navigation items, filtered by permissions and sorted.
     */
    public static function items(): Collection
    {
        return collect(self::$items)
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
