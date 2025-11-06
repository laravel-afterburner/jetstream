<?php

namespace App\Livewire;

use App\Models\Team;
use App\Models\User;
use App\Support\Features;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

class Impersonation extends Component
{
    public $isOpen = false;
    public $selectedTeamId = null;
    public $searchQuery = '';
    public $searchUserQuery = '';

    #[On('open-impersonation-modal')]
    public function openModal()
    {
        $this->isOpen = true;
        $this->selectedTeamId = null;
        $this->searchQuery = '';
        $this->searchUserQuery = '';
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->selectedTeamId = null;
        $this->searchQuery = '';
        $this->searchUserQuery = '';
    }

    public function selectTeam($teamId)
    {
        $this->selectedTeamId = $teamId;
        $this->searchUserQuery = '';
    }

    public function backToTeams()
    {
        $this->selectedTeamId = null;
        $this->searchUserQuery = '';
    }

    #[Computed]
    public function teams()
    {
        // If teams feature is disabled, return empty collection
        if (!Features::hasTeamFeatures()) {
            return collect();
        }

        if (empty($this->searchQuery)) {
            return Team::orderBy('name')->limit(20)->get();
        }

        return Team::where('name', 'like', '%' . $this->searchQuery . '%')
            ->orderBy('name')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function users()
    {
        // If teams feature is disabled, query all users directly
        if (!Features::hasTeamFeatures()) {
            $query = User::query();

            if (!empty($this->searchUserQuery)) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->searchUserQuery . '%')
                      ->orWhere('email', 'like', '%' . $this->searchUserQuery . '%');
                });
            }

            return $query->orderBy('name')->limit(20)->get();
        }

        // Teams feature enabled - use team-based filtering
        if (!$this->selectedTeamId) {
            return collect();
        }

        $team = Team::find($this->selectedTeamId);
        if (!$team) {
            return collect();
        }

        $query = $team->users();

        if (!empty($this->searchUserQuery)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchUserQuery . '%')
                  ->orWhere('email', 'like', '%' . $this->searchUserQuery . '%');
            });
        }

        return $query->orderBy('name')->limit(20)->get();
    }

    public function render()
    {
        return view('system-admin.impersonation');
    }
}

