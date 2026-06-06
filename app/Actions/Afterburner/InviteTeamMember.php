<?php

namespace App\Actions\Afterburner;

use App\Support\EntityLabel;
use App\Events\InvitingTeamMember;
use App\Models\Role;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Support\Features;
use App\Notifications\TeamInvitationNotification;
use App\Notifications\TeamInvitationRegistrationRequired;
use App\Services\EmailRateLimiter;
use Closure;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

        $invitation = $team->teamInvitations()->where('email', $email)->first();

        if ($invitation) {
            $invitation->update(['roles' => $roles, 'declined_at' => null]);
        } else {
            $invitation = $team->teamInvitations()->create([
                'email' => $email,
                'roles' => $roles,
            ]);
        }

        $this->deliverInvitation($invitation);
    }

    /**
     * Notify or immediately add an invited user, depending on configuration.
     */
    public function deliverInvitation(TeamInvitation $invitation): void
    {
        $email = $invitation->email;
        $existingUser = User::where('email', $email)->first();
        $rateLimiter = app(EmailRateLimiter::class);

        if ($existingUser && ! Features::allowsTeamCreation()) {
            app(AcceptTeamInvitation::class)->add(
                $existingUser,
                $invitation->team,
                $invitation->email,
                $invitation->roles
            );

            return;
        }

        if ($existingUser) {
            if ($rateLimiter->canSendToUser($existingUser)) {
                $existingUser->notify(new TeamInvitationNotification($invitation));
                $rateLimiter->incrementLimits($existingUser, $email);
            } else {
                throw ValidationException::withMessages([
                    'email' => __('Email rate limit exceeded. Please try again later.'),
                ])->errorBag('addTeamMember');
            }

            return;
        }

        if ($rateLimiter->canSendToAddress($email)) {
            Notification::route('mail', $email)
                ->notify(new TeamInvitationRegistrationRequired($invitation));
            $rateLimiter->incrementLimits(null, $email);
        } else {
            throw ValidationException::withMessages([
                'email' => __('Email rate limit exceeded. Please try again later.'),
            ])->errorBag('addTeamMember');
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
        ], $this->rules(), [
            'email.required' => __('The email field is required.'),
            'email.email' => __('The email must be a valid email address.'),
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
    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
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
                __('This user already belongs to the :entity.', ['entity' => EntityLabel::singular()])
            );
        };
    }

    /**
     * Ensure that the roles are not at max capacity.
     */
    protected function ensureRolesAreNotAtMaxCapacity(Team $team, ?array $roles): Closure
    {
        return function ($validator) use ($team, $roles) {
            if (! $roles) {
                return;
            }

            foreach ($roles as $roleSlug) {
                $role = Role::where('slug', $roleSlug)->first();

                if ($role && $role->hasReachedMaxMembers($team->id)) {
                    $validator->errors()->add(
                        'roles',
                        __('The :role role has reached its maximum capacity of :max members.', [
                            'role' => $role->name,
                            'max' => $role->max_members,
                        ])
                    );
                }
            }
        };
    }
}
