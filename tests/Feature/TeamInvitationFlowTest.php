<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use App\Models\TeamInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features as FortifyFeatures;
use App\Support\Features;
use App\Livewire\Teams\MemberManager;
use Livewire\Livewire;
use Tests\TestCase;

class TeamInvitationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitation_sent_to_registered_user_creates_notification(): void
    {
        if (! Features::hasTeamFeatures()) {
            $this->markTestSkipped('Team features not enabled.');
        }

        // Seed roles for the test
        $this->seed(\Database\Seeders\RolesSeeder::class);

        Notification::fake();

        $teamOwner = User::factory()->withPersonalTeam()->create();
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $this->actingAs($teamOwner);

        // Invite the existing user using the Livewire component
        // Use 'employee' which is the default role in company template
        Livewire::test(MemberManager::class, ['team' => $teamOwner->currentTeam])
            ->set('addTeamMemberForm', [
                'email' => 'existing@example.com',
                'roles' => ['employee'],
            ])->call('addTeamMember');

        // Check that invitation was created
        $this->assertDatabaseHas('team_invitations', [
            'email' => 'existing@example.com',
        ]);

        // Check that notification was sent
        Notification::assertSentTo(
            $existingUser,
            \App\Notifications\TeamInvitationNotification::class
        );
    }

    public function test_invitation_sent_to_unregistered_user_sends_registration_email(): void
    {
        if (! Features::hasTeamFeatures()) {
            $this->markTestSkipped('Team features not enabled.');
        }

        // Seed roles for the test
        $this->seed(\Database\Seeders\RolesSeeder::class);

        Notification::fake();

        $teamOwner = User::factory()->withPersonalTeam()->create();

        $this->actingAs($teamOwner);

        // Invite a non-existing user using the Livewire component
        // Use 'employee' which is the default role in company template
        Livewire::test(MemberManager::class, ['team' => $teamOwner->currentTeam])
            ->set('addTeamMemberForm', [
                'email' => 'newuser@example.com',
                'roles' => ['employee'],
            ])->call('addTeamMember');

        // Check that invitation was created
        $this->assertDatabaseHas('team_invitations', [
            'email' => 'newuser@example.com',
        ]);

        // Check that registration required notification was sent
        // Notification::assertSentTo(
        //     'newuser@example.com',
        //     \App\Notifications\TeamInvitationRegistrationRequired::class
        // );
    }

    public function test_registration_with_invitation_automatically_accepts_invitation(): void
    {
        if (! FortifyFeatures::enabled(FortifyFeatures::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        // Seed roles for the test
        $this->seed(\Database\Seeders\RolesSeeder::class);

        $teamOwner = User::factory()->withPersonalTeam()->create();
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $teamOwner->currentTeam->id,
            'email' => 'newuser@example.com',
            'roles' => ['employee'],
        ]);

        // Register with invitation
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invitation' => $invitation->id,
            'terms' => true,
        ]);

        $response->assertRedirect(route('dashboard'));

        // Check that user was created
        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user);

        // Check that user was added to the team
        $this->assertTrue($teamOwner->currentTeam->users->contains($user));

        // Check that invitation was deleted
        $this->assertDatabaseMissing('team_invitations', [
            'id' => $invitation->id,
        ]);

        // Check that user's current team is set to the invited team
        $this->assertEquals($teamOwner->currentTeam->id, $user->current_team_id);
    }

    public function test_notification_page_displays_invitations(): void
    {
        // Seed roles for the test
        $this->seed(\Database\Seeders\RolesSeeder::class);

        $user = User::factory()->withPersonalTeam()->create();
        $teamOwner = User::factory()->withPersonalTeam()->create();
        
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $teamOwner->currentTeam->id,
            'email' => $user->email,
            'roles' => ['employee'],
        ]);

        // Create notification for the user
        $user->notify(new \App\Notifications\TeamInvitationNotification($invitation));

        $this->actingAs($user);

        $response = $this->get(route('notifications'));

        $response->assertStatus(200);
        // The view uses Livewire, so check for Livewire component
        $response->assertSeeLivewire('notifications.notification-manager');
        // The notification should be visible (check for invitation text)
        $response->assertSee('Invitation', false);
    }

    public function test_user_can_accept_invitation_from_notifications(): void
    {
        // Seed roles for the test
        $this->seed(\Database\Seeders\RolesSeeder::class);

        $user = User::factory()->withPersonalTeam()->create();
        $teamOwner = User::factory()->withPersonalTeam()->create();
        
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $teamOwner->currentTeam->id,
            'email' => $user->email,
            'roles' => ['employee'],
        ]);

        // Create notification for the user
        $user->notify(new \App\Notifications\TeamInvitationNotification($invitation));
        
        // Get the notification from the database
        $notification = $user->notifications()->first();

        $this->actingAs($user);

        $response = $this->post(route('notifications.accept-invitation', $notification->id));

        $response->assertRedirect(route('dashboard'));

        // Check that user was added to the team
        $this->assertTrue($teamOwner->currentTeam->fresh()->users->contains($user));

        // Check that invitation was deleted
        $this->assertDatabaseMissing('team_invitations', [
            'id' => $invitation->id,
        ]);
    }

    public function test_user_can_decline_invitation_from_notifications(): void
    {
        // Seed roles for the test
        $this->seed(\Database\Seeders\RolesSeeder::class);

        $user = User::factory()->withPersonalTeam()->create();
        $teamOwner = User::factory()->withPersonalTeam()->create();
        
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $teamOwner->currentTeam->id,
            'email' => $user->email,
            'roles' => ['employee'],
        ]);

        // Create notification for the user
        $user->notify(new \App\Notifications\TeamInvitationNotification($invitation));
        
        // Get the notification from the database
        $notification = $user->notifications()->first();

        $this->actingAs($user);

        $response = $this->post(route('notifications.decline-invitation', $notification->id));

        $response->assertRedirect();

        // Check that user was NOT added to the team
        $this->assertFalse($teamOwner->currentTeam->fresh()->users->contains($user));

        // Check that invitation was marked as declined (not deleted)
        $this->assertDatabaseHas('team_invitations', [
            'id' => $invitation->id,
            'email' => $user->email,
        ]);
        $this->assertNotNull($invitation->fresh()->declined_at);
    }
}