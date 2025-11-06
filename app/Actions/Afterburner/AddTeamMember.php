<?php

namespace App\Actions\Afterburner;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Events\AddingTeamMember;
use App\Events\TeamMemberAdded;
use App\Support\Afterburner;
use Closure;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class AddTeamMember
{
    /**
     * Add a new member to the given entity.
     */
    public function add(User $user, Team $team, string $email, ?array $roles = null): void
    {
        Gate::forUser($user)->authorize('addTeamMember', $team);

        $this->validate($team, $email, $roles);

        $newMember = Afterburner::findUserByEmailOrFail($email);

        AddingTeamMember::dispatch($team, $newMember);

        $team->users()->attach($newMember);

        // Always assign the default role
        $defaultRole = Role::where('is_default', true)->first();
        if ($defaultRole) {
            $newMember->assignRole($defaultRole->slug, $team->id);
        }

        // Assign additional selected roles if provided
        if ($roles && is_array($roles)) {
            foreach ($roles as $roleSlug) {
                $newMember->assignRole($roleSlug, $team->id);
            }
        }

        TeamMemberAdded::dispatch($team, $newMember);
    }

    /**
     * Validate the add member operation.
     */
    protected function validate(Team $team, string $email, ?array $roles): void
    {
        Validator::make([
            'email' => $email,
            'roles' => $roles,
        ], $this->rules(), [
            'email.exists' => __('We were unable to find a registered user with this email address.'),
        ])->after(
            $this->ensureUserIsNotAlreadyInEntity($team, $email)
        )->validateWithBag('addTeamMember');
    }

    /**
     * Get the validation rules for adding a member.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users'],
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
}