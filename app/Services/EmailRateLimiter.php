<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class EmailRateLimiter
{
    /**
     * Check if email can be sent to a user.
     *
     * @param  \App\Models\User|null  $user
     * @param  string|null  $emailAddress
     * @return bool
     */
    public function canSendToUser(?User $user, ?string $emailAddress = null): bool
    {
        // Check global rate limit first
        if (!$this->checkGlobalLimit()) {
            Log::warning('Email rate limit exceeded: global limit reached');
            return false;
        }

        // Check per-user rate limit if user is provided
        if ($user && !$this->checkPerUserLimit($user)) {
            Log::warning('Email rate limit exceeded: per-user limit reached', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
            return false;
        }

        // Check per-email-address rate limit if email address is provided
        if ($emailAddress && !$this->checkPerAddressLimit($emailAddress)) {
            Log::warning('Email rate limit exceeded: per-address limit reached', [
                'email' => $emailAddress,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Check if email can be sent to an email address (without user).
     *
     * @param  string  $emailAddress
     * @return bool
     */
    public function canSendToAddress(string $emailAddress): bool
    {
        // Check global rate limit first
        if (!$this->checkGlobalLimit()) {
            Log::warning('Email rate limit exceeded: global limit reached');
            return false;
        }

        // Check per-email-address rate limit
        if (!$this->checkPerAddressLimit($emailAddress)) {
            Log::warning('Email rate limit exceeded: per-address limit reached', [
                'email' => $emailAddress,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Attempt to send email with rate limiting.
     * Returns true if rate limit allows, false otherwise.
     *
     * @param  \App\Models\User|null  $user
     * @param  string|null  $emailAddress
     * @param  callable  $sendCallback
     * @return bool
     */
    public function attempt(?User $user, ?string $emailAddress, callable $sendCallback): bool
    {
        $email = $emailAddress ?? ($user ? $user->email : null);

        if (!$email) {
            Log::warning('Email rate limiter: No email address or user provided');
            return false;
        }

        if (!$this->canSendToUser($user, $email)) {
            return false;
        }

        try {
            $sendCallback();
            
            // Increment rate limiters after successful send
            $this->incrementLimits($user, $email);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Email sending failed after rate limit check', [
                'user_id' => $user?->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check global email rate limit.
     *
     * @return bool
     */
    protected function checkGlobalLimit(): bool
    {
        return !RateLimiter::tooManyAttempts('emails:global', config('mail.rate_limit.global', 100));
    }

    /**
     * Check per-user email rate limit.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    protected function checkPerUserLimit(User $user): bool
    {
        $key = 'emails:per-user:user:' . $user->id;
        return !RateLimiter::tooManyAttempts($key, config('mail.rate_limit.per_user', 10));
    }

    /**
     * Check per-email-address rate limit.
     *
     * @param  string  $emailAddress
     * @return bool
     */
    protected function checkPerAddressLimit(string $emailAddress): bool
    {
        $key = 'emails:per-address:email:' . $emailAddress;
        return !RateLimiter::tooManyAttempts($key, config('mail.rate_limit.per_address', 5));
    }

    /**
     * Increment rate limiters after successful email send.
     *
     * @param  \App\Models\User|null  $user
     * @param  string  $emailAddress
     * @return void
     */
    public function incrementLimits(?User $user, string $emailAddress): void
    {
        // Increment with 60 second decay (per minute limits)
        RateLimiter::hit('emails:global', 60);
        
        if ($user) {
            $key = 'emails:per-user:user:' . $user->id;
            RateLimiter::hit($key, 60);
        }
        
        $key = 'emails:per-address:email:' . $emailAddress;
        RateLimiter::hit($key, 60);
    }

    /**
     * Get remaining attempts for a user.
     *
     * @param  \App\Models\User|null  $user
     * @param  string|null  $emailAddress
     * @return array
     */
    public function remainingAttempts(?User $user, ?string $emailAddress = null): array
    {
        $email = $emailAddress ?? ($user ? $user->email : null);

        $result = [
            'global' => RateLimiter::remaining('emails:global', config('mail.rate_limit.global', 100)),
        ];

        if ($user) {
            $key = 'emails:per-user:user:' . $user->id;
            $result['per_user'] = RateLimiter::remaining($key, config('mail.rate_limit.per_user', 10));
        } else {
            $result['per_user'] = null;
        }

        if ($email) {
            $key = 'emails:per-address:email:' . $email;
            $result['per_address'] = RateLimiter::remaining($key, config('mail.rate_limit.per_address', 5));
        } else {
            $result['per_address'] = null;
        }

        return $result;
    }
}
