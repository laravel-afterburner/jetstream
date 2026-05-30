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
                if (isset($item['permission']) && is_callable($item['permission'])) {
                    return $item['permission'](auth()->user());
                }

                return true;
            })
            ->map(function ($item) {
                return self::resolveItem($item);
            })
            ->filter(function ($item) {
                if (! empty($item['children'])) {
                    return count($item['children']) > 0;
                }

                return isset($item['route']);
            })
            ->sortBy('order')
            ->values();
    }

    /**
     * Resolve route params, badges, and nested children for a navigation item.
     */
    protected static function resolveItem(array $item): array
    {
        if (isset($item['route_params']) && is_callable($item['route_params'])) {
            $item['route_params'] = $item['route_params']();
        }

        if (isset($item['badge']) && is_callable($item['badge'])) {
            $item['badge'] = $item['badge']();
        }

        if (isset($item['children']) && is_array($item['children'])) {
            $item['children'] = collect($item['children'])
                ->filter(function ($child) {
                    if (isset($child['permission']) && is_callable($child['permission'])) {
                        return $child['permission'](auth()->user());
                    }

                    return true;
                })
                ->map(fn (array $child) => self::resolveItem($child))
                ->values()
                ->all();

            if (! isset($item['badge']) || $item['badge'] === null) {
                $childBadgeTotal = collect($item['children'])->sum(fn ($child) => (int) ($child['badge'] ?? 0));

                if ($childBadgeTotal > 0) {
                    $item['badge'] = $childBadgeTotal;
                }
            }
        }

        return $item;
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

