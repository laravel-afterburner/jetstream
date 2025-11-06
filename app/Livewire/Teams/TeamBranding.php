<?php

namespace App\Livewire\Teams;

use App\Traits\InteractsWithBanner;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
class TeamBranding extends Component
{
    use InteractsWithBanner;
    use WithFileUploads;

    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * The branding form state.
     *
     * @var array
     */
    public $brandingForm = [
        'primary_color' => '',
        'secondary_color' => '',
    ];

    /**
     * The new logo for the team.
     *
     * @var mixed
     */
    public $logo;

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;
        $this->brandingForm['primary_color'] = $team->primary_color ?? '';
        $this->brandingForm['secondary_color'] = $team->secondary_color ?? '';
    }

    /**
     * Update the team's branding.
     *
     * @return void
     */
    public function updateBranding()
    {
        $this->resetErrorBag();

        if (! Gate::check('update', $this->team)) {
            return;
        }

        $this->validate([
            'brandingForm.primary_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'brandingForm.secondary_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB max
        ], [], [
            'brandingForm.primary_color' => 'primary color',
            'brandingForm.secondary_color' => 'secondary color',
            'logo' => 'logo',
        ]);

        // Handle logo upload
        $logoUrl = $this->team->logo_url;
        if ($this->logo) {
            // Delete old logo if it exists
            if ($this->team->logo_url && str_starts_with($this->team->logo_url, 'teams/')) {
                Storage::disk('public')->delete($this->team->logo_url);
            }

            // Store new logo
            $logoUrl = $this->logo->store('teams/' . $this->team->id, 'public');
        }

        // Update team branding
        $this->team->forceFill([
            'primary_color' => $this->brandingForm['primary_color'] ?: null,
            'secondary_color' => $this->brandingForm['secondary_color'] ?: null,
            'logo_url' => $logoUrl,
        ])->save();

        $this->team = $this->team->fresh();
        $this->logo = null;

        $this->dispatch('saved');
        $this->dispatch('team-branding-updated');
    }

    /**
     * Delete the team's logo.
     *
     * @return void
     */
    public function deleteLogo()
    {
        if (! Gate::check('update', $this->team)) {
            return;
        }

        if ($this->team->logo_url && str_starts_with($this->team->logo_url, 'teams/')) {
            Storage::disk('public')->delete($this->team->logo_url);
        }

        $this->team->forceFill([
            'logo_url' => null,
        ])->save();

        $this->team = $this->team->fresh();

        $this->dispatch('saved');
        $this->dispatch('team-branding-updated');
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
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('teams.team-branding');
    }
}
