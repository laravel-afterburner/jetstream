<?php

namespace App\Livewire\Notifications;

use App\Traits\InteractsWithBanner;
use App\Models\Role;
use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationManager extends Component
{
    use InteractsWithBanner;
    use WithPagination;

    /**
     * Indicates if the application is confirming notification deletion.
     *
     * @var bool
     */
    public $confirmingNotificationDeletion = false;

    /**
     * The notification being deleted.
     *
     * @var mixed
     */
    public $notificationBeingDeleted = null;

    /**
     * Get the user's notifications.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getNotificationsProperty()
    {
        return Auth::user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    /**
     * Get the unread notifications count.
     *
     * @return int
     */
    public function getUnreadCountProperty()
    {
        return Auth::user()->unreadNotifications()->count();
    }

    /**
     * Mark a notification as read.
     *
     * @param  string  $notificationId
     * @return void
     */
    public function markAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        $notification->markAsRead();

        $this->dispatch('refresh-notifications');
    }

    /**
     * Mark all notifications as read.
     *
     * @return void
     */
    public function markAllAsRead()
    {
        $count = Auth::user()->unreadNotifications->count();
        Auth::user()->unreadNotifications->markAsRead();

        $this->dispatch('refresh-notifications');

        if ($count > 0) {
            $this->banner(__('All notifications marked as read.'));
        }
    }

    /**
     * Accept a team invitation from notification.
     *
     * @param  string  $notificationId
     * @return void
     */
    public function acceptInvitation($notificationId)
    {
        $notification = Auth::user()->notifications()
            ->where('id', $notificationId)
            ->where('type', 'App\Notifications\TeamInvitationNotification')
            ->firstOrFail();

        $data = $notification->data;
        $invitationId = $data['invitation_id'] ?? null;
        $invitation = $invitationId ? TeamInvitation::find($invitationId) : null;

        if (! $invitation) {
            $notification->markAsRead();
            $this->dangerBanner('This invitation is no longer available.');
            $this->dispatch('refresh-notifications');
            return;
        }

        // Verify the invitation is for this user
        if ($invitation->email !== Auth::user()->email) {
            $this->dangerBanner('This invitation is not for you.');
            return;
        }

        // Accept the invitation using the action class
        $invitation->accept(Auth::user());

        // Update notification to mark as accepted
        $data = $notification->data;
        $data['status'] = 'accepted';
        $data['accepted_at'] = now()->toDateTimeString();
        $notification->data = $data;
        $notification->markAsRead();
        $notification->save();

        $this->dispatch('refresh-notifications');
        $this->dispatch('saved');
    }

    /**
     * Decline a team invitation from notification.
     *
     * @param  string  $notificationId
     * @return void
     */
    public function declineInvitation($notificationId)
    {
        $notification = Auth::user()->notifications()
            ->where('id', $notificationId)
            ->where('type', 'App\Notifications\TeamInvitationNotification')
            ->firstOrFail();

        $data = $notification->data;
        $invitationId = $data['invitation_id'] ?? null;
        $invitation = $invitationId ? TeamInvitation::find($invitationId) : null;

        if (! $invitation) {
            $notification->markAsRead();
            $this->dangerBanner('This invitation is no longer available.');
            $this->dispatch('refresh-notifications');
            return;
        }

        // Verify the invitation is for this user
        if ($invitation->email !== Auth::user()->email) {
            $this->dangerBanner('This invitation is not for you.');
            return;
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

        $this->dispatch('refresh-notifications');
        $this->dispatch('saved');
    }

    /**
     * Confirm notification deletion.
     *
     * @param  string  $notificationId
     * @return void
     */
    public function confirmNotificationDeletion($notificationId)
    {
        $this->notificationBeingDeleted = Auth::user()->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        $this->confirmingNotificationDeletion = true;
    }

    /**
     * Delete the notification.
     *
     * @return void
     */
    public function deleteNotification()
    {
        if ($this->notificationBeingDeleted) {
            $this->notificationBeingDeleted->delete();
        }

        $this->confirmingNotificationDeletion = false;
        $this->notificationBeingDeleted = null;

        $this->dispatch('refresh-notifications');
        $this->dispatch('saved');
    }

    /**
     * Cancel notification deletion.
     *
     * @return void
     */
    public function cancelNotificationDeletion()
    {
        $this->confirmingNotificationDeletion = false;
        $this->notificationBeingDeleted = null;
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return Auth::user();
    }

    /**
     * Get the badge color class for a role.
     */
    public function getRoleBadgeColor($roleSlug)
    {
        $storedValue = Role::where('slug', $roleSlug)->value('badge_color');

        // Default classes if nothing stored
        $default = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';

        if (! $storedValue) {
            return $default;
        }

        // If it's a palette key, resolve via config
        if (config("badge-colors.options.$storedValue.classes")) {
            return config("badge-colors.options.$storedValue.classes");
        }

        // Otherwise treat as stored class string
        return $storedValue ?: $default;
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('notifications.notification-manager');
    }
}