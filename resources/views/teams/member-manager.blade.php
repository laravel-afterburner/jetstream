<div>
    @if ($team->users->isNotEmpty())
        <!-- Manage Entity Members -->
        <x-action-section>
            <x-slot name="title">
                Current Members
            </x-slot>

            <x-slot name="description">
                All of the people that are part of this {{ config('afterburner.entity_label') }}.
            </x-slot>

            <!-- Entity Member List -->
            <x-slot name="content">
                <ul role="list" class="divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden bg-white dark:bg-gray-800 shadow-sm outline outline-1 outline-gray-900/5 dark:outline-gray-700/50 sm:rounded-xl">
                    @foreach ($this->sortedTeamUsers as $user)
                        <li class="relative flex justify-between gap-x-6 px-4 py-5 hover:bg-gray-50 dark:hover:bg-gray-700 sm:px-6">
                            <div class="flex min-w-0 gap-x-4">
                                <img class="size-12 flex-none rounded-full object-cover" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
                                <div class="min-w-0 flex-auto leading-tight">
                                    <div class="text-gray-900 dark:text-white">{{ $user->name }}</div>
                                    <div class="text-gray-700 dark:text-gray-300 text-sm"><a href="mailto:{{ $user->email }}" class="relative truncate hover:underline">{{ $user->email }}</a></div>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-x-4">
                                <div class="hidden sm:flex sm:flex-col sm:items-end">
                                    <p class="text-sm/6 text-gray-900 dark:text-white">
                                        @if($this->getUserDisplayRoles($user)->isNotEmpty())
                                            <button 
                                                type="button"
                                                wire:click="showMemberPermissions('{{ $user->id }}')"
                                                class="cursor-pointer hover:underline hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                                                {{ $this->getUserDisplayRoles($user)->first()->name }}
                                            </button>
                                        @else
                                            <button 
                                                type="button"
                                                wire:click="showMemberPermissions('{{ $user->id }}')"
                                                class="cursor-pointer hover:underline hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                                                Basic User
                                            </button>
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <!-- Manage Entity Member Roles -->
                                    @if (Gate::check('updateTeamMember', $team))
                                        <button 
                                            wire:click="manageRole('{{ $user->id }}')" 
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center p-2.5 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200"
                                            title="Manage roles">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </button>
                                    @endif

                                    <!-- Leave Entity -->
                                    @if ($this->user->id === $user->id)
                                        @if ($this->canLeaveTeam())
                                            <button 
                                                wire:click="$toggle('confirmingLeavingTeam')" 
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center p-2.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors duration-200"
                                                title="Leave team">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                                </svg>
                                            </button>
                                        @else
                                            <button 
                                                disabled
                                                class="inline-flex items-center p-2.5 text-gray-300 dark:text-gray-600 cursor-not-allowed"
                                                title="{{ $this->getCannotLeaveReason() }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                                </svg>
                                            </button>
                                        @endif

                                    <!-- Remove Entity Member -->
                                    @elseif (Gate::check('removeTeamMember', $team))
                                        <button 
                                            wire:click="confirmTeamMemberRemoval('{{ $user->id }}')" 
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center p-2.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors duration-200"
                                            title="Remove member">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-slot>
        </x-action-section>
    @endif

    @if (Gate::check('addTeamMember', $team))
        <x-section-border />

        <!-- Add Entity Member -->
        <div class="mt-10 sm:mt-0">
            <x-form-section submit="addTeamMember">
                <x-slot name="title">
                    Add New Member
                </x-slot>

                <x-slot name="description">
                    Add a new member to your {{ config('afterburner.entity_label') }}. All members automatically receive basic user access, and you may optionally assign additional roles.
                </x-slot>

                <x-slot name="form">
                    <div class="col-span-6">
                        <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                            Please provide the email address of the person you would like to add to this {{ config('afterburner.entity_label') }}.
                        </div>
                    </div>

                    <!-- Member Email -->
                    <div class="col-span-6 sm:col-span-4">
                        <x-label for="email" value="{{ __('Email') }}" />
                        <x-input id="email" type="email" class="mt-1 block w-full" wire:model="addTeamMemberForm.email" />
                        <x-input-error for="email" class="mt-2" />
                    </div>

                    <!-- Roles Selection -->
                    @if (count($this->roles) > 0)
                        <div class="col-span-6 lg:col-span-4">
                            <div class="flex items-center justify-between">
                                <x-label for="roles" value="{{ __('Select Role(s)') }}" />
                                <a href="{{ route('roles.show', $team) }}" class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    Manage Roles
                                </a>
                            </div>
                            <x-input-error for="roles" class="mt-2" />

                            <div class="relative z-0 mt-1 border border-gray-200 dark:border-gray-700 rounded-lg">
                                @foreach ($this->roles as $index => $role)
                                    @php
                                        $isDisabled = $role->is_default || $role->is_at_max_capacity;
                                        $isSelected = in_array($role->key, $addTeamMemberForm['roles']) || $role->is_default;
                                    @endphp
                                    <button type="button" 
                                            class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 {{ $index > 0 ? 'border-t border-gray-200 dark:border-gray-700 focus:border-none rounded-t-none' : '' }} {{ ! $loop->last ? 'rounded-b-none' : '' }} {{ $role->is_default ? 'bg-gray-50 dark:bg-gray-800' : '' }} {{ $role->is_at_max_capacity && !$role->is_default ? 'bg-gray-100 dark:bg-gray-700 cursor-not-allowed opacity-50' : '' }}"
                                            wire:click="toggleInvitationRole('{{ $role->key }}')"
                                            @if($isDisabled) disabled @endif
                                            @if($role->is_at_max_capacity && !$role->is_default) title="This role is not available - maximum capacity reached" @endif>
                                        <div class="{{ !$isSelected && !$role->is_default ? 'opacity-50' : '' }} {{ $role->is_at_max_capacity && !$role->is_default ? 'opacity-75' : '' }}">
                                            
                                            <div class="flex items-center">
                                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 {{ $isSelected ? 'font-semibold' : '' }} {{ $role->is_at_max_capacity && !$role->is_default ? 'text-gray-400 dark:text-gray-500' : '' }}">
                                                    <img src="{{ asset('icons/' . $this->getRoleIcon($role->key)) }}" alt="{{ $role->name }}" class="w-4 h-4 mr-2" />
                                                    <span class="cursor-pointer hover:underline" 
                                                          wire:click="showRolePermissions('{{ $role->key }}')"
                                                          wire:key="role-name-{{ $role->key }}">
                                                        {{ $role->name }}
                                                    </span>
                                                    @if($role->is_default)
                                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">(Basic User)</span>
                                                    @elseif($role->is_at_max_capacity)
                                                        <span class="text-xs text-gray-400 dark:text-gray-500 ml-1">(Max capacity reached)</span>
                                                    @elseif($role->max_members !== null)
                                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">({{ $role->available_slots }} available)</span>
                                                    @endif
                                                </div>

                                                @if($isSelected)
                                                    <svg class="ms-2 size-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                @elseif($role->is_at_max_capacity && !$role->is_default)
                                                    <svg class="ms-2 size-5 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                @endif
                                            </div>

                                            <!-- Role Description -->
                                            <div class="mt-2 text-xs text-gray-600 dark:text-gray-400 text-start {{ $role->is_at_max_capacity && !$role->is_default ? 'text-gray-400 dark:text-gray-500' : '' }}">
                                                {{ $role->description }}
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </x-slot>

                <x-slot name="actions">
                    <x-action-message class="me-3" on="saved">
                        {{ __('Added.') }}
                    </x-action-message>

                    <x-button>
                        {{ __('Add') }}
                    </x-button>
                </x-slot>
            </x-form-section>
        </div>
    @endif

    @if ($team->teamInvitations->isNotEmpty() && Gate::check('addTeamMember', $team))
        <x-section-border />

        <!-- Entity Member Invitations -->
        <div class="mt-10 sm:mt-0">
            <x-action-section>
                <x-slot name="title">
                    Pending Invitations
                </x-slot>

                <x-slot name="description">
                    These people have been invited to your {{ config('afterburner.entity_label') }} and have been sent an invitation email. They may join the {{ config('afterburner.entity_label') }} by accepting the email invitation.
                </x-slot>

                <x-slot name="content">
                    <ul role="list" class="divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden bg-white dark:bg-gray-800 shadow-sm outline outline-1 outline-gray-900/5 dark:outline-gray-700/50 sm:rounded-xl">
                        @foreach ($this->teamInvitationsWithUsers as $invitation)
                            @php
                                $invitedUser = $invitation->invited_user;
                                $isDeclined = $invitation->declined_at !== null;
                            @endphp
                            <li class="relative flex justify-between gap-x-6 px-4 py-5 hover:bg-gray-50 dark:hover:bg-gray-700 sm:px-6 {{ $isDeclined ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                                <div class="flex min-w-0 gap-x-4">
                                    @if($invitedUser)
                                        <img src="{{ $invitedUser->profile_photo_url }}" alt="{{ $invitedUser->name }}" class="size-12 flex-none rounded-full object-cover" />
                                    @else
                                        <div class="size-12 flex-none rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-auto">
                                        <div class="flex items-center gap-2">
                                            <div class="text-gray-900 dark:text-white">
                                                @if($invitedUser)
                                                    {{ $invitedUser->name }}
                                                @else
                                                    {{ $invitation->email }}
                                                @endif
                                            </div>
                                            @if($isDeclined)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                    Declined
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-gray-700 dark:text-gray-300 text-sm">
                                            <a href="mailto:{{ $invitation->email }}" class="relative truncate hover:underline">{{ $invitation->email }}</a>
                                        </div>
                                        @if($invitation->roles && !empty($invitation->roles))
                                            <div class="mt-2 flex flex-wrap gap-1">
                                                @foreach($invitation->roles as $roleSlug)
                                                    <button 
                                                        type="button"
                                                        wire:click="showRolePermissions('{{ $roleSlug }}')"
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer transition-opacity hover:opacity-80 {{ $this->getRoleBadgeColor($roleSlug) }}">
                                                        <img src="{{ asset('icons/' . $this->getRoleIcon($roleSlug)) }}" alt="{{ $this->getRoleName($roleSlug) }}" class="w-3 h-3 mr-1" />
                                                        {{ $this->getRoleName($roleSlug) }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if($isDeclined)
                                            @php
                                                $declinedTime = $team->toTeamTimezone($invitation->declined_at);
                                            @endphp
                                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                                                Declined on {{ $declinedTime->format('M j, Y g:i A') }} ({{ $declinedTime->format('T') }})
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex shrink-0 items-center gap-x-4">
                                    <div class="flex items-center space-x-2">
                                        @if($isDeclined)
                                            <!-- Resend Invitation -->
                                            <button 
                                                wire:click="resendTeamInvitation({{ $invitation->id }})" 
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center p-2.5 text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors duration-200"
                                                title="Resend invitation">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                            </button>
                                            <!-- Delete Declined Invitation -->
                                            <button 
                                                wire:click="deleteDeclinedInvitation({{ $invitation->id }})" 
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center p-2.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors duration-200"
                                                title="Delete declined invitation">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        @else
                                            @if (Gate::check('removeTeamMember', $team))
                                                <!-- Cancel Entity Invitation -->
                                                <button 
                                                    wire:click="confirmInvitationCancellation({{ $invitation->id }})" 
                                                    wire:loading.attr="disabled"
                                                    class="inline-flex items-center p-2.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors duration-200"
                                                    title="Cancel invitation">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </x-slot>
            </x-action-section>
        </div>
    @endif

    <!-- Role Management Modal -->
    <x-dialog-modal wire:model.live="currentlyManagingRole">
        <x-slot name="title">
            {{ __('Manage Roles') }}
        </x-slot>

        <x-slot name="content">
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Select additional roles for this member. All members automatically have basic access.
            </div>

            <div class="relative z-0 border border-gray-200 dark:border-gray-700 rounded-lg">
                @foreach ($this->roles as $index => $role)
                    @php
                        $isDisabled = $role->is_default || ($role->is_at_max_capacity && !in_array($role->key, $selectedRoles));
                        $isSelected = in_array($role->key, $selectedRoles);
                    @endphp
                    <button type="button" 
                            class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 {{ $index > 0 ? 'border-t border-gray-200 dark:border-gray-700 focus:border-none rounded-t-none' : '' }} {{ ! $loop->last ? 'rounded-b-none' : '' }} {{ $role->is_default ? 'bg-gray-50 dark:bg-gray-800' : '' }} {{ $role->is_at_max_capacity && !$isSelected ? 'bg-gray-100 dark:bg-gray-700 cursor-not-allowed opacity-50' : '' }}"
                            wire:click="toggleRole('{{ $role->key }}')"
                            @if($isDisabled) disabled @endif
                            @if($role->is_at_max_capacity && !$isSelected) title="This role is not available - maximum capacity reached" @endif>
                        <div class="{{ !$isSelected && !$role->is_default ? 'opacity-50' : '' }} {{ $role->is_at_max_capacity && !$isSelected ? 'opacity-75' : '' }}">
                            <!-- Role Name -->
                            <div class="flex items-center">
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 {{ $isSelected ? 'font-semibold' : '' }} {{ $role->is_at_max_capacity && !$isSelected ? 'text-gray-400 dark:text-gray-500' : '' }}">
                                    <img src="{{ asset('icons/' . $this->getRoleIcon($role->key)) }}" alt="{{ $role->name }}" class="w-4 h-4 mr-2" />
                                    <span class="cursor-pointer hover:underline" 
                                          wire:click="showRolePermissions('{{ $role->key }}')"
                                          wire:key="modal-role-name-{{ $role->key }}">
                                        {{ $role->name }}
                                    </span>
                                    @if($role->is_default)
                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">(Basic User)</span>
                                    @elseif($role->is_at_max_capacity && !$isSelected)
                                        <span class="text-xs text-gray-400 dark:text-gray-500 ml-1">(Max capacity reached)</span>
                                    @elseif($role->max_members !== null)
                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">({{ $role->available_slots }} available)</span>
                                    @endif
                                </div>

                                @if ($isSelected)
                                    <svg class="ms-2 size-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @elseif($role->is_at_max_capacity && !$isSelected)
                                    <svg class="ms-2 size-5 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                @endif
                            </div>

                            <!-- Role Description -->
                            <div class="mt-2 text-xs text-gray-600 dark:text-gray-400 text-start {{ $role->is_at_max_capacity && !$isSelected ? 'text-gray-400 dark:text-gray-500' : '' }}">
                                {{ $role->description }}
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="stopManagingRole" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-button class="ms-3" wire:click="updateRole" wire:loading.attr="disabled">
                {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Leave Entity Confirmation Modal -->
    <x-confirmation-modal wire:model.live="confirmingLeavingTeam">
        <x-slot name="title">
            Leave {{ Str::title(config('afterburner.entity_label')) }}
        </x-slot>

        <x-slot name="content">
            Are you sure you would like to leave this {{ config('afterburner.entity_label') }}?
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingLeavingTeam')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-danger-button class="ms-3" wire:click="leaveTeam" wire:loading.attr="disabled">
                {{ __('Leave') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

    <!-- Remove Entity Member Confirmation Modal -->
    <x-confirmation-modal wire:model.live="confirmingTeamMemberRemoval">
        <x-slot name="title">
            Remove {{ Str::title(config('afterburner.entity_label')) }} Member
        </x-slot>

        <x-slot name="content">
            Are you sure you would like to remove this person from the {{ config('afterburner.entity_label') }}?
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingTeamMemberRemoval')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-danger-button class="ms-3" wire:click="removeTeamMember" wire:loading.attr="disabled">
                {{ __('Remove') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

    <!-- Permission Details Modal -->
    <x-dialog-modal wire:model.live="showingPermissionDetails">
        <x-slot name="title">
            {{ $viewingBadgeName }} Role
        </x-slot>

        <x-slot name="content">
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                The <span class="font-semibold">{{ $viewingBadgeName }}</span> role includes the following permissions:
            </div>

            @if($viewingPermissions && $viewingPermissions->isNotEmpty())
                <div class="space-y-3">
                    @foreach($viewingPermissions as $permission)
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <svg class="size-5 text-green-500 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ $permission->name }}
                                </div>
                                @if($permission->description)
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ $permission->description }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-sm text-gray-500 dark:text-gray-400 italic">
                    No permissions assigned to this role.
                </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="closePermissionDetails" wire:loading.attr="disabled">
                {{ __('Close') }}
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Member Permissions Modal -->
    <x-dialog-modal wire:model.live="showingMemberPermissions">
        <x-slot name="title">
            {{ $viewingMemberPermissions->name ?? 'Member' }} - All Permissions
        </x-slot>

        <x-slot name="content">
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                All permissions for <span class="font-semibold">{{ $viewingMemberPermissions->name ?? 'this member' }}</span>, grouped by role:
            </div>

            @if($memberPermissionsByRole && $memberPermissionsByRole->isNotEmpty())
                <div class="space-y-6">
                    @foreach($memberPermissionsByRole as $roleData)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <img src="{{ asset('icons/' . $this->getRoleIcon($roleData['role']->slug)) }}" alt="{{ $roleData['role']->name }}" class="w-5 h-5 mr-2" />
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $roleData['role']->name }}
                                </h3>
                                @if($roleData['role']->is_default)
                                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">(Basic User)</span>
                                @endif
                            </div>
                            
                            @if($roleData['permissions'] && $roleData['permissions']->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($roleData['permissions'] as $permission)
                                        <div class="flex items-start gap-3 p-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                            <svg class="size-4 text-green-500 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900 dark:text-white text-sm">
                                                    {{ $permission->name }}
                                                </div>
                                                @if($permission->description)
                                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                        {{ $permission->description }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-sm text-gray-500 dark:text-gray-400 italic">
                                    No permissions assigned to this role.
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-sm text-gray-500 dark:text-gray-400 italic">
                    No roles or permissions found for this member.
                </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="closeMemberPermissions" wire:loading.attr="disabled">
                {{ __('Close') }}
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Cancel Team Invitation Confirmation Modal -->
    <x-confirmation-modal wire:model.live="confirmingInvitationCancellation">
        <x-slot name="title">
            Cancel {{ Str::title(config('afterburner.entity_label')) }} Invitation
        </x-slot>

        <x-slot name="content">
            Are you sure you would like to cancel this {{ config('afterburner.entity_label') }} invitation? The person will no longer be able to join the {{ config('afterburner.entity_label') }} using this invitation.
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingInvitationCancellation')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-danger-button class="ms-3" wire:click="cancelTeamInvitation({{ $invitationIdBeingCanceled }})" wire:loading.attr="disabled">
                {{ __('Cancel Invitation') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>