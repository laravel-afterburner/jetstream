<?php

namespace App\Traits;

use App\Models\TeamInvitation;

trait HasRoleMembershipLimits
{
    /**
     * Check if this role has reached its maximum members for a specific team.
     * This includes both current members and pending invitations.
     */
    public function hasReachedMaxMembers(int $teamId): bool
    {
        // If max_members is null, there's no limit
        if ($this->max_members === null) {
            return false;
        }

        // Count current members with this role in the team
        $currentMembers = $this->users()
            ->wherePivot('team_id', $teamId)
            ->count();

        // Count pending invitations with this role for the team
        $pendingInvitations = TeamInvitation::where('team_id', $teamId)
            ->whereJsonContains('roles', $this->slug)
            ->count();

        $totalAssigned = $currentMembers + $pendingInvitations;

        return $totalAssigned >= $this->max_members;
    }

    /**
     * Get the number of available slots for this role in a specific team.
     */
    public function getAvailableSlots(int $teamId): int
    {
        // If max_members is null, return -1 to indicate unlimited
        if ($this->max_members === null) {
            return -1;
        }

        // Count current members with this role in the team
        $currentMembers = $this->users()
            ->wherePivot('team_id', $teamId)
            ->count();

        // Count pending invitations with this role for the team
        $pendingInvitations = TeamInvitation::where('team_id', $teamId)
            ->whereJsonContains('roles', $this->slug)
            ->count();

        $totalAssigned = $currentMembers + $pendingInvitations;

        return max(0, $this->max_members - $totalAssigned);
    }
}

