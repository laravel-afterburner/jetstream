<?php

namespace Tests\Feature;

use App\Livewire\Teams\TeamBranding;
use App\Models\User;
use App\Services\FaviconService;
use App\Support\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class TeamLogoResizeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        if (! Features::hasTeamFeatures()) {
            $this->markTestSkipped('Team features not enabled.');
        }
    }

    public function test_team_logo_is_resized_before_storage(): void
    {
        Storage::fake('public');

        $mock = Mockery::mock(FaviconService::class);
        $mock->shouldReceive('generateFromLogo')->once()->andReturn(true);
        $mock->shouldReceive('deleteFavicon')->zeroOrMoreTimes();
        $this->app->instance(FaviconService::class, $mock);

        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->currentTeam;
        $this->actingAs($user);

        Livewire::test(TeamBranding::class, ['team' => $team])
            ->set('logo', UploadedFile::fake()->image('logo.png', 1600, 1200))
            ->call('updateBranding');

        $team->refresh();
        $this->assertNotNull($team->logo_url);
        $this->assertStringEndsWith('.jpg', $team->logo_url);

        $stored = Storage::disk('public')->get($team->logo_url);
        $image = imagecreatefromstring($stored);
        $this->assertNotFalse($image);
        $this->assertLessThanOrEqual(800, imagesx($image));
        $this->assertLessThanOrEqual(800, imagesy($image));
        imagedestroy($image);
    }
}
