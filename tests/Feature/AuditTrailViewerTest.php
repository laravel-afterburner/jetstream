<?php

namespace Tests\Feature;

use App\Livewire\Audit\AuditTrailViewer;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditTrailViewerTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_detail_modal_renders_formatted_changes(): void
    {
        $admin = User::factory()->create(['is_system_admin' => true]);
        $this->actingAs($admin);

        $log = AuditLog::create([
            'action_type' => 'updated',
            'category' => 'user',
            'event_name' => 'user.updated',
            'changes' => [
                'name' => ['before' => 'Old Name', 'after' => 'New Name'],
            ],
            'metadata' => [
                'ip' => '127.0.0.1',
                'browser' => 'Chrome',
            ],
        ]);

        Livewire::test(AuditTrailViewer::class)
            ->call('showDetails', $log->id)
            ->assertSet('showingDetailsModal', true)
            ->assertSee('Old Name')
            ->assertSee('New Name')
            ->assertSee('Request Context')
            ->assertSee('127.0.0.1');
    }

    public function test_non_admin_cannot_access_audit_trail_viewer(): void
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $this->actingAs($user);

        Livewire::test(AuditTrailViewer::class)
            ->assertForbidden();
    }
}
