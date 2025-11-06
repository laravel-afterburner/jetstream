<?php

namespace App\Console\Commands;

use App\Models\FeatureFlag;
use App\Support\Features;
use Illuminate\Console\Command;

class Timezone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afterburner:timezone {--disabled : Disable timezone management feature} {--user : Enable/disable user timezone feature only} {--team : Enable/disable team timezone feature only} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable or disable timezone management features (user and/or team)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $disabled = $this->option('disabled');
        $userOnly = $this->option('user');
        $teamOnly = $this->option('team');

        // If neither --user nor --team is specified, handle both
        $handleUser = !$teamOnly;
        $handleTeam = !$userOnly;

        if ($disabled) {
            return $this->disableFeatures($handleUser, $handleTeam);
        }

        return $this->enableFeatures($handleUser, $handleTeam);
    }

    /**
     * Enable the features.
     */
    protected function enableFeatures(bool $handleUser, bool $handleTeam): int
    {
        $userEnabled = !$handleUser || Features::hasUserTimezoneManagement();
        $teamEnabled = !$handleTeam || Features::hasTeamTimezoneManagement();

        if ($userEnabled && $teamEnabled) {
            $features = [];
            if ($handleUser) $features[] = 'user timezone';
            if ($handleTeam) $features[] = 'team timezone';
            $this->info('Timezone management features (' . implode(' and ', $features) . ') are already enabled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $features = [];
            if ($handleUser) $features[] = 'user timezone';
            if ($handleTeam) $features[] = 'team timezone';
            $this->info('This will enable timezone management functionality for: ' . implode(' and ', $features) . '.');
            if (!$this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        if ($handleUser && !$userEnabled) {
            FeatureFlag::updateOrCreate(
                ['key' => Features::userTimezone()],
                ['enabled' => true]
            );
            $this->info('✓ User timezone management feature enabled.');
        }

        if ($handleTeam && !$teamEnabled) {
            FeatureFlag::updateOrCreate(
                ['key' => Features::teamTimezone()],
                ['enabled' => true]
            );
            $this->info('✓ Team timezone management feature enabled.');
        }

        return Command::SUCCESS;
    }

    /**
     * Disable the features.
     */
    protected function disableFeatures(bool $handleUser, bool $handleTeam): int
    {
        $userDisabled = !$handleUser || !Features::hasUserTimezoneManagement();
        $teamDisabled = !$handleTeam || !Features::hasTeamTimezoneManagement();

        if ($userDisabled && $teamDisabled) {
            $features = [];
            if ($handleUser) $features[] = 'user timezone';
            if ($handleTeam) $features[] = 'team timezone';
            $this->info('Timezone management features (' . implode(' and ', $features) . ') are already disabled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $features = [];
            if ($handleUser) $features[] = 'user timezone';
            if ($handleTeam) $features[] = 'team timezone';
            $this->warn('Disabling timezone management will remove timezone-related functionality for: ' . implode(' and ', $features) . '.');
            if (!$this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        if ($handleUser && !$userDisabled) {
            FeatureFlag::updateOrCreate(
                ['key' => Features::userTimezone()],
                ['enabled' => false]
            );
            $this->info('✓ User timezone management feature disabled.');
        }

        if ($handleTeam && !$teamDisabled) {
            FeatureFlag::updateOrCreate(
                ['key' => Features::teamTimezone()],
                ['enabled' => false]
            );
            $this->info('✓ Team timezone management feature disabled.');
        }

        $this->comment('To re-enable: php artisan afterburner:timezone [--user] [--team]');

        return Command::SUCCESS;
    }
}

