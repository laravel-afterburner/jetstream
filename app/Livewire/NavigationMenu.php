<?php

namespace App\Livewire;

use App\Models\User;
use App\Support\Afterburner;
use App\Support\Features;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Str;

class NavigationMenu extends Component
{
    /**
     * Indicates if the mobile menu is open.
     *
     * @var bool
     */
    public $mobileMenuOpen = false;

    /**
     * Route active states cached in mount.
     *
     * @var bool
     */
    public $isDashboardActive = false;
    public $isProfileActive = false;
    public $isSecurityActive = false;
    public $isNotificationsActive = false;
    public $isTeamsMembersActive = false;
    public $isTeamsInformationActive = false;
    public $isTeamsCreateActive = false;

    /**
     * Mount the component.
     *
     * @return void
     */
    public function mount()
    {
        // Cache route checks so they persist across re-renders
        $this->isDashboardActive = request()->routeIs('dashboard');
        $this->isProfileActive = request()->routeIs('profile.show');
        $this->isSecurityActive = request()->routeIs('security.show');
        $this->isNotificationsActive = request()->routeIs('notifications');
        $this->isTeamsMembersActive = request()->routeIs('teams.members');
        $this->isTeamsInformationActive = request()->routeIs('teams.information');
        $this->isTeamsCreateActive = request()->routeIs('teams.create');
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    #[On('refresh-navigation-menu')]
    #[Computed]
    public function user()
    {
        return Auth::user();
    }

    /**
     * Get the current team name.
     *
     * @return string
     */
    #[On('team-name-updated')]
    #[Computed]
    public function currentTeamName(): string
    {
        if (!Features::hasTeamFeatures()) {
            return '';
        }

        if (!$this->user || !$this->user->currentTeam) {
            return 'No '. Str::title(config('afterburner.entity_label'));
        }
        
        return $this->user->currentTeam->name;
    }

    /**
     * Get the current team's logo URL.
     *
     * @return string
     */
    #[On('team-branding-updated')]
    #[Computed]
    public function currentTeamLogoUrl(): string
    {
        if (!Features::hasTeamFeatures() || !$this->user || !$this->user->currentTeam) {
            return asset('media/logo.png');
        }
        
        $team = $this->user->currentTeam;
        return $team->getLogoUrl();
    }

    /**
     * Refresh navigation menu when team branding is updated.
     * This forces a re-render so the Blade view gets updated $teamBranding from middleware.
     *
     * @return void
     */
    #[On('team-branding-updated')]
    public function refreshBranding()
    {
        // Refresh the user's currentTeam relationship to get updated branding
        if ($this->user && $this->user->currentTeam) {
            // Clear the relationship cache first
            $this->user->unsetRelation('currentTeam');
            // Reload the relationship to get fresh data
            $this->user->load('currentTeam');
        }
        
        // Get the new logo URL after refreshing the relationship
        $team = $this->user?->currentTeam;
        $newLogoUrl = $team ? $team->getLogoUrl() : asset('media/logo.png');
        
        // Dispatch browser event with the new logo URL
        $this->dispatch('team-branding-changed', ['logoUrl' => $newLogoUrl]);
    }

    /**
     * Get all teams for the current user.
     *
     * @return \Illuminate\Support\Collection
     */
    #[Computed]
    public function allTeams()
    {
        if (!Features::hasTeamFeatures() || !$this->user) {
            return collect();
        }
        return $this->user->allTeams();
    }

    /**
     * Get the unread notifications count.
     *
     * @return int
     */
    #[On('refresh-notifications')] 
    #[Computed]
    public function unreadNotificationsCount(): int
    {
        if (!$this->user) {
            return 0;
        }
        return $this->user->unreadNotifications->count();
    }

    /**
     * Get the unread announcements count.
     *
     * @return int
     */
    #[On('refresh-navigation-menu')]
    #[Computed]
    public function unreadAnnouncementsCount(): int
    {
        if (!$this->user || !Features::hasTeamAnnouncements()) {
            return 0;
        }
        
        if (!$this->user->currentTeam) {
            return 0;
        }
        
        return \App\Models\TeamAnnouncement::getUnreadCountForUser($this->user);
    }

    /**
     * Toggle the mobile menu.
     *
     * @return void
     */
    public function toggleMobileMenu()
    {
        $this->mobileMenuOpen = !$this->mobileMenuOpen;
    }

    /**
     * Close the mobile menu.
     *
     * @return void
     */
    public function closeMobileMenu()
    {
        $this->mobileMenuOpen = false;
    }

    /**
     * Check if the user can create teams.
     *
     * @return bool
     */
    public function canCreateTeam()
    {
        if (!Features::hasTeamFeatures()) {
            return false;
        }
        return $this->user && $this->user->can('create', Afterburner::newTeamModel());
    }

    /**
     * Check if Afterburner manages profile photos.
     *
     * @return bool
     */
    public function managesProfilePhotos()
    {
        return Afterburner::managesProfilePhotos();
    }

    /**
     * Check if currently impersonating a user.
     *
     * @return bool
     */
    #[Computed]
    public function isImpersonating(): bool
    {
        return Session::has('impersonating');
    }

    /**
     * Check if the current user is a system admin.
     *
     * @return bool
     */
    #[Computed]
    public function isSystemAdmin(): bool
    {
        return $this->user && $this->user->isSystemAdmin();
    }

    /**
     * Get the impersonated user.
     *
     * @return mixed
     */
    #[Computed]
    public function impersonatedUser()
    {
        if (!$this->isImpersonating) {
            return null;
        }

        $userId = Session::get('impersonated_user_id');
        return $userId ? User::find($userId) : null;
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('navigation-menu');
    }
}