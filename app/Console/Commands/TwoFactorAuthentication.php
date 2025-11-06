<?php

namespace App\Console\Commands;

use App\Models\FeatureFlag;
use App\Support\Features;
use Illuminate\Console\Command;

class TwoFactorAuthentication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afterburner:two-factor-authentication {--disabled : Disable two-factor authentication feature} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable or disable two-factor authentication feature';

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
        if (Features::hasTwoFactorAuthenticationFeatures()) {
            $this->info('Two-factor authentication feature is already enabled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->info('This will enable two-factor authentication (2FA) for users.');
            if (!$this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        FeatureFlag::updateOrCreate(
            ['key' => Features::twoFactorAuthentication()],
            ['enabled' => true]
        );

        $this->info('✓ Two-factor authentication feature enabled.');

        return Command::SUCCESS;
    }

    /**
     * Disable the feature.
     */
    protected function disableFeature(): int
    {
        if (!Features::hasTwoFactorAuthenticationFeatures()) {
            $this->info('Two-factor authentication feature is already disabled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->warn('Disabling two-factor authentication will remove 2FA functionality for users.');
            if (!$this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        FeatureFlag::updateOrCreate(
            ['key' => Features::twoFactorAuthentication()],
            ['enabled' => false]
        );

        $this->info('✓ Two-factor authentication feature disabled.');
        $this->comment('To re-enable: php artisan afterburner:two-factor-authentication');

        return Command::SUCCESS;
    }
}

