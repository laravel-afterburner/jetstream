<?php

namespace Tests\Unit;

use App\Support\EntityLabel;
use Tests\TestCase;

class EntityLabelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'afterburner.entity_label' => 'strata',
            'afterburner.entity_url_slug' => 'strata',
        ]);
    }

    public function test_strata_uses_url_slug_for_plural_display(): void
    {
        $this->assertSame('strata', EntityLabel::singular());
        $this->assertSame('Strata', EntityLabel::singularTitle());
        $this->assertSame('Strata', EntityLabel::pluralTitle());
        $this->assertSame('strata', EntityLabel::plural());
    }

    public function test_team_uses_url_slug_for_plural_display(): void
    {
        config([
            'afterburner.entity_label' => 'team',
            'afterburner.entity_url_slug' => 'teams',
        ]);

        $this->assertSame('Teams', EntityLabel::pluralTitle());
        $this->assertSame('teams', EntityLabel::plural());
    }

    public function test_url_slug_falls_back_to_pluralized_entity_label(): void
    {
        config([
            'afterburner.entity_label' => 'company',
            'afterburner.entity_url_slug' => null,
        ]);

        $this->assertSame('companies', EntityLabel::urlSlug());
        $this->assertSame('companies', entity_url_slug());
        $this->assertSame('/companies/5/meetings', entity_path('5/meetings'));
    }
}
