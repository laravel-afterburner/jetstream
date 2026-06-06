<?php

namespace App\Support;

use App\Models\Role;
use Illuminate\Support\Collection;

class DirectoryCouncilRoles
{
    /**
     * Role IDs shown in the resident directory council section.
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
     * Role slugs shown in the resident directory council section.
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
