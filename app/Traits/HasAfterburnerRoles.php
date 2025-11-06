<?php

namespace App\Traits;

use App\Models\Role;
use App\Support\Features;
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
        // If teams feature is disabled, use global roles (null team_id)
        if (! Features::hasTeamFeatures()) {
            $teamId = null;
        } else {
            $teamId = $teamId ?? $this->currentTeam?->id;
        }

        // For teams-enabled, require team_id
        if (Features::hasTeamFeatures() && !$teamId) {
            return false;
        }

        return $this->roles()
            ->where('team_id', $teamId)
            ->whereHas('permissions', function ($query) use ($permissionSlug) {
                $query->where('slug', $permissionSlug);
            })
            ->exists();
    }

    /**
     * Check if user has a specific role within a team context.
     * Supports global roles (null team_id) when teams feature is disabled.
     */
    public function hasRole(string $roleSlug, ?int $teamId = null): bool
    {
        // If teams feature is disabled, use global roles (null team_id)
        if (! Features::hasTeamFeatures()) {
            $teamId = null;
        } else {
            $teamId = $teamId ?? $this->currentTeam?->id;
        }

        // For teams-enabled, require team_id
        if (Features::hasTeamFeatures() && !$teamId) {
            return false;
        }

        return $this->roles()
            ->where('team_id', $teamId)
            ->where('slug', $roleSlug)
            ->exists();
    }

    /**
     * Get all permissions for the user within a team context.
     * Supports global roles (null team_id) when teams feature is disabled.
     */
    public function getPermissions(?int $teamId = null): \Illuminate\Support\Collection
    {
        // If teams feature is disabled, use global roles (null team_id)
        if (! Features::hasTeamFeatures()) {
            $teamId = null;
        } else {
            $teamId = $teamId ?? $this->currentTeam?->id;
        }

        // For teams-enabled, require team_id
        if (Features::hasTeamFeatures() && !$teamId) {
            return collect();
        }

        return $this->roles()
            ->where('team_id', $teamId)
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }

    /**
     * Assign a role to the user for a specific team.
     * When teams are disabled, teamId should be null for global roles.
     */
    public function assignRole(string $roleSlug, ?int $teamId): void
    {
        $role = Role::where('slug', $roleSlug)->firstOrFail();
        
        // Check if the role is already assigned to avoid duplicates
        $exists = $this->roles()
            ->wherePivot('team_id', $teamId)
            ->where('roles.id', $role->id)
            ->exists();
            
        if (!$exists) {
            $this->roles()->attach($role->id, ['team_id' => $teamId]);
        }
    }

    /**
     * Remove a role from the user for a specific team.
     * When teams are disabled, teamId should be null for global roles.
     */
    public function removeRole(string $roleSlug, ?int $teamId): void
    {
        $role = Role::where('slug', $roleSlug)->first();
        
        if ($role) {
            $this->roles()
                ->wherePivot('team_id', $teamId)
                ->detach($role->id);
        }
    }

    /**
     * Get formatted role names for display in a specific team.
     * Supports global roles (null team_id) when teams feature is disabled.
     */
    public function roleNamesForTeam(?int $teamId = null): string
    {
        // If teams feature is disabled, use global roles (null team_id)
        if (! Features::hasTeamFeatures()) {
            $teamId = null;
        } else {
            $teamId = $teamId ?? $this->currentTeam?->id;
        }

        // For teams-enabled, require team_id
        if (Features::hasTeamFeatures() && !$teamId) {
            return '';
        }

        return $this->roles()
            ->where('team_id', $teamId)
            ->pluck('name')
            ->join(', ');
    }
}

