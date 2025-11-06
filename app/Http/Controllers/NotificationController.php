<?php

namespace App\Http\Controllers;

use App\Actions\Afterburner\AcceptTeamInvitation;
use App\Models\TeamInvitation;
use App\Support\Features;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display the notifications page.
     */
    public function index(Request $request): View
    {
        // Sort unread first, then by creation date within each group
        $notifications = $request->user()->notifications()
            ->orderByRaw('read_at IS NULL DESC')  // Unread first (NULL values first)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, string $notificationId): RedirectResponse
    {
        $notification = $request->user()->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        $notification->markAsRead();

        return redirect()->back();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $count = $request->user()->unreadNotifications->count();
        $request->user()->unreadNotifications->markAsRead();

        if ($count > 0) {
            return redirect()->back()->banner('All notifications marked as read.');
        }

        return redirect()->back();
    }

    /**
     * Accept a team invitation from notification.
     */
    public function acceptInvitation(Request $request, string $notificationId): RedirectResponse
    {
        // Teams feature must be enabled for invitations
        if (! Features::hasTeamFeatures()) {
            abort(404);
        }

        $notification = $request->user()->notifications()
            ->where('id', $notificationId)
            ->where('type', 'App\Notifications\TeamInvitationNotification')
            ->firstOrFail();

        $data = $notification->data;
        $invitationId = $data['invitation_id'] ?? null;
        $invitation = $invitationId ? TeamInvitation::find($invitationId) : null;

        // If the invitation no longer exists, mark notification read and bail gracefully
        if (! $invitation) {
            $notification->markAsRead();
            return redirect()->back()->dangerBanner('This invitation is no longer available.');
        }

        // Verify the invitation is for this user
        if ($invitation->email !== $request->user()->email) {
            abort(403, 'This invitation is not for you.');
        }

        $teamName = $invitation->team->name;

        // Accept the invitation using the action class
        app(AcceptTeamInvitation::class)->add(
            $request->user(),
            $invitation->team,
            $invitation->email,
            $invitation->roles
        );

        // Update notification to mark as accepted
        $data = $notification->data;
        $data['status'] = 'accepted';
        $data['accepted_at'] = now()->toDateTimeString();
        $notification->data = $data;
        $notification->markAsRead();
        $notification->save();

        return redirect()->route('dashboard')->banner(
            "You've joined {$teamName}!"
        );
    }

    /**
     * Decline a team invitation from notification.
     */
    public function declineInvitation(Request $request, string $notificationId): RedirectResponse
    {
        // Teams feature must be enabled for invitations
        if (! Features::hasTeamFeatures()) {
            abort(404);
        }

        $notification = $request->user()->notifications()
            ->where('id', $notificationId)
            ->where('type', 'App\Notifications\TeamInvitationNotification')
            ->firstOrFail();

        $data = $notification->data;
        $invitationId = $data['invitation_id'] ?? null;
        $invitation = $invitationId ? TeamInvitation::find($invitationId) : null;

        // If the invitation no longer exists, mark notification read and bail gracefully
        if (! $invitation) {
            $notification->markAsRead();
            return redirect()->back()->dangerBanner('This invitation is no longer available.');
        }

        // Verify the invitation is for this user
        if ($invitation->email !== $request->user()->email) {
            abort(403, 'This invitation is not for you.');
        }

        // Mark invitation as declined but don't delete it
        $invitation->update(['declined_at' => now()]);

        // Update notification to mark as declined
        $data = $notification->data;
        $data['status'] = 'declined';
        $data['declined_at'] = now()->toDateTimeString();
        $notification->data = $data;
        $notification->markAsRead();
        $notification->save();

        return redirect()->back()->banner('Invitation declined.');
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, string $notificationId): RedirectResponse
    {
        $notification = $request->user()->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        $notification->delete();

        return redirect()->back()->banner('Notification deleted.');
    }
}