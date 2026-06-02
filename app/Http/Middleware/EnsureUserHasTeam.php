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

        if (! $user) {
            return $next($request);
        }

        if (! Features::hasTeamFeatures()) {
            return $next($request);
        }

        if ($request->routeIs('livewire.*')) {
            return $next($request);
        }

        if ($this->routeAllowedWithoutTeam($request)) {
            return $next($request);
        }

        if ($user->current_team_id) {
            $currentTeam = $user->currentTeam;

            $mayUseTeamWithoutMembership = $user->isSystemAdmin()
                && ($request->session()->get('role_impersonating') || $request->session()->get('impersonating'));

            if (! $currentTeam || (! $user->belongsToTeam($currentTeam) && ! $mayUseTeamWithoutMembership)) {
                $user->forceFill(['current_team_id' => null])->save();
            }
        }

        if (! $user->current_team_id) {
            $availableTeam = $user->allTeams()->first();

            if ($availableTeam) {
                $user->switchTeam($availableTeam);

                return $next($request);
            }

            if (! Features::allowsTeamCreation()) {
                return redirect()->route('profile.show');
            }

            return $this->redirectUserWithoutTeam($request, $user);
        }

        return $next($request);
    }

    /**
     * Routes accessible without a current team context.
     *
     * @return list<string>
     */
    protected function routesAllowedWithoutTeam(): array
    {
        $routes = [
            'logout',
            'impersonate.stop',
            'impersonate.start',
            'impersonate-role.stop',
            'impersonate-role.start',
            'impersonate-role.start.global',
            'profile.show',
            'security.show',
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
            'verification.verify',
            'verification.send',
            'webauthn.register',
            'webauthn.register.options',
        ];

        if (Features::allowsTeamCreation()) {
            $routes[] = 'teams.create';
        }

        return $routes;
    }

    protected function routeAllowedWithoutTeam(Request $request): bool
    {
        foreach ($this->routesAllowedWithoutTeam() as $routeName) {
            if ($request->routeIs($routeName)) {
                return true;
            }
        }

        return false;
    }

    protected function redirectUserWithoutTeam(Request $request, $user): Response
    {
        $unreadInvitations = $user->notifications()
            ->where('type', 'App\Notifications\TeamInvitationNotification')
            ->whereNull('read_at')
            ->get();

        $readPendingInvitations = $user->notifications()
            ->where('type', 'App\Notifications\TeamInvitationNotification')
            ->whereNotNull('read_at')
            ->where(function ($query) {
                $query->whereNull('data->status')
                    ->orWhere('data->status', 'pending');
            })
            ->get();

        if ($unreadInvitations->count() > 0) {
            $firstInvitation = $unreadInvitations->first();
            $teamName = $firstInvitation->data['team_name'] ?? 'a '.config('afterburner.entity_label');

            return redirect()->route('notifications')->banner(
                __('You have been invited to join :teamName! Please check your notifications to accept the invitation.', [
                    'teamName' => $teamName,
                ])
            );
        }

        if ($readPendingInvitations->count() > 0) {
            $firstInvitation = $readPendingInvitations->first();
            $teamName = $firstInvitation->data['team_name'] ?? 'a '.config('afterburner.entity_label');

            return redirect()->route('teams.create')->warningBanner(
                __('Please create a :entity to continue, or accept the invitation to :teamName in your notifications.', [
                    'entity' => config('afterburner.entity_label'),
                    'teamName' => $teamName,
                ])
            );
        }

        return redirect()->route('teams.create')->warningBanner(
            __('Please create a :entity to continue.', [
                'entity' => config('afterburner.entity_label'),
            ])
        );
    }
}
