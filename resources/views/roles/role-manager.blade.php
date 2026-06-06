<div>
    <x-action-section>
        <x-slot name="title">
            Role Management
        </x-slot>

        <x-slot name="description">
            Create and manage roles for this {{ config('afterburner.entity_label') }}. Roles define what permissions users have within the system.
        </x-slot>

        <x-slot name="content">
            <!-- Roles List -->
            <div class="col-span-6 space-y-4" id="roles-container" wire:ignore.self>
                @foreach($this->roles as $role)
                    <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg {{ $role->is_system ? '' : 'draggable-role' }}" 
                         data-role-id="{{ $role->id }}" 
                         data-hierarchy="{{ $role->hierarchy }}"
                         @if(!$role->is_system) draggable="true" @endif>
                        <div class="flex items-center space-x-4">

                            <!-- Role Info -->
                            <div class="flex-1">
                                <div class="flex items-center text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $role->name }}
                                </div>
                                @if($role->description)
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $role->description }}
                                    </div>
                                @endif
                                <div class="flex items-center mt-2 space-x-4">
                                    @if(!$role->is_default)
                                        <div class="text-xs text-gray-400 dark:text-gray-500">
                                            @if($role->max_members)
                                                Max: {{ $role->max_members }}
                                            @else
                                                No Limit
                                            @endif
                                        </div>
                                    @endif
                                    <!-- Badge Color Preview -->
                                    <div class="flex items-center">
                                        <span class="text-xs text-gray-400 dark:text-gray-500 mr-2">Badge:</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $this->getRoleBadgeColor($role->slug) }}">
                                            {{ $role->name }}
                                        </span>
                                    </div>
                                    @if ($role->show_in_directory_council)
                                        <div class="text-xs text-indigo-600 dark:text-indigo-400">
                                            Directory: Council
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Role Actions -->
                        @if (Gate::check('updateRole', $team))
                            <div class="flex items-center space-x-0.5">
                                <!-- Copy Button -->
                                <button 
                                    type="button"
                                    wire:click="copyRole({{ $role->id }})" 
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center p-2 text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors duration-200"
                                    title="Copy role">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                                
                                <!-- Edit Button -->
                                <button 
                                    type="button"
                                    wire:click="editRole({{ $role->id }})" 
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center p-2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200"
                                    title="Edit role">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                
                                <!-- Delete Button (team custom roles only) -->
                                @if (!$role->is_default && !$role->is_system && $role->team_id === $team->id)
                                    <button 
                                        type="button"
                                        wire:click="confirmRoleDeletion({{ $role->id }})" 
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors duration-200"
                                        title="Delete role">
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
        </x-slot>

    </x-action-section>

    @if (Gate::check('createRole', $team))
        <x-section-border />

        <!-- Add New Role -->
        <div class="mt-10 sm:mt-0" id="create-role-form">
            <x-form-section submit="createRole">
                <x-slot name="title">
                    @if($copyingRole && $roleBeingCopied)
                        Copy Role: {{ $roleBeingCopied->name }}
                    @else
                        Add New Role
                    @endif
                </x-slot>

                <x-slot name="description">
                    @if($copyingRole && $roleBeingCopied)
                        You are copying the "{{ $roleBeingCopied->name }}" role. Modify the details below and create a new role with these settings.
                    @else
                        Create a new role for this {{ config('afterburner.entity_label') }}. Roles define what permissions users have within the system.
                    @endif
                </x-slot>

                <x-slot name="form">
                    <div class="col-span-6">
                        <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                            Please provide the details for the new role you would like to create.
                        </div>
                    </div>

                    <!-- Role Name -->
                    <div class="col-span-6">
                        <x-label for="create_name" value="{{ __('Role Name') }}" />
                        <x-input id="create_name" type="text" class="mt-1 block w-full" wire:model="createRoleForm.name" />
                        <x-input-error for="createRoleForm.name" class="mt-2" />
                    </div>

                    <!-- Role Description -->
                    <div class="col-span-6">
                        <x-label for="create_description" value="{{ __('Description') }}" />
                        <textarea id="create_description" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" wire:model="createRoleForm.description" rows="3"></textarea>
                        <x-input-error for="createRoleForm.description" class="mt-2" />
                    </div>

                    <!-- Badge Color and Max Members -->
                    <div class="col-span-6 sm:col-span-3">
                        <x-label for="create_badge_color" value="{{ __('Badge Color') }}" />
                        <select id="create_badge_color" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" wire:model.live="createRoleForm.badge_color">
                            @foreach($this->badgeColorOptions as $key => $color)
                                <option value="{{ $key }}">{{ $color['label'] }}</option>
                            @endforeach
                        </select>
                        <x-input-error for="createRoleForm.badge_color" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-label for="create_max_members" value="{{ __('Member Limit (Optional)') }}" />
                        <x-input id="create_max_members" type="number" class="mt-1 block w-full" wire:model="createRoleForm.max_members" />
                        <x-input-error for="createRoleForm.max_members" class="mt-2" />
                    </div>

                    <div class="col-span-6">
                        <label class="flex items-center gap-2">
                            <x-checkbox wire:model="createRoleForm.show_in_directory_council" id="create_show_in_directory_council" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Council Role
                            </span>
                        </label>
                    </div>

                    <!-- Permissions -->
                    <div class="col-span-6">
                        <x-label value="{{ __('Permissions') }}" />
                        @include('roles.partials.permissions-panel', ['form' => 'createRoleForm'])
                        <x-input-error for="createRoleForm.permissions" class="mt-2" />
                    </div>
                </x-slot>

                <x-slot name="actions">
                    <x-action-message class="me-3" on="saved">
                        {{ __('Created.') }}
                    </x-action-message>

                    @if($copyingRole)
                        <x-secondary-button wire:click="cancelCopyRole" wire:loading.attr="disabled" class="me-3">
                            {{ __('Cancel Copy') }}
                        </x-secondary-button>
                    @endif

                    <x-button>
                        {{ $copyingRole ? __('Create Copy') : __('Create Role') }}
                    </x-button>
                </x-slot>
            </x-form-section>
        </div>
    @endif

    <!-- Edit Role Modal -->
    <x-dialog-modal wire:model.live="editingRole">
        <x-slot name="title">
            {{ __('Edit Role') }}
        </x-slot>

        <x-slot name="content">
            @if($roleBeingEdited && $roleBeingEdited->is_default)
                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>Default Role:</strong> This is the default role that all users receive automatically. You can edit its properties but it will always remain the default role.
                        </div>
                    </div>
                </div>
            @endif

            <div class="space-y-4">
                <div>
                    <x-label value="{{ __('Role') }}" />
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $roleBeingEdited->name }}</p>
                    @if($roleBeingEdited->is_system)
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('System roles are permanent. You can adjust permissions and member limits for this :entity only.', ['entity' => config('afterburner.entity_label')]) }}</p>
                    @endif
                </div>

                @if(!$roleBeingEdited->is_system)
                    <div>
                        <x-label for="edit_description" value="{{ __('Description') }}" />
                        <textarea id="edit_description" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" wire:model="editRoleForm.description" rows="3"></textarea>
                        <x-input-error for="editRoleForm.description" class="mt-2" />
                    </div>
                @endif

                <div class="max-w-xs">
                    <x-label for="edit_max_members" value="{{ __('Member Limit (Optional)') }}" />
                    <x-input id="edit_max_members" type="number" class="mt-1 block w-full" wire:model="editRoleForm.max_members" />
                    <x-input-error for="editRoleForm.max_members" class="mt-2" />
                </div>

                @unless ($roleBeingEdited?->is_default)
                    <label class="flex items-center gap-2">
                        <x-checkbox wire:model="editRoleForm.show_in_directory_council" id="edit_show_in_directory_council" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            Council Role
                        </span>
                    </label>
                @endunless

                <!-- Permissions -->
                <div>
                    <x-label value="{{ __('Permissions') }}" />
                    @include('roles.partials.permissions-panel', ['form' => 'editRoleForm'])
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="cancelEditRole" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-button class="ms-3" wire:click="updateRole" wire:loading.attr="disabled">
                {{ __('Update Role') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Delete Role Confirmation Modal -->
    @if($roleBeingDeleted && !$roleBeingDeleted->is_default)
        <x-confirmation-modal wire:model.live="confirmingRoleDeletion">
            <x-slot name="title">
                {{ __('Delete Role') }}
            </x-slot>

            <x-slot name="content">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete this role? This action cannot be undone and will remove the role from members of this {{ config('afterburner.entity_label') }} only.
                </div>
                
                @if($roleBeingDeleted)
                    <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div class="text-sm font-medium text-red-800 dark:text-red-200">
                            Role: {{ $roleBeingDeleted->name }}
                        </div>
                        @if($roleBeingDeleted->description)
                            <div class="text-sm text-red-600 dark:text-red-300 mt-1">
                                {{ $roleBeingDeleted->description }}
                            </div>
                        @endif
                    </div>
                @endif
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="cancelRoleDeletion" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3" wire:click="deleteRole" wire:loading.attr="disabled">
                    {{ __('Delete Role') }}
                </x-danger-button>
            </x-slot>
        </x-confirmation-modal>
    @endif
</div>

<script>
document.addEventListener('livewire:init', () => {
    const container = document.getElementById('roles-container');
    
    if (!container) return;

    let sortableInstance = null;

    // Initialize SortableJS when Livewire is ready
    function initSortable() {
        if (sortableInstance) {
            sortableInstance.destroy();
        }

        // Wait for Sortable to be available (from app.js bundle)
        if (typeof Sortable === 'undefined') {
            console.warn('SortableJS not loaded yet');
            return;
        }

        sortableInstance = new Sortable(container, {
            animation: 150,
            handle: '.draggable-role',
            ghostClass: 'dragging',
            onEnd: function(evt) {
                updateHierarchy();
            }
        });
    }

    // Initialize on component mount
    initSortable();

    // Re-initialize when Livewire updates the DOM
    Livewire.hook('morph.updated', () => {
        // Small delay to ensure DOM is ready
        setTimeout(() => {
            initSortable();
        }, 50);
    });

    function updateHierarchy() {
        const roles = container.querySelectorAll('.draggable-role');
        const hierarchyData = [];

        roles.forEach((role, index) => {
            hierarchyData.push({
                role_id: role.getAttribute('data-role-id'),
                hierarchy: index + 1
            });
        });

        if (hierarchyData.length > 0) {
            @this.call('updateRoleHierarchy', hierarchyData);
        }
    }
});

// Handle scroll to create form when copying a role
document.addEventListener('livewire:init', function () {
    Livewire.on('scroll-to-create-form', function () {
        setTimeout(() => {
            const createForm = document.getElementById('create-role-form');
            if (createForm) {
                createForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 100);
    });
});
</script>
