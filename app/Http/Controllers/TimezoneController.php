<?php

namespace App\Http\Controllers;

use App\Support\Features;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TimezoneController extends Controller
{
    /**
     * Update the user's timezone from detected timezone.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        if (!Features::hasUserTimezoneManagement()) {
            return response()->json(['error' => 'User timezone management is not enabled.'], 403);
        }

        $detectedTimezone = $request->header('X-Timezone') ?? $request->cookie('timezone');
        
        if (!$detectedTimezone || !in_array($detectedTimezone, timezone_identifiers_list(), true)) {
            return response()->json(['error' => 'Invalid timezone.'], 400);
        }

        $user = Auth::user();
        $user->update(['timezone' => $detectedTimezone]);

        Session::forget('detected_timezone');
        Session::forget('timezone_suggestion_dismissed');

        return response()->json([
            'success' => true,
            'message' => __('Timezone updated successfully.'),
            'timezone' => $detectedTimezone,
        ]);
    }

    /**
     * Dismiss the timezone suggestion.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dismiss(Request $request)
    {
        Session::put('timezone_suggestion_dismissed', true);
        Session::forget('detected_timezone');

        return response()->json(['success' => true]);
    }
}
