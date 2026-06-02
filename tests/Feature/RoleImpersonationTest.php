<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Support\RoleImpersonation;
use App\Support\TeamRolePermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SeedsCompanyRoles;
use Tests\TestCase;

class RoleImpersonationTest extends TestCase
{
    use RefreshDatabase;
    use SeedsCompanyRoles;

    public function test_system_admin_can_start_and_stop_role_impersonation(): void
    {
        $this->seedCompanyRoles();

        $admin = User::factory()->create(['is_system_admin' => true]);
        $team = Team::factory()->create();
        TeamRolePermissions::seedTeam($team->id);

        $role = Role::where('slug', 'employee')->first();

        $response = $this->actingAs($admin)->post(route('impersonate-role.start', [
            'team' => $team,
            'role' => $role,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas(RoleImpersonation::SESSION_ACTIVE, true);
        $response->assertSessionHas(RoleImpersonation::SESSION_ROLE_ID, $role->id);

        $stop = $this->actingAs($admin)->post(route('impersonate-role.stop'));
        $stop->assertRedirect();
        $stop->assertSessionMissing(RoleImpersonation::SESSION_ACTIVE);
    }

    public function test_role_impersonation_suppresses_team_owner_bypass(): void
    {
        $this->seedCompanyRoles();

        $owner = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $owner->id]);
        $team->users()->attach($owner);
        $owner->switchTeam($team);

        TeamRolePermissions::seedTeam($team->id);

        $role = Role::where('slug', 'employee')->first();
        TeamRolePermissions::syncForRole($role, $team->id, []);

        RoleImpersonation::start($owner->id, $role->id, $team->id);

        $this->assertFalse($owner->hasPermission('manage_users', $team->id));
    }

    public function test_non_admin_cannot_impersonate_role(): void
    {
        $this->seedCompanyRoles();

        $user = User::factory()->create();
        $team = Team::factory()->create();
        $role = Role::where('slug', 'employee')->first();

        $this->actingAs($user)
            ->post(route('impersonate-role.start', ['team' => $team, 'role' => $role]));

        $this->assertFalse(RoleImpersonation::isActive());
    }
}
