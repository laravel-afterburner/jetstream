<?php

namespace Tests\Feature;

use App\Models\FeatureFlag;
use App\Models\Team;
use App\Models\User;
use App\Support\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Livewire\Teams\DeleteTeamForm;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_teams_can_be_deleted(): void
    {
        if (! Features::hasTeamDeletionFeatures()) {
            $this->markTestSkipped('Team deletion is not enabled.');
        }

        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        $user->ownedTeams()->save($team = Team::factory()->make([
            'personal_team' => false,
        ]));

        $team->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'test-role']
        );

        Livewire::test(DeleteTeamForm::class, ['team' => $team->fresh()])
            ->call('deleteTeam');

        // Team uses soft deletes, so check for deleted_at instead of null
        $this->assertNotNull($team->fresh()->deleted_at);
        $this->assertCount(0, $otherUser->fresh()->teams);
    }

    public function test_personal_teams_cant_be_deleted_when_feature_enabled(): void
    {
        if (! Features::hasTeamDeletionFeatures()) {
            $this->markTestSkipped('Team deletion is not enabled.');
        }

        // Enable personal teams feature
        FeatureFlag::updateOrCreate(
            ['key' => Features::personalTeams()],
            ['enabled' => true]
        );

        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        Livewire::test(DeleteTeamForm::class, ['team' => $user->currentTeam])
            ->call('deleteTeam')
            ->assertHasErrors(['team']);

        $this->assertNotNull($user->currentTeam->fresh());
    }

    public function test_teams_cannot_be_deleted_when_feature_disabled(): void
    {
        FeatureFlag::updateOrCreate(
            ['key' => Features::teamDeletion()],
            ['enabled' => false]
        );

        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        $user->ownedTeams()->save($team = Team::factory()->make([
            'personal_team' => false,
        ]));

        $this->assertFalse($user->can('delete', $team));

        Livewire::test(DeleteTeamForm::class, ['team' => $team->fresh()])
            ->call('deleteTeam')
            ->assertForbidden();

        $this->assertNull($team->fresh()->deleted_at);
    }
}
