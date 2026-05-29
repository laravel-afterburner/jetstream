<?php

namespace Tests\Support;

use Database\Seeders\RolesSeeder;

trait SeedsCompanyRoles
{
    protected function seedCompanyRoles(): void
    {
        $this->app->make(RolesSeeder::class)->run('company');
    }
}
