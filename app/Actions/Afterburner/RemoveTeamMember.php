<?php

namespace App\Actions\Afterburner;

use App\Models\Team;
use App\Models\User;
use App\Notifications\TeamMemberLeft;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use App\Events\TeamMemberRemoved;

class RemoveTeamMember
{
    /**
     * Remove the member from the given entity.
     */
    public function remove(User $user, Team $team, User $teamMember): void
    {
        $this->authorize($user, $team, $teamMember);

        $this->ensureUserDoesNotOwnEntity($teamMember, $team);
        $this->ensureTeamHasOtherMembers($team, $teamMember);

        // Capture the member's roles before removal for notifications
        $memberRoles = $teamMember->roles()
            ->where('team_id', $team->id)
            ->pluck('name')
            ->toArray();

        // Remove all remaining role assignments for this entity
        // (Should be minimal after observers fire, but ensures complete cleanup)
        $teamMember->roles()
            ->wherePivot('team_id', $team->id)
            ->detach();

        $team->removeUser($teamMember);

        // Notify the team owner that a member has left
        if ($team->owner->id !== $teamMember->id) {
            $team->owner->notify(new TeamMemberLeft($team, $teamMember, $memberRoles));
        }

        TeamMemberRemoved::dispatch($team, $teamMember);
    }

    /**
     * Authorize that the user can remove the member.
     */
    protected function authorize(User $user, Team $team, User $teamMember): void
    {
        if (! Gate::forUser($user)->check('removeTeamMember', $team) &&
            $user->id !== $teamMember->id) {
            throw new AuthorizationException;
        }
    }

    /**
     * Ensure that the user does not own the entity.
     */
    protected function ensureUserDoesNotOwnEntity(User $teamMember, Team $team): void
    {
        if ($teamMember->id === $team->owner->id) {
            throw ValidationException::withMessages([
                'team' => [__('You may not leave a :entity that you created. You must first assign a new owner.', ['entity' => config('afterburner.entity_label')])],
            ])->errorBag('removeTeamMember');
        }
    }

    /**
     * Ensure that the team has other members before allowing someone to leave.
     */
    protected function ensureTeamHasOtherMembers(Team $team, User $teamMember): void
    {
        $teamMemberCount = $team->users()->count();
        
        if ($teamMemberCount <= 1) {
            throw ValidationException::withMessages([
                'team' => [__('You cannot leave the :entity as you are the only member. Add other members first.', ['entity' => config('afterburner.entity_label')])],
            ])->errorBag('removeTeamMember');
        }
    }
}