<?php

namespace Tests\Feature;

use App\Actions\Afterburner\AcceptTeamInvitation;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepting_invitation_assigns_default_and_selected_roles(): void
    {
        // Seed roles with strata template to get council_member role
        $seeder = new \Database\Seeders\RolesSeeder();
        $seeder->run('strata');

        // Create an owner with a team
        $owner = User::factory()->withPersonalTeam()->create();
        $team = $owner->currentTeam;

        // Create an invitation with additional roles
        $additionalRole = Role::where('slug', 'council_member')->first();
        $this->assertNotNull($additionalRole);

        $invitation = $team->teamInvitations()->create([
            'email' => 'invitee@example.com',
            'roles' => [$additionalRole->slug],
        ]);

        // Existing user accepts the invitation
        $invitee = User::factory()->create(['email' => 'invitee@example.com']);

        $action = app(AcceptTeamInvitation::class);
        $action->add($invitee, $team, $invitee->email, null);

        // Assert user is now attached to team
        $this->assertTrue($team->fresh()->users->contains($invitee));

        // Default role assigned
        $defaultRole = Role::where('is_default', true)->first();
        $this->assertNotNull($defaultRole);

        // Refresh the user to ensure we have the latest data
        $invitee->refresh();

        // Use hasRole method from HasRoles trait which properly checks team_id
        $this->assertTrue(
            $invitee->hasRole($defaultRole->slug, $team->id),
            'Invitee should have default role after accepting invite.'
        );

        // Additional selected role assigned
        $this->assertTrue(
            $invitee->hasRole($additionalRole->slug, $team->id),
            'Invitee should have additional role from invitation.'
        );

        // Invitation removed
        $this->assertDatabaseMissing($invitation->getTable(), ['id' => $invitation->id]);
    }
}


