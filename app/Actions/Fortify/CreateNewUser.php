<?php

namespace App\Actions\Fortify;

use App\Events\TeamMemberAdded;
use App\Models\Role;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Support\Afterburner;
use App\Support\Features;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Afterburner::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ];

        // If this is an invitation-based registration, validate email against invitation
        if (isset($input['invitation'])) {
            $rules['email'][] = function ($attribute, $value, $fail) use ($input) {
                $invitation = TeamInvitation::find($input['invitation']);
                if (!$invitation) {
                    $fail('Invalid or expired invitation.');
                    return;
                }
                if ($invitation->email !== $value) {
                    $fail('The email address must match the invitation.');
                }
            };
        }

        Validator::make($input, $rules)->validate();

        return DB::transaction(function () use ($input) {
            return tap(User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]), function (User $user) use ($input) {
                // Check if teams feature is enabled
                if (! Features::hasTeamFeatures()) {
                    // Assign global default role (no team context)
                    $defaultRole = Role::where('is_default', true)->first();
                    if ($defaultRole) {
                        $user->assignRole($defaultRole->slug, null);
                    }
                    return;
                }

                // Check if this is an invitation-based registration
                if (isset($input['invitation'])) {
                    $this->handleInvitationRegistration($user, $input['invitation']);
                } else {
                    $this->createTeam($user);
                }
            });
        });
    }

    /**
     * Create a team for the user.
     * 
     * If personal teams feature is enabled, marks the team as personal.
     * Otherwise creates a regular team.
     */
    protected function createTeam(User $user): void
    {
        $teamData = [
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s ".ucfirst(config('afterburner.entity_label')),
        ];

        // Set personal_team flag based on feature state
        // When feature is enabled, this is a personal team
        // When feature is disabled, explicitly set to false for consistency
        $teamData['personal_team'] = Features::hasPersonalTeams();

        $team = $user->ownedTeams()->save(Team::forceCreate($teamData));

        // Attach user to their team
        $team->users()->attach($user);

        // Assign the application's default role dynamically
        $defaultRole = Role::where('is_default', true)->first();
        if ($defaultRole) {
            $user->assignRole($defaultRole->slug, $team->id);
        }

        // Set the team as the user's current team
        $user->switchTeam($team);
    }

    /**
     * Handle invitation-based registration.
     */
    protected function handleInvitationRegistration(User $user, string $invitationToken): void
    {
        // Teams must be enabled for invitations to work
        if (! Features::hasTeamFeatures()) {
                    // Fallback: assign global role if teams disabled
                    $defaultRole = Role::where('is_default', true)->first();
            if ($defaultRole) {
                $user->assignRole($defaultRole->slug, null);
            }
            return;
        }

        // Find the invitation
        $invitation = TeamInvitation::find($invitationToken);
        
        if (!$invitation) {
            // If invitation not found, create personal team as fallback
            $this->createTeam($user);
            return;
        }

        // Verify the invitation is for this user's email
        if ($invitation->email !== $user->email) {
            // If email doesn't match, create personal team as fallback
            $this->createTeam($user);
            return;
        }

        $team = $invitation->team;

        // Attach user to the invited team
        $team->users()->attach($user);

        // Always assign the default role
        $defaultRole = Role::where('is_default', true)->first();
        if ($defaultRole) {
            $user->assignRole($defaultRole->slug, $team->id);
        }

        // Assign additional roles if specified in the invitation
        if ($invitation->roles && is_array($invitation->roles)) {
            foreach ($invitation->roles as $roleSlug) {
                $user->assignRole($roleSlug, $team->id);
            }
        }

        // Delete the invitation
        $invitation->delete();

        // Dispatch event
        TeamMemberAdded::dispatch($team, $user);

        // Set the current team to the invited team
        $user->current_team_id = $team->id;
        $user->save();
    }
}