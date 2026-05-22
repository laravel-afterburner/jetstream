<?php

namespace App\Support;

use Illuminate\Support\Collection;

class Navigation
{
    protected static array $items = [];

    /**
     * Register a navigation menu item.
     *
     * @param  array  $item  Navigation item configuration
     * @return void
     */
    public static function register(array $item): void
    {
        self::$items[] = array_merge([
            'order' => 100,
            'permission' => null,
            'active' => null,
            'icon' => null,
            'badge' => null,
            'route_params' => [],
        ], $item);
    }

    /**
     * Get all registered navigation items, filtered by permissions and sorted.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function items(): Collection
    {
        return collect(self::$items)
            ->filter(function ($item) {
                // Check permission if provided
                if (isset($item['permission']) && is_callable($item['permission'])) {
                    return $item['permission'](auth()->user());
                }
                return true;
            })
            ->map(function ($item) {
                // Resolve route params if callable
                if (isset($item['route_params']) && is_callable($item['route_params'])) {
                    $item['route_params'] = $item['route_params']();
                }
                
                // Resolve badge if callable
                if (isset($item['badge']) && is_callable($item['badge'])) {
                    $item['badge'] = $item['badge']();
                }
                
                return $item;
            })
            ->sortBy('order')
            ->values();
    }

    /**
     * Clear all registered navigation items (useful for testing).
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$items = [];
    }
}

