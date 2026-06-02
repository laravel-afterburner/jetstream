<?php

namespace App\Traits;

use App\Models\TeamInvitation;
use App\Support\TeamRolePermissions;

trait HasRoleMembershipLimits
{
    public function hasReachedMaxMembers(int $teamId): bool
    {
        $maxMembers = TeamRolePermissions::maxMembersForRole($this, $teamId);

        if ($maxMembers === null) {
            return false;
        }

        $currentMembers = $this->users()
            ->wherePivot('team_id', $teamId)
            ->count();

        $pendingInvitations = TeamInvitation::where('team_id', $teamId)
            ->whereJsonContains('roles', $this->slug)
            ->count();

        return ($currentMembers + $pendingInvitations) >= $maxMembers;
    }

    public function getAvailableSlots(int $teamId): int
    {
        $maxMembers = TeamRolePermissions::maxMembersForRole($this, $teamId);

        if ($maxMembers === null) {
            return -1;
        }

        $currentMembers = $this->users()
            ->wherePivot('team_id', $teamId)
            ->count();

        $pendingInvitations = TeamInvitation::where('team_id', $teamId)
            ->whereJsonContains('roles', $this->slug)
            ->count();

        return max(0, $maxMembers - ($currentMembers + $pendingInvitations));
    }
}
