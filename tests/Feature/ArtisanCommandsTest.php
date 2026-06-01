<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtisanCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_install_command_exists(): void
    {
        $this->artisan('afterburner:install', ['--no-migrate' => true, '--no-seed' => true])
            ->expectsOutput('Installing Afterburner packages...')
            ->expectsOutput('Afterburner installation complete.')
            ->assertExitCode(0);
    }

    public function test_publish_command_exists(): void
    {
        $this->artisan('afterburner:publish')
            ->expectsOutput('Publishing Afterburner assets...')
            ->expectsOutput('This command is a placeholder and will be implemented in a future step.')
            ->assertExitCode(0);
    }

    public function test_personal_teams_command_exists(): void
    {
        $this->artisan('afterburner:personal-teams --help')
            ->assertExitCode(0);
    }

    public function test_personal_teams_command_accepts_disabled_flag(): void
    {
        $this->artisan('afterburner:personal-teams', ['--disabled' => true, '--force' => true])
            ->assertExitCode(0);
    }

    public function test_team_deletion_command_exists(): void
    {
        $this->artisan('afterburner:team-deletion --help')
            ->assertExitCode(0);
    }

    public function test_team_deletion_command_accepts_disabled_flag(): void
    {
        $this->artisan('afterburner:team-deletion', ['--disabled' => true, '--force' => true])
            ->assertExitCode(0);
    }

    public function test_install_command_accepts_options(): void
    {
        $this->artisan('afterburner:install', ['--no-migrate' => true, '--no-seed' => true, '--force' => true])
            ->assertExitCode(0);
    }

    public function test_publish_command_accepts_options(): void
    {
        $this->artisan('afterburner:publish', ['--tag' => ['test']])
            ->assertExitCode(0);

        $this->artisan('afterburner:publish', ['--force' => true])
            ->assertExitCode(0);
    }
}
