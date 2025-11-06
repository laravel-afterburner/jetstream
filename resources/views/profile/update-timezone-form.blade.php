<x-form-section submit="updateTimezone">
    <x-slot name="title">
        {{ __('Timezone') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Update your timezone preference. Dates and times will be displayed in your selected timezone.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4" 
             x-data="{ open: @entangle('showResults') }" 
             @click.away="open = false"
             x-init="
                 // Refresh detected timezone after page load when cookie is available
                 setTimeout(() => {
                     @this.call('refreshDetectedTimezoneAction');
                 }, 100);
             ">
            <x-label for="timezone-search" value="{{ __('Timezone') }}" />
            
            <div class="mt-1 relative">
                <div class="relative">
                    <input 
                        type="text" 
                        id="timezone-search"
                        wire:model.live.debounce.300ms="searchQuery"
                        wire:focus="$set('showResults', true)"
                        placeholder="{{ __('Type to search for your timezone...') }}"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        autocomplete="off"
                    />
                    @if($savedTimezone)
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    @endif
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
            <x-input-error for="timezone" class="mt-2" />
            
            @if($savedTimezone)
                <div class="mt-3 space-y-2">
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">
                                    {{ __('Saved Timezone') }}
                                </p>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">
                                    {{ $this->savedTimezoneDisplay }} 
                                    <span class="font-mono text-xs">({{ $savedTimezone }})</span>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Current time') }}</p>
                                @php
                                    $currentTime = now()->setTimezone($savedTimezone);
                                @endphp
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-1" wire:key="timezone-preview-{{ $savedTimezone }}">
                                    {{ $currentTime->format('g:i:s A') }} ({{ $currentTime->format('T') }})
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($detectedTimezone && $detectedTimezone !== $savedTimezone)
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800" wire:key="detected-{{ $detectedTimezone }}">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-xs text-blue-600 dark:text-blue-400 uppercase">
                                        {{ __('Your Location') }}
                                    </p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">
                                        {{ $this->detectedTimezoneDisplay }} 
                                        <span class="font-mono text-xs">({{ $detectedTimezone }})</span>
                                    </p>
                                </div>
                                <div class="text-right flex items-end gap-3">
                                    <div>
                                        <p class="text-xs text-blue-600 dark:text-blue-400 uppercase">{{ __('Current time') }}</p>
                                        @php
                                            $detectedTime = now()->setTimezone($detectedTimezone);
                                        @endphp
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">
                                            {{ $detectedTime->format('g:i:s A') }} ({{ $detectedTime->format('T') }})
                                        </p>
                                    </div>
                                    <button
                                        wire:click="updateToDetectedTimezone"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <span wire:loading.remove wire:target="updateToDetectedTimezone">{{ __('Update') }}</span>
                                        <span wire:loading wire:target="updateToDetectedTimezone">{{ __('Updating...') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled">
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('timezone-updated', () => {
        // Dispatch browser event to dismiss the timezone suggestion banner
        window.dispatchEvent(new CustomEvent('timezone-updated'));
    });
});
</script>

