<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => env('AFTERBURNER_USERNAME', 'Laravel Afterburner'),
            'email' => env('AFTERBURNER_EMAIL', 'admin@laravel-afterburner.com'),
            'password' => Hash::make('Afterburner'),
            'email_verified_at' => now(),
            'is_system_admin' => true,
        ]);

        // Check if teams feature is enabled
        if (\App\Support\Features::hasTeamFeatures()) {
            // Teams enabled - create team and assign team-based roles
            // Check if personal teams feature is enabled
            $isPersonalTeam = \App\Support\Features::hasPersonalTeams();
            
            $team = Team::create([
                'user_id' => $user->id,
                'name' => 'System Admin',
                'personal_team' => $isPersonalTeam,
            ]);

            $user->update([
                'current_team_id' => $team->id,
            ]);

            // Attach user to team
            $team->users()->attach($user);

            // Assign default role (works with any template)
            $defaultRole = \App\Models\Role::where('is_default', true)->first();
            if ($defaultRole) {
                $user->assignRole($defaultRole->slug, $team->id);
            }

            // Assign lead role (try team_lead first for team template, fallback to president for strata template, or highest hierarchy)
            $leadRole = \App\Models\Role::where('slug', 'team_lead')->first()
                ?? \App\Models\Role::where('slug', 'president')->first()
                ?? \App\Models\Role::where('hierarchy', 1)->first();
            
            if ($leadRole) {
                $user->assignRole($leadRole->slug, $team->id);
            }
        } else {
            // Teams disabled - assign global roles (null team_id)
            $defaultRole = \App\Models\Role::where('is_default', true)->first();
            if ($defaultRole) {
                $user->assignRole($defaultRole->slug, null);
            }

            // Assign lead role globally
            $leadRole = \App\Models\Role::where('slug', 'team_lead')->first()
                ?? \App\Models\Role::where('slug', 'president')->first()
                ?? \App\Models\Role::where('hierarchy', 1)->first();
            
            if ($leadRole) {
                $user->assignRole($leadRole->slug, null);
            }
        }

        $this->command->info('System admin data seeded successfully!');
    }
}