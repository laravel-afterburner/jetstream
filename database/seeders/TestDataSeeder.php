<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Team;
use App\Models\Role;
use App\Models\TeamInvitation;
use App\Notifications\TeamInvitationNotification;
use App\Notifications\TeamMemberLeft;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Only run in non-production environments
        if (app()->environment('production')) {
            $this->command->info('Skipping TestDataSeeder in production environment.');
            return;
        }

        // Get the system admin user from SystemAdminSeeder
        $systemAdmin = User::where('email', 'andrew@laravel-afterburner.com')->first();
       
        if (!$systemAdmin) {
            $this->command->error('System Admin does not exist. Test data seeder aborted.');
            return;
        }

        // Create or find users
        $user1 = User::firstOrCreate(
            ['email' => 'pamela@laravel-afterburner.com'],
            [
                'name' => 'Pamela Spitfire',
                'password' => Hash::make('Afterburner'),
                'email_verified_at' => now(),
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'fox3@laravel-afterburner.com'],
            [
                'name' => 'Fox Three',
                'password' => Hash::make('Afterburner'),
                'email_verified_at' => now(),
            ]
        );

        $user3 = User::firstOrCreate(
            ['email' => 'foxinsocks@laravel-afterburner.com'],
            [
                'name' => 'Fox Insocks',
                'password' => Hash::make('Afterburner'),
                'email_verified_at' => now(),
            ]
        );

        // Get roles dynamically (works with any template)
        $defaultRole = Role::where('is_default', true)->first();
        $leadRole = Role::where('slug', 'team_lead')->first() 
            ?? Role::where('slug', 'president')->first()
            ?? Role::where('hierarchy', 1)->first();

        // Check if teams feature is enabled
        if (\App\Support\Features::hasTeamFeatures()) {
            // Check if personal teams feature is enabled
            $hasPersonalTeams = \App\Support\Features::hasPersonalTeams();
            $entityLabel = ucfirst(config('afterburner.entity_label'));
            
            // Determine teams based on whether personal teams are enabled
            if ($hasPersonalTeams) {
                // Personal teams enabled - create/use personal teams only
                $team1 = $this->ensurePersonalTeam($user1);
                $team2 = $this->ensurePersonalTeam($user2);
                $team3 = $this->ensurePersonalTeam($user3);
                
                // Ensure current teams are set
                $user1->update(['current_team_id' => $team1->id]);
                $user2->update(['current_team_id' => $team2->id]);
                $user3->update(['current_team_id' => $team3->id]);
            } else {
                // Personal teams disabled - create named teams with entity-type-aware names
                $team1 = Team::firstOrCreate(
                    ['name' => 'Rainbow Country Estates ' . $entityLabel],
                    [
                        'user_id' => $user1->id,
                        'personal_team' => false,
                    ]
                );

                $team2 = Team::firstOrCreate(
                    ['name' => 'Andrew\'s ' . $entityLabel],
                    [
                        'user_id' => $user2->id,
                        'personal_team' => false,
                    ]
                );

                $team3 = Team::firstOrCreate(
                    ['name' => 'Keegan\'s ' . $entityLabel],
                    [
                        'user_id' => $user3->id,
                        'personal_team' => false,
                    ]
                );

                // Set current teams
                $user1->update(['current_team_id' => $team1->id]);
                $user2->update(['current_team_id' => $team2->id]);
                $user3->update(['current_team_id' => $team3->id]);
            }

            // Attach users to teams (using syncWithoutDetaching to avoid duplicates)
            $team1->users()->syncWithoutDetaching([$user1->id]);
            $team2->users()->syncWithoutDetaching([$user2->id]);
            $team3->users()->syncWithoutDetaching([$user3->id]);

            // Assign roles to users (team-based)
            if ($defaultRole) {
                $user1->roles()->syncWithoutDetaching([
                    $defaultRole->id => ['team_id' => $team1->id],
                ]);
            }

            // User 2 (andrew@andrewfox.ca) - Andrew's Team
            $roles2 = [];
            if ($defaultRole) {
                $roles2[$defaultRole->id] = ['team_id' => $team2->id];
            }
            if ($leadRole) {
                $roles2[$leadRole->id] = ['team_id' => $team2->id];
            }
            if (!empty($roles2)) {
                $user2->roles()->syncWithoutDetaching($roles2);
            }

            // User 3 (keegan@mynameisfox.com) - Keegan's Team
            $roles3 = [];
            if ($defaultRole) {
                $roles3[$defaultRole->id] = ['team_id' => $team3->id];
            }
            if ($leadRole) {
                $roles3[$leadRole->id] = ['team_id' => $team3->id];
            }
            if (!empty($roles3)) {
                $user3->roles()->syncWithoutDetaching($roles3);
            }

            // Get additional roles for invitations
            $coordinatorRole = Role::where('slug', 'coordinator')->first()
                ?? Role::where('slug', 'vice_president')->first();
            $treasurerRole = Role::where('slug', 'treasurer')->first();
            $secretaryRole = Role::where('slug', 'secretary')->first();
            $volunteerRole = Role::where('slug', 'volunteer')->first()
                ?? Role::where('slug', 'council_member')->first();

            // Build role arrays for invitations
            $defaultRoleSlug = $defaultRole ? $defaultRole->slug : null;
            
            // Create team invitations between all four users (no overlaps)
            // user2 invited to user1's team (team1) - as Coordinator
            $roles1 = array_filter([$defaultRoleSlug, $coordinatorRole?->slug]);
            $invitation1 = TeamInvitation::create([
                'team_id' => $team1->id,
                'email' => $user2->email,
                'roles' => array_values($roles1),
            ]);

            // user3 invited to user1's team (team1) - as Treasurer
            $roles2 = array_filter([$defaultRoleSlug, $treasurerRole?->slug]);
            $invitation2 = TeamInvitation::create([
                'team_id' => $team1->id,
                'email' => $user3->email,
                'roles' => array_values($roles2),
            ]);

            // user1 invited to user2's team (team2) - as Member
            $roles3 = array_filter([$defaultRoleSlug]);
            $invitation3 = TeamInvitation::create([
                'team_id' => $team2->id,
                'email' => $user1->email,
                'roles' => array_values($roles3),
            ]);

            // user3 invited to user2's team (team2) - as Volunteer
            $roles4 = array_filter([$defaultRoleSlug, $volunteerRole?->slug]);
            $invitation4 = TeamInvitation::create([
                'team_id' => $team2->id,
                'email' => $user3->email,
                'roles' => array_values($roles4),
            ]);

            // user1 invited to user3's team (team3) - as Coordinator (or Secretary if exists)
            $roles5 = array_filter([$defaultRoleSlug, $coordinatorRole?->slug ?? $secretaryRole?->slug]);
            $invitation5 = TeamInvitation::create([
                'team_id' => $team3->id,
                'email' => $user1->email,
                'roles' => array_values($roles5),
            ]);

            // user2 invited to user3's team (team3) - as Coordinator
            $roles6 = array_filter([$defaultRoleSlug, $coordinatorRole?->slug]);
            $invitation6 = TeamInvitation::create([
                'team_id' => $team3->id,
                'email' => $user2->email,
                'roles' => array_values($roles6),
            ]);

            // systemAdmin invited to user3's team (team3)
            $roles7 = array_filter([$defaultRoleSlug, $treasurerRole?->slug]);
            $invitation7 = TeamInvitation::create([
                'team_id' => $team3->id,
                'email' => $systemAdmin->email,
                'roles' => array_values($roles7),
            ]);

            // Create notifications for team invitations
            // Use a safe mail driver during seeding to avoid SMTP connection issues
            $originalMailDriver = config('mail.default');
            $safeMailDriver = in_array($originalMailDriver, ['smtp', 'sendmail']) ? 'array' : $originalMailDriver;
            
            // Temporarily switch to a safe mail driver if needed
            if ($safeMailDriver !== $originalMailDriver) {
                config(['mail.default' => $safeMailDriver]);
            }

            try {
                // Using notifyNow() to send synchronously
                // This bypasses the queue so notifications use the current environment's mail configuration
                $user1->notifyNow(new TeamInvitationNotification($invitation3));
                $user1->notifyNow(new TeamInvitationNotification($invitation5));
                $user2->notifyNow(new TeamInvitationNotification($invitation1));
                $user2->notifyNow(new TeamInvitationNotification($invitation6));
                $user3->notifyNow(new TeamInvitationNotification($invitation2));
                $user3->notifyNow(new TeamInvitationNotification($invitation4));
                $systemAdmin->notifyNow(new TeamInvitationNotification($invitation7));

                // Create team member left notifications
                $leftRoles1 = array_filter([$defaultRoleSlug, $coordinatorRole?->slug]);
                $leftRoles2 = array_filter([$defaultRoleSlug, $volunteerRole?->slug]);
                $leftRoles3 = array_filter([$defaultRoleSlug, $coordinatorRole?->slug ?? $secretaryRole?->slug]);
                
                $user1->notifyNow(new TeamMemberLeft($team1, $user2, array_values($leftRoles1)));
                $user2->notifyNow(new TeamMemberLeft($team2, $user3, array_values($leftRoles2)));
                $user3->notifyNow(new TeamMemberLeft($team3, $user1, array_values($leftRoles3)));
            } catch (\Exception $e) {
                // Log the error but don't fail the seeder
                Log::warning('Failed to send notifications during seeding: ' . $e->getMessage());
                $this->command->warn('Some notifications could not be sent during seeding. This is normal if mail is not configured.');
            } finally {
                // Restore original mail driver
                if ($safeMailDriver !== $originalMailDriver) {
                    config(['mail.default' => $originalMailDriver]);
                }
            }
        } else {
            // Teams disabled - assign global roles (null team_id)
            if ($defaultRole) {
                $user1->roles()->syncWithoutDetaching([
                    $defaultRole->id => ['team_id' => null],
                ]);
            }

            // User 2 - assign default and lead roles globally
            $roles2 = [];
            if ($defaultRole) {
                $roles2[$defaultRole->id] = ['team_id' => null];
            }
            if ($leadRole) {
                $roles2[$leadRole->id] = ['team_id' => null];
            }
            if (!empty($roles2)) {
                $user2->roles()->syncWithoutDetaching($roles2);
            }

            // User 3 - assign default and lead roles globally
            $roles3 = [];
            if ($defaultRole) {
                $roles3[$defaultRole->id] = ['team_id' => null];
            }
            if ($leadRole) {
                $roles3[$leadRole->id] = ['team_id' => null];
            }
            if (!empty($roles3)) {
                $user3->roles()->syncWithoutDetaching($roles3);
            }
        }

        $this->command->info('Test data seeded successfully!');
    }

    /**
     * Ensure a user has a personal team if the feature is enabled.
     *
     * @param  \App\Models\User  $user
     * @return \App\Models\Team|null
     */
    protected function ensurePersonalTeam(User $user): ?Team
    {
        if (!\App\Support\Features::hasPersonalTeams()) {
            return null;
        }

        // Check if user already has a personal team
        $personalTeam = $user->personalTeam();
        
        if (!$personalTeam) {
            // Create a personal team for the user
            $teamData = [
                'user_id' => $user->id,
                'name' => explode(' ', $user->name, 2)[0]."'s ".ucfirst(config('afterburner.entity_label')),
                'personal_team' => true,
            ];

            $team = Team::create($teamData);

            // Attach user to their team
            $team->users()->attach($user);

            // Assign default role
            $defaultRole = \App\Models\Role::where('is_default', true)->first();
            if ($defaultRole) {
                $user->assignRole($defaultRole->slug, $team->id);
            }

            // Set as current team if user doesn't have one
            if (!$user->current_team_id) {
                $user->update(['current_team_id' => $team->id]);
            }
            
            return $team;
        }
        
        return $personalTeam;
    }
}
