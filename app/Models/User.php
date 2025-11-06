<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laragear\WebAuthn\Contracts\WebAuthnAuthenticatable;
use Laragear\WebAuthn\WebAuthnAuthentication;
use App\Traits\HasProfilePhoto;
use App\Traits\HasAfterburnerRoles;
use App\Traits\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use App\Notifications\VerifyEmail;

class User extends Authenticatable implements MustVerifyEmail, WebAuthnAuthenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasAfterburnerRoles;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use WebAuthnAuthentication;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_system_admin',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_system_admin' => 'boolean',
        ];
    }

    /**
     * Check if the user is a system admin.
     * System admins can impersonate users for troubleshooting.
     */
    public function isSystemAdmin(): bool
    {
        return $this->is_system_admin === true;
    }

    /**
     * Get the user's timezone or fall back to application default.
     *
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->timezone ?? config('app.timezone', 'UTC');
    }

    /**
     * Convert a UTC datetime to the user's timezone.
     *
     * @param  \Carbon\Carbon|string|\DateTimeInterface|null  $dateTime
     * @return \Carbon\Carbon|null
     */
    public function toUserTimezone($dateTime): ?Carbon
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
     * Parse a datetime string from the user's timezone and convert to UTC.
     *
     * @param  string  $dateTime
     * @return \Carbon\Carbon
     */
    public function fromUserTimezone(string $dateTime): Carbon
    {
        return Carbon::parse($dateTime, $this->getTimezone())->utc();
    }

    /**
     * Get the current time in the user's timezone.
     *
     * @return \Carbon\Carbon
     */
    public function nowInTimezone(): Carbon
    {
        return now()->setTimezone($this->getTimezone());
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }
}