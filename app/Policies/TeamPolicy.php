<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Users can view teams they belong to.
     */
    public function view(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     * Users can update if they own the team or have manage_team_settings permission.
     */
    public function update(User $user, Team $team): bool
    {
        // Team owners can always update
        if ($user->ownsTeam($team)) {
            return true;
        }

        // Check for manage_team_settings permission
        return $user->hasPermission('manage_team_settings', $team->id);
    }

    /**
     * Determine whether the user can add team members.
     * Users can add members if they own the team or have manage_users permission.
     */
    public function addTeamMember(User $user, Team $team): bool
    {
        // Team owners can always add members
        if ($user->ownsTeam($team)) {
            return true;
        }

        // Check for manage_users permission
        return $user->hasPermission('manage_users', $team->id);
    }

    /**
     * Determine whether the user can update team member permissions.
     * Users can update member roles if they own the team or have manage_users permission.
     */
    public function updateTeamMember(User $user, Team $team): bool
    {
        // Team owners can always update members
        if ($user->ownsTeam($team)) {
            return true;
        }

        // Check for manage_users permission
        return $user->hasPermission('manage_users', $team->id);
    }

    /**
     * Determine whether the user can remove team members.
     * Users can remove members if they own the team or have manage_users permission.
     */
    public function removeTeamMember(User $user, Team $team): bool
    {
        // Team owners can always remove members
        if ($user->ownsTeam($team)) {
            return true;
        }

        // Check for manage_users permission
        return $user->hasPermission('manage_users', $team->id);
    }

    /**
     * Determine whether the user can delete the model.
     * Users can delete if they own the team or have manage_team_settings permission.
     */
    public function delete(User $user, Team $team): bool
    {
        // Team owners can always delete
        if ($user->ownsTeam($team)) {
            return true;
        }

        // Check for manage_team_settings permission
        return $user->hasPermission('manage_team_settings', $team->id);
    }

    /**
     * Determine whether the user can change the team owner.
     * Users can change owner if they own the team or have manage_team_settings permission.
     */
    public function changeOwner(User $user, Team $team): bool
    {
        // Team owners can always change owner
        if ($user->ownsTeam($team)) {
            return true;
        }

        // Check for manage_team_settings permission
        return $user->hasPermission('manage_team_settings', $team->id);
    }
}
