<?php

namespace App\Console\Commands;

use App\Models\FeatureFlag;
use App\Support\Features;
use Illuminate\Console\Command;

class ProfilePhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afterburner:profile-photos {--disabled : Disable profile photos feature} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable or disable profile photos feature';

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
        if (Features::managesProfilePhotos()) {
            $this->info('Profile photos feature is already enabled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->info('This will enable profile photo uploads for users.');
            if (!$this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        FeatureFlag::updateOrCreateByKey(
            Features::profilePhotos(),
            ['enabled' => true]
        );

        $this->info('✓ Profile photos feature enabled.');

        return Command::SUCCESS;
    }

    /**
     * Disable the feature.
     */
    protected function disableFeature(): int
    {
        if (!Features::managesProfilePhotos()) {
            $this->info('Profile photos feature is already disabled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->warn('Disabling profile photos will prevent users from uploading profile photos.');
            if (!$this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        FeatureFlag::updateOrCreateByKey(
            Features::profilePhotos(),
            ['enabled' => false]
        );

        $this->info('✓ Profile photos feature disabled.');
        $this->comment('To re-enable: php artisan afterburner:profile-photos');

        return Command::SUCCESS;
    }
}

