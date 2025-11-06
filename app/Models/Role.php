<?php

namespace App\Models;

use App\Traits\HasPermissions;
use App\Traits\HasRoleMembershipLimits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;
    use HasPermissions;
    use HasRoleMembershipLimits;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_default',
        'hierarchy',
        'badge_color',
        'icon',
        'max_members',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role')
            ->withPivot('team_id')
            ->withTimestamps();
    }
}