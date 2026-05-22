<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemAdminGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_admin_can_update_team_they_do_not_belong_to(): void
    {
        if (! Features::hasTeamFeatures()) {
            $this->markTestSkipped('Team features not enabled.');
        }

        $owner = User::factory()->withPersonalTeam()->create();
        $team = $owner->currentTeam;

        $admin = User::factory()->create(['is_system_admin' => true]);

        $this->assertTrue($admin->can('update', $team));
    }

    public function test_non_admin_cannot_update_team_they_do_not_belong_to(): void
    {
        if (! Features::hasTeamFeatures()) {
            $this->markTestSkipped('Team features not enabled.');
        }

        $owner = User::factory()->withPersonalTeam()->create();
        $team = $owner->currentTeam;

        $otherUser = User::factory()->create();

        $this->assertFalse($otherUser->can('update', $team));
    }
}
