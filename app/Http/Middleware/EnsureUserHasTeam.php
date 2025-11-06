<?php

namespace App\Http\Middleware;

use App\Support\Features;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip if not authenticated
        if (!$user) {
            return $next($request);
        }

        // Skip if teams feature is disabled
        if (! Features::hasTeamFeatures()) {
            return $next($request);
        }

        // Skip Livewire requests (they handle their own component context)
        // Livewire AJAX requests come from pages the user already has access to,
        // so we don't need to re-check team membership for component updates.
        // The initial page load already enforces team requirements.
        if ($request->routeIs('livewire.*')) {
            return $next($request);
        }

        // Routes that don't require a team context
        $excludedRoutes = [
            'teams.create',
            'logout',
            'impersonate.stop',
            'profile.show',
            'user-profile-information.update',
            'user-password.update',
            'notifications',
            'notifications.mark-read',
            'notifications.mark-all-read',
            'notifications.accept-invitation',
            'notifications.decline-invitation',
            'password.confirm',
            'password.confirm.store',
            'password.confirmation',
            'two-factor.enable',
            'two-factor.disable',
            'two-factor.confirm',
            'two-factor.qr-code',
            'two-factor.recovery-codes',
            'two-factor.regenerate-recovery-codes',
            'two-factor.secret-key',
        ];

        foreach ($excludedRoutes as $routeName) {
            if ($request->routeIs($routeName)) {
                return $next($request);
            }
        }

        // Check if current team is valid (exists and not soft deleted)
        if ($user->current_team_id) {
            $currentTeam = $user->currentTeam;
            
            // If current team is deleted or user no longer belongs to it
            if (!$currentTeam || !$user->belongsToTeam($currentTeam)) {
                $user->forceFill(['current_team_id' => null])->save();
            }
        }

        // If no current team, try to switch to an available team
        if (!$user->current_team_id) {
            $availableTeam = $user->allTeams()->first();

            if ($availableTeam) {
                $user->switchTeam($availableTeam);
                return $next($request);
            }

            // Check for pending team invitations before redirecting to create team
            $unreadInvitations = $user->notifications()
                ->where('type', 'App\Notifications\TeamInvitationNotification')
                ->whereNull('read_at')
                ->get();

            $readPendingInvitations = $user->notifications()
                ->where('type', 'App\Notifications\TeamInvitationNotification')
                ->whereNotNull('read_at')
                ->where(function($query) {
                    $query->whereNull('data->status')
                          ->orWhere('data->status', 'pending');
                })
                ->get();

            if ($unreadInvitations->count() > 0) {
                // User has unread invitations - redirect to notifications page
                $firstInvitation = $unreadInvitations->first();
                $teamName = $firstInvitation->data['team_name'] ?? 'a ' . config('afterburner.entity_label');
                
                return redirect()->route('notifications')->banner(
                    __('You have been invited to join :teamName! Please check your notifications to accept the invitation.', [
                        'teamName' => $teamName,
                    ])
                );
            } elseif ($readPendingInvitations->count() > 0) {
                // User has read but not accepted invitations - show warning banner with both options
                $firstInvitation = $readPendingInvitations->first();
                $teamName = $firstInvitation->data['team_name'] ?? 'a ' . config('afterburner.entity_label');
                
                return redirect()->route('teams.create')->warningBanner(
                    __('Please create a :entity to continue, or accept the invitation to :teamName in your notifications.', [
                        'entity' => config('afterburner.entity_label'),
                        'teamName' => $teamName,
                    ])
                );
            }

            // No teams available and no pending invitations - redirect to create team
            return redirect()->route('teams.create')->warningBanner(
                __('Please create a :entity to continue.', [
                    'entity' => config('afterburner.entity_label'),
                ])
            );
        }

        return $next($request);
    }
}