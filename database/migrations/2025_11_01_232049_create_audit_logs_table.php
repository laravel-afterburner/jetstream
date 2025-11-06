<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('impersonated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action_type'); // 'model_event', 'custom_event', 'http_request', 'action_class', 'livewire'
            $table->string('category'); // 'team', 'role', 'user', 'subscription', 'document', etc.
            $table->string('event_name'); // 'team.created', 'user.updated', 'subscription.renewed', etc.
            $table->nullableMorphs('auditable'); // polymorphic to affected model
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->json('changes')->nullable(); // before/after values or full state
            $table->json('metadata')->nullable(); // IP, user agent, route, request params, etc.
            $table->string('request_id')->nullable(); // UUID for grouping related actions
            $table->timestamps();
            
            // Indexes for performance
            // Note: nullableMorphs() already creates an index on auditable_type and auditable_id
            $table->index(['user_id', 'created_at']);
            $table->index(['team_id', 'created_at']);
            $table->index(['category', 'created_at']);
            $table->index('request_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
