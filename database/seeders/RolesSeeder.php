<?php

namespace Database\Seeders;

use App\Support\RoleTemplates;
use App\Support\TeamRolePermissions;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(?string $template = null): void
    {
        $template = $template ?? config('afterburner.entity_label', 'company');

        $templateData = RoleTemplates::get($template);

        if (! $templateData) {
            if (isset($this->command)) {
                $this->command->error("Role template '{$template}' not found. Available templates: ".implode(', ', RoleTemplates::keys()));
            }

            return;
        }

        $now = Carbon::now();

        $roles = array_map(fn (array $role) => $role + [
            'show_in_directory_council' => $role['show_in_directory_council'] ?? false,
            'is_system' => true,
            'team_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ], $templateData['roles']);

        DB::table('roles')->insert($roles);

        $permissions = collect($templateData['permissions'])->unique('slug')->values()->all();
        DB::table('permissions')->insert(array_map(fn ($p) => $p + ['created_at' => $now, 'updated_at' => $now], $permissions));

        TeamRolePermissions::applyTemplate(null, $templateData);

        if (isset($this->command)) {
            $this->command->info("Seeded roles and permissions for template: {$template}");
        }
    }
}
