<?php

namespace Tests\Unit;

use App\Models\Team;
use App\Support\TeamTimestamp;
use Carbon\Carbon;
use Tests\TestCase;

class TeamTimestampTest extends TestCase
{
    public function test_english_ordinal_suffixes(): void
    {
        $this->assertSame('st', TeamTimestamp::englishOrdinalSuffix(1));
        $this->assertSame('nd', TeamTimestamp::englishOrdinalSuffix(2));
        $this->assertSame('rd', TeamTimestamp::englishOrdinalSuffix(3));
        $this->assertSame('th', TeamTimestamp::englishOrdinalSuffix(11));
        $this->assertSame('nd', TeamTimestamp::englishOrdinalSuffix(22));
    }

    public function test_format_carbon_in_team_zone_includes_superscript(): void
    {
        $dt = Carbon::parse('2026-03-15 14:30:00', 'America/Vancouver');

        $formatted = TeamTimestamp::formatCarbonInTeamZone($dt);

        $this->assertStringContainsString('<sup>th</sup>', (string) $formatted);
        $this->assertStringContainsString('March 15', (string) $formatted);
        $this->assertStringContainsString('2:30 PM', (string) $formatted);
    }

    public function test_format_for_team_converts_from_utc(): void
    {
        $team = new Team(['timezone' => 'America/Vancouver']);
        $instant = Carbon::parse('2026-01-01 08:00:00', 'UTC');

        $formatted = TeamTimestamp::formatForTeam($team, $instant, includeTime: false);

        $this->assertNotNull($formatted);
        $this->assertStringContainsString('January 1', (string) $formatted);
    }

    public function test_format_date_superscript_helper(): void
    {
        $date = Carbon::parse('2026-05-22');

        $formatted = format_date_superscript($date);

        $this->assertStringContainsString('<sup>nd</sup>', $formatted);
        $this->assertStringContainsString('May 22', $formatted);
    }
}
