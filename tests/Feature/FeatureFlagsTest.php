<?php

namespace Tests\Feature;

use App\Support\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureFlagsTest extends TestCase
{
    use RefreshDatabase;

    public function test_feature_flags_can_be_checked(): void
    {
        // Test that feature flag methods exist and return boolean values
        $this->assertIsBool(Features::hasTeamFeatures());
        $this->assertIsBool(Features::hasPersonalTeams());
        $this->assertIsBool(Features::hasApiFeatures());
        $this->assertIsBool(Features::managesProfilePhotos());
    }

    public function test_feature_flags_use_config_defaults(): void
    {
        // Test that feature flags fall back to config values
        // These should match the defaults in config/afterburner.php
        $this->assertTrue(Features::hasTeamFeatures());
        $this->assertTrue(Features::hasPersonalTeams()); // Enabled in config/afterburner.php
    }

    public function test_enabled_feature_check_exists(): void
    {
        // Test the generic enabled() method
        $this->assertIsBool(Features::enabled('team_invitations'));
        $this->assertIsBool(Features::enabled('impersonation'));
    }
}

