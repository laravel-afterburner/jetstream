<?php

namespace App\Actions\Afterburner;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Support\Afterburner;
use App\Support\Features;
use App\Events\AddingTeam;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class CreateTeam
{
    /**
     * Validate and create a new team for the given user.
     *
     * @param  array<string, string>  $input
     */
    public function create(User $user, array $input): Team
    {
        Gate::forUser($user)->authorize('create', Afterburner::newTeamModel());

        $rules = [
            'name' => ['required', 'string', 'max:255'],
        ];

        // Only validate timezone if team timezone management is enabled
        if (Features::hasTeamTimezoneManagement() && isset($input['timezone'])) {
            $rules['timezone'] = ['nullable', 'string', 'timezone'];
        }

        Validator::make($input, $rules)->validateWithBag('createTeam');

        AddingTeam::dispatch($user);

        $teamData = ['name' => $input['name']];

        // Explicitly set personal_team flag based on feature state
        // When feature is enabled, new teams are always non-personal
        // When feature is disabled, explicitly set to false for consistency
        $teamData['personal_team'] = false;

        // Set timezone if provided and team timezone management is enabled
        if (Features::hasTeamTimezoneManagement() && isset($input['timezone']) && !empty($input['timezone'])) {
            $teamData['timezone'] = $input['timezone'];
        }

        $user->switchTeam($team = $user->ownedTeams()->create($teamData));

        // Attach the creator to their new team
        $team->users()->attach($user);

        // Assign the application's default role dynamically
        $defaultRole = Role::where('is_default', true)->first();
        if ($defaultRole) {
            $user->assignRole($defaultRole->slug, $team->id);
        }

        return $team;
    }
}