<?php

namespace App\Console\Commands;

use App\Models\FeatureFlag;
use App\Support\Features;
use Illuminate\Console\Command;

class Teams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afterburner:teams {--disabled : Disable teams feature} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable or disable teams feature';

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
        if (Features::hasTeamFeatures()) {
            $this->info('Teams feature is already enabled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->warn('Enabling teams feature will enable team management functionality.');
            if (!$this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        FeatureFlag::updateOrCreate(
            ['key' => Features::teams()],
            ['enabled' => true]
        );

        $this->info('✓ Teams feature enabled.');

        return Command::SUCCESS;
    }

    /**
     * Disable the feature.
     */
    protected function disableFeature(): int
    {
        if (!Features::hasTeamFeatures()) {
            $this->info('Teams feature is already disabled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->warn('WARNING: Disabling teams feature will disable all team-related functionality.');
            $this->comment('This may affect user access and team management features.');
            if (!$this->confirm('Are you sure you want to continue?')) {
                return Command::FAILURE;
            }
        }

        FeatureFlag::updateOrCreate(
            ['key' => Features::teams()],
            ['enabled' => false]
        );

        $this->info('✓ Teams feature disabled.');
        $this->comment('To re-enable: php artisan afterburner:teams');

        return Command::SUCCESS;
    }
}

