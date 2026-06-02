<?php

namespace App\Models;

use App\Traits\HasRoleMembershipLimits;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;
    use HasRoleMembershipLimits;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_default',
        'is_system',
        'team_id',
        'hierarchy',
        'badge_color',
        'max_members',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function isSystemRole(): bool
    {
        return $this->is_system === true;
    }

    public function isCustomTeamRole(): bool
    {
        return ! $this->is_system && $this->team_id !== null;
    }

    /**
     * @deprecated Use TeamRolePermissions::permissionIdsForRole() for team-scoped grants.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'team_role_permission')
            ->withPivot('team_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role')
            ->withPivot('team_id')
            ->withTimestamps();
    }

    public function scopeForTeam(Builder $query, ?int $teamId): Builder
    {
        return $query->where(function (Builder $query) use ($teamId) {
            $query->where('is_system', true);

            if ($teamId !== null) {
                $query->orWhere('team_id', $teamId);
            }
        });
    }
}
