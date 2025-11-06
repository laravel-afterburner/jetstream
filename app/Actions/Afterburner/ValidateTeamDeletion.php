<?php

namespace App\Actions\Afterburner;

use App\Support\Features;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ValidateTeamDeletion
{
    /**
     * Validate that the team can be deleted by the given user.
     *
     * @param  mixed  $user
     * @param  mixed  $team
     * @return void
     */
    public function validate($user, $team)
    {
        Gate::forUser($user)->authorize('delete', $team);

        // Only prevent deletion of personal teams if feature is enabled
        if (Features::hasPersonalTeams() && $team->personal_team) {
            throw ValidationException::withMessages([
                'team' => __('You may not delete your personal team.'),
            ])->errorBag('deleteTeam');
        }
    }
}

