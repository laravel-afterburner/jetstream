<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Livewire\Teams\MemberManager;
use Livewire\Livewire;
use Tests\TestCase;

class LeaveTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_leave_teams(): void
    {
        // Seed roles for the test
        $this->seed(\Database\Seeders\RolesSeeder::class);

        $user = User::factory()->withPersonalTeam()->create();
        
        // Ensure owner is attached to their team (factory might not do this automatically)
        $user->currentTeam->users()->syncWithoutDetaching([$user->id]);

        $user->currentTeam->users()->attach(
            $otherUser = User::factory()->create()
        );
        
        // Assign role to other user (using 'employee' from company template)
        $otherUser->assignRole('employee', $user->currentTeam->id);

        // Verify setup: should have 2 users (owner + otherUser)
        $this->assertCount(2, $user->currentTeam->fresh()->users);

        $this->actingAs($otherUser);

        // Call leaveTeam - it returns a redirect but should still execute the removal
        Livewire::test(MemberManager::class, ['team' => $user->currentTeam])
            ->call('leaveTeam', app(\App\Actions\Afterburner\RemoveTeamMember::class));

        // Verify user was removed from team
        // Refresh the team to ensure we get fresh data from the database
        $team = $user->currentTeam->fresh();
        $this->assertCount(1, $team->users, 'Expected 1 user but found ' . $team->users->count());
        $this->assertFalse($team->users->contains($otherUser), 'Other user should not be in team');
    }

    public function test_team_owners_cant_leave_their_own_team(): void
    {
        // Seed roles for the test
        $this->seed(\Database\Seeders\RolesSeeder::class);

        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        // Should not be able to leave as owner
        $component = Livewire::test(MemberManager::class, ['team' => $user->currentTeam]);
        
        // Verify canLeaveTeam returns false
        $this->assertFalse($component->instance()->canLeaveTeam());

        $this->assertNotNull($user->currentTeam->fresh());
    }

    public function test_users_cannot_leave_team_when_they_are_the_only_member(): void
    {
        // Seed roles for the test
        $this->seed(\Database\Seeders\RolesSeeder::class);

        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        // Should not be able to leave as only member
        $component = Livewire::test(MemberManager::class, ['team' => $user->currentTeam]);
        
        // Verify canLeaveTeam returns false
        $this->assertFalse($component->instance()->canLeaveTeam());

        $this->assertNotNull($user->currentTeam->fresh());
    }

    public function test_team_owners_cannot_leave_even_with_other_members(): void
    {
        // Seed roles for the test
        $this->seed(\Database\Seeders\RolesSeeder::class);

        $user = User::factory()->withPersonalTeam()->create();
        
        $user->currentTeam->users()->attach(
            $otherUser = User::factory()->create()
        );
        
        // Assign role to other user (using 'employee' from company template)
        $otherUser->assignRole('employee', $user->currentTeam->id);

        $this->actingAs($user);

        // Should not be able to leave as owner even with other members
        $component = Livewire::test(MemberManager::class, ['team' => $user->currentTeam]);
        
        // Verify canLeaveTeam returns false
        $this->assertFalse($component->instance()->canLeaveTeam());

        $this->assertNotNull($user->currentTeam->fresh());
    }

    public function test_non_owners_can_leave_team_when_other_members_exist(): void
    {
        // Seed roles for the test
        $this->seed(\Database\Seeders\RolesSeeder::class);

        $user = User::factory()->withPersonalTeam()->create();
        
        // Ensure owner is attached to their team
        $user->currentTeam->users()->syncWithoutDetaching([$user->id]);
        
        $user->currentTeam->users()->attach(
            $otherUser = User::factory()->create()
        );
        
        // Assign role to other user (using 'employee' from company template)
        $otherUser->assignRole('employee', $user->currentTeam->id);

        $this->actingAs($otherUser);

        Livewire::test(MemberManager::class, ['team' => $user->currentTeam])
            ->call('leaveTeam', app(\App\Actions\Afterburner\RemoveTeamMember::class));

        // Verify user was removed from team (redirect happens but doesn't work in tests)
        $team = $user->currentTeam->fresh();
        $this->assertCount(1, $team->users);
        $this->assertFalse($team->users->contains($otherUser));
    }
}
