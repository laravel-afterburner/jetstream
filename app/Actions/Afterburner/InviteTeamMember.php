<?php

namespace App\Actions\Afterburner;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Events\InvitingTeamMember;
use App\Mail\TeamInvitation;
use App\Notifications\TeamInvitationNotification;
use App\Notifications\TeamInvitationRegistrationRequired;
use App\Support\Afterburner;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InviteTeamMember
{
    /**
     * Invite a new member to the given entity.
     */
    public function invite(User $user, Team $team, string $email, ?array $roles = null): void
    {
        Gate::forUser($user)->authorize('addTeamMember', $team);

        $this->validate($team, $email, $roles);

        InvitingTeamMember::dispatch($team, $email, $roles);

        $invitation = $team->teamInvitations()->create([
            'email' => $email,
            'roles' => $roles, // Store the additional roles to be assigned on acceptance
        ]);

        // Check if user exists in the system
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            // User exists - send notification to their account
            $existingUser->notify(new TeamInvitationNotification($invitation));
        } else {
            // User doesn't exist - send registration required email
            Notification::route('mail', $email)
                ->notify(new TeamInvitationRegistrationRequired($invitation));
        }
    }

    /**
     * Validate the invite member operation.
     */
    protected function validate(Team $team, string $email, ?array $roles): void
    {
        Validator::make([
            'email' => $email,
            'roles' => $roles,
        ], $this->rules($team), [
            'email.unique' => __('This user has already been invited to the :entity.', ['entity' => config('afterburner.entity_label')]),
        ])->after(
            $this->ensureUserIsNotAlreadyInEntity($team, $email)
        )->after(
            $this->ensureRolesAreNotAtMaxCapacity($team, $roles)
        )->validateWithBag('addTeamMember');
    }

    /**
     * Get the validation rules for inviting a member.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function rules(Team $team): array
    {
        return [
            'email' => [
                'required', 'email',
                Rule::unique(Afterburner::teamInvitationModel())->where(function (Builder $query) use ($team) {
                    $query->where('team_id', $team->id);
                }),
            ],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,slug'],
        ];
    }

    /**
     * Ensure that the user is not already in the entity.
     */
    protected function ensureUserIsNotAlreadyInEntity(Team $team, string $email): Closure
    {
        return function ($validator) use ($team, $email) {
            $validator->errors()->addIf(
                $team->hasUserWithEmail($email),
                'email',
                __('This user already belongs to the :entity.', ['entity' => config('afterburner.entity_label')])
            );
        };
    }

    /**
     * Ensure that the roles are not at max capacity.
     */
    protected function ensureRolesAreNotAtMaxCapacity(Team $team, ?array $roles): Closure
    {
        return function ($validator) use ($team, $roles) {
            if (!$roles) {
                return;
            }

            foreach ($roles as $roleSlug) {
                $role = Role::where('slug', $roleSlug)->first();
                
                if ($role && $role->hasReachedMaxMembers($team->id)) {
                    $validator->errors()->add(
                        'roles',
                        __('The :role role has reached its maximum capacity of :max members.', [
                            'role' => $role->name,
                            'max' => $role->max_members
                        ])
                    );
                }
            }
        };
    }
}