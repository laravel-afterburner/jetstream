<div>
    <!-- Entity Name Section -->
    <x-form-section submit="updateTeamName">
        <x-slot name="title">
            Name
        </x-slot>

        <x-slot name="description">
            The {{ config('afterburner.entity_label') }}'s name.
        </x-slot>

        <x-slot name="form">
            <!-- Entity Name -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="name" value="Name" />

                <x-input id="name"
                            type="text"
                            class="mt-1 block w-full"
                            wire:model="updateTeamNameForm.name"
                            :disabled="! Gate::check('update', $team)" />

                <x-input-error for="updateTeamNameForm.name" class="mt-2" />
            </div>
        </x-slot>

        @if (Gate::check('update', $team))
            <x-slot name="actions">
                <x-action-message class="me-3" on="saved">
                    {{ __('Saved.') }}
                </x-action-message>

                <x-button>
                    {{ __('Save') }}
                </x-button>
            </x-slot>
        @endif
    </x-form-section>

    <x-section-border />

    <!-- Entity Ownership Section -->
    <x-action-section>
        <x-slot name="title">
            Ownership
        </x-slot>

        <x-slot name="description">
            The {{ config('afterburner.entity_label') }} owner information.
        </x-slot>

        <x-slot name="content">
            <ul role="list" class="divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden bg-white dark:bg-gray-800 shadow-sm outline outline-1 outline-gray-900/5 dark:outline-gray-700/50 sm:rounded-xl">
                <!-- Owner -->
                <li class="relative flex justify-between gap-x-6 px-4 py-5 hover:bg-gray-50 dark:hover:bg-gray-700 sm:px-6">
                    <div class="flex min-w-0 gap-x-4">
                        <img class="size-12 flex-none rounded-full object-cover" src="{{ $team->owner->profile_photo_url }}" alt="{{ $team->owner->name }}">
                        <div class="min-w-0 flex-auto leading-tight">
                            <div class="text-gray-900 dark:text-white">{{ $team->owner->name }}</div>
                            <div class="text-gray-700 dark:text-gray-300 text-sm"><a href="mailto:{{ $team->owner->email }}" class="relative truncate hover:underline">{{ $team->owner->email }}</a></div>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-x-4">
                        <div class="hidden sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm/6 text-gray-900 dark:text-white">Owner</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if (Gate::check('changeOwner', $team))
                                @if($this->hasOtherUsers)
                                    <button 
                                        wire:click="confirmTeamOwnerChange" 
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center p-2.5 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200"
                                        title="Change owner">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </button>
                                @else
                                    <button 
                                        disabled
                                        class="inline-flex items-center p-2.5 text-gray-300 dark:text-gray-600 cursor-not-allowed"
                                        title="{{ $this->getCannotChangeOwnerReason() }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </li>
            </ul>
        </x-slot>
    </x-action-section>

    <!-- Entity Owner Change Modal -->
    <x-dialog-modal wire:model.live="confirmingTeamOwnerChange">
        <x-slot name="title">
            Change {{ Str::title(config('afterburner.entity_label')) }} Owner
        </x-slot>

        <x-slot name="content">
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Select a new owner for this {{ config('afterburner.entity_label') }}. The new owner will have full control over the {{ config('afterburner.entity_label') }}.
            </div>

            <div class="space-y-3">
                @foreach($this->teamMembers as $user)
                    <button type="button" 
                            class="relative w-full px-4 py-3 text-left border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 {{ $updateTeamOwnerForm['user_id'] == $user->id ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-300 dark:border-indigo-600' : '' }}"
                            wire:click="$set('updateTeamOwnerForm.user_id', '{{ $user->id }}')">
                        <div class="flex items-center">
                            <img class="size-10 rounded-full object-cover" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
                            <div class="ml-3 flex-1">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                            </div>
                            @if($updateTeamOwnerForm['user_id'] == $user->id)
                                <svg class="size-5 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>

            <x-input-error for="updateTeamOwnerForm.user_id" class="mt-2" />
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="cancelTeamOwnerChange" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-button class="ms-3" wire:click="updateTeamOwner" wire:loading.attr="disabled">
                {{ __('Change Owner') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
