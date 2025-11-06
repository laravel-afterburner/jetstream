<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afterburner:install
                            {--tag=* : The tag(s) to publish}
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install add-ons into an existing Afterburner project';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Installing Afterburner add-ons...');

        // TODO: Implement add-on installation logic
        // - Merge Afterburner environment variables into .env.example
        // - Publish config, migrations, and views
        // - Run migrations

        $this->comment('This command is a placeholder and will be implemented in a future step.');

        return Command::SUCCESS;
    }
}

