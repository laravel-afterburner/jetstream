<?php

namespace App\Console\Commands;

use App\Models\FeatureFlag;
use App\Support\Features;
use Illuminate\Console\Command;

class Api extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afterburner:api {--disabled : Disable API feature} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable or disable API feature';

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
        if (Features::hasApiFeatures()) {
            $this->info('API feature is already enabled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->info('This will enable API functionality and token management.');
            if (!$this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        FeatureFlag::updateOrCreate(
            ['key' => Features::api()],
            ['enabled' => true]
        );

        $this->info('✓ API feature enabled.');

        return Command::SUCCESS;
    }

    /**
     * Disable the feature.
     */
    protected function disableFeature(): int
    {
        if (!Features::hasApiFeatures()) {
            $this->info('API feature is already disabled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->warn('Disabling API will remove API access and token management functionality.');
            if (!$this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        FeatureFlag::updateOrCreate(
            ['key' => Features::api()],
            ['enabled' => false]
        );

        $this->info('✓ API feature disabled.');
        $this->comment('To re-enable: php artisan afterburner:api');

        return Command::SUCCESS;
    }
}

