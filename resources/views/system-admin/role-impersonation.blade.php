<div>
    <div x-data x-show="$wire.isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                 x-on:click="$wire.closeModal()"></div>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                                Impersonate Role
                            </h3>

                            @if(!App\Support\Features::hasTeamFeatures() || $selectedTeamId)
                                <div class="mb-4">
                                    @if(App\Support\Features::hasTeamFeatures() && $selectedTeamId)
                                        <button type="button"
                                                wire:click="backToTeams"
                                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 mb-2">
                                            {!! '&larr;' !!} Back to {{ entity_plural() }}
                                        </button>
                                    @endif
                                    <label for="role-search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Search Role
                                    </label>
                                    <input type="text"
                                           wire:model.live.debounce.300ms="searchRoleQuery"
                                           id="role-search"
                                           placeholder="Type to search roles..."
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-300 sm:text-sm">
                                </div>

                                <div class="max-h-64 overflow-y-auto">
                                    @forelse($this->roles as $role)
                                        @if(App\Support\Features::hasTeamFeatures())
                                            <form method="POST" action="{{ route('impersonate-role.start', ['team' => $selectedTeamId, 'role' => $role]) }}" class="block">
                                                @csrf
                                                <button type="submit"
                                                        class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm text-gray-700 dark:text-gray-300">
                                                    <div class="font-medium">{{ $role->name }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $role->slug }}</div>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('impersonate-role.start.global', $role) }}" class="block">
                                                @csrf
                                                <button type="submit"
                                                        class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm text-gray-700 dark:text-gray-300">
                                                    <div class="font-medium">{{ $role->name }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $role->slug }}</div>
                                                </button>
                                            </form>
                                        @endif
                                    @empty
                                        <p class="text-sm text-gray-500 dark:text-gray-400 px-4 py-2">No roles found.</p>
                                    @endforelse
                                </div>
                            @else
                                <div class="mb-4">
                                    <label for="team-search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Search {{ entity_title() }}
                                    </label>
                                    <input type="text"
                                           wire:model.live.debounce.300ms="searchQuery"
                                           id="team-search"
                                           placeholder="Type to search {{ entity_plural() }}..."
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-300 sm:text-sm">
                                </div>

                                <div class="max-h-64 overflow-y-auto">
                                    @forelse($this->teams as $team)
                                        <button type="button"
                                                wire:click="selectTeam({{ $team->id }})"
                                                class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm text-gray-700 dark:text-gray-300">
                                            {{ $team->name }}
                                        </button>
                                    @empty
                                        <p class="text-sm text-gray-500 dark:text-gray-400 px-4 py-2">No {{ entity_plural() }} found.</p>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button"
                            wire:click="closeModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
