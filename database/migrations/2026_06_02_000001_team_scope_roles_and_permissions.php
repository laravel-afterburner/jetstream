<?php

use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('roles', 'is_system')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('is_system')->default(false)->after('is_default');
                $table->foreignId('team_id')->nullable()->after('is_system')->constrained('teams')->cascadeOnDelete();
            });
        }

        if ($this->hasIndex('roles', 'roles_name_unique')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropUnique(['name']);
                $table->dropUnique(['slug']);
            });
        }

        if (! $this->hasIndex('roles', 'roles_team_id_name_unique')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->unique(['team_id', 'name']);
                $table->unique(['team_id', 'slug']);
            });
        }

        DB::table('roles')->whereNull('team_id')->update([
            'is_system' => true,
        ]);

        if (! Schema::hasTable('team_role_permission')) {
            Schema::create('team_role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->unique(['team_id', 'role_id', 'permission_id']);
            });
        }

        if (! Schema::hasTable('team_role_settings')) {
            Schema::create('team_role_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->unsignedInteger('max_members')->nullable();
            $table->timestamps();
            $table->unique(['team_id', 'role_id']);
            });
        }

        $this->migrateLegacyRolePermissions();
        $this->migrateLegacyMaxMembers();

        Schema::dropIfExists('role_permission');
    }

    public function down(): void
    {
        Schema::create('role_permission', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        $rows = DB::table('team_role_permission')
            ->whereNull('team_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('role_permission')->insertOrIgnore([
                'role_id' => $row->role_id,
                'permission_id' => $row->permission_id,
            ]);
        }

        Schema::dropIfExists('team_role_settings');
        Schema::dropIfExists('team_role_permission');

        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['team_id', 'name']);
            $table->dropUnique(['team_id', 'slug']);
            $table->dropConstrainedForeignId('team_id');
            $table->dropColumn('is_system');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->unique('name');
            $table->unique('slug');
        });
    }

    protected function migrateLegacyRolePermissions(): void
    {
        if (! Schema::hasTable('role_permission')) {
            return;
        }

        $legacy = DB::table('role_permission')->get();

        if ($legacy->isEmpty()) {
            return;
        }

        $teamIds = Schema::hasTable('teams')
            ? Team::query()->pluck('id')->all()
            : [];

        $scopes = array_merge([null], $teamIds);

        foreach ($scopes as $teamId) {
            foreach ($legacy as $row) {
                DB::table('team_role_permission')->insertOrIgnore([
                    'team_id' => $teamId,
                    'role_id' => $row->role_id,
                    'permission_id' => $row->permission_id,
                ]);
            }
        }
    }

    protected function hasIndex(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);

        foreach ($indexes as $index) {
            if (($index['name'] ?? '') === $indexName) {
                return true;
            }
        }

        return false;
    }

    protected function migrateLegacyMaxMembers(): void
    {
        $roles = DB::table('roles')->get(['id', 'max_members']);
        $teamIds = Schema::hasTable('teams')
            ? Team::query()->pluck('id')->all()
            : [];

        $scopes = array_merge([null], $teamIds);

        foreach ($scopes as $teamId) {
            foreach ($roles as $role) {
                DB::table('team_role_settings')->insertOrIgnore([
                    'team_id' => $teamId,
                    'role_id' => $role->id,
                    'max_members' => $role->max_members,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
