<nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Entity Dropdown -->
                @if(App\Support\Afterburner::hasTeamFeatures())
                    <div class="ms-3 relative">
                        <x-dropdown align="right" width="60">
                            <x-slot name="trigger">
                                <span class="inline-flex rounded-md relative">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                        {{ $this->currentTeamName() }}

                                        <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                        </svg>
                                    </button>
                                    
                                    <!-- Announcements badge -->
                                    @if($this->unreadAnnouncementsCount > 0)
                                        <span class="absolute -top-1 -left-1 flex items-center justify-center h-4 w-4 bg-red-500 text-white text-xs font-bold rounded-full border-2 border-white dark:border-gray-800">
                                            {{ $this->unreadAnnouncementsCount > 9 ? '9+' : $this->unreadAnnouncementsCount }}
                                        </span>
                                    @endif
                                </span>
                            </x-slot>

                            <x-slot name="content">
                                <div class="w-60">
                                    <!-- Entity Management -->
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        {{ $this->currentTeamName() }}
                                    </div>

                                    <!-- Entity Settings -->
                                    @if($this->user->currentTeam)
                                        <x-dropdown-link href="{{ route('teams.information', $this->user->currentTeam->id) }}" :active="request()->routeIs('teams.information')">
                                            Details
                                        </x-dropdown-link>

                                        <x-dropdown-link href="{{ route('teams.members', $this->user->currentTeam->id) }}" :active="request()->routeIs('teams.members')">
                                            Members
                                        </x-dropdown-link>

                                        @if(App\Support\Features::hasTeamAnnouncements())
                                            <x-dropdown-link href="{{ route('team-announcements.index', $this->user->currentTeam->id) }}" :active="request()->routeIs('team-announcements.index')">
                                                <div class="flex items-center">
                                                    <span>Announcements</span>
                                                    @if($this->unreadAnnouncementsCount > 0)
                                                        <span class="ml-2 inline-flex items-center justify-center h-4 w-4 bg-red-500 text-white text-xs font-bold rounded-full">
                                                            {{ $this->unreadAnnouncementsCount > 9 ? '9+' : $this->unreadAnnouncementsCount }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </x-dropdown-link>
                                        @endif
                                    @endif

                                    <!-- Entity Switcher -->
                                    @if ($this->allTeams->count() > 1)
                                        <div class="border-t border-gray-200 dark:border-gray-600"></div>

                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Switch {{ Str::plural(Str::title(config('afterburner.entity_label'))) }}
                                        </div>

                                        @foreach ($this->allTeams as $team)
                                            <x-switchable-team :team="$team" />
                                        @endforeach
                                    @endif

                                    @if($this->canCreateTeam())
                                        <div class="border-t border-gray-200 dark:border-gray-600"></div>

                                        <x-dropdown-link href="{{ route('teams.create') }}" :active="request()->routeIs('teams.create')">
                                            Create {{ Str::title(config('afterburner.entity_label')) }}
                                        </x-dropdown-link>
                                    @endif
                                </div>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endif

                <!-- Settings Dropdown -->
                <div class="ms-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if ($this->managesProfilePhotos())
                                <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                    <div class="shrink-0 relative">
                                        <!-- Colored ring container when notifications exist -->
                                        @if($this->unreadNotificationsCount > 0)
                                            <div class="absolute -inset-1 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 animate-pulse"></div>
                                            <img class="relative size-8 rounded-full object-cover border-2 border-white dark:border-gray-800" 
                                                 src="{{ $this->user->profile_photo_url }}" 
                                                 alt="{{ $this->user->name }}" />
                                        @else
                                            <img class="size-8 rounded-full object-cover" 
                                                 src="{{ $this->user->profile_photo_url }}" 
                                                 alt="{{ $this->user->name }}" />
                                        @endif
                                        
                                        <!-- Notification badge -->
                                        @if($this->unreadNotificationsCount > 0)
                                            <span class="absolute -bottom-1 -right-1 flex items-center justify-center h-4 w-4 bg-red-500 text-white text-xs font-bold rounded-full border-2 border-white dark:border-gray-800">
                                                {{ $this->unreadNotificationsCount > 9 ? '9+' : $this->unreadNotificationsCount }}
                                            </span>
                                        @endif
                                    </div>
                                </button>
                            @else
                                <span class="inline-flex rounded-md relative">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                        {{ $this->user->name }}

                                        <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                    
                                    <!-- Notification badge when profile photos are disabled -->
                                    @if($this->unreadNotificationsCount > 0)
                                        <span class="absolute -top-1 -left-1 flex items-center justify-center h-4 w-4 bg-red-500 text-white text-xs font-bold rounded-full border-2 border-white dark:border-gray-800">
                                            {{ $this->unreadNotificationsCount > 9 ? '9+' : $this->unreadNotificationsCount }}
                                        </span>
                                    @endif
                                </span>
                            @endif
                        </x-slot>

                        <x-slot name="content">
                            <!-- Account Management -->
                            <div class="block px-4 py-2 text-xs text-gray-400">
                                {{ __('Manage Account') }}
                            </div>

                            <x-dropdown-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <x-dropdown-link href="{{ route('security.show') }}" :active="request()->routeIs('security.show')">
                                {{ __('Security') }}
                            </x-dropdown-link>

                            <x-dropdown-link href="{{ route('notifications') }}" :active="request()->routeIs('notifications')">
                                <div class="flex items-center">
                                    <span>{{ __('Notifications') }}</span>
                                    @if($this->unreadNotificationsCount > 0)
                                        <span class="ml-2 inline-flex items-center justify-center h-4 w-4 bg-red-500 text-white text-xs font-bold rounded-full">
                                            {{ $this->unreadNotificationsCount > 9 ? '9+' : $this->unreadNotificationsCount }}
                                        </span>
                                    @endif
                                </div>
                            </x-dropdown-link>

                            <!-- System Administration -->
                            @if($this->isImpersonating)
                                <div class="border-t border-gray-200 dark:border-gray-600"></div>
                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    {{ __('System Administration') }}
                                </div>
                                <form method="POST" action="{{ route('impersonate.stop') }}" x-data>
                                    @csrf
                                    <x-dropdown-link href="{{ route('impersonate.stop') }}"
                                             @click.prevent="$root.submit();" class="text-orange-600 dark:text-orange-400">
                                        {{ __('Stop Impersonating') }}
                                    </x-dropdown-link>
                                </form>
                            @elseif($this->isSystemAdmin)
                                <div class="border-t border-gray-200 dark:border-gray-600"></div>
                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    {{ __('System Administration') }}
                                </div>
                                <x-dropdown-link href="#" wire:click="$dispatch('open-impersonation-modal')" class="text-orange-600 dark:text-orange-400">
                                    {{ __('Impersonate User') }}
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('audit.index') }}">
                                    {{ __('Audit Logs') }}
                                </x-dropdown-link>
                            @endif

                            <div class="border-t border-gray-200 dark:border-gray-600"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf

                                <x-dropdown-link href="{{ route('logout') }}"
                                         @click.prevent="$root.submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button wire:click="toggleMobileMenu" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path x-show="!$wire.mobileMenuOpen" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="$wire.mobileMenuOpen" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div class="sm:hidden" x-show="$wire.mobileMenuOpen" x-transition>
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="$this->isDashboardActive">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="flex items-center px-4">
                @if ($this->managesProfilePhotos())
                    <div class="shrink-0 me-3 relative">
                        <!-- Colored ring container when notifications exist -->
                        @if($this->unreadNotificationsCount > 0)
                            <div class="absolute -inset-1 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 animate-pulse"></div>
                            <img class="relative size-10 rounded-full object-cover border-2 border-white dark:border-gray-800" 
                                 src="{{ $this->user->profile_photo_url }}" 
                                 alt="{{ $this->user->name }}" />
                        @else
                            <img class="size-10 rounded-full object-cover" 
                                 src="{{ $this->user->profile_photo_url }}" 
                                 alt="{{ $this->user->name }}" />
                        @endif
                        
                        <!-- Notification badge (Q-tail position - bottom right) -->
                        @if($this->unreadNotificationsCount > 0)
                            <span class="absolute -bottom-1 -right-1 flex items-center justify-center h-5 w-5 bg-red-500 text-white text-xs font-bold rounded-full border-2 border-white dark:border-gray-800">
                                {{ $this->unreadNotificationsCount > 9 ? '9+' : $this->unreadNotificationsCount }}
                            </span>
                        @endif
                    </div>
                @endif

                <div class="relative flex-1">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200 relative inline-block">
                        {{ $this->user->name }}
                        
                        <!-- Notification badge when profile photos are disabled -->
                        @if(!$this->managesProfilePhotos() && $this->unreadNotificationsCount > 0)
                            <span class="absolute -top-1 -left-1 flex items-center justify-center h-5 w-5 bg-red-500 text-white text-xs font-bold rounded-full border-2 border-white dark:border-gray-800">
                                {{ $this->unreadNotificationsCount > 9 ? '9+' : $this->unreadNotificationsCount }}
                            </span>
                        @endif
                    </div>
                    <div class="font-medium text-sm text-gray-500">{{ $this->user->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Account Management -->
                <div class="block px-4 py-2 text-xs text-gray-400">
                    {{ __('Manage Account') }}
                </div>

                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="$this->isProfileActive">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('security.show') }}" :active="$this->isSecurityActive">
                    {{ __('Security') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('notifications') }}" :active="$this->isNotificationsActive">
                    <div class="flex items-center">
                        <span>{{ __('Notifications') }}</span>
                        @if($this->unreadNotificationsCount > 0)
                            <span class="ml-2 inline-flex items-center justify-center h-4 w-4 bg-red-500 text-white text-xs font-bold rounded-full">
                                {{ $this->unreadNotificationsCount > 9 ? '9+' : $this->unreadNotificationsCount }}
                            </span>
                        @endif
                    </div>
                </x-responsive-nav-link>

                @if($this->isImpersonating)
                    <div class="border-t border-gray-200 dark:border-gray-600"></div>
                    <form method="POST" action="{{ route('impersonate.stop') }}" x-data>
                        @csrf
                        <x-responsive-nav-link href="{{ route('impersonate.stop') }}"
                                 @click.prevent="$root.submit();" class="text-orange-600 dark:text-orange-400">
                            {{ __('Stop Impersonating') }}
                        </x-responsive-nav-link>
                    </form>
                @elseif($this->isSystemAdmin)
                    <div class="border-t border-gray-200 dark:border-gray-600"></div>
                    <x-responsive-nav-link href="#" wire:click="$dispatch('open-impersonation-modal')" class="text-orange-600 dark:text-orange-400">
                        {{ __('Impersonate User') }}
                    </x-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <div class="border-t border-gray-200 dark:border-gray-600"></div>
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf

                    <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>

                <!-- Entity Management -->
                @if(App\Support\Afterburner::hasTeamFeatures())
                    <div class="border-t border-gray-200 dark:border-gray-600"></div>

                    <div class="block px-4 py-2 text-xs text-gray-400">
                        {{ $this->currentTeamName() }}
                    </div>

                    <!-- Entity Settings -->
                    @if($this->user->currentTeam)
                        <x-responsive-nav-link href="{{ route('teams.information', $this->user->currentTeam->id) }}" :active="$this->isTeamsInformationActive">
                            Details
                        </x-responsive-nav-link>
                        <x-responsive-nav-link href="{{ route('teams.members', $this->user->currentTeam->id) }}" :active="$this->isTeamsMembersActive">
                            Members
                        </x-responsive-nav-link>

                        @if(App\Support\Features::hasTeamAnnouncements())
                            <x-responsive-nav-link href="{{ route('team-announcements.index', $this->user->currentTeam->id) }}" :active="request()->routeIs('team-announcements.index')">
                                <div class="flex items-center">
                                    <span>Announcements</span>
                                    @if($this->unreadAnnouncementsCount > 0)
                                        <span class="ml-2 inline-flex items-center justify-center h-4 w-4 bg-blue-500 text-white text-xs font-bold rounded-full">
                                            {{ $this->unreadAnnouncementsCount > 9 ? '9+' : $this->unreadAnnouncementsCount }}
                                        </span>
                                    @endif
                                </div>
                            </x-responsive-nav-link>
                        @endif
                    @endif

                    <!-- Entity Switcher -->
                    @if ($this->allTeams->count() > 1)
                        <div class="border-t border-gray-200 dark:border-gray-600"></div>

                        <div class="block px-4 py-2 text-xs text-gray-400">
                            Switch {{ Str::plural(Str::title(config('afterburner.entity_label'))) }}
                        </div>

                        @foreach ($this->allTeams as $team)
                            <x-switchable-team :team="$team" component="responsive-nav-link" />
                        @endforeach
                    @endif

                    @if($this->canCreateTeam())
                        <div class="border-t border-gray-200 dark:border-gray-600"></div>

                        <x-responsive-nav-link href="{{ route('teams.create') }}" :active="$this->isTeamsCreateActive">
                            Create {{ Str::title(config('afterburner.entity_label')) }}
                        </x-responsive-nav-link>
                    @endif
                @endif
            </div>
        </div>
    </div>
</nav>