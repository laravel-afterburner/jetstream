<?php

namespace App\Support;

use App\Models\Team;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\HtmlString;

class TeamTimestamp
{
    /**
     * Format an instant in the team's timezone with ordinal superscript.
     */
    public static function formatForTeam(Team $team, DateTimeInterface|string|null $instant, bool $includeTime = true): ?HtmlString
    {
        if ($instant === null) {
            return null;
        }

        $dt = $team->toTeamTimezone($instant);

        return self::formatCarbonInTeamZone($dt, $includeTime);
    }

    /**
     * @param  Carbon  $dt  Wall time in the zone to display (already correct for display).
     */
    public static function formatCarbonInTeamZone(Carbon $dt, bool $includeTime = true): HtmlString
    {
        $day = (int) $dt->format('j');
        $suffix = self::englishOrdinalSuffix($day);
        $datePart = $dt->format('F').' '.$day.'<sup>'.$suffix.'</sup>'.$dt->format(' Y');

        if (! $includeTime) {
            return new HtmlString($datePart);
        }

        $timePart = $dt->format('g:i A').' ('.$dt->format('T').')';

        return new HtmlString($datePart.', '.$timePart);
    }

    public static function englishOrdinalSuffix(int $day): string
    {
        return match (true) {
            in_array($day % 100, [11, 12, 13], true) => 'th',
            $day % 10 === 1 => 'st',
            $day % 10 === 2 => 'nd',
            $day % 10 === 3 => 'rd',
            default => 'th',
        };
    }

    /**
     * 12-hour clock with timezone abbreviation only.
     */
    public static function formatTimeInTeamZone(Carbon $dt): HtmlString
    {
        return new HtmlString($dt->format('g:i A').' ('.$dt->format('T').')');
    }
}
