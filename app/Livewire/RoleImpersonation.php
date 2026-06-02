<?php

namespace App\Livewire;

use App\Models\Team;
use App\Support\Features;
use App\Support\TeamRolePermissions;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class RoleImpersonation extends Component
{
    public $isOpen = false;

    public $selectedTeamId = null;

    public $searchQuery = '';

    public $searchRoleQuery = '';

    #[On('open-role-impersonation-modal')]
    public function openModal(): void
    {
        $this->isOpen = true;
        $this->selectedTeamId = null;
        $this->searchQuery = '';
        $this->searchRoleQuery = '';
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->selectedTeamId = null;
        $this->searchQuery = '';
        $this->searchRoleQuery = '';
    }

    public function selectTeam($teamId): void
    {
        $this->selectedTeamId = $teamId;
        $this->searchRoleQuery = '';
    }

    public function backToTeams(): void
    {
        $this->selectedTeamId = null;
        $this->searchRoleQuery = '';
    }

    #[Computed]
    public function teams()
    {
        if (! Features::hasTeamFeatures()) {
            return collect();
        }

        if (empty($this->searchQuery)) {
            return Team::orderBy('name')->limit(20)->get();
        }

        return Team::where('name', 'like', '%'.$this->searchQuery.'%')
            ->orderBy('name')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function roles()
    {
        if (! Features::hasTeamFeatures()) {
            return TeamRolePermissions::rolesForTeam(null);
        }

        if (! $this->selectedTeamId) {
            return collect();
        }

        $roles = TeamRolePermissions::rolesForTeam($this->selectedTeamId);

        if (! empty($this->searchRoleQuery)) {
            $needle = strtolower($this->searchRoleQuery);
            $roles = $roles->filter(function ($role) use ($needle) {
                return str_contains(strtolower($role->name), $needle)
                    || str_contains(strtolower($role->slug), $needle);
            });
        }

        return $roles->values();
    }

    public function render()
    {
        return view('system-admin.role-impersonation');
    }
}
