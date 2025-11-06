<?php

namespace App\Livewire\Teams;

use App\Traits\InteractsWithBanner;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class TeamInformation extends Component
{
    use InteractsWithBanner;
    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * The "update team name" form state.
     *
     * @var array
     */
    public $updateTeamNameForm = [
        'name' => '',
    ];

    /**
     * The "update team owner" form state.
     *
     * @var array
     */
    public $updateTeamOwnerForm = [
        'user_id' => null,
    ];

    /**
     * The original owner ID when the component was mounted.
     *
     * @var int|null
     */
    public $originalOwnerId;

    /**
     * Indicates if the application is confirming team owner change.
     *
     * @var bool
     */
    public $confirmingTeamOwnerChange = false;

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;
        $this->updateTeamNameForm['name'] = $team->name;
        $this->originalOwnerId = $team->user_id;
        $this->updateTeamOwnerForm['user_id'] = $team->user_id;
    }

    /**
     * Update the team's name.
     *
     * @return void
     */
    public function updateTeamName()
    {
        $this->resetErrorBag();

        if (! Gate::check('update', $this->team)) {
            return;
        }

        $this->validate([
            'updateTeamNameForm.name' => ['required', 'string', 'max:255'],
        ], [], [
            'updateTeamNameForm.name' => 'team name',
        ]);

        $this->team->forceFill([
            'name' => $this->updateTeamNameForm['name'],
        ])->save();

        $this->team = $this->team->fresh();

        $this->dispatch('saved');
        $this->dispatch('team-name-updated');
    }

    /**
     * Update the team's owner.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTeamOwner()
    {
        $this->resetErrorBag();

        if (! Gate::check('changeOwner', $this->team)) {
            return;
        }

        // Validate that the selected user is a team member
        if ($this->updateTeamOwnerForm['user_id']) {
            $isTeamMember = $this->team->users()->where('users.id', $this->updateTeamOwnerForm['user_id'])->exists();
            if (!$isTeamMember) {
                $this->addError('updateTeamOwnerForm.user_id', 'The selected user must be a team member.');
                return;
            }
        }

        $newOwner = User::find($this->updateTeamOwnerForm['user_id']);
        $newOwnerName = $newOwner ? $newOwner->name : 'Unknown';

        $this->team->forceFill([
            'user_id' => $this->updateTeamOwnerForm['user_id'],
        ])->save();

        $this->team = $this->team->fresh();
        $this->originalOwnerId = $this->team->user_id;
        $this->confirmingTeamOwnerChange = false;
        
        // Store banner message in session so it persists after redirect
        session()->flash('flash', [
            'bannerStyle' => 'success',
            'banner' => __('Team owner changed to :name.', ['name' => $newOwnerName]),
        ]);
        
        // Redirect back to refresh all components and re-evaluate conditional rendering
        return redirect()->route('teams.information', $this->team);
    }

    /**
     * Confirm the team owner change.
     *
     * @return void
     */
    public function confirmTeamOwnerChange()
    {
        $this->confirmingTeamOwnerChange = true;
    }

    /**
     * Cancel the team owner change.
     *
     * @return void
     */
    public function cancelTeamOwnerChange()
    {
        $this->confirmingTeamOwnerChange = false;
        $this->updateTeamOwnerForm['user_id'] = $this->originalOwnerId;
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
     * Get the team members for owner selection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTeamMembersProperty()
    {
        return $this->team->users()
            ->with(['roles' => function($query) {
                $query->where('team_id', $this->team->id)->orderBy('hierarchy');
            }])
            ->get()
            ->sortBy(function($user) {
                $highestRole = $user->roles->first();
                return $highestRole ? $highestRole->hierarchy : 999;
            })
            ->values();
    }

    /**
     * Check if there are other users available for owner selection.
     *
     * @return bool
     */
    public function getHasOtherUsersProperty()
    {
        return $this->teamMembers->count() > 1;
    }

    /**
     * Get the reason why the team owner cannot be changed.
     *
     * @return string|null
     */
    public function getCannotChangeOwnerReason()
    {
        if ($this->teamMembers->count() <= 1) {
            return 'You cannot change the team owner as there are no other team members. Add other members first.';
        }
        
        return null;
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('teams.team-information');
    }
}
