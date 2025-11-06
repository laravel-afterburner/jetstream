<?php

namespace App\Console\Commands;

use App\Models\FeatureFlag;
use App\Support\Features;
use Illuminate\Console\Command;

class TeamAnnouncements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afterburner:team-announcements {--disabled : Disable team announcements feature} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable or disable team announcements feature';

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
        if (Features::hasTeamAnnouncements()) {
            $this->info('Team announcements feature is already enabled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->info('This will enable team announcements functionality.');
            if (!$this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        FeatureFlag::updateOrCreate(
            ['key' => Features::teamAnnouncements()],
            ['enabled' => true]
        );

        $this->info('✓ Team announcements feature enabled.');

        return Command::SUCCESS;
    }

    /**
     * Disable the feature.
     */
    protected function disableFeature(): int
    {
        if (!Features::hasTeamAnnouncements()) {
            $this->info('Team announcements feature is already disabled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->warn('Disabling team announcements will remove announcement functionality.');
            if (!$this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        FeatureFlag::updateOrCreate(
            ['key' => Features::teamAnnouncements()],
            ['enabled' => false]
        );

        $this->info('✓ Team announcements feature disabled.');
        $this->comment('To re-enable: php artisan afterburner:team-announcements');

        return Command::SUCCESS;
    }
}

