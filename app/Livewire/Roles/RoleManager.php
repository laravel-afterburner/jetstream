<?php

namespace App\Livewire\Roles;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class RoleManager extends Component
{
    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;


    /**
     * Indicates if the application is editing a role.
     *
     * @var bool
     */
    public $editingRole = false;

    /**
     * The role being edited.
     *
     * @var mixed
     */
    public $roleBeingEdited = null;

    /**
     * The "create role" form state.
     *
     * @var array
     */
    public $createRoleForm = [
        'name' => '',
        'description' => '',
        'badge_color' => 'gray',
        'icon' => 'member.svg',
        'max_members' => null,
        'permissions' => [],
    ];

    /**
     * Indicates if the application is copying a role.
     *
     * @var bool
     */
    public $copyingRole = false;

    /**
     * The role being copied.
     *
     * @var mixed
     */
    public $roleBeingCopied = null;

    /**
     * The "edit role" form state.
     *
     * @var array
     */
    public $editRoleForm = [
        'name' => '',
        'slug' => '',
        'description' => '',
        'badge_color' => 'gray',
        'icon' => 'member.svg',
        'max_members' => null,
        'permissions' => [],
    ];

    /**
     * Indicates if the application is confirming role deletion.
     *
     * @var bool
     */
    public $confirmingRoleDeletion = false;

    /**
     * The role being deleted.
     *
     * @var mixed
     */
    public $roleBeingDeleted = null;

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        // Handle both model instances and ID strings
        if (is_string($team) || is_numeric($team)) {
            $this->team = Team::findOrFail($team);
        } else {
            $this->team = $team;
        }
    }

    /**
     * Generate slug from role name.
     *
     * @param string $name
     * @return string
     */
    public function generateSlug($name)
    {
        $baseSlug = str_replace('-', '_', \Str::slug($name));
        $slug = $baseSlug;
        $counter = 1;

        // Check for uniqueness and add counter if needed
        while (Role::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '_' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Update the slug when the role name changes.
     *
     * @return void
     */
    public function updatedCreateRoleFormName()
    {
        $this->createRoleForm['slug'] = $this->generateSlug($this->createRoleForm['name']);
    }

    /**
     * Update the slug when the role name changes in edit form.
     *
     * @return void
     */
    public function updatedEditRoleFormName()
    {
        // Auto-generate slug from name for edit form (snake_case)
        $this->editRoleForm['slug'] = str_replace('-', '_', \Str::slug($this->editRoleForm['name']));
    }

    /**
     * Create a new role.
     *
     * @return void
     */
    public function createRole()
    {
        $this->resetErrorBag();

        if (! Gate::check('createRole', $this->team)) {
            return;
        }

        // Auto-generate slug from name
        $slug = $this->generateSlug($this->createRoleForm['name']);

        $this->validate([
            'createRoleForm.name' => 'required|string|max:255|unique:roles,name',
            'createRoleForm.description' => 'nullable|string|max:500',
            'createRoleForm.badge_color' => 'required|string',
            'createRoleForm.icon' => 'required|string',
            'createRoleForm.max_members' => 'nullable|integer|min:1',
        ], [
            'createRoleForm.name.unique' => 'This role name has already been taken.',
            'createRoleForm.name.required' => 'The role name field is required.',
            'createRoleForm.name.max' => 'The role name may not be greater than 255 characters.',
            'createRoleForm.description.max' => 'The description may not be greater than 500 characters.',
            'createRoleForm.badge_color.required' => 'The badge color field is required.',
            'createRoleForm.icon.required' => 'The icon field is required.',
            'createRoleForm.max_members.integer' => 'The member limit must be a number.',
            'createRoleForm.max_members.min' => 'The member limit must be at least 1.',
        ]);

        // Set hierarchy to the end of the list
        $maxHierarchy = Role::max('hierarchy') ?? 0;
        
        $role = Role::create([
            'name' => $this->createRoleForm['name'],
            'slug' => $slug,
            'description' => $this->createRoleForm['description'],
            'badge_color' => $this->createRoleForm['badge_color'],
            'icon' => $this->createRoleForm['icon'],
            'hierarchy' => $maxHierarchy + 1,
            'max_members' => $this->createRoleForm['max_members'],
            'is_default' => false,
        ]);

        // Assign permissions
        if (!empty($this->createRoleForm['permissions'])) {
            $role->permissions()->sync($this->createRoleForm['permissions']);
        }

        $this->resetCreateRoleForm();

        $this->dispatch('saved');
    }

    /**
     * Edit a role.
     *
     * @param  int  $roleId
     * @return void
     */
    public function editRole($roleId)
    {
        $role = Role::findOrFail($roleId);

        if (! Gate::check('updateRole', [$this->team, $role])) {
            return;
        }
        $this->roleBeingEdited = $role;
        $this->editRoleForm = [
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => $role->description,
            'badge_color' => $role->badge_color ?: 'gray',
            'icon' => $role->icon ?: 'member.svg',
            'max_members' => $role->max_members,
            'permissions' => $role->permissions->pluck('id')->toArray(),
        ];

        $this->editingRole = true;
    }

    /**
     * Copy a role to create a new one.
     *
     * @param  int  $roleId
     * @return void
     */
    public function copyRole($roleId)
    {
        $role = Role::findOrFail($roleId);

        if (! Gate::check('viewRole', [$this->team, $role])) {
            return;
        }
        $this->roleBeingCopied = $role;
        
        // Populate the create form with the role's data
        $this->createRoleForm = [
            'name' => $role->name . ' (Copy)',
            'description' => $role->description,
            'badge_color' => $role->badge_color ?: 'gray',
            'icon' => $role->icon ?: 'member.svg',
            'max_members' => $role->max_members,
            'permissions' => $role->permissions->pluck('id')->toArray(),
        ];

        $this->copyingRole = true;
        
        // Scroll to the create role form
        $this->dispatch('scroll-to-create-form');
    }

    /**
     * Cancel role copying.
     *
     * @return void
     */
    public function cancelCopyRole()
    {
        $this->resetErrorBag();
        $this->resetCreateRoleForm();
        $this->copyingRole = false;
        $this->roleBeingCopied = null;
    }

    /**
     * Update the role being edited.
     *
     * @return void
     */
    public function updateRole()
    {
        $this->resetErrorBag();

        if (! Gate::check('updateRole', [$this->team, $this->roleBeingEdited])) {
            return;
        }

        // Generate new slug from the updated name (snake_case)
        $newSlug = str_replace('-', '_', \Str::slug($this->editRoleForm['name']));

        $this->validate([
            'editRoleForm.name' => 'required|string|max:255|unique:roles,name,' . $this->roleBeingEdited->id,
            'editRoleForm.description' => 'nullable|string|max:500',
            'editRoleForm.badge_color' => 'required|string',
            'editRoleForm.icon' => 'required|string',
            'editRoleForm.max_members' => 'nullable|integer|min:1',
        ], [
            'editRoleForm.name.unique' => 'This role name has already been taken.',
            'editRoleForm.name.required' => 'The role name field is required.',
            'editRoleForm.name.max' => 'The role name may not be greater than 255 characters.',
            'editRoleForm.description.max' => 'The description may not be greater than 500 characters.',
            'editRoleForm.badge_color.required' => 'The badge color field is required.',
            'editRoleForm.icon.required' => 'The icon field is required.',
            'editRoleForm.max_members.integer' => 'The member limit must be a number.',
            'editRoleForm.max_members.min' => 'The member limit must be at least 1.',
        ]);

        // Prepare update data
        $updateData = [
            'name' => $this->editRoleForm['name'],
            'slug' => $newSlug,
            'description' => $this->editRoleForm['description'],
            'badge_color' => $this->editRoleForm['badge_color'],
            'icon' => $this->editRoleForm['icon'],
            'max_members' => $this->editRoleForm['max_members'],
        ];

        // Never allow changing the is_default flag
        // (it's not in the form, but just to be safe)
        $this->roleBeingEdited->update($updateData);

        // Update permissions
        $this->roleBeingEdited->permissions()->sync($this->editRoleForm['permissions']);

        $this->resetEditRoleForm();
        $this->editingRole = false;

        $this->dispatch('saved');
    }

    /**
     * Confirm role deletion.
     *
     * @param  int  $roleId
     * @return void
     */
    public function confirmRoleDeletion($roleId)
    {
        $role = Role::findOrFail($roleId);

        if (! Gate::check('deleteRole', [$this->team, $role])) {
            return;
        }
        
        // Don't allow deleting default roles
        if ($role->is_default) {
            return;
        }

        $this->roleBeingDeleted = $role;
        $this->confirmingRoleDeletion = true;
    }

    /**
     * Delete the role.
     *
     * @return void
     */
    public function deleteRole()
    {
        if (! Gate::check('deleteRole', [$this->team, $this->roleBeingDeleted])) {
            return;
        }

        if ($this->roleBeingDeleted) {
            $this->roleBeingDeleted->delete();
        }

        $this->confirmingRoleDeletion = false;
        $this->roleBeingDeleted = null;

        $this->dispatch('saved');
    }


    /**
     * Cancel role editing.
     *
     * @return void
     */
    public function cancelEditRole()
    {
        $this->resetErrorBag();
        $this->resetEditRoleForm();
        $this->editingRole = false;
    }

    /**
     * Cancel role deletion.
     *
     * @return void
     */
    public function cancelRoleDeletion()
    {
        $this->confirmingRoleDeletion = false;
        $this->roleBeingDeleted = null;
    }

    /**
     * Reset the create role form.
     *
     * @return void
     */
    public function resetCreateRoleForm()
    {
        $this->resetErrorBag();
        $this->createRoleForm = [
            'name' => '',
            'description' => '',
            'badge_color' => 'gray',
            'icon' => 'member.svg',
            'max_members' => null,
            'permissions' => [],
        ];
        $this->copyingRole = false;
        $this->roleBeingCopied = null;
    }

    /**
     * Reset the edit role form.
     *
     * @return void
     */
    public function resetEditRoleForm()
    {
        $this->editRoleForm = [
            'name' => '',
            'slug' => '',
            'description' => '',
            'badge_color' => 'gray',
            'icon' => 'member.svg',
            'max_members' => null,
            'permissions' => [],
        ];
        $this->roleBeingEdited = null;
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return Auth::user();
    }

    /**
     * Get the available roles.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRolesProperty()
    {
        return Role::orderBy('hierarchy')->get();
    }

    /**
     * Get the available permissions.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPermissionsProperty()
    {
        return Permission::orderBy('name')->get();
    }

    /**
     * Get the badge color options.
     *
     * @return array
     */
    public function getBadgeColorOptionsProperty()
    {
        return config('badge-colors.options', []);
    }

    /**
     * Get the badge color class for a role.
     */
    public function getRoleBadgeColor($roleSlug)
    {
        $storedValue = Role::where('slug', $roleSlug)->value('badge_color');

        // Default classes if nothing stored
        $default = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';

        if (! $storedValue) {
            return $default;
        }

        // If it's a palette key, resolve via config
        if (config("badge-colors.options.$storedValue.classes")) {
            return config("badge-colors.options.$storedValue.classes");
        }

        // Otherwise treat as stored class string
        return $storedValue ?: $default;
    }

    /**
     * Update role hierarchy based on drag and drop order.
     *
     * @param array $hierarchyData
     * @return void
     */
    public function updateRoleHierarchy(array $hierarchyData): void
    {
        if (! Gate::check('updateRoleHierarchy', $this->team)) {
            return;
        }

        // Validate input structure
        $validated = \Validator::make($hierarchyData, [
            '*.role_id' => 'required|integer|exists:roles,id',
            '*.hierarchy' => 'required|integer|min:1',
        ])->validate();

        foreach ($validated as $data) {
            Role::where('id', $data['role_id'])
                ->update(['hierarchy' => $data['hierarchy']]);
        }

        $this->dispatch('saved');
    }

    /**
     * Get the icon path for a role.
     */
    public function getRoleIcon($roleSlug)
    {
        // First try to get the icon from the database
        $role = Role::where('slug', $roleSlug)->first();
        if ($role && $role->icon) {
            return $role->icon;
        }

        // Fallback to the old hardcoded mapping for backward compatibility
        $iconMap = [
            'president' => 'leader.svg',
            'vice_president' => 'deputy.svg',
            'treasurer' => 'finance.svg',
            'secretary' => 'records.svg',
            'council_member' => 'governance.svg',
            'strata_member' => 'member.svg',
        ];

        return $iconMap[$roleSlug] ?? 'member.svg';
    }

    /**
     * Get available icon options for role selection.
     */
    public function getIconOptions()
    {
        return config('role-icons.options', []);
    }

    /**
     * Get icon options for the view.
     */
    public function getIconOptionsProperty()
    {
        return $this->getIconOptions();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('roles.role-manager');
    }
}
