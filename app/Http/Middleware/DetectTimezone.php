<?php

namespace App\Http\Middleware;

use App\Support\Features;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class DetectTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if user timezone management feature is disabled
        if (!Features::hasUserTimezoneManagement()) {
            return $next($request);
        }

        // Skip if user is not authenticated
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Get timezone from request header (sent by JavaScript)
        $detectedTimezone = $request->header('X-Timezone') ?? $request->cookie('timezone');

        // Only process if we have a detected timezone
        if ($detectedTimezone && $this->isValidTimezone($detectedTimezone)) {
            $userTimezone = $user->timezone ?? config('app.timezone', 'UTC');
            
            // Only store detected timezone if it differs from user's saved timezone
            // and user hasn't dismissed the suggestion
            if ($detectedTimezone !== $userTimezone && !Session::get('timezone_suggestion_dismissed')) {
                Session::put('detected_timezone', $detectedTimezone);
            } else {
                // Clear detected timezone if it matches user's timezone or was dismissed
                Session::forget('detected_timezone');
            }
        }

        return $next($request);
    }

    /**
     * Validate that the timezone string is valid.
     *
     * @param  string  $timezone
     * @return bool
     */
    protected function isValidTimezone(string $timezone): bool
    {
        return in_array($timezone, timezone_identifiers_list(), true);
    }
}

