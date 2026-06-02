<?php

namespace App\Traits;

use App\Models\Role;
use App\Support\Features;
use App\Support\RoleImpersonation;
use App\Support\TeamRolePermissions;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasAfterburnerRoles
{
    /**
     * Get the roles assigned to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->withPivot('team_id')
            ->withTimestamps();
    }

    /**
     * Check if user has a specific permission within a team context.
     * Supports global roles (null team_id) when teams feature is disabled.
     */
    public function hasPermission(string $permissionSlug, ?int $teamId = null): bool
    {
        if (! Features::hasTeamFeatures()) {
            $teamId = null;
        } else {
            $teamId = $teamId ?? $this->currentTeam?->id;
        }

        if (Features::hasTeamFeatures() && ! $teamId) {
            return false;
        }

        if (RoleImpersonation::isActive() && RoleImpersonation::teamId() === $teamId) {
            return TeamRolePermissions::impersonatedRoleHasPermission($permissionSlug);
        }

        if (Features::hasTeamFeatures() && $teamId && ! RoleImpersonation::shouldBypassOwnerPrivileges() && $this->ownsTeamById($teamId)) {
            return true;
        }

        $roleIds = $this->roles()
            ->where('team_id', $teamId)
            ->pluck('roles.id');

        return TeamRolePermissions::userHasPermissionViaRoles($roleIds, $permissionSlug, $teamId);
    }

    /**
     * Check if user has a specific role within a team context.
     */
    public function hasRole(string $roleSlug, ?int $teamId = null): bool
    {
        if (! Features::hasTeamFeatures()) {
            $teamId = null;
        } else {
            $teamId = $teamId ?? $this->currentTeam?->id;
        }

        if (Features::hasTeamFeatures() && ! $teamId) {
            return false;
        }

        if (RoleImpersonation::isActive()
            && RoleImpersonation::teamId() === $teamId) {
            $impersonated = Role::find(RoleImpersonation::roleId());

            return $impersonated?->slug === $roleSlug;
        }

        return $this->roles()
            ->where('team_id', $teamId)
            ->where('slug', $roleSlug)
            ->exists();
    }

    /**
     * Get all permissions for the user within a team context.
     */
    public function getPermissions(?int $teamId = null): \Illuminate\Support\Collection
    {
        if (! Features::hasTeamFeatures()) {
            $teamId = null;
        } else {
            $teamId = $teamId ?? $this->currentTeam?->id;
        }

        if (Features::hasTeamFeatures() && ! $teamId) {
            return collect();
        }

        if (RoleImpersonation::isActive() && RoleImpersonation::teamId() === $teamId) {
            $roleId = RoleImpersonation::roleId();

            if (! $roleId) {
                return collect();
            }

            $permissionIds = TeamRolePermissions::permissionIdsForRole(Role::findOrFail($roleId), $teamId);

            return \App\Models\Permission::query()->whereIn('id', $permissionIds)->get();
        }

        $roleIds = $this->roles()
            ->where('team_id', $teamId)
            ->pluck('roles.id');

        if ($roleIds->isEmpty()) {
            return collect();
        }

        $permissionIds = collect();

        foreach ($roleIds as $roleId) {
            $role = Role::find($roleId);

            if ($role) {
                $permissionIds = $permissionIds->merge(
                    TeamRolePermissions::permissionIdsForRole($role, $teamId)
                );
            }
        }

        return \App\Models\Permission::query()
            ->whereIn('id', $permissionIds->unique()->all())
            ->get();
    }

    public function assignRole(string $roleSlug, ?int $teamId): void
    {
        $role = TeamRolePermissions::resolveRoleSlug($roleSlug, $teamId);

        if (! $role) {
            return;
        }

        $exists = $this->roles()
            ->wherePivot('team_id', $teamId)
            ->where('roles.id', $role->id)
            ->exists();

        if (! $exists) {
            $this->roles()->attach($role->id, ['team_id' => $teamId]);
        }
    }

    public function removeRole(string $roleSlug, ?int $teamId): void
    {
        $role = TeamRolePermissions::resolveRoleSlug($roleSlug, $teamId);

        if ($role) {
            $this->roles()
                ->wherePivot('team_id', $teamId)
                ->detach($role->id);
        }
    }

    public function roleNamesForTeam(?int $teamId = null): string
    {
        if (! Features::hasTeamFeatures()) {
            $teamId = null;
        } else {
            $teamId = $teamId ?? $this->currentTeam?->id;
        }

        if (Features::hasTeamFeatures() && ! $teamId) {
            return '';
        }

        return $this->roles()
            ->where('team_id', $teamId)
            ->pluck('name')
            ->join(', ');
    }
}
