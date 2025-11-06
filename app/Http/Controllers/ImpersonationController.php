<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Features;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonationController extends Controller
{
    /**
     * Start impersonating a user.
     */
    public function start(User $user)
    {
        // Only allow system admins to impersonate
        if (!Auth::user()->isSystemAdmin()) {
            abort(403);
        }

        // Store original user ID in session
        Session::put('impersonator_id', Auth::id());
        Session::put('impersonating', true);
        Session::put('impersonated_user_id', $user->id);

        // Login as the target user
        Auth::login($user);
        
        // Switch to their current team if teams feature is enabled and they have one
        if (Features::hasTeamFeatures() && $user->currentTeam) {
            $user->switchTeam($user->currentTeam);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Stop impersonating and return to original user.
     */
    public function stop()
    {
        if (!Session::has('impersonator_id')) {
            return redirect()->route('dashboard');
        }

        $originalUserId = Session::get('impersonator_id');
        Session::forget(['impersonator_id', 'impersonating', 'impersonated_user_id']);

        Auth::loginUsingId($originalUserId);

        return redirect()->route('dashboard');
    }
}

