<?php

namespace App\Http\Controllers;

use App\Actions\Afterburner\AcceptTeamInvitation;
use App\Models\TeamInvitation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class TeamInvitationController extends Controller
{
    /**
     * Accept a team invitation.
     */
    public function accept(Request $request, TeamInvitation $invitation): RedirectResponse
    {
        // Check if user is authenticated
        if (!$request->user()) {
            // User is not logged in, redirect to login with invitation parameter
            return redirect()->route('login', ['invitation' => $invitation->id]);
        }

        $user = $request->user();

        // Verify the invitation is for this user
        if ($invitation->email !== $user->email) {
            throw new AuthorizationException('This invitation is not for you.');
        }

        // Use the action class to consolidate logic
        app(AcceptTeamInvitation::class)->add(
            $user,
            $invitation->team,
            $invitation->email,
            $invitation->roles
        );

        return redirect(config('fortify.home'))->banner(
            "Welcome! You've joined {$invitation->team->name}!"
        );
    }

    /**
     * Cancel the given team invitation.
     */
    public function destroy(Request $request, TeamInvitation $invitation): RedirectResponse
    {
        if (! Gate::forUser($request->user())->check('removeTeamMember', $invitation->team)) {
            throw new AuthorizationException;
        }

        $invitation->delete();

        return back(303)->banner('Team invitation canceled.');
    }
}