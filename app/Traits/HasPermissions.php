<?php

namespace App\Traits;

use App\Models\Permission;

trait HasPermissions
{
    /**
     * Check if this role has a specific permission.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()
            ->where('slug', $permissionSlug)
            ->exists();
    }

    /**
     * Give a permission to this role.
     */
    public function givePermission(string $permissionSlug): void
    {
        $permission = Permission::where('slug', $permissionSlug)->firstOrFail();
        $this->permissions()->syncWithoutDetaching($permission->id);
    }

    /**
     * Revoke a permission from this role.
     */
    public function revokePermission(string $permissionSlug): void
    {
        $permission = Permission::where('slug', $permissionSlug)->first();
        
        if ($permission) {
            $this->permissions()->detach($permission->id);
        }
    }
}

