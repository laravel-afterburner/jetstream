<?php

namespace App\Console\Commands;

use App\Models\FeatureFlag;
use App\Models\User;
use App\Support\Features;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class PersonalTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afterburner:personal-teams {--disabled : Disable personal teams feature} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable or disable personal teams feature';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $disabled = $this->option('disabled');

        if ($disabled) {
            return $this->disablePersonalTeams();
        }

        return $this->enablePersonalTeams();
    }

    /**
     * Enable personal teams feature.
     */
    protected function enablePersonalTeams(): int
    {
        // Check if already enabled
        if (Features::hasPersonalTeams()) {
            $this->info('Personal teams feature is already enabled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->info('This will enable personal teams for all users.');
            $this->comment('Lazy migration will automatically assign personal teams as users access the system.');
            if (!$this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        // 1. Ensure database column exists
        if (!Schema::hasColumn('teams', 'personal_team')) {
            $this->info('Adding personal_team column to teams table...');
            Schema::table('teams', function ($table) {
                $table->boolean('personal_team')->nullable()->default(false)->after('name');
            });
            $this->info('✓ Column added.');
        }

        // 2. Enable feature flag in database
        FeatureFlag::updateOrCreate(
            ['key' => Features::personalTeams()],
            ['enabled' => true]
        );
        $this->info('✓ Feature flag enabled in database.');

        // 3. Optionally backfill existing users (lazy migration handles it, but we can do bulk)
        $this->info('');
        if ($this->confirm('Would you like to backfill personal teams for all existing users now? (Otherwise it will happen automatically as users access the system)', true)) {
            $this->backfillPersonalTeams();
        } else {
            $this->comment('Personal teams will be assigned automatically as users access the system.');
        }

        $this->info('');
        $this->info('✓ Personal teams feature is now enabled!');
        $this->comment('Users will automatically get personal teams assigned on their next access, or you can run this command again to backfill all at once.');
        
        return Command::SUCCESS;
    }

    /**
     * Disable personal teams feature.
     */
    protected function disablePersonalTeams(): int
    {
        if (!Features::hasPersonalTeams()) {
            $this->info('Personal teams feature is already disabled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->warn('Disabling personal teams will make all teams deletable, including previously personal teams.');
            $this->comment('Note: The personal_team column and data will remain in the database but will be ignored.');
            if (!$this->confirm('Continue?')) {
                return Command::FAILURE;
            }
        }

        // Disable feature flag
        FeatureFlag::updateOrCreate(
            ['key' => Features::personalTeams()],
            ['enabled' => false]
        );

        $this->info('✓ Personal teams feature disabled.');
        $this->comment('Note: personal_team column and data remain in database but are ignored.');
        $this->comment('To re-enable: php artisan afterburner:personal-teams');

        return Command::SUCCESS;
    }

    /**
     * Backfill personal teams for all existing users.
     */
    protected function backfillPersonalTeams(): void
    {
        $users = User::whereDoesntHave('ownedTeams', function ($query) {
            $query->where('personal_team', true);
        })->get();

        if ($users->isEmpty()) {
            $this->info('All users already have personal teams assigned.');
            return;
        }

        $this->info("Backfilling personal teams for {$users->count()} users...");
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            // Use current_team_id if user owns it
            if ($user->current_team_id) {
                $currentTeam = $user->ownedTeams()
                    ->where('id', $user->current_team_id)
                    ->first();
                
                if ($currentTeam) {
                    $currentTeam->update(['personal_team' => true]);
                    $bar->advance();
                    continue;
                }
            }

            // Fallback to first owned team
            $firstTeam = $user->ownedTeams()->oldest()->first();
            if ($firstTeam) {
                if (!$user->current_team_id) {
                    $user->update(['current_team_id' => $firstTeam->id]);
                }
                $firstTeam->update(['personal_team' => true]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✓ Backfill completed!');
    }
}

