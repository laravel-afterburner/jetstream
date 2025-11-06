<?php

namespace Tests\Feature;

use App\Actions\Fortify\CreateNewUser;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_team_and_assigns_default_role(): void
    {
        // Seed roles to ensure default role exists
        $this->seed(\Database\Seeders\RolesSeeder::class);

        $action = app(CreateNewUser::class);

        $input = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ];

        // Accept terms if the feature is enabled
        if (\App\Support\Features::hasTermsAndPrivacyPolicyFeature()) {
            $input['terms'] = true;
        }

        /** @var User $user */
        $user = $action->create($input);

        $this->assertInstanceOf(User::class, $user);

        // Team created (current_team_id should be set by CreateNewUser::createTeam)
        $team = $user->ownedTeams()->first() ?? $user->teams()->first();
        $this->assertInstanceOf(Team::class, $team, 'A team should be created for the user.');
        
        // Verify current_team_id is set
        $this->assertNotNull($user->current_team_id, 'User should have a current team set after registration.');
        $this->assertEquals($team->id, $user->current_team_id, 'User\'s current team should match the created team.');
        
        // personal_team depends on feature flag - by default it's disabled
        // If feature is enabled, team will be marked as personal
        if (\App\Support\Features::hasPersonalTeams()) {
            $this->assertTrue((bool) $team->personal_team, 'Team should be marked as personal when feature is enabled.');
        } else {
            $this->assertFalse((bool) $team->personal_team, 'Team should not be marked as personal when feature is disabled.');
        }

        // Default role assigned to the user for the created team
        $defaultRole = Role::where('is_default', true)->first();
        $this->assertNotNull($defaultRole);

        $hasDefaultRole = $user->roles()
            ->where('team_id', $team->id)
            ->where('roles.id', $defaultRole->id)
            ->exists();

        $this->assertTrue($hasDefaultRole, 'User should have default role on their team after registration.');
    }
}


