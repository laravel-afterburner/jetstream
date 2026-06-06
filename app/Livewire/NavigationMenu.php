<?php

namespace App\Livewire;

use Afterburner\Support\EntityLabel;
use App\Models\User;
use App\Support\Afterburner;
use App\Support\RoleImpersonation;
use App\Support\NavigationActive;
use App\Support\Features;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
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

    public $isTeamSystemSettingsActive = false;

    public $isTeamActive = false;

    public $isDocumentsActive = false;

    /**
     * Mount the component.
     *
     * @return void
     */
    public function mount()
    {
        // Cache route checks so they persist across Livewire re-renders
        $this->isDashboardActive = NavigationActive::routeIs('dashboard');
        $this->isProfileActive = NavigationActive::routeIs('profile.show');
        $this->isSecurityActive = NavigationActive::routeIs('security.show');
        $this->isNotificationsActive = NavigationActive::routeIs('notifications');
        $this->isTeamsMembersActive = NavigationActive::routeIs('teams.members');
        $this->isTeamsInformationActive = NavigationActive::routeIs('teams.information');
        $this->isTeamsCreateActive = NavigationActive::routeIs('teams.create');
        $this->isTeamSystemSettingsActive = NavigationActive::routeIs('teams.system-settings');
        $this->isDocumentsActive = NavigationActive::routeIs('teams.documents.*');
        $this->isTeamActive = NavigationActive::routeIs('teams.information')
            || NavigationActive::routeIs('teams.members')
            || NavigationActive::routeIs('teams.system-settings')
            || NavigationActive::routeIs('teams.create');
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
     */
    #[On('team-name-updated')]
    #[Computed]
    public function currentTeamName(): string
    {
        if (! Features::hasTeamFeatures()) {
            return '';
        }

        if (! $this->user || ! $this->user->currentTeam) {
            return 'No '.EntityLabel::singularTitle();
        }

        return $this->user->currentTeam->name;
    }

    /**
     * Get the current team's logo URL.
     */
    #[On('team-branding-updated')]
    #[Computed]
    public function currentTeamLogoUrl(): string
    {
        if (! Features::hasTeamFeatures() || ! $this->user || ! $this->user->currentTeam) {
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
        if (! Features::hasTeamFeatures() || ! $this->user) {
            return collect();
        }

        $teams = $this->user->allTeams();

        if ($this->user->currentTeam && $teams->count() > 1) {
            $currentTeam = $this->user->currentTeam;
            $current = $teams->firstWhere('id', $currentTeam->id);
            $others = $teams->filter(fn ($t) => $t->id !== $currentTeam->id)->values();

            return $current ? collect([$current])->merge($others) : $teams;
        }

        return $teams;
    }

    /**
     * Get the unread notifications count.
     */
    #[On('refresh-notifications')]
    #[Computed]
    public function unreadNotificationsCount(): int
    {
        if (! $this->user) {
            return 0;
        }

        return $this->user->unreadNotifications->count();
    }

    /**
     * Get registered navigation items.
     *
     * @return \Illuminate\Support\Collection
     */
    #[Computed]
    public function navigationItems()
    {
        if (! class_exists(\App\Support\Navigation::class)) {
            return collect();
        }

        return \App\Support\Navigation::items();
    }

    /**
     * Toggle the mobile menu.
     *
     * @return void
     */
    public function toggleMobileMenu()
    {
        $this->mobileMenuOpen = ! $this->mobileMenuOpen;
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
        if (! Features::hasTeamFeatures() || ! Features::allowsTeamCreation()) {
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
     */
    #[Computed]
    public function isImpersonating(): bool
    {
        return Session::has('impersonating');
    }

    #[Computed]
    public function isImpersonatingRole(): bool
    {
        return RoleImpersonation::isActive();
    }

    #[Computed]
    public function isImpersonatingAny(): bool
    {
        return $this->isImpersonating || $this->isImpersonatingRole;
    }

    /**
     * Check if the current user is a system admin.
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
        if (! $this->isImpersonating) {
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
