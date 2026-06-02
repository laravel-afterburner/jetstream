<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Support\TeamRolePermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SeedsCompanyRoles;
use Tests\TestCase;

class TeamScopedRolesTest extends TestCase
{
    use RefreshDatabase;
    use SeedsCompanyRoles;

    public function test_system_roles_are_marked_and_custom_roles_are_team_scoped(): void
    {
        $this->seedCompanyRoles();

        $team = Team::factory()->create();
        $otherTeam = Team::factory()->create();

        TeamRolePermissions::seedTeam($team->id);
        TeamRolePermissions::seedTeam($otherTeam->id);

        $custom = Role::create([
            'name' => 'Pool Committee',
            'slug' => 'pool_committee',
            'description' => 'Pool',
            'badge_color' => 'gray',
            'hierarchy' => 50,
            'is_default' => false,
            'is_system' => false,
            'team_id' => $team->id,
        ]);

        $permission = Permission::first();
        TeamRolePermissions::syncForRole($custom, $team->id, [$permission->id]);

        $this->assertTrue($custom->isCustomTeamRole());
        $this->assertDatabaseHas('team_role_permission', [
            'team_id' => $team->id,
            'role_id' => $custom->id,
            'permission_id' => $permission->id,
        ]);
        $this->assertDatabaseMissing('team_role_permission', [
            'team_id' => $otherTeam->id,
            'role_id' => $custom->id,
        ]);
    }

    public function test_deleting_custom_role_only_affects_one_team(): void
    {
        $this->seedCompanyRoles();

        $team = Team::factory()->create();
        $user = User::factory()->withPersonalTeam()->create();
        $team->users()->attach($user);

        TeamRolePermissions::seedTeam($team->id);

        $custom = Role::create([
            'name' => 'Custom',
            'slug' => 'custom_role',
            'badge_color' => 'gray',
            'hierarchy' => 99,
            'is_system' => false,
            'team_id' => $team->id,
        ]);

        $user->assignRole('custom_role', $team->id);
        $custom->delete();

        $this->assertDatabaseMissing('roles', ['id' => $custom->id]);
        $this->assertDatabaseMissing('user_role', [
            'user_id' => $user->id,
            'role_id' => $custom->id,
        ]);

        $this->assertGreaterThan(0, Role::where('is_system', true)->count());
    }

    public function test_team_permission_maps_differ_per_team(): void
    {
        $this->seedCompanyRoles();

        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();

        TeamRolePermissions::seedTeam($teamA->id);
        TeamRolePermissions::seedTeam($teamB->id);

        $role = Role::where('slug', 'employee')->first();
        $permission = Permission::where('slug', 'view_documents')->first();

        TeamRolePermissions::syncForRole($role, $teamA->id, [$permission->id]);
        TeamRolePermissions::syncForRole($role, $teamB->id, []);

        $this->assertTrue(TeamRolePermissions::roleHasPermission($role, 'view_documents', $teamA->id));
        $this->assertFalse(TeamRolePermissions::roleHasPermission($role, 'view_documents', $teamB->id));
    }
}
