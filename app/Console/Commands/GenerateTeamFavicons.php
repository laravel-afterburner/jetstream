<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Services\FaviconService;
use Illuminate\Console\Command;

class GenerateTeamFavicons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teams:generate-favicons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate all favicon types (ico, png sizes, apple-touch, android) for all teams that have logos';

    /**
     * Execute the console command.
     */
    public function handle(FaviconService $faviconService)
    {
        $teams = Team::whereNotNull('logo_url')
            ->where('logo_url', 'like', 'teams/%')
            ->get();

        $this->info("Found {$teams->count()} teams with logos.");

        $bar = $this->output->createProgressBar($teams->count());
        $bar->start();

        foreach ($teams as $team) {
            $faviconService->generateFromLogo($team->logo_url, $team->id);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done! Generated all favicon types for all teams with logos.');
    }
}

