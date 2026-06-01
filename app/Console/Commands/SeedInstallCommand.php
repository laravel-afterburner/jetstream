<?php

namespace App\Console\Commands;

use App\Support\AfterburnerInstallConfig;
use App\Support\PackageSeederRegistry;
use Database\Seeders\RolesSeeder;
use Database\Seeders\SystemAdminSeeder;
use Illuminate\Console\Command;

class SeedInstallCommand extends Command
{
    protected $signature = 'afterburner:seed-install
                            {--entity= : Entity label (team, strata, company, organization)}
                            {--admin-name= : System admin display name for local seeding}
                            {--admin-email= : System admin email for local seeding}
                            {--skip-admin : Skip creating the system admin user}
                            {--skip-packages : Skip package permission seeders}';

    protected $description = 'Seed roles, optional system admin, and package permissions for a fresh install';

    public function handle(): int
    {
        $entity = $this->option('entity');

        if ($entity !== null && $entity !== '') {
            AfterburnerInstallConfig::setEntityLabel($entity);
            $this->callSilent('config:clear');
            $this->components->info("Entity label set to {$entity} in config/afterburner.php");
        }

        $template = $entity ?: config('afterburner.entity_label', 'company');

        $this->call(RolesSeeder::class, false, ['template' => $template]);

        if (! $this->option('skip-admin')) {
            if (app()->environment('production')) {
                $this->components->warn('Skipping system admin seeder in production.');
            } else {
                SystemAdminSeeder::configureInstall(
                    $this->option('admin-name'),
                    $this->option('admin-email')
                );

                $this->call(SystemAdminSeeder::class);

                SystemAdminSeeder::configureInstall(null, null);
            }
        }

        if (! $this->option('skip-packages')) {
            foreach (PackageSeederRegistry::all() as $seederClass) {
                if (class_exists($seederClass)) {
                    $this->call($seederClass);
                }
            }
        }

        $this->components->info('Afterburner install seeding complete.');

        return Command::SUCCESS;
    }
}
