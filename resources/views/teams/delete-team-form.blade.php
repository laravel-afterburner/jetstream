<x-action-section>
    <x-slot name="title">
        Delete
    </x-slot>

    <x-slot name="description">
        Permanently delete this {{ config('afterburner.entity_label') }}.
    </x-slot>

    <x-slot name="content">
        @if ($this->isPersonalTeam)
            <div class="max-w-xl rounded-md bg-yellow-50 dark:bg-yellow-900/20 p-4 mb-5">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            Personal {{ Str::title(config('afterburner.entity_label')) }}
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                            <p>You cannot delete your personal {{ config('afterburner.entity_label') }}. This is your primary {{ config('afterburner.entity_label') }} and is required for your account to function properly.</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                Once a {{ config('afterburner.entity_label') }} is deleted, all of its resources and data will be permanently deleted. Before deleting this {{ config('afterburner.entity_label') }}, please download any data or information regarding this {{ config('afterburner.entity_label') }} that you wish to retain.
            </div>
        @endif

        <div class="mt-5">
            @if($this->isPersonalTeam)
                <x-danger-button disabled class="opacity-50 cursor-not-allowed">
                    Delete {{ Str::title(config('afterburner.entity_label')) }}
                </x-danger-button>
            @else
                <x-danger-button wire:click="$toggle('confirmingTeamDeletion')" wire:loading.attr="disabled">
                    Delete {{ Str::title(config('afterburner.entity_label')) }}
                </x-danger-button>
            @endif
        </div>

        <!-- Delete Entity Confirmation Modal -->
        @if (!$this->isPersonalTeam)
            <x-confirmation-modal wire:model.live="confirmingTeamDeletion">
                <x-slot name="title">
                    Delete {{ Str::title(config('afterburner.entity_label')) }}
                </x-slot>

                <x-slot name="content">
                    Are you sure you want to delete this {{ config('afterburner.entity_label') }}? Once a {{ config('afterburner.entity_label') }} is deleted, all of its resources and data will be permanently deleted.
                </x-slot>

                <x-slot name="footer">
                    <x-secondary-button wire:click="$toggle('confirmingTeamDeletion')" wire:loading.attr="disabled">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-danger-button class="ms-3" wire:click="deleteTeam" wire:loading.attr="disabled">
                        Delete {{ Str::title(config('afterburner.entity_label')) }}
                    </x-danger-button>
                </x-slot>
            </x-confirmation-modal>
        @endif
    </x-slot>
</x-action-section>
