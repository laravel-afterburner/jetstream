<?php

namespace App\Notifications;

use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamDeleted extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Team $team)
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
        $ownerName = $this->team->owner->name;
        $teamName = $this->team->name;
        $entityLabel = config('afterburner.entity_label');

        $mailMessage = (new MailMessage)
            ->from(config('mail.from.address'), $teamName)
            ->subject("The {$teamName} {$entityLabel} has been deleted")
            ->greeting("Hello {$notifiable->name},")
            ->line("The {$entityLabel} \"{$teamName}\" has been deleted by its owner, {$ownerName}.")
            ->line("You no longer have access to this {$entityLabel} and its data.")
            ->line("If you believe this was done in error, please contact {$ownerName} or a system administrator to restore the {$entityLabel}.")
            ->line("Only the {$entityLabel} owner can request restoration within 30 days of deletion.");

        $mailMessage->viewData = array_merge($mailMessage->viewData ?: [], ['team' => $this->team]);

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
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'owner_id' => $this->team->user_id,
        ];
    }
}