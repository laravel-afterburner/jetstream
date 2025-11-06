<?php

namespace App\Actions\Afterburner;

use App\Models\Role;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Events\TeamMemberAdded;
use Illuminate\Support\Facades\DB;

class AcceptTeamInvitation
{
    /**
     * Accept the team invitation and add the user to the team.
     */
    public function add(User $user, Team $team, string $email, ?array $roles = null): void
    {
        DB::transaction(function () use ($user, $team, $email, $roles) {
            // Find the invitation for this user
            $invitation = $team->teamInvitations()
                ->where('email', $email)
                ->firstOrFail();

            // Attach user to team (syncWithoutDetaching prevents duplicate errors)
            $team->users()->syncWithoutDetaching($user);

            // Always assign the default role
            $defaultRole = Role::where('is_default', true)->first();
            if ($defaultRole) {
                $user->assignRole($defaultRole->slug, $team->id);
            }

            // Assign additional roles if specified (use parameter or fall back to invitation's roles)
            $rolesToAssign = $roles ?? $invitation->roles;
            if ($rolesToAssign && is_array($rolesToAssign)) {
                foreach ($rolesToAssign as $roleSlug) {
                    $user->assignRole($roleSlug, $team->id);
                }
            }

            // Delete the invitation
            $invitation->delete();

            // Set the current team if the user doesn't have one
            // Use current_team_id directly to avoid triggering the accessor that might auto-create teams
            if (!$user->current_team_id) {
                $user->switchTeam($team);
            }

            TeamMemberAdded::dispatch($team, $user);
        });
    }
}