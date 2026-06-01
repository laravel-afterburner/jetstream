<?php

namespace App\Console\Commands;

use App\Support\PackageSeederRegistry;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'afterburner:install
                            {--force : Overwrite published files}
                            {--with-views : Publish package view assets (for intentional customizations only)}
                            {--no-migrate : Skip running migrations}
                            {--no-seed : Skip seeding package permissions}';

    protected $description = 'Install Afterburner packages (config by default; views only with --with-views)';

    public function handle(): int
    {
        $this->info('Installing Afterburner packages...');

        $force = $this->option('force') ? ['--force' => true] : [];

        $publishGroups = [
            'Documents' => ['afterburner-documents-config'],
            'Communications' => ['afterburner-communications-config'],
            'Meetings' => ['afterburner-meetings-config'],
            'Voting' => ['afterburner-voting-config'],
            'Subscriptions' => ['afterburner-subscriptions-config'],
            'Playbook' => ['afterburner-playbook-config'],
        ];

        if ($this->option('with-views')) {
            foreach ($publishGroups as $label => &$tags) {
                $tags[] = str_replace('-config', '-assets', $tags[0]);
            }
            unset($tags);
        }

        foreach ($publishGroups as $label => $tags) {
            $this->components->task($label.' package', function () use ($tags, $force) {
                foreach ($tags as $tag) {
                    $this->callSilently('vendor:publish', array_merge(['--tag' => $tag], $force));
                }

                return true;
            });
        }

        if (! $this->option('with-views')) {
            $this->comment('Skipped view assets. Path-repo package views load from vendor/ automatically.');
            $this->comment('Publish only files you customize: php artisan afterburner:publish --tag=<package>-assets');
        }

        if (! $this->option('no-migrate')) {
            $this->info('Running migrations...');
            $this->call('migrate', ['--force' => true]);
        }

        if (! $this->option('no-seed')) {
            $this->info('Seeding package permissions...');
            foreach (PackageSeederRegistry::all() as $seederClass) {
                $this->seedIfAvailable($seederClass);
            }
        }

        $this->newLine();
        $this->info('Afterburner installation complete.');
        $this->comment('Package migrations load automatically from their service providers.');
        $this->comment('Run `php artisan db:seed` to seed roles if this is a fresh project.');
        $this->comment('Run `php artisan afterburner:audit-integration` to verify host integration conventions.');

        return Command::SUCCESS;
    }

    protected function seedIfAvailable(string $seederClass): void
    {
        if (! class_exists($seederClass)) {
            return;
        }

        $seeder = new $seederClass;
        $seeder->setCommand($this);
        $seeder->run();
    }
}
