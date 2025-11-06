<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any roles.
     * Users can view roles if they belong to the team.
     */
    public function viewAny(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }

    /**
     * Determine whether the user can view the role.
     * Users can view roles if they belong to the team.
     */
    public function view(User $user, Team $team, ?Role $role = null): bool
    {
        return $user->belongsToTeam($team);
    }

    /**
     * Determine whether the user can create roles.
     * Users can create roles if they own the team or have manage_users permission.
     */
    public function create(User $user, Team $team): bool
    {
        // Team owners can always create roles
        if ($user->ownsTeam($team)) {
            return true;
        }

        // Check for manage_users permission
        return $user->hasPermission('manage_users', $team->id);
    }

    /**
     * Determine whether the user can update the role.
     * Users can update roles if they own the team or have manage_users permission.
     */
    public function update(User $user, Team $team, ?Role $role = null): bool
    {
        // Team owners can always update roles
        if ($user->ownsTeam($team)) {
            return true;
        }

        // Check for manage_users permission
        return $user->hasPermission('manage_users', $team->id);
    }

    /**
     * Determine whether the user can delete the role.
     * Users can delete roles if they own the team or have manage_users permission.
     * Default roles cannot be deleted.
     */
    public function delete(User $user, Team $team, Role $role): bool
    {
        // Cannot delete default roles
        if ($role->is_default) {
            return false;
        }

        // Team owners can delete non-default roles
        if ($user->ownsTeam($team)) {
            return true;
        }

        // Check for manage_users permission
        return $user->hasPermission('manage_users', $team->id);
    }

    /**
     * Determine whether the user can update role hierarchy.
     * Users can update hierarchy if they own the team or have manage_users permission.
     */
    public function updateHierarchy(User $user, Team $team): bool
    {
        // Team owners can always update hierarchy
        if ($user->ownsTeam($team)) {
            return true;
        }

        // Check for manage_users permission
        return $user->hasPermission('manage_users', $team->id);
    }
}

