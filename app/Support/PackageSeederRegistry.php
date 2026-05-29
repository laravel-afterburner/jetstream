<?php

namespace App\Support;

class PackageSeederRegistry
{
    /** @var array<int, class-string<\Illuminate\Database\Seeder>> */
    protected static array $seeders = [];

    /**
     * @param  class-string<\Illuminate\Database\Seeder>  $seederClass
     */
    public static function register(string $seederClass): void
    {
        if (! in_array($seederClass, static::$seeders, true)) {
            static::$seeders[] = $seederClass;
        }
    }

    /**
     * @return array<int, class-string<\Illuminate\Database\Seeder>>
     */
    public static function all(): array
    {
        return static::$seeders;
    }
}
