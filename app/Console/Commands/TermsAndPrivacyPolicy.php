<?php

namespace App\Console\Commands;

use App\Models\FeatureFlag;
use App\Support\Features;
use Illuminate\Console\Command;

class TermsAndPrivacyPolicy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afterburner:terms-and-privacy-policy {--disabled : Disable terms and privacy policy feature} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable or disable terms and privacy policy feature';

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
        if (Features::hasTermsAndPrivacyPolicyFeature()) {
            $this->info('Terms and privacy policy feature is already enabled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->info('This will enable terms and privacy policy confirmation for users.');
            if (!$this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        FeatureFlag::updateOrCreate(
            ['key' => Features::termsAndPrivacyPolicy()],
            ['enabled' => true]
        );

        $this->info('✓ Terms and privacy policy feature enabled.');

        return Command::SUCCESS;
    }

    /**
     * Disable the feature.
     */
    protected function disableFeature(): int
    {
        if (!Features::hasTermsAndPrivacyPolicyFeature()) {
            $this->info('Terms and privacy policy feature is already disabled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->warn('Disabling terms and privacy policy will remove the requirement for users to accept terms.');
            if (!$this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        FeatureFlag::updateOrCreate(
            ['key' => Features::termsAndPrivacyPolicy()],
            ['enabled' => false]
        );

        $this->info('✓ Terms and privacy policy feature disabled.');
        $this->comment('To re-enable: php artisan afterburner:terms-and-privacy-policy');

        return Command::SUCCESS;
    }
}

