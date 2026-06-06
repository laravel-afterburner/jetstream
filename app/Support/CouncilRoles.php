<?php

namespace App\Support;

use App\Models\Role;
use Illuminate\Support\Collection;

class CouncilRoles
{
    /**
     * Role IDs flagged as council roles in role management.
     *
     * @return Collection<int, int>
     */
    public static function roleIds(): Collection
    {
        return Role::query()
            ->where('show_in_directory_council', true)
            ->pluck('id');
    }

    /**
     * Role slugs flagged as council roles in role management.
     *
     * @return array<int, string>
     */
    public static function slugs(): array
    {
        return Role::query()
            ->where('show_in_directory_council', true)
            ->pluck('slug')
            ->all();
    }
}
