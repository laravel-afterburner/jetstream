<?php

namespace App\Models;

use App\Actions\Afterburner\AcceptTeamInvitation;
use App\Support\Afterburner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamInvitation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'roles',
        'declined_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'roles' => 'array',
        'declined_at' => 'datetime',
    ];

    /**
     * Get the team that the invitation belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Afterburner::teamModel());
    }

    /**
     * Accept the team invitation and add the user to the team.
     * 
     * This method delegates to the AcceptTeamInvitation action class
     * to follow Laravel Afterburner conventions for complex operations.
     */
    public function accept(User $user): void
    {
        app(AcceptTeamInvitation::class)->add(
            $user,
            $this->team,
            $this->email,
            $this->roles
        );
    }
}
