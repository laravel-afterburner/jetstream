<?php

namespace Database\Seeders;

use App\Support\RoleTemplates;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * @param string|null $template The role template to use. Can be set via: $this->call(RolesSeeder::class, ['template' => 'team']);
     *                              Defaults to env('AFTERBURNER_ENTITY_LABEL') if set, otherwise 'company'.
     */
    public function run(?string $template = null): void
    {
        $template = $template ?? (env('AFTERBURNER_ENTITY_LABEL') ?? 'company');
        
        $templateData = RoleTemplates::get($template);

        if (!$templateData) {
            if (isset($this->command)) {
                $this->command->error("Role template '{$template}' not found. Available templates: " . implode(', ', RoleTemplates::keys()));
            }
            return;
        }

        $now = Carbon::now();

        // Insert roles
        $roles = $templateData['roles'];
        DB::table('roles')->insert(array_map(fn($r) => $r + ['created_at' => $now, 'updated_at' => $now], $roles));

        // Insert permissions (deduplicate by slug)
        $permissions = collect($templateData['permissions'])->unique('slug')->values()->all();
        DB::table('permissions')->insert(array_map(fn($p) => $p + ['created_at' => $now, 'updated_at' => $now], $permissions));

        // Map permissions to roles
        $roleIds = DB::table('roles')->pluck('id', 'slug');
        $permissionIds = DB::table('permissions')->pluck('id', 'slug');

        foreach ($templateData['permission_map'] as $roleSlug => $permissionSlugs) {
            if (!isset($roleIds[$roleSlug])) {
                continue;
            }

            foreach ($permissionSlugs as $permissionSlug) {
                if (!isset($permissionIds[$permissionSlug])) {
                    continue;
                }

                // Use insertOrIgnore to avoid duplicate entries if seeder is run multiple times
                DB::table('role_permission')->insertOrIgnore([
                    'role_id' => $roleIds[$roleSlug],
                    'permission_id' => $permissionIds[$permissionSlug],
                ]);
            }
        }

        if (isset($this->command)) {
            $this->command->info("Seeded roles and permissions for template: {$template}");
        }
    }
}


