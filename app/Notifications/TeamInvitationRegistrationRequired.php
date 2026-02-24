<?php

namespace App\Notifications;

use App\Models\Role;
use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitationRegistrationRequired extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public TeamInvitation $invitation)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Refresh the invitation to check if it still exists
        $this->invitation->refresh();
        
        // Check if the invitation still exists
        if (!$this->invitation->exists) {
            // If invitation was deleted, don't send the email
            throw new \Exception('Team invitation no longer exists');
        }

        $team = $this->invitation->team;
        $inviter = $team->owner;
        $teamName = $team->name;
        $entityLabel = config('afterburner.entity_label');
        
        // Create signed URL for registration with invitation token
        $registrationUrl = route('register', [
            'invitation' => $this->invitation->id
        ]);

        $mailMessage = (new MailMessage)
            ->from(config('mail.from.address'), $inviter->name ?? $team->name)
            ->subject("You've been invited to join {$teamName}")
            ->greeting('Hello!')
            ->line("You've been invited to join {$teamName}.");

        // Add role information if available
        if ($this->invitation->roles && !empty($this->invitation->roles)) {
            $mailMessage->line("You have been invited with the following roles:");
            foreach ($this->invitation->roles as $roleSlug) {
                $role = Role::where('slug', $roleSlug)->first();
                if ($role) {
                    $mailMessage->line("- **{$role->name}**: {$role->description}");
                }
            }
        }

        $mailMessage
            ->line("To accept this invitation, you'll need to create a free account first.")
            ->line("This invitation link will automatically accept the invitation after you complete registration.")
            ->action('Create Account & Accept Invitation', $registrationUrl)
            ->line("If you didn't expect this invitation, you can safely ignore this email.");

        $mailMessage->viewData = array_merge($mailMessage->viewData ?: [], ['team' => $team]);

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'team_invitation_registration_required',
            'invitation_id' => $this->invitation->id,
            'team_id' => $this->invitation->team->id,
            'team_name' => $this->invitation->team->name,
            'roles' => $this->invitation->roles,
            'invited_by' => $this->invitation->team->owner->name,
        ];
    }
}