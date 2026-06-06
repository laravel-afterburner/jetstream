<?php

namespace App\Livewire\Roles;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use App\Support\PermissionGroups;
use App\Support\TeamRolePermissions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RoleManager extends Component
{
    public $team;

    public $editingRole = false;

    public $roleBeingEdited = null;

    public $createRoleForm = [
        'name' => '',
        'description' => '',
        'badge_color' => 'gray',
        'max_members' => null,
        'show_in_directory_council' => false,
        'permissions' => [],
    ];

    public $copyingRole = false;

    public $roleBeingCopied = null;

    public $editRoleForm = [
        'description' => '',
        'max_members' => null,
        'show_in_directory_council' => false,
        'permissions' => [],
    ];

    public $confirmingRoleDeletion = false;

    public $roleBeingDeleted = null;

    public function mount($team)
    {
        if (is_string($team) || is_numeric($team)) {
            $this->team = Team::findOrFail($team);
        } else {
            $this->team = $team;
        }
    }

    public function generateSlug(string $name): string
    {
        $baseSlug = str_replace('-', '_', \Str::slug($name));
        $slug = $baseSlug;
        $counter = 1;

        while ($this->slugExistsForTeam($slug)) {
            $slug = $baseSlug.'_'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugExistsForTeam(string $slug): bool
    {
        if (in_array($slug, TeamRolePermissions::systemRoleSlugs(), true)) {
            return true;
        }

        return Role::query()
            ->where('team_id', $this->team->id)
            ->where('slug', $slug)
            ->exists();
    }

    public function updatedCreateRoleFormName(): void
    {
        $this->createRoleForm['slug'] = $this->generateSlug($this->createRoleForm['name']);
    }

    public function createRole(): void
    {
        $this->resetErrorBag();

        if (! Gate::check('createRole', $this->team)) {
            return;
        }

        $slug = $this->generateSlug($this->createRoleForm['name']);

        $this->validate([
            'createRoleForm.name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where(fn ($q) => $q->where('team_id', $this->team->id)),
            ],
            'createRoleForm.description' => 'nullable|string|max:500',
            'createRoleForm.badge_color' => 'required|string',
            'createRoleForm.max_members' => 'nullable|integer|min:1',
            'createRoleForm.show_in_directory_council' => 'boolean',
        ]);

        $maxHierarchy = Role::query()
            ->forTeam($this->team->id)
            ->max('hierarchy') ?? 0;

        $role = Role::create([
            'name' => $this->createRoleForm['name'],
            'slug' => $slug,
            'description' => $this->createRoleForm['description'],
            'badge_color' => $this->createRoleForm['badge_color'],
            'hierarchy' => $maxHierarchy + 1,
            'show_in_directory_council' => (bool) $this->createRoleForm['show_in_directory_council'],
            'is_default' => false,
            'is_system' => false,
            'team_id' => $this->team->id,
        ]);

        TeamRolePermissions::syncForRole($role, $this->team->id, $this->createRoleForm['permissions']);
        TeamRolePermissions::setMaxMembers($role, $this->team->id, $this->createRoleForm['max_members']);

        $this->resetCreateRoleForm();
        $this->dispatch('saved');
    }

    public function editRole($roleId): void
    {
        $role = Role::findOrFail($roleId);

        if (! Gate::check('updateRole', [$this->team, $role])) {
            return;
        }

        $this->roleBeingEdited = $role;
        $this->editRoleForm = [
            'description' => $role->description,
            'max_members' => TeamRolePermissions::maxMembersForRole($role, $this->team->id),
            'show_in_directory_council' => $role->show_in_directory_council,
            'permissions' => TeamRolePermissions::permissionIdsForRole($role, $this->team->id),
        ];

        $this->editingRole = true;
    }

    public function copyRole($roleId): void
    {
        $role = Role::findOrFail($roleId);

        if (! Gate::check('viewRole', [$this->team, $role])) {
            return;
        }

        $this->roleBeingCopied = $role;
        $this->createRoleForm = [
            'name' => $role->name.' (Copy)',
            'description' => $role->description,
            'badge_color' => $role->badge_color ?: 'gray',
            'max_members' => TeamRolePermissions::maxMembersForRole($role, $this->team->id),
            'show_in_directory_council' => $role->show_in_directory_council,
            'permissions' => TeamRolePermissions::permissionIdsForRole($role, $this->team->id),
        ];

        $this->copyingRole = true;
        $this->dispatch('scroll-to-create-form');
    }

    public function cancelCopyRole(): void
    {
        $this->resetErrorBag();
        $this->resetCreateRoleForm();
        $this->copyingRole = false;
        $this->roleBeingCopied = null;
    }

    public function updateRole(): void
    {
        $this->resetErrorBag();

        if (! Gate::check('updateRole', [$this->team, $this->roleBeingEdited])) {
            return;
        }

        $role = $this->roleBeingEdited;

        if ($role->isSystemRole()) {
            $this->validate([
                'editRoleForm.max_members' => 'nullable|integer|min:1',
                'editRoleForm.show_in_directory_council' => 'boolean',
            ]);
        } else {
            $this->validate([
                'editRoleForm.description' => 'nullable|string|max:500',
                'editRoleForm.max_members' => 'nullable|integer|min:1',
                'editRoleForm.show_in_directory_council' => 'boolean',
            ]);

            $role->update([
                'description' => $this->editRoleForm['description'],
            ]);
        }

        if (! $role->is_default) {
            $role->update([
                'show_in_directory_council' => (bool) $this->editRoleForm['show_in_directory_council'],
            ]);
        }

        TeamRolePermissions::syncForRole($role, $this->team->id, $this->editRoleForm['permissions']);
        TeamRolePermissions::setMaxMembers($role, $this->team->id, $this->editRoleForm['max_members']);

        $this->resetEditRoleForm();
        $this->editingRole = false;
        $this->dispatch('saved');
    }

    public function confirmRoleDeletion($roleId): void
    {
        $role = Role::findOrFail($roleId);

        if (! Gate::check('deleteRole', [$this->team, $role])) {
            return;
        }

        if ($role->is_system || $role->is_default) {
            return;
        }

        if ($role->team_id !== $this->team->id) {
            return;
        }

        $this->roleBeingDeleted = $role;
        $this->confirmingRoleDeletion = true;
    }

    public function deleteRole(): void
    {
        if (! Gate::check('deleteRole', [$this->team, $this->roleBeingDeleted])) {
            return;
        }

        $role = $this->roleBeingDeleted;

        if ($role && ! $role->is_system && $role->team_id === $this->team->id) {
            $role->delete();
        }

        $this->confirmingRoleDeletion = false;
        $this->roleBeingDeleted = null;
        $this->dispatch('saved');
    }

    public function cancelEditRole(): void
    {
        $this->resetErrorBag();
        $this->resetEditRoleForm();
        $this->editingRole = false;
    }

    public function cancelRoleDeletion(): void
    {
        $this->confirmingRoleDeletion = false;
        $this->roleBeingDeleted = null;
    }

    public function resetCreateRoleForm(): void
    {
        $this->resetErrorBag();
        $this->createRoleForm = [
            'name' => '',
            'description' => '',
            'badge_color' => 'gray',
            'max_members' => null,
            'show_in_directory_council' => false,
            'permissions' => [],
        ];
        $this->copyingRole = false;
        $this->roleBeingCopied = null;
    }

    public function resetEditRoleForm(): void
    {
        $this->editRoleForm = [
            'description' => '',
            'max_members' => null,
            'show_in_directory_council' => false,
            'permissions' => [],
        ];
        $this->roleBeingEdited = null;
    }

    public function getUserProperty()
    {
        return Auth::user();
    }

    public function getRolesProperty(): Collection
    {
        return TeamRolePermissions::rolesForTeam($this->team->id)
            ->map(function (Role $role) {
                $role->setAttribute(
                    'max_members',
                    TeamRolePermissions::maxMembersForRole($role, $this->team->id)
                );

                return $role;
            });
    }

    public function getGroupedPermissionsProperty(): array
    {
        return PermissionGroups::group($this->permissions);
    }

    public function getPermissionsProperty(): Collection
    {
        return Permission::orderBy('name')->get();
    }

    public function getBadgeColorOptionsProperty(): array
    {
        return config('badge-colors.options', []);
    }

    public function getRoleBadgeColor($roleSlug): string
    {
        $role = TeamRolePermissions::resolveRoleSlug($roleSlug, $this->team->id)
            ?? Role::where('slug', $roleSlug)->first();

        $storedValue = $role?->badge_color;

        $default = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';

        if (! $storedValue) {
            return $default;
        }

        if (config("badge-colors.options.$storedValue.classes")) {
            return config("badge-colors.options.$storedValue.classes");
        }

        return $storedValue ?: $default;
    }

    public function updateRoleHierarchy(array $hierarchyData): void
    {
        if (! Gate::check('updateRoleHierarchy', $this->team)) {
            return;
        }

        $validated = \Validator::make($hierarchyData, [
            '*.role_id' => 'required|integer|exists:roles,id',
            '*.hierarchy' => 'required|integer|min:1',
        ])->validate();

        foreach ($validated as $data) {
            $role = Role::find($data['role_id']);

            if (! $role || $role->is_system || $role->team_id !== $this->team->id) {
                continue;
            }

            $role->update(['hierarchy' => $data['hierarchy']]);
        }

        $this->dispatch('saved');
    }

    public function render()
    {
        return view('roles.role-manager');
    }
}
