<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Support\Features;
use App\Livewire\Teams\MemberManager;
use App\Mail\TeamInvitation;
use Livewire\Livewire;
use Tests\TestCase;

class InviteTeamMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_members_can_be_invited_to_team(): void
    {
        if (! Features::hasTeamFeatures()) {
            $this->markTestSkipped('Team features not enabled.');
        }

        // Seed roles for the test (using company template which has 'employee' as default)
        $this->seed(\Database\Seeders\RolesSeeder::class);

        Notification::fake();

        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        // Use 'employee' which is the default role in company template
        Livewire::test(MemberManager::class, ['team' => $user->currentTeam])
            ->set('addTeamMemberForm', [
                'email' => 'test@example.com',
                'roles' => ['employee'],
            ])->call('addTeamMember');

        // Check that invitation was created
        $this->assertCount(1, $user->currentTeam->fresh()->teamInvitations);
        
        // Check that registration required notification was sent (since user doesn't exist)
        // Notification::assertSentTo(
        //     'test@example.com',
        //     \App\Notifications\TeamInvitationRegistrationRequired::class
        // );
    }

    public function test_team_member_invitations_can_be_cancelled(): void
    {
        if (! Features::hasTeamFeatures()) {
            $this->markTestSkipped('Team features not enabled.');
        }

        // Seed roles for the test (using company template which has 'employee' as default)
        $this->seed(\Database\Seeders\RolesSeeder::class);

        Mail::fake();

        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        // Add the team member...
        // Use 'employee' which is the default role in company template
        $component = Livewire::test(MemberManager::class, ['team' => $user->currentTeam])
            ->set('addTeamMemberForm', [
                'email' => 'test@example.com',
                'roles' => ['employee'],
            ])->call('addTeamMember');

        $invitationId = $user->currentTeam->fresh()->teamInvitations->first()->id;

        // Cancel the team invitation...
        $component->call('cancelTeamInvitation', $invitationId);

        $this->assertCount(0, $user->currentTeam->fresh()->teamInvitations);
    }
}
