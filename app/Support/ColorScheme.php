<?php

namespace App\Support;

use App\Models\User;

class ColorScheme
{
    public const Light = 'light';

    public const Dark = 'dark';

    public const System = 'system';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Light => __('Light'),
            self::Dark => __('Dark'),
            self::System => __('System'),
        ];
    }

    public static function resolve(?string $scheme): string
    {
        return in_array($scheme, [self::Light, self::Dark, self::System], true)
            ? $scheme
            : self::System;
    }

    public static function forUser(?User $user): string
    {
        if (! $user) {
            return self::System;
        }

        return self::resolve($user->color_scheme);
    }
}
