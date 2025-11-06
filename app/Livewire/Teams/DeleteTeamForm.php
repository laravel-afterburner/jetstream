<?php

namespace App\Livewire\Teams;

use App\Support\Features;
use Illuminate\Support\Facades\Auth;
use App\Actions\Afterburner\ValidateTeamDeletion;
use App\Actions\Afterburner\DeleteTeam;
use App\Traits\RedirectsActions;
use Livewire\Component;

class DeleteTeamForm extends Component
{
    use RedirectsActions;

    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * Indicates if team deletion is being confirmed.
     *
     * @var bool
     */
    public $confirmingTeamDeletion = false;

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;
    }

    /**
     * Delete the team.
     *
     * @param  \App\Actions\Afterburner\ValidateTeamDeletion  $validator
     * @param  \App\Actions\Afterburner\DeleteTeam  $deleter
     * @return mixed
     */
    public function deleteTeam(ValidateTeamDeletion $validator, DeleteTeam $deleter)
    {
        $validator->validate(Auth::user(), $this->team);

        $teamName = $this->team->name;
        $deleter->delete($this->team);

        $this->team = null;

        // Flash success banner message for the redirect
        session()->flash('flash', [
            'bannerStyle' => 'success',
            'banner' => __('The :entity ":name" has been deleted.', [
                'entity' => config('afterburner.entity_label'),
                'name' => $teamName,
            ]),
        ]);

        return $this->redirectPath($deleter);
    }

    /**
     * Determine if this is the user's personal team.
     *
     * @return bool
     */
    public function getIsPersonalTeamProperty()
    {
        return Features::hasPersonalTeams() && $this->team->personal_team;
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('teams.delete-team-form');
    }
}
