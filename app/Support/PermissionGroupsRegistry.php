<?php

namespace App\Support;

class PermissionGroupsRegistry
{
    /** @var array<string, array<int, string>> */
    protected static array $groups = [];

    /**
     * @param  array<int, string>  $slugs
     */
    public static function register(string $label, array $slugs): void
    {
        static::$groups[$label] = array_values(array_unique(array_merge(
            static::$groups[$label] ?? [],
            $slugs
        )));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function all(): array
    {
        return static::$groups;
    }
}
