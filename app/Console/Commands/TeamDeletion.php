<?php

namespace App\Console\Commands;

use App\Models\FeatureFlag;
use App\Support\Features;
use Illuminate\Console\Command;

class TeamDeletion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afterburner:team-deletion {--disabled : Disable team deletion feature} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable or disable team deletion feature';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $disabled = $this->option('disabled');

        if ($disabled) {
            return $this->disableFeature();
        }

        return $this->enableFeature();
    }

    /**
     * Enable the feature.
     */
    protected function enableFeature(): int
    {
        if (Features::hasTeamDeletionFeatures()) {
            $this->info('Team deletion feature is already enabled.');
            return Command::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->info('This will enable team deletion functionality for authorized users.');
            if (! $this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        FeatureFlag::updateOrCreate(
            ['key' => Features::teamDeletion()],
            ['enabled' => true]
        );

        $this->info('✓ Team deletion feature enabled.');

        return Command::SUCCESS;
    }

    /**
     * Disable the feature.
     */
    protected function disableFeature(): int
    {
        if (! Features::hasTeamDeletionFeatures()) {
            $this->info('Team deletion feature is already disabled.');
            return Command::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->warn('Disabling team deletion will prevent users from deleting teams.');
            if (! $this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        FeatureFlag::updateOrCreate(
            ['key' => Features::teamDeletion()],
            ['enabled' => false]
        );

        $this->info('✓ Team deletion feature disabled.');
        $this->comment('To re-enable: php artisan afterburner:team-deletion');

        return Command::SUCCESS;
    }
}
