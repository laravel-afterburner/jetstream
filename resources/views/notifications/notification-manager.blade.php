<div>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            @if($this->notifications->count() > 0)
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        @if($this->unreadCount > 0)
                            You have {{ $this->unreadCount }} unread {{ Str::plural('notification', $this->unreadCount) }}
                        @else
                            All notifications
                        @endif
                    </h3>
                    @if($this->unreadCount > 0)
                        <button 
                            wire:click="markAllAsRead" 
                            wire:loading.attr="disabled"
                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">
                            Mark all as read
                        </button>
                    @endif
                </div>

                <div class="space-y-4">
                    @foreach($this->notifications as $notification)
                        @php
                            $status = $notification->data['status'] ?? 'pending';
                            $isUnread = is_null($notification->read_at);
                        @endphp
                        
                        <div class="border rounded-lg p-4 transition-colors
                            {{ $isUnread ? 'border-indigo-200 bg-indigo-50 dark:border-indigo-800 dark:bg-indigo-900/20' : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/50' }}">
                            
                            @if($notification->type === 'App\Notifications\TeamInvitationNotification')
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 flex-wrap mb-2">
                                            @if($isUnread)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                    Unread
                                                </span>
                                            @endif
                                            
                                            @if($status === 'accepted')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                    {!! '&#10003;' !!} Accepted
                                                </span>
                                            @elseif($status === 'declined')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                    {!! '&times;' !!} Declined
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                    {!! '&#8987;' !!} Pending
                                                </span>
                                            @endif

                                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ Str::title(config('afterburner.entity_label')) }} Invitation
                                            </h4>
                                        </div>
                                        
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            You've been invited to join <strong>{{ $notification->data['team_name'] }}</strong>
                                            @if(isset($notification->data['roles']) && !empty($notification->data['roles']))
                                                with the following {{ Str::plural('role', count($notification->data['roles'])) }}:
                                            @endif
                                        </p>
                                        
                                        @if(isset($notification->data['roles']) && !empty($notification->data['roles']))
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @foreach($notification->data['roles'] as $roleSlug)
                                                    @php
                                                        $role = \App\Models\Role::where('slug', $roleSlug)->first();
                                                    @endphp
                                                    @if($role)
                                                        <button 
                                                            type="button"
                                                            onclick="showRoleDetails('{{ $role->slug }}', '{{ $role->name }}', '{{ addslashes($role->description) }}')"
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer transition-opacity hover:opacity-80 {{ $this->getRoleBadgeColor($role->slug) }}">
                                                            {{ $role->name }}
                                                        </button>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                        
                                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 space-y-1">
                                            @php
                                                $receivedTime = auth()->user()->toUserTimezone($notification->created_at);
                                            @endphp
                                            <p>Received: {{ $receivedTime->format('M j, Y g:i A') }} ({{ $receivedTime->format('T') }})</p>
                                            @if($status === 'accepted' && isset($notification->data['accepted_at']))
                                                @php
                                                    $acceptedTime = auth()->user()->toUserTimezone($notification->data['accepted_at']);
                                                @endphp
                                                <p class="text-green-600 dark:text-green-400">
                                                    Accepted: {{ $acceptedTime->format('M j, Y g:i A') }} ({{ $acceptedTime->format('T') }})
                                                </p>
                                            @elseif($status === 'declined' && isset($notification->data['declined_at']))
                                                @php
                                                    $declinedTime = auth()->user()->toUserTimezone($notification->data['declined_at']);
                                                @endphp
                                                <p class="text-red-600 dark:text-red-400">
                                                    Declined: {{ $declinedTime->format('M j, Y g:i A') }} ({{ $declinedTime->format('T') }})
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    @if($status === 'pending')
                                        <div class="flex flex-col sm:flex-row gap-2 ml-4">
                                            <button 
                                                wire:click="acceptInvitation('{{ $notification->id }}')" 
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                Accept
                                            </button>
                                            <button 
                                                wire:click="declineInvitation('{{ $notification->id }}')" 
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Decline
                                            </button>
                                            @if($isUnread)
                                                <button 
                                                    wire:click="markAsRead('{{ $notification->id }}')" 
                                                    wire:loading.attr="disabled"
                                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    Mark as read
                                                </button>
                                            @endif
                                        </div>
                                    @elseif($isUnread)
                                        <div class="flex flex-col sm:flex-row gap-2 ml-4">
                                            <button 
                                                wire:click="markAsRead('{{ $notification->id }}')" 
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Mark as read
                                            </button>
                                        </div>
                                    @else
                                        {{-- Show delete button for read or actioned notifications --}}
                                        <button 
                                            wire:click="confirmNotificationDeletion('{{ $notification->id }}')" 
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center p-2.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors duration-200 ml-4" 
                                            title="Delete notification">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            @else
                                {{-- Generic notification type --}}
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            @if($isUnread)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                    Unread
                                                </span>
                                            @endif
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                @php
                                                    $type = class_basename($notification->type);
                                                    $formattedType = match($type) {
                                                        'TeamMemberLeft' => 'Team Member Left',
                                                        'TeamDeleted' => 'Team Deleted',
                                                        'TeamInvitationNotification' => 'Team Invitation',
                                                        'TeamInvitationRegistrationRequired' => 'Team Invitation',
                                                        default => ucfirst(str_replace('_', ' ', $type))
                                                    };
                                                @endphp
                                                {{ $formattedType }}
                                            </h4>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $notification->data['message'] ?? 'You have a new notification.' }}
                                        </p>
                                        @php
                                            $receivedTimeCompact = auth()->user()->toUserTimezone($notification->created_at);
                                        @endphp
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Received: {{ $receivedTimeCompact->format('M j, Y g:i A') }} ({{ $receivedTimeCompact->format('T') }})
                                        </p>
                                    </div>
                                    @if($isUnread)
                                        <div class="flex flex-col sm:flex-row gap-2 ml-4">
                                            <button 
                                                wire:click="markAsRead('{{ $notification->id }}')" 
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Mark as read
                                            </button>
                                        </div>
                                    @else
                                        {{-- Show delete button for read notifications --}}
                                        <button 
                                            wire:click="confirmNotificationDeletion('{{ $notification->id }}')" 
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center p-2.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors duration-200 ml-4" 
                                            title="Delete notification">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $this->notifications->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No notifications</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You're all caught up! Check back later for new notifications.</p>
                </div>
            @endif

            <!-- Role Details Modal -->
            <div id="roleDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 dark:bg-gray-900 dark:bg-opacity-75 overflow-y-auto h-full w-full hidden z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800 dark:border-gray-700">
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100" id="roleDetailsTitle">Role Details</h3>
                            <button onclick="closeRoleDetails()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400" id="roleDetailsDescription">Role description will appear here.</p>
                        </div>
                        <div class="flex justify-end">
                            <button onclick="closeRoleDetails()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Notification Confirmation Modal -->
    @if($notificationBeingDeleted)
        <x-confirmation-modal wire:model.live="confirmingNotificationDeletion">
            <x-slot name="title">
                {{ __('Delete Notification') }}
            </x-slot>

            <x-slot name="content">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete this notification? This action cannot be undone.
                </div>
                
                @if($notificationBeingDeleted)
                    <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div class="text-sm font-medium text-red-800 dark:text-red-200">
                            Notification
                        </div>
                        <div class="text-sm text-red-600 dark:text-red-300 mt-1">
                            @if($notificationBeingDeleted->type === 'App\Notifications\TeamInvitationNotification')
                                Invitation for {{ $notificationBeingDeleted->data['team_name'] ?? 'this team' }}
                            @else
                                {{ $notificationBeingDeleted->data['message'] ?? 'This notification' }}
                            @endif
                        </div>
                    </div>
                @endif
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="cancelNotificationDeletion" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3" wire:click="deleteNotification" wire:loading.attr="disabled">
                    {{ __('Delete Notification') }}
                </x-danger-button>
            </x-slot>
        </x-confirmation-modal>
    @endif

    <script>
        function showRoleDetails(slug, name, description) {
            document.getElementById('roleDetailsTitle').textContent = name;
            document.getElementById('roleDetailsDescription').textContent = description || 'No description available.';
            document.getElementById('roleDetailsModal').classList.remove('hidden');
        }

        function closeRoleDetails() {
            document.getElementById('roleDetailsModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('roleDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRoleDetails();
            }
        });
    </script>
</div>
