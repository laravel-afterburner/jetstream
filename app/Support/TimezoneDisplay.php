<?php

namespace App\Support;

final class TimezoneDisplay
{
    public static function label(?string $timezone): string
    {
        if (! $timezone) {
            return '';
        }

        $parts = explode('/', $timezone);

        return isset($parts[1])
            ? str_replace('_', ' ', implode('/', array_slice($parts, 1)))
            : str_replace('_', ' ', $timezone);
    }
}
