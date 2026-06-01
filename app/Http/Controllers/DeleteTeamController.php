<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use App\Actions\Afterburner\DeleteTeam;

class DeleteTeamController extends Controller
{
    /**
     * Delete the given team.
     */
    public function destroy(Request $request, Team $team, DeleteTeam $deleter): RedirectResponse
    {
        Gate::authorize('delete', $team);

        $user = $request->user();
        $teamName = $team->name;

        // Delete the team (soft delete)
        $deleter->delete($team);

        // Find another team the user belongs to
        $nextTeam = $user->allTeams()->first();

        if ($nextTeam) {
            // Switch to another team
            $user->switchTeam($nextTeam);

            return redirect()->route('dashboard')->banner(
                __('The :entity ":name" has been deleted. Switched to :current.', [
                    'entity' => config('afterburner.entity_label'),
                    'name' => $teamName,
                    'current' => $nextTeam->name,
                ])
            );
        } else {
            // No other teams - clear current team
            $user->forceFill([
                'current_team_id' => null,
            ])->save();

            // Check for pending team invitations
            $unreadInvitations = $user->notifications()
                ->where('type', 'App\Notifications\TeamInvitationNotification')
                ->whereNull('read_at')
                ->get();

            if (! \App\Support\Features::allowsTeamCreation()) {
                return redirect()->route('profile.show')->banner(
                    __('The :entity ":name" has been deleted.', [
                        'entity' => config('afterburner.entity_label'),
                        'name' => $teamName,
                    ])
                );
            }

            if ($unreadInvitations->count() > 0) {
                $firstInvitation = $unreadInvitations->first();
                $invitedTeamName = $firstInvitation->data['team_name'] ?? 'a '.config('afterburner.entity_label');

                return redirect()->route('notifications')->banner(
                    __('The :entity ":name" has been deleted. You have been invited to join :invitedTeamName! Please check your notifications.', [
                        'entity' => config('afterburner.entity_label'),
                        'name' => $teamName,
                        'invitedTeamName' => $invitedTeamName,
                    ])
                );
            }

            return redirect()->route('teams.create')->banner(
                __('The :entity ":name" has been deleted.', [
                    'entity' => config('afterburner.entity_label'),
                    'name' => $teamName,
                ])
            );
        }
    }
}