<?php

namespace App\Livewire\Teams;

use App\Actions\Afterburner\InviteTeamMember;
use App\Actions\Afterburner\RemoveTeamMember;
use App\Models\Role;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use App\Notifications\TeamInvitationRegistrationRequired;
use App\Support\Afterburner;
use App\Support\Features;
use App\Traits\InteractsWithBanner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;
use Livewire\Attributes\Computed;

class MemberManager extends Component
{
    use InteractsWithBanner;
    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * Indicates if a user's role is currently being managed.
     *
     * @var bool
     */
    public $currentlyManagingRole = false;

    /**
     * The user that is having their role managed.
     *
     * @var mixed
     */
    public $managingRoleFor;

    /**
     * The currently selected additional roles.
     *
     * @var array
     */
    public $selectedRoles = [];

    /**
     * Indicates if the application is confirming if a user wishes to leave the current team.
     *
     * @var bool
     */
    public $confirmingLeavingTeam = false;

    /**
     * Indicates if the application is confirming if a team member should be removed.
     *
     * @var bool
     */
    public $confirmingTeamMemberRemoval = false;

    /**
     * The ID of the team member being removed.
     *
     * @var int|null
     */
    public $teamMemberIdBeingRemoved = null;

    /**
     * Indicates if the application is confirming if a team invitation should be canceled.
     *
     * @var bool
     */
    public $confirmingInvitationCancellation = false;

    /**
     * The ID of the team invitation being canceled.
     *
     * @var int|null
     */
    public $invitationIdBeingCanceled = null;

    /**
     * The "add team member" form state.
     *
     * @var array
     */
    public $addTeamMemberForm = [
        'email' => '',
        'roles' => [],
    ];

    /**
     * Indicates if the permission details modal is open.
     *
     * @var bool
     */
    public $showingPermissionDetails = false;

    /**
     * The name of the role being viewed.
     *
     * @var string|null
     */
    public $viewingBadgeName = null;

    /**
     * The permissions for the role being viewed.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public $viewingPermissions = null;

    /**
     * Indicates if the member permissions modal is open.
     *
     * @var bool
     */
    public $showingMemberPermissions = false;

    /**
     * The user whose permissions are being viewed.
     *
     * @var mixed
     */
    public $viewingMemberPermissions = null;

    /**
     * The permissions grouped by roles for the member being viewed.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public $memberPermissionsByRole = null;

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;
        
        // Initialize invitation form with default role selected
        $defaultRole = Role::where('is_default', true)->first();
        if ($defaultRole) {
            $this->addTeamMemberForm['roles'] = [$defaultRole->slug];
        }
    }

    /**
     * Add a new team member to a team.
     *
     * @return void
     */
    public function addTeamMember()
    {
        $this->resetErrorBag();

        app(InviteTeamMember::class)->invite(
            $this->user,
            $this->team,
            $this->addTeamMemberForm['email'],
            $this->addTeamMemberForm['roles']
        );

        // Reset form but keep default role selected
        $defaultRole = Role::where('is_default', true)->first();
        $this->addTeamMemberForm = [
            'email' => '',
            'roles' => $defaultRole ? [$defaultRole->slug] : [],
        ];

        $this->team = $this->team->fresh();

        $this->dispatch('saved');
    }

    /**
     * Cancel a pending team member invitation.
     *
     * @param  int  $invitationId
     * @return void
     */
    public function cancelTeamInvitation($invitationId)
    {
        if (! empty($invitationId)) {
            $model = Afterburner::teamInvitationModel();

            $model::whereKey($invitationId)->delete();
        }

        $this->confirmingInvitationCancellation = false;
        $this->invitationIdBeingCanceled = null;
        $this->team = $this->team->fresh();

        $this->banner(__('Team invitation canceled.'));
    }

    /**
     * Resend a declined team invitation.
     *
     * @param  int  $invitationId
     * @return void
     */
    public function resendTeamInvitation($invitationId)
    {
        if (! empty($invitationId)) {
            $invitation = Afterburner::teamInvitationModel()::findOrFail($invitationId);
            
            // Reset the declined status
            $invitation->update(['declined_at' => null]);
            
            // Send new notification
            $existingUser = User::where('email', $invitation->email)->first();
            if ($existingUser) {
                $existingUser->notify(new TeamInvitationNotification($invitation));
            } else {
                Notification::route('mail', $invitation->email)
                    ->notify(new TeamInvitationRegistrationRequired($invitation));
            }
        }

        $this->team = $this->team->fresh();
        $this->dispatch('saved');
    }

    /**
     * Delete a declined team invitation.
     *
     * @param  int  $invitationId
     * @return void
     */
    public function deleteDeclinedInvitation($invitationId)
    {
        if (! empty($invitationId)) {
            $model = Afterburner::teamInvitationModel();
            $model::whereKey($invitationId)->delete();
        }

        $this->team = $this->team->fresh();
        $this->dispatch('saved');
    }

    /**
     * Confirm that the given team invitation should be canceled.
     *
     * @param  int  $invitationId
     * @return void
     */
    public function confirmInvitationCancellation($invitationId)
    {
        $this->confirmingInvitationCancellation = true;
        $this->invitationIdBeingCanceled = $invitationId;
    }

    /**
     * Allow the given user's role to be managed.
     *
     * @param  int  $userId
     * @return void
     */
    public function manageRole($userId)
    {
        if (! Auth::user()->can('updateTeamMember', $this->team)) {
            return;
        }

        $this->currentlyManagingRole = true;
        $this->managingRoleFor = Afterburner::findUserByIdOrFail($userId);
        
        // Get current roles for this user (including default role)
        $this->selectedRoles = $this->managingRoleFor->roles()
            ->where('team_id', $this->team->id)
            ->pluck('slug')
            ->toArray();
    }

    /**
     * Stop managing the role of a given user.
     *
     * @return void
     */
    public function stopManagingRole()
    {
        $this->currentlyManagingRole = false;
        $this->managingRoleFor = null;
        $this->selectedRoles = [];
    }

    /**
     * Toggle a role selection.
     *
     * @param  string  $roleKey
     * @return void
     */
    public function toggleRole($roleKey)
    {
        // Get the role to check if it's the default role or at max capacity
        $role = Role::where('slug', $roleKey)->first();
        
        // Don't allow unchecking the default role
        if ($role && $role->is_default && in_array($roleKey, $this->selectedRoles)) {
            return;
        }
        
        // Don't allow selecting roles that are at max capacity (unless it's the default role)
        if ($role && $role->hasReachedMaxMembers($this->team->id) && !$role->is_default && !in_array($roleKey, $this->selectedRoles)) {
            return;
        }
        
        if (in_array($roleKey, $this->selectedRoles)) {
            $this->selectedRoles = array_values(array_diff($this->selectedRoles, [$roleKey]));
        } else {
            $this->selectedRoles[] = $roleKey;
        }
    }

    /**
     * Toggle a role selection for the invitation form.
     *
     * @param  string  $roleKey
     * @return void
     */
    public function toggleInvitationRole($roleKey)
    {
        // Get the role to check if it's at max capacity
        $role = Role::where('slug', $roleKey)->first();
        
        // Don't allow selecting roles that are at max capacity (unless it's the default role)
        if ($role && $role->hasReachedMaxMembers($this->team->id) && !$role->is_default) {
            return;
        }
        
        if (in_array($roleKey, $this->addTeamMemberForm['roles'])) {
            $this->addTeamMemberForm['roles'] = array_values(array_diff($this->addTeamMemberForm['roles'], [$roleKey]));
        } else {
            $this->addTeamMemberForm['roles'][] = $roleKey;
        }
    }

    /**
     * Save the roles for the user being managed.
     *
     * @return void
     */
    public function updateRole()
    {
        if (! $this->managingRoleFor) {
            return;
        }

        // Get all current roles
        $currentRoles = $this->managingRoleFor->roles()
            ->where('team_id', $this->team->id)
            ->pluck('slug')
            ->toArray();

        // Get the default role
        $defaultRole = Role::where('is_default', true)->first();
        
        // Ensure default role is always in selected roles
        if ($defaultRole && !in_array($defaultRole->slug, $this->selectedRoles)) {
            $this->selectedRoles[] = $defaultRole->slug;
        }

        // Remove roles that are no longer selected (except default role)
        foreach ($currentRoles as $roleSlug) {
            if (!in_array($roleSlug, $this->selectedRoles)) {
                $role = Role::where('slug', $roleSlug)->first();
                // Don't remove the default role
                if (!$role || !$role->is_default) {
                    $this->managingRoleFor->removeRole($roleSlug, $this->team->id);
                }
            }
        }

        // Add newly selected roles
        foreach ($this->selectedRoles as $roleSlug) {
            if (!in_array($roleSlug, $currentRoles)) {
                $this->managingRoleFor->assignRole($roleSlug, $this->team->id);
            }
        }

        $this->team = $this->team->fresh();
        $this->stopManagingRole();

        $this->banner(__('Member roles updated.'));
    }

    /**
     * Remove the currently authenticated user from the team.
     *
     * @param  \App\Actions\Afterburner\RemoveTeamMember  $remover
     * @return \Illuminate\Http\RedirectResponse
     */
    public function leaveTeam(RemoveTeamMember $remover)
    {
        $teamName = $this->team->name;

        $remover->remove(
            $this->user,
            $this->team,
            $this->user
        );

        $this->confirmingLeavingTeam = false;

        $this->team = $this->team->fresh();

        // Flash success banner message for the redirect
        session()->flash('flash', [
            'bannerStyle' => 'success',
            'banner' => __('You have left :name.', [
                'name' => $teamName,
            ]),
        ]);

        return redirect(config('fortify.home'));
    }

    /**
     * Confirm that the given team member should be removed.
     *
     * @param  int  $userId
     * @return void
     */
    public function confirmTeamMemberRemoval($userId)
    {
        $this->confirmingTeamMemberRemoval = true;
        $this->teamMemberIdBeingRemoved = $userId;
    }

    /**
     * Remove a team member from the team.
     *
     * @param  \App\Actions\Afterburner\RemoveTeamMember  $remover
     * @return void
     */
    public function removeTeamMember(RemoveTeamMember $remover)
    {
        $user = Afterburner::findUserByIdOrFail($this->teamMemberIdBeingRemoved);
        $userName = $user->name;

        $remover->remove(
            $this->user,
            $this->team,
            $user
        );

        $this->confirmingTeamMemberRemoval = false;
        $this->teamMemberIdBeingRemoved = null;
        $this->team = $this->team->fresh();

        $this->banner(__(':name has been removed from the team.', [
            'name' => $userName,
        ]));
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return Auth::user();
    }

    /**
     * Get the available roles (including the default role).
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRolesProperty()
    {
        return Role::orderBy('hierarchy')
            ->get()
            ->map(function ($role) {
                $isAtMaxCapacity = $role->hasReachedMaxMembers($this->team->id);
                $availableSlots = $role->getAvailableSlots($this->team->id);
                
                return (object) [
                    'key' => $role->slug,
                    'name' => $role->name,
                    'description' => $role->description,
                    'is_default' => $role->is_default,
                    'is_at_max_capacity' => $isAtMaxCapacity,
                    'available_slots' => $availableSlots,
                    'max_members' => $role->max_members,
                ];
            });
    }

    /**
     * Get non-default roles for a user in the current team.
     *
     * @param  mixed  $user
     * @return \Illuminate\Support\Collection
     */
    public function getUserDisplayRoles($user)
    {
        return $user->roles()
            ->where('team_id', $this->team->id)
            ->where('is_default', false)
            ->orderBy('hierarchy')
            ->get();
    }

    /**
     * Get the badge color class for a role.
     */
    public function getRoleBadgeColor($roleSlug)
    {
        $storedValue = Role::where('slug', $roleSlug)->value('badge_color');

        // Default classes if nothing stored
        $default = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';

        if (! $storedValue) {
            return $default;
        }

        // Otherwise treat as palette key and resolve via config
        $classes = config("badge-colors.options.$storedValue.classes");
        return $classes ?: $default;
    }

    /**
     * Get the role name from a role slug.
     */
    public function getRoleName($roleSlug)
    {
        if (!$roleSlug) {
            return null;
        }

        return Role::where('slug', $roleSlug)->value('name');
    }

    /**
     * Get the icon path for a role.
     */
    public function getRoleIcon($roleSlug)
    {
        // First try to get the icon from the database
        $role = Role::where('slug', $roleSlug)->first();
        if ($role && $role->icon) {
            return $role->icon;
        }

        // Fallback to the old hardcoded mapping for backward compatibility
        $iconMap = [
            'president' => 'leader.svg',
            'vice_president' => 'deputy.svg',
            'treasurer' => 'finance.svg',
            'secretary' => 'records.svg',
            'council_member' => 'governance.svg',
            'strata_member' => 'member.svg',
        ];

        return $iconMap[$roleSlug] ?? 'member.svg';
    }

    /**
     * Show permissions for a role.
     *
     * @param  int|string  $roleIdentifier - Can be role ID (int) or role slug (string)
     * @return void
     */
    public function showRolePermissions($roleIdentifier)
    {
        // Check if it's a numeric ID or a string slug
        if (is_numeric($roleIdentifier)) {
            $role = Role::with('permissions')->findOrFail($roleIdentifier);
        } else {
            $role = Role::with('permissions')->where('slug', $roleIdentifier)->firstOrFail();
        }
        
        $this->viewingBadgeName = $role->name;
        $this->viewingPermissions = $role->permissions;
        $this->showingPermissionDetails = true;
    }

    /**
     * Close the permission details modal.
     *
     * @return void
     */
    public function closePermissionDetails()
    {
        $this->showingPermissionDetails = false;
        $this->viewingBadgeName = null;
        $this->viewingPermissions = null;
    }

    /**
     * Show permissions for a team member.
     *
     * @param  int  $userId
     * @return void
     */
    public function showMemberPermissions($userId)
    {
        $user = User::findOrFail($userId);
        
        // Get all roles for this user in the current team
        $userRoles = $user->roles()
            ->where('team_id', $this->team->id)
            ->with('permissions')
            ->orderBy('hierarchy')
            ->get();
        
        // Group permissions by role
        $permissionsByRole = $userRoles->map(function($role) {
            return [
                'role' => $role,
                'permissions' => $role->permissions
            ];
        });
        
        $this->viewingMemberPermissions = $user;
        $this->memberPermissionsByRole = $permissionsByRole;
        $this->showingMemberPermissions = true;
    }

    /**
     * Close the member permissions modal.
     *
     * @return void
     */
    public function closeMemberPermissions()
    {
        $this->showingMemberPermissions = false;
        $this->viewingMemberPermissions = null;
        $this->memberPermissionsByRole = null;
    }

    /**
     * Get team invitations with user information eagerly loaded.
     *
     * @return \Illuminate\Support\Collection
     */
    #[Computed]
    public function teamInvitationsWithUsers()
    {
        $invitations = $this->team->teamInvitations;
        $emails = $invitations->pluck('email')->unique();
        
        // Single query to get all users at once
        $users = User::whereIn('email', $emails)->get()->keyBy('email');
        
        // Attach user to each invitation
        return $invitations->map(function($invitation) use ($users) {
            $invitation->invited_user = $users->get($invitation->email);
            return $invitation;
        });
    }

    /**
     * Get user information for an invitation email if the user exists.
     *
     * @param string $email
     * @return \App\Models\User|null
     */
    public function getInvitedUser($email)
    {
        return User::where('email', $email)->first();
    }

    /**
     * Check if the current user can leave the team.
     *
     * @return bool
     */
    public function canLeaveTeam()
    {
        $user = $this->getUserProperty();
        
        // Can't leave if you're the only member
        if ($this->team->users()->count() <= 1) {
            return false;
        }
        
        // Can't leave if you're the owner (need to assign new owner first)
        if ($user->ownsTeam($this->team)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get the reason why the user cannot leave the team.
     *
     * @return string|null
     */
    public function getCannotLeaveReason()
    {
        $user = $this->getUserProperty();
        
        if ($this->team->users()->count() <= 1) {
            return 'You cannot leave the ' . config('afterburner.entity_label') . ' as you are the only member. Add other members first, or delete the ' . config('afterburner.entity_label') . '.';
        }
        
        if ($user->ownsTeam($this->team)) {
            return 'You cannot leave the ' . config('afterburner.entity_label') . ' that you own, you must first assign a new owner.';
        }
        
        return null;
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    /**
     * Get team users sorted by hierarchy.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSortedTeamUsersProperty()
    {
        return $this->team->users()
            ->with(['roles' => function($query) {
                $query->where('team_id', $this->team->id)
                    ->orderBy('hierarchy')
                    ->with('permissions');
            }])
            ->get()
            ->sortBy(function($user) {
                $highestRole = $user->roles->first();
                return $highestRole ? $highestRole->hierarchy : 999;
            })
            ->values();
    }

    public function render()
    {
        return view('teams.member-manager');
    }
}