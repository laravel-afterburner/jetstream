<x-form-section submit="createTeam">
    <x-slot name="title">
        {{ Str::title(config('afterburner.entity_label')) }} Details
    </x-slot>

    <x-slot name="description">
        Create a new account to manage your {{ config('afterburner.entity_label') }}
    </x-slot>

    <x-slot name="form">
        <!-- Entity Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="name" value="Name" />

            <x-input id="name"
                        type="text"
                        class="mt-1 block w-full"
                        wire:model="state.name"
                        autofocus />

            <x-input-error for="name" class="mt-2" />
        </div>

        @if(\App\Support\Features::hasTeamTimezoneManagement())
            <!-- Timezone -->
            <div class="col-span-6 sm:col-span-4" 
                 x-data="{ open: @entangle('showResults') }" 
                 @click.away="open = false">
                <x-label for="timezone-search" value="{{ __('Timezone') }}" />
                
                <div class="mt-1 relative">
                    <div class="relative">
                        <input 
                            type="text" 
                            id="timezone-search"
                            wire:model.live.debounce.300ms="searchQuery"
                            wire:focus="$set('showResults', true)"
                            placeholder="{{ __('Type to search for any timezone...') }}"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            autocomplete="off"
                        />
                    </div>

                    @if($showResults && count($this->filteredTimezones) > 0)
                        <div 
                            x-show="open"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                        >
                            @foreach($this->filteredTimezones as $tz)
                                <button
                                    type="button"
                                    wire:click="selectTimezone('{{ $tz['timezone'] }}')"
                                    class="w-full text-left px-4 py-2 hover:bg-indigo-50 hover:text-indigo-900 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-100 focus:bg-indigo-50 focus:text-indigo-900 dark:focus:bg-indigo-900/30 dark:focus:text-indigo-100 cursor-pointer {{ $this->state['timezone'] === $tz['timezone'] ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-900 dark:text-indigo-100' : 'text-gray-900 dark:text-gray-100' }}"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="font-medium">{{ $tz['display'] }}</div>
                                            <div class="text-xs {{ $this->state['timezone'] === $tz['timezone'] ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-500 dark:text-gray-400' }}">
                                                {{ $tz['timezone'] }}
                                            </div>
                                        </div>
                                        @if($this->state['timezone'] === $tz['timezone'])
                                            <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @elseif($showResults && !empty($searchQuery))
                        <div 
                            x-show="open"
                            x-transition
                            class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 sm:text-sm"
                        >
                            <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No timezones found matching "') }}{{ $searchQuery }}{{ __('"') }}
                            </div>
                        </div>
                    @endif
                </div>

                <input type="hidden" wire:model="state.timezone" />
                <x-input-error for="state.timezone" class="mt-2" />
            </div>
        @endif

        <!-- Entity Owner Information -->
        <div class="col-span-6">
            <x-label value="Owner" />

            <div class="flex items-center mt-2">
                <img class="size-12 rounded-full object-cover" src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}">

                <div class="ms-4 leading-tight">
                    <div class="text-gray-900 dark:text-white">{{ $this->user->name }}</div>
                    <div class="text-gray-700 dark:text-gray-300 text-sm">{{ $this->user->email }}</div>
                </div>
            </div>
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-button>
            {{ __('Create') }}
        </x-button>
    </x-slot>
</x-form-section>
