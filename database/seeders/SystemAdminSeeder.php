<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemAdminSeeder extends Seeder
{
    public const DEFAULT_NAME = 'Laravel Afterburner';

    public const DEFAULT_EMAIL = 'admin@laravel-afterburner.com';

    private const DEFAULT_PASSWORD = 'Afterburner';

    protected static ?string $installName = null;

    protected static ?string $installEmail = null;

    public static function configureInstall(?string $name = null, ?string $email = null): void
    {
        static::$installName = $name;
        static::$installEmail = $email;
    }

    public static function installEmail(): string
    {
        return static::$installEmail ?? static::DEFAULT_EMAIL;
    }

    public static function installName(): string
    {
        return static::$installName ?? static::DEFAULT_NAME;
    }

    public function run(): void
    {
        if (app()->environment('production')) {
            if (isset($this->command)) {
                $this->command->info('Skipping SystemAdminSeeder in production.');
            }

            return;
        }

        $user = User::firstOrCreate(
            ['email' => static::installEmail()],
            [
                'name' => static::installName(),
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'email_verified_at' => now(),
                'is_system_admin' => true,
            ]
        );

        if (\App\Support\Features::hasTeamFeatures()) {
            $isPersonalTeam = \App\Support\Features::hasPersonalTeams();

            $team = Team::firstOrCreate(
                ['user_id' => $user->id, 'name' => 'System Admin'],
                ['personal_team' => $isPersonalTeam]
            );

            $user->update([
                'current_team_id' => $team->id,
            ]);

            if (! $team->users()->where('user_id', $user->id)->exists()) {
                $team->users()->attach($user);
            }

            $defaultRole = \App\Models\Role::where('is_default', true)->first();
            if ($defaultRole) {
                $user->assignRole($defaultRole->slug, $team->id);
            }

            $leadRole = \App\Models\Role::where('slug', 'team_lead')->first()
                ?? \App\Models\Role::where('slug', 'president')->first()
                ?? \App\Models\Role::where('hierarchy', 1)->first();

            if ($leadRole) {
                $user->assignRole($leadRole->slug, $team->id);
            }
        } else {
            $defaultRole = \App\Models\Role::where('is_default', true)->first();
            if ($defaultRole) {
                $user->assignRole($defaultRole->slug, null);
            }

            $leadRole = \App\Models\Role::where('slug', 'team_lead')->first()
                ?? \App\Models\Role::where('slug', 'president')->first()
                ?? \App\Models\Role::where('hierarchy', 1)->first();

            if ($leadRole) {
                $user->assignRole($leadRole->slug, null);
            }
        }

        if (isset($this->command)) {
            $this->command->info('System admin data seeded successfully!');
        }
    }
}
