<?php

namespace Tests\Feature;

use App\Livewire\NavigationMenu;
use App\Models\Team;
use App\Models\User;
use App\Support\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Livewire\Livewire;
use Tests\TestCase;

class NavigationMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_teams_lists_current_team_first(): void
    {
        if (! Features::hasTeamFeatures()) {
            $this->markTestSkipped('Team features not enabled.');
        }

        $user = User::factory()->withPersonalTeam()->create();
        $secondTeam = Team::factory()->create(['name' => 'Second Team', 'user_id' => $user->id]);
        $secondTeam->users()->attach($user);

        $user->switchTeam($secondTeam);
        $this->actingAs($user);

        $component = Livewire::test(NavigationMenu::class);
        $teams = $component->instance()->allTeams();

        $this->assertCount(2, $teams);
        $this->assertSame($secondTeam->id, $teams->first()->id);
    }

    public function test_team_route_active_flags_are_cached_on_mount(): void
    {
        if (! Features::hasTeamFeatures()) {
            $this->markTestSkipped('Team features not enabled.');
        }

        $user = User::factory()->withPersonalTeam()->create();
        $this->actingAs($user);

        $url = route('teams.members', $user->currentTeam);
        $request = Request::create($url, 'GET');
        $request->setUserResolver(fn () => $user);
        $route = app('router')->getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);
        $this->app->instance('request', $request);

        $menu = new NavigationMenu;
        $menu->mount();

        $this->assertTrue($menu->isTeamsMembersActive);
        $this->assertTrue($menu->isTeamActive);
    }
}
