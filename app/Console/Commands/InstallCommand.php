<?php

namespace App\Console\Commands;

use App\Support\PackageSeederRegistry;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'afterburner:install
                            {--force : Overwrite published files}
                            {--no-migrate : Skip running migrations}
                            {--no-seed : Skip seeding package permissions}';

    protected $description = 'Install Afterburner packages (documents, voting, meetings, communications when present)';

    public function handle(): int
    {
        $this->info('Installing Afterburner packages...');

        $force = $this->option('force') ? ['--force' => true] : [];

        $publishGroups = [
            'Documents' => ['afterburner-documents-config', 'afterburner-documents-assets'],
            'Voting' => ['afterburner-voting-config', 'afterburner-voting-assets'],
            'Meetings' => ['afterburner-meetings-config', 'afterburner-meetings-assets'],
            'Communications' => ['afterburner-communications-config', 'afterburner-communications-assets'],
        ];

        foreach ($publishGroups as $label => $tags) {
            $this->components->task($label.' package', function () use ($tags, $force) {
                foreach ($tags as $tag) {
                    $this->callSilently('vendor:publish', array_merge(['--tag' => $tag], $force));
                }

                return true;
            });
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
