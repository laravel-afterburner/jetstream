<?php

namespace App\Support;

use Illuminate\Support\Collection;

class SystemAdminNavigation
{
    protected static array $items = [];

    /**
     * Register a system admin profile menu item.
     *
     * @param  array<string, mixed>  $item
     */
    public static function register(array $item): void
    {
        self::$items[] = array_merge([
            'order' => 100,
            'active' => null,
        ], $item);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function items(): Collection
    {
        return collect(self::$items)
            ->sortBy('order')
            ->values();
    }

    public static function clear(): void
    {
        self::$items = [];
    }
}
