<?php

namespace Database\Seeders;

use App\Support\PackageSeederRegistry;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            SystemAdminSeeder::class,
        ]);

        foreach (PackageSeederRegistry::all() as $seederClass) {
            if (class_exists($seederClass)) {
                $this->call($seederClass);
            }
        }
    }
}
