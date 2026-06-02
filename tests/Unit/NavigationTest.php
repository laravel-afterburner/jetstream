<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\Navigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Navigation::clear();

        parent::tearDown();
    }

    public function test_parent_permission_is_required_for_items_with_children(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $this->actingAs($user);

        Navigation::register([
            'label' => 'Events',
            'icon' => 'user-group',
            'order' => 20,
            'permission' => fn () => false,
            'children' => [
                [
                    'label' => 'Meetings',
                    'route' => 'teams.meetings.index',
                    'route_params' => ['team' => $user->currentTeam->id],
                ],
                [
                    'label' => 'Calendar',
                    'route' => 'teams.meetings.calendar',
                    'route_params' => ['team' => $user->currentTeam->id],
                ],
            ],
        ]);

        $this->assertCount(0, Navigation::items());
    }

    public function test_parent_permission_allows_items_with_children(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $this->actingAs($user);

        Navigation::register([
            'label' => 'Events',
            'icon' => 'user-group',
            'order' => 20,
            'permission' => fn () => true,
            'children' => [
                [
                    'label' => 'Meetings',
                    'route' => 'teams.meetings.index',
                    'route_params' => ['team' => $user->currentTeam->id],
                ],
            ],
        ]);

        $items = Navigation::items();

        $this->assertCount(1, $items);
        $this->assertSame('Events', $items->first()['label']);
        $this->assertCount(1, $items->first()['children']);
    }

    public function test_navigation_hides_inaccessible_routes_when_subscription_inactive(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->currentTeam->update(['trial_ends_at' => now()->subDay()]);
        $this->actingAs($user);

        Navigation::register([
            'label' => 'Reports',
            'route' => 'teams.reports.index',
            'order' => 10,
            'route_params' => ['team' => $user->currentTeam->id],
        ]);

        Navigation::register([
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'order' => 5,
        ]);

        $items = Navigation::items();

        $this->assertCount(1, $items);
        $this->assertSame('Dashboard', $items->first()['label']);
    }
}
