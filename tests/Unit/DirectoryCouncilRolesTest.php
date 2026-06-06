<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Support\DirectoryCouncilRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectoryCouncilRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_ids_include_only_roles_marked_for_directory_council(): void
    {
        $council = Role::query()->create([
            'name' => 'Vice President',
            'slug' => 'vice_president',
            'hierarchy' => 2,
            'show_in_directory_council' => true,
        ]);

        Role::query()->create([
            'name' => 'Strata Owner',
            'slug' => 'strata_owner',
            'hierarchy' => 5,
            'is_default' => true,
            'show_in_directory_council' => false,
        ]);

        $this->assertSame([$council->id], DirectoryCouncilRoles::roleIds()->all());
        $this->assertSame(['vice_president'], DirectoryCouncilRoles::slugs());
    }
}
