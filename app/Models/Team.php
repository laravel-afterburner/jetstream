<?php

namespace App\Models;

use App\Events\TeamCreated;
use App\Events\TeamDeleted;
use App\Events\TeamUpdated;
use App\Support\Afterburner;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Team extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
        'logo_url',
        'primary_color',
        'secondary_color',
        'timezone',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
        ];
    }

    /**
     * Get the owner of the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(Afterburner::userModel(), 'user_id');
    }

    /**
     * Get all of the team's users including its owner.
     *
     * @return \Illuminate\Support\Collection
     */
    public function allUsers()
    {
        return $this->users->merge([$this->owner]);
    }

    /**
     * Get all of the users that belong to the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(Afterburner::userModel(), Afterburner::membershipModel())
                        ->withPivot('role')
                        ->withTimestamps()
                        ->as('membership');
    }

    /**
     * Determine if the given user belongs to the team.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function hasUser($user)
    {
        return $this->users->contains($user) || $user->ownsTeam($this);
    }

    /**
     * Determine if the given email address belongs to a user on the team.
     *
     * @param  string  $email
     * @return bool
     */
    public function hasUserWithEmail(string $email)
    {
        return $this->allUsers()->contains(function ($user) use ($email) {
            return $user->email === $email;
        });
    }

    /**
     * Determine if the given user has the given permission on the team.
     *
     * @param  \App\Models\User  $user
     * @param  string  $permission
     * @return bool
     */
    public function userHasPermission($user, $permission)
    {
        return $user->hasTeamPermission($this, $permission);
    }

    /**
     * Get all of the pending user invitations for the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function teamInvitations()
    {
        return $this->hasMany(Afterburner::teamInvitationModel());
    }

    /**
     * Remove the given user from the team.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function removeUser($user)
    {
        if ($user->current_team_id === $this->id) {
            $user->forceFill([
                'current_team_id' => null,
            ])->save();
        }

        $this->users()->detach($user);
    }

    /**
     * Purge all of the team's resources.
     *
     * @return void
     */
    public function purge()
    {
        $this->owner()->where('current_team_id', $this->id)
                ->update(['current_team_id' => null]);

        $this->users()->where('current_team_id', $this->id)
                ->update(['current_team_id' => null]);

        $this->users()->detach();

        $this->delete();
    }

    /**
     * Get the full logo URL (handles storage paths and provides fallback).
     *
     * @return string
     */
    public function getLogoUrl(): string
    {
        if (!$this->logo_url) {
            return asset('media/logo.png');
        }

        // If it's a storage path, convert it to a fully qualified URL using the public disk
        if (str_starts_with($this->logo_url, 'teams/')) {
            $relativePath = Storage::disk('public')->url($this->logo_url);
            // Ensure we have a fully qualified URL for emails and external use
            if (!filter_var($relativePath, FILTER_VALIDATE_URL)) {
                return rtrim(config('app.url'), '/') . '/' . ltrim($relativePath, '/');
            }
            return $relativePath;
        }

        // If it's already a full URL, return it
        if (filter_var($this->logo_url, FILTER_VALIDATE_URL)) {
            return $this->logo_url;
        }

        // For other relative paths, use asset helper
        return asset($this->logo_url);
    }

    /**
     * Get the team's timezone or fall back to app default.
     *
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->timezone ?? config('app.timezone', 'UTC');
    }

    /**
     * Convert a datetime to the team's timezone.
     *
     * @param  mixed  $dateTime
     * @return \Carbon\Carbon|null
     */
    public function toTeamTimezone($dateTime): ?Carbon
    {
        if (!$dateTime) {
            return null;
        }
        if (!$dateTime instanceof Carbon) {
            $dateTime = Carbon::parse($dateTime);
        }
        return $dateTime->setTimezone($this->getTimezone());
    }

    /**
     * Convert a datetime from the team's timezone to UTC.
     *
     * @param  string  $dateTime
     * @return \Carbon\Carbon
     */
    public function fromTeamTimezone(string $dateTime): Carbon
    {
        return Carbon::parse($dateTime, $this->getTimezone())->utc();
    }

    /**
     * Get current time in team's timezone.
     *
     * @return \Carbon\Carbon
     */
    public function nowInTimezone(): Carbon
    {
        return now()->setTimezone($this->getTimezone());
    }

    /**
     * Convert a UTC datetime to team timezone, then to user's local timezone for datetime-local input.
     * This accounts for the fact that HTML5 datetime-local inputs interpret values in the browser's local timezone.
     *
     * @param  mixed  $dateTime
     * @param  string|null  $userTimezone
     * @return string|null
     */
    public function toDateTimeLocal($dateTime, ?string $userTimezone = null): ?string
    {
        if (!$dateTime) {
            return null;
        }

        // Convert UTC to team timezone first
        $teamTimezone = $this->toTeamTimezone($dateTime);
        
        // If user timezone is provided and different from team timezone, convert to user timezone
        // Otherwise, just format in team timezone (browser will interpret it correctly if they match)
        if ($userTimezone && $userTimezone !== $this->getTimezone()) {
            // Convert team timezone datetime to user's local timezone
            // This ensures the browser displays the correct time when it interprets the value
            $userTimezoneDateTime = $teamTimezone->copy()->setTimezone($userTimezone);
            return $userTimezoneDateTime->format('Y-m-d\TH:i');
        }
        
        // If timezones match or no user timezone provided, format in team timezone
        return $teamTimezone->format('Y-m-d\TH:i');
    }

    /**
     * Convert a datetime-local value from user's local timezone to UTC.
     * 
     * This accounts for the fact that HTML5 datetime-local inputs interpret values in the browser's local timezone.
     * The value displayed in the input is the team timezone time converted to the user's local timezone
     * (via toDateTimeLocal). When the user submits, the browser sends it as user's local timezone,
     * so we convert it back to UTC which represents the correct moment in time.
     *
     * @param  string  $dateTimeLocal
     * @param  string|null  $userTimezone
     * @return \Carbon\Carbon
     */
    public function fromDateTimeLocal(string $dateTimeLocal, ?string $userTimezone = null): Carbon
    {
        if ($userTimezone && $userTimezone !== $this->getTimezone()) {
            // Parse the datetime-local value as user's local timezone and convert to UTC
            // This preserves the correct moment in time that was displayed to the user
            return Carbon::parse($dateTimeLocal, $userTimezone)->utc();
        }
        
        // If timezones match or no user timezone provided, parse as team timezone
        return $this->fromTeamTimezone($dateTimeLocal);
    }
}
