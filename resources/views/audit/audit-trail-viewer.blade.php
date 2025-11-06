<div>
    <!-- Filters -->
    <div class="mb-6 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search Query -->
            <div>
                <label for="searchQuery" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Search
                </label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchQuery"
                    id="searchQuery"
                    placeholder="Search events, users..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                />
            </div>

            <!-- Category Filter -->
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Category
                </label>
                <select
                    wire:model.live="category"
                    id="category"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                >
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Event Name Filter -->
            <div>
                <label for="eventName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Event Name
                </label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="eventName"
                    id="eventName"
                    placeholder="e.g., team.created"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                />
            </div>

            <!-- User Filter -->
            <div>
                <label for="userId" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    User
                </label>
                <select
                    wire:model.live="userId"
                    id="userId"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                >
                    <option value="">All Users</option>
                    <option value="system">System</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label for="dateFrom" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    From Date
                </label>
                <input
                    type="date"
                    wire:model.live="dateFrom"
                    id="dateFrom"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                />
            </div>

            <!-- Date To -->
            <div>
                <label for="dateTo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    To Date
                </label>
                <input
                    type="date"
                    wire:model.live="dateTo"
                    id="dateTo"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                />
            </div>

            <!-- Team Filter -->
            <div>
                <label for="teamId" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Team
                </label>
                <select
                    wire:model.live="teamId"
                    id="teamId"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                >
                    <option value="">All Teams</option>
                    @foreach(\App\Models\Team::orderBy('name')->get() as $teamOption)
                        <option value="{{ $teamOption->id }}">{{ $teamOption->name }} (ID: {{ $teamOption->id }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Clear Filters Button -->
            <div class="flex items-end">
                <button
                    wire:click="clearFilters"
                    type="button"
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
                >
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Audit Log Table -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            User
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Category
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Event
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Action Type
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Details
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($logs as $log)
                        @php
                            // If teamId filter is active and log has a team, use team timezone
                            // Otherwise, use the action user's timezone
                            if ($teamId && $log->team_id && $log->team) {
                                $actionTimezone = $log->team->getTimezone();
                            } else {
                                $actionTimezone = $log->user?->getTimezone() ?? config('app.timezone', 'UTC');
                            }
                            $displayTime = $log->created_at->setTimezone($actionTimezone);
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $displayTime->format('Y-m-d g:i:s A') }} ({{ $displayTime->format('T') }})
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                <div>
                                    {{ $log->user?->name ?? 'System' }}
                                    @if($log->user)
                                        <span class="text-gray-500 dark:text-gray-400 text-xs block">{{ $log->user->email }}</span>
                                    @endif
                                    @if($log->impersonated_by)
                                        <span class="text-orange-600 dark:text-orange-400 text-xs block">
                                            Impersonated by: {{ $log->impersonator?->name ?? 'Unknown' }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ ucfirst($log->category) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                <code class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                    {{ $log->event_name }}
                                </code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ ucfirst(str_replace('_', ' ', $log->action_type)) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                <button
                                    wire:click="showDetails({{ $log->id }})"
                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium"
                                >
                                    View Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No audit logs found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $logs->links() }}
        </div>
    </div>

    <!-- Audit Log Details Modal -->
    <x-dialog-modal wire:model.live="showingDetailsModal" maxWidth="3xl">
        <x-slot name="title">
            Audit Log Details
        </x-slot>

        <x-slot name="content">
            @if($selectedLog)
                @php
                    // If teamId filter is active and log has a team, use team timezone
                    // Otherwise, use the action user's timezone
                    if ($teamId && $selectedLog->team_id && $selectedLog->team) {
                        $actionTimezone = $selectedLog->team->getTimezone();
                    } else {
                        $actionTimezone = $selectedLog->user?->getTimezone() ?? config('app.timezone', 'UTC');
                    }
                    $displayTime = $selectedLog->created_at->setTimezone($actionTimezone);
                @endphp
                <div class="space-y-4">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Basic Information</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2 text-sm">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Date</dt>
                                <dd class="mt-1 text-gray-900 dark:text-gray-100">
                                    {{ $displayTime->format('Y-m-d g:i:s A') }} ({{ $displayTime->format('T') }})
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Event Name</dt>
                                <dd class="mt-1">
                                    <code class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $selectedLog->event_name }}</code>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Category</dt>
                                <dd class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ ucfirst($selectedLog->category) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Action Type</dt>
                                <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $selectedLog->action_type)) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- User Information -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">User Information</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2 text-sm">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">User</dt>
                                <dd class="mt-1 text-gray-900 dark:text-gray-100">
                                    {{ $selectedLog->user?->name ?? 'System' }}
                                    @if($selectedLog->user)
                                        <span class="block text-gray-500 dark:text-gray-400 text-xs">{{ $selectedLog->user->email }}</span>
                                    @endif
                                </dd>
                            </div>
                            @if($selectedLog->impersonated_by)
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Impersonated By</dt>
                                    <dd class="mt-1 text-orange-600 dark:text-orange-400">
                                        {{ $selectedLog->impersonator?->name ?? 'Unknown' }}
                                        @if($selectedLog->impersonator)
                                            <span class="block text-gray-500 dark:text-gray-400 text-xs">{{ $selectedLog->impersonator->email }}</span>
                                        @endif
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Context Information -->
                    @if($selectedLog->team_id || ($selectedLog->auditable_type && $selectedLog->auditable_id))
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Context</h3>
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2 text-sm">
                                @if($selectedLog->team_id)
                                    <div>
                                        <dt class="text-gray-500 dark:text-gray-400">Team</dt>
                                        <dd class="mt-1 text-gray-900 dark:text-gray-100">
                                            @if($selectedLog->team)
                                                {{ $selectedLog->team->name }} (ID: {{ $selectedLog->team_id }})
                                            @else
                                                {{ $selectedLog->team_id }}
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                                @if($selectedLog->auditable_type && $selectedLog->auditable_id)
                                    <div>
                                        <dt class="text-gray-500 dark:text-gray-400">Related Model</dt>
                                        <dd class="mt-1 text-gray-900 dark:text-gray-100">
                                            {{ class_basename($selectedLog->auditable_type) }} #{{ $selectedLog->auditable_id }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif

                    <!-- Changes -->
                    @if($selectedLog->changes)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Changes</h3>
                            
                            @if(($selectedLog->event_name === 'user.updated' || $selectedLog->event_name === 'timezone.updated') && isset($selectedLog->changes['timezone']))
                                {{-- Special formatting for timezone changes --}}
                                <div class="mt-2 space-y-3">
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                        <div class="grid grid-cols-1 gap-3">
                                            <div>
                                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">From Timezone</dt>
                                                <dd class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">
                                                    {{ $selectedLog->changes['timezone']['before'] ?? '(none - using app default)' }}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">To Timezone</dt>
                                                <dd class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100 font-semibold">
                                                    {{ $selectedLog->changes['timezone']['after'] ?? '(unknown)' }}
                                                </dd>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Also show raw JSON for completeness --}}
                                    <details class="mt-2">
                                        <summary class="text-xs text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300">Show raw JSON</summary>
                                        <pre class="mt-2 p-3 bg-gray-50 dark:bg-gray-900 rounded text-xs overflow-auto max-h-64 border border-gray-200 dark:border-gray-700">{{ json_encode($selectedLog->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                </div>
                            @else
                                {{-- Default JSON display for other changes --}}
                                <pre class="mt-1 p-3 bg-gray-50 dark:bg-gray-900 rounded text-xs overflow-auto max-h-64 border border-gray-200 dark:border-gray-700">{{ json_encode($selectedLog->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @endif
                        </div>
                    @endif

                    <!-- Metadata -->
                    @if($selectedLog->metadata)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Metadata</h3>
                            <pre class="mt-1 p-3 bg-gray-50 dark:bg-gray-900 rounded text-xs overflow-auto max-h-64 border border-gray-200 dark:border-gray-700">{{ json_encode($selectedLog->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif
                </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="closeDetailsModal">
                Close
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>
</div>

