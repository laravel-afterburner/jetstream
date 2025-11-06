<?php

namespace App\Actions\Afterburner;

use App\Models\Team;
use App\Notifications\TeamDeleted;
use Illuminate\Support\Facades\Notification;

class DeleteTeam
{
    /**
     * Delete the given team.
     */
    public function delete(Team $team): void
    {
        // Get all team members (excluding the owner) before deletion
        $members = $team->users()->where('user_id', '!=', $team->user_id)->get();

        // Soft delete the team
        $team->delete();

        // Notify all members about the deletion
        if ($members->isNotEmpty()) {
            Notification::send($members, new TeamDeleted($team));
        }
    }
}