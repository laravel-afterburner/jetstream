<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Team;
use App\Support\Afterburner;
use App\Support\Features;
use App\Support\OwnerRole;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

trait HasTeams
{
    /**
     * Determine if the given team is the current team.
     *
     * @param  mixed  $team
     * @return bool
     */
    public function isCurrentTeam($team)
    {
        if (! Features::hasTeamFeatures() || !$team) {
            return false;
        }

        $currentTeam = $this->currentTeam;
        return $currentTeam && $team->id === $currentTeam->id;
    }

    /**
     * Get the current team of the user's context.
     * 
     * Note: Supports optional personal teams feature. If feature is disabled,
     * falls back to first owned team. If feature is enabled but no personal team
     * is marked, uses lazy migration to auto-assign one.
     * 
     * If user has no teams at all, automatically creates a team for them
     * (similar to registration flow).
     * 
     * If teams feature is disabled, returns null.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function currentTeam()
    {
        // If teams feature is disabled, return null
        if (! Features::hasTeamFeatures()) {
            return null;
        }

        if (is_null($this->current_team_id) && $this->id) {
            // Check if user has any teams (owned or member of)
            $availableTeam = $this->allTeams()->first();
            
            if ($availableTeam) {
                // User has teams - use appropriate one
                if (Features::hasPersonalTeams()) {
                    // Feature enabled - try personal team first
                    $personalTeam = $this->personalTeam();
                    
                    if (!$personalTeam) {
                        // Lazy fallback: use first owned team if no personal team marked
                        $personalTeam = $this->ownedTeams()->first();
                    }
                    
                    if ($personalTeam) {
                        $this->switchTeam($personalTeam);
                    } else {
                        // No owned teams but feature requires personal team - create one
                        // This handles scenario where personal team was deleted when feature was disabled
                        $this->ensureUserHasTeam();
                    }
                } else {
                    // Feature disabled - use first owned team
                    $firstOwnedTeam = $this->ownedTeams()->first();
                    if ($firstOwnedTeam) {
                        $this->switchTeam($firstOwnedTeam);
                    } else {
                        // No owned teams, but has member teams - use first one
                        $this->switchTeam($availableTeam);
                    }
                }
            } else {
                // User has no teams at all
                // Only auto-create if personal teams feature is enabled
                // When disabled, let middleware handle prompting user to accept invitation or create team
                if (Features::hasPersonalTeams()) {
                    $this->ensureUserHasTeam();
                }
                // If personal teams disabled, return null relationship - middleware will handle prompting
            }
        }

        return $this->belongsTo(Afterburner::teamModel(), 'current_team_id');
    }

    /**
     * Automatically create a team for a user who has none.
     * 
     * This follows the same logic as user registration team creation.
     * 
     * @return \App\Models\Team|null
     */
    protected function ensureUserHasTeam()
    {
        // Don't create teams if feature is disabled
        if (! Features::hasTeamFeatures()) {
            return null;
        }

        $teamData = [
            'user_id' => $this->id,
            'name' => explode(' ', $this->name, 2)[0]."'s ".ucfirst(config('afterburner.entity_label')),
        ];

        // Set personal_team flag based on feature state
        // When feature is enabled, this is a personal team
        // When feature is disabled, explicitly set to false for consistency
        $teamData['personal_team'] = Features::hasPersonalTeams();

        $team = $this->ownedTeams()->save(
            Team::forceCreate($teamData)
        );

        // Attach user to their team
        $team->users()->attach($this);

        // Assign the application's default role dynamically
        $defaultRole = Role::where('is_default', true)->first();
        if ($defaultRole) {
            $this->assignRole($defaultRole->slug, $team->id);
        }

        // Set the team as the user's current team
        $this->switchTeam($team);
        
        return $team;
    }

    /**
     * Switch the user's context to the given team.
     *
     * @param  mixed  $team
     * @return bool
     */
    public function switchTeam($team)
    {
        if (! $this->belongsToTeam($team)) {
            return false;
        }

        $this->forceFill([
            'current_team_id' => $team->id,
        ])->save();

        $this->setRelation('currentTeam', $team);

        return true;
    }

    /**
     * Get all of the teams the user owns or belongs to.
     *
     * @return \Illuminate\Support\Collection
     */
    public function allTeams()
    {
        return $this->ownedTeams->merge($this->teams)->sortBy('name');
    }

    /**
     * Get all of the teams the user owns.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ownedTeams()
    {
        return $this->hasMany(Afterburner::teamModel());
    }

    /**
     * Get all of the teams the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teams()
    {
        return $this->belongsToMany(Afterburner::teamModel(), Afterburner::membershipModel())
                        ->withPivot('role')
                        ->withTimestamps()
                        ->as('membership');
    }

    /**
     * Get the user's "personal" team.
     * 
     * Implements lazy migration: if feature is enabled but no personal team
     * is marked yet, automatically assigns one from existing teams.
     * 
     * Strategy: Uses current_team_id if user owns it, otherwise first owned team.
     *
     * @return \App\Models\Team|null
     */
    public function personalTeam()
    {
        if (! Features::hasPersonalTeams()) {
            return null;
        }
        
        $team = $this->ownedTeams()->where('personal_team', true)->first();
        
        // Lazy migration: If feature enabled but no personal team marked, auto-assign
        if (!$team && $this->id) {
            // Strategy 1: Use current_team_id if user owns it
            if ($this->current_team_id) {
                $currentTeam = $this->ownedTeams()
                    ->where('id', $this->current_team_id)
                    ->first();
                
                if ($currentTeam) {
                    $currentTeam->update(['personal_team' => true]);
                    return $currentTeam->fresh();
                }
            }
            
            // Strategy 2: Use first owned team (oldest)
            $firstTeam = $this->ownedTeams()->oldest()->first();
            if ($firstTeam) {
                // Ensure user's current_team_id is set
                if (!$this->current_team_id) {
                    $this->update(['current_team_id' => $firstTeam->id]);
                }
                $firstTeam->update(['personal_team' => true]);
                return $firstTeam->fresh();
            }
        }
        
        return $team;
    }

    /**
     * Determine if the user owns the given team.
     *
     * @param  mixed  $team
     * @return bool
     */
    public function ownsTeam($team)
    {
        if (is_null($team)) {
            return false;
        }

        return $this->id == $team->{$this->getForeignKey()};
    }

    /**
     * Determine if the user belongs to the given team.
     *
     * @param  mixed  $team
     * @return bool
     */
    public function belongsToTeam($team)
    {
        if (is_null($team)) {
            return false;
        }

        return $this->ownsTeam($team) || $this->teams->contains(function ($t) use ($team) {
            return $t->id === $team->id;
        });
    }

    /**
     * Get the role that the user has on the team.
     *
     * @param  mixed  $team
     * @return \App\Support\Role|null
     */
    public function teamRole($team)
    {
        if ($this->ownsTeam($team)) {
            return new OwnerRole;
        }

        if (! $this->belongsToTeam($team)) {
            return;
        }

        $role = $team->users
            ->where('id', $this->id)
            ->first()
            ->membership
            ->role;

        // Note: Afterburner role system is not used - custom role system is used instead
        // This method returns null as roles are managed via App\Models\Role
        return null;
    }

    /**
     * Determine if the user has the given role on the given team.
     *
     * @param  mixed  $team
     * @param  string  $role
     * @return bool
     */
    public function hasTeamRole($team, string $role)
    {
        if ($this->ownsTeam($team)) {
            return true;
        }

        // Note: Afterburner role system is not used - custom role system is used instead
        // This method returns false as roles are managed via App\Models\Role
        // Use User::hasRole() method from the custom role system instead
        return false;
    }

    /**
     * Get the user's permissions for the given team.
     *
     * @param  mixed  $team
     * @return array
     */
    public function teamPermissions($team)
    {
        if ($this->ownsTeam($team)) {
            return ['*'];
        }

        if (! $this->belongsToTeam($team)) {
            return [];
        }

        // Note: Afterburner role system is not used - custom role system is used instead
        // This method returns empty array as roles/permissions are managed via App\Models\Role
        // Use User::getPermissions() method from the custom role system instead
        return [];
    }

    /**
     * Determine if the user has the given permission on the given team.
     *
     * @param  mixed  $team
     * @param  string  $permission
     * @return bool
     */
    public function hasTeamPermission($team, string $permission)
    {
        if ($this->ownsTeam($team)) {
            return true;
        }

        if (! $this->belongsToTeam($team)) {
            return false;
        }

        if (in_array(HasApiTokens::class, class_uses_recursive($this)) &&
            ! $this->tokenCan($permission) &&
            $this->currentAccessToken() !== null) {
            return false;
        }

        $permissions = $this->teamPermissions($team);

        return in_array($permission, $permissions) ||
               in_array('*', $permissions) ||
               (Str::endsWith($permission, ':create') && in_array('*:create', $permissions)) ||
               (Str::endsWith($permission, ':update') && in_array('*:update', $permissions));
    }
}

