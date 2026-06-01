<?php

namespace Tests\Feature;

use App\Actions\Afterburner\InviteTeamMember;
use App\Models\User;
use App\Support\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features as FortifyFeatures;
use Tests\Support\SeedsCompanyRoles;
use Tests\TestCase;

class TeamCreationConfigTest extends TestCase
{
    use RefreshDatabase;
    use SeedsCompanyRoles;

    protected function setUp(): void
    {
        parent::setUp();

        config(['afterburner.allow_team_creation' => false]);
    }

    public function test_public_registration_screen_is_not_available(): void
    {
        if (! FortifyFeatures::enabled(FortifyFeatures::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $this->get('/register')->assertNotFound();
    }

    public function test_authenticated_user_cannot_access_create_team_screen(): void
    {
        if (! Features::hasTeamFeatures()) {
            $this->markTestSkipped('Team features are not enabled.');
        }

        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($user)
            ->get(route('teams.create'))
            ->assertForbidden();
    }

    public function test_user_without_team_can_access_profile_without_create_team_prompt(): void
    {
        if (! Features::hasTeamFeatures()) {
            $this->markTestSkipped('Team features are not enabled.');
        }

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('profile.show'))
            ->assertSessionMissing('flash');
    }

    public function test_inviting_existing_user_adds_them_to_team_immediately(): void
    {
        if (! Features::hasTeamFeatures()) {
            $this->markTestSkipped('Team features are not enabled.');
        }

        $this->seedCompanyRoles();

        $owner = User::factory()->withPersonalTeam()->create();
        $invitee = User::factory()->create();
        $team = $owner->currentTeam;

        app(InviteTeamMember::class)->invite($owner, $team, $invitee->email, ['employee']);

        $this->assertTrue($team->fresh()->hasUserWithEmail($invitee->email));
        $this->assertCount(0, $team->fresh()->teamInvitations);
        $this->assertEquals($team->id, $invitee->fresh()->current_team_id);
    }
}
