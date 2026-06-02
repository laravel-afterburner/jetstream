<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeamRolePermissions
{
    /**
     * Seed default template permissions for a team (or global scope when teams are disabled).
     */
    public static function seedTeam(?int $teamId): void
    {
        $template = RoleTemplates::get(config('afterburner.entity_label', 'company'));

        if (! $template) {
            return;
        }

        self::applyTemplate($teamId, $template);
    }

    /**
     * @param  array<string, mixed>  $template
     */
    public static function applyTemplate(?int $teamId, array $template): void
    {
        if (! Schema::hasTable('team_role_permission')) {
            return;
        }

        $roleIds = Role::query()
            ->where('is_system', true)
            ->pluck('id', 'slug');

        $permissionIds = Permission::query()->pluck('id', 'slug');

        foreach ($template['permission_map'] as $roleSlug => $permissionSlugs) {
            $roleId = $roleIds[$roleSlug] ?? null;

            if (! $roleId) {
                continue;
            }

            foreach ($permissionSlugs as $permissionSlug) {
                $permissionId = $permissionIds[$permissionSlug] ?? null;

                if (! $permissionId) {
                    continue;
                }

                DB::table('team_role_permission')->insertOrIgnore([
                    'team_id' => $teamId,
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }

            $role = Role::find($roleId);

            if ($role && Schema::hasTable('team_role_settings')) {
                DB::table('team_role_settings')->insertOrIgnore([
                    'team_id' => $teamId,
                    'role_id' => $roleId,
                    'max_members' => $role->max_members,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public static function syncForRole(Role $role, ?int $teamId, array $permissionIds): void
    {
        DB::table('team_role_permission')
            ->where('team_id', $teamId)
            ->where('role_id', $role->id)
            ->delete();

        foreach ($permissionIds as $permissionId) {
            DB::table('team_role_permission')->insert([
                'team_id' => $teamId,
                'role_id' => $role->id,
                'permission_id' => $permissionId,
            ]);
        }
    }

    /**
     * @return list<int>
     */
    public static function permissionIdsForRole(Role $role, ?int $teamId): array
    {
        if (! Schema::hasTable('team_role_permission')) {
            return self::legacyPermissionIdsForRole($role);
        }

        return DB::table('team_role_permission')
            ->where('team_id', $teamId)
            ->where('role_id', $role->id)
            ->pluck('permission_id')
            ->all();
    }

    public static function roleHasPermission(Role $role, string $permissionSlug, ?int $teamId): bool
    {
        if (RoleImpersonation::isActive()
            && RoleImpersonation::teamId() === $teamId
            && RoleImpersonation::roleId() === $role->id) {
            return self::impersonatedRoleHasPermission($permissionSlug);
        }

        if (! Schema::hasTable('team_role_permission')) {
            return self::legacyRoleHasPermission($role, $permissionSlug);
        }

        return DB::table('team_role_permission')
            ->join('permissions', 'permissions.id', '=', 'team_role_permission.permission_id')
            ->where('team_role_permission.team_id', $teamId)
            ->where('team_role_permission.role_id', $role->id)
            ->where('permissions.slug', $permissionSlug)
            ->exists();
    }

    public static function userHasPermissionViaRoles(Collection $roleIds, string $permissionSlug, ?int $teamId): bool
    {
        if (RoleImpersonation::isActive() && RoleImpersonation::teamId() === $teamId) {
            return self::impersonatedRoleHasPermission($permissionSlug);
        }

        if ($roleIds->isEmpty()) {
            return false;
        }

        if (! Schema::hasTable('team_role_permission')) {
            foreach ($roleIds as $roleId) {
                $role = Role::find($roleId);

                if ($role && self::legacyRoleHasPermission($role, $permissionSlug)) {
                    return true;
                }
            }

            return false;
        }

        return DB::table('team_role_permission')
            ->join('permissions', 'permissions.id', '=', 'team_role_permission.permission_id')
            ->where('team_role_permission.team_id', $teamId)
            ->whereIn('team_role_permission.role_id', $roleIds)
            ->where('permissions.slug', $permissionSlug)
            ->exists();
    }

    public static function impersonatedRoleHasPermission(string $permissionSlug): bool
    {
        $roleId = RoleImpersonation::roleId();
        $teamId = RoleImpersonation::teamId();

        if (! $roleId) {
            return false;
        }

        $role = Role::find($roleId);

        if (! $role) {
            return false;
        }

        if (! Schema::hasTable('team_role_permission')) {
            return self::legacyRoleHasPermission($role, $permissionSlug);
        }

        return DB::table('team_role_permission')
            ->join('permissions', 'permissions.id', '=', 'team_role_permission.permission_id')
            ->where('team_role_permission.team_id', $teamId)
            ->where('team_role_permission.role_id', $roleId)
            ->where('permissions.slug', $permissionSlug)
            ->exists();
    }

    /**
     * @return list<int>
     */
    protected static function legacyPermissionIdsForRole(Role $role): array
    {
        if (! Schema::hasTable('role_permission')) {
            return [];
        }

        return DB::table('role_permission')
            ->where('role_id', $role->id)
            ->pluck('permission_id')
            ->all();
    }

    protected static function legacyRoleHasPermission(Role $role, string $permissionSlug): bool
    {
        if (! Schema::hasTable('role_permission')) {
            return false;
        }

        return DB::table('role_permission')
            ->join('permissions', 'permissions.id', '=', 'role_permission.permission_id')
            ->where('role_permission.role_id', $role->id)
            ->where('permissions.slug', $permissionSlug)
            ->exists();
    }

    public static function setMaxMembers(Role $role, ?int $teamId, ?int $maxMembers): void
    {
        if (! Schema::hasTable('team_role_settings')) {
            $role->update(['max_members' => $maxMembers]);

            return;
        }

        DB::table('team_role_settings')->updateOrInsert(
            ['team_id' => $teamId, 'role_id' => $role->id],
            ['max_members' => $maxMembers, 'updated_at' => now(), 'created_at' => now()],
        );
    }

    public static function maxMembersForRole(Role $role, ?int $teamId): ?int
    {
        if (! Schema::hasTable('team_role_settings')) {
            return $role->max_members;
        }

        $value = DB::table('team_role_settings')
            ->where('team_id', $teamId)
            ->where('role_id', $role->id)
            ->value('max_members');

        return $value !== null ? (int) $value : null;
    }

    public static function rolesForTeam(?int $teamId): Collection
    {
        if (! Schema::hasColumn('roles', 'is_system')) {
            return Role::orderBy('hierarchy')->get();
        }

        return Role::query()
            ->forTeam($teamId)
            ->orderBy('hierarchy')
            ->get();
    }

    public static function resolveRoleSlug(string $slug, ?int $teamId): ?Role
    {
        $custom = Role::query()
            ->where('team_id', $teamId)
            ->where('slug', $slug)
            ->first();

        if ($custom) {
            return $custom;
        }

        return Role::query()
            ->where('is_system', true)
            ->where('slug', $slug)
            ->first();
    }

    public static function systemRoleSlugs(): array
    {
        return Role::query()
            ->where('is_system', true)
            ->pluck('slug')
            ->all();
    }
}
