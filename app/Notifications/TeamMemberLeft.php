<?php

namespace App\Notifications;

use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamMemberLeft extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Team $team,
        public User $memberWhoLeft,
        public array $memberRoles = []
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $teamName = $this->team->name;
        $memberName = $this->memberWhoLeft->name;
        $entityLabel = config('afterburner.entity_label');
        
        $rolesText = empty($this->memberRoles) ? 'No specific roles' : implode(', ', $this->memberRoles);

        $mailMessage = (new MailMessage)
            ->from(config('mail.from.address'), $teamName)
            ->subject("Team member left {$teamName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$memberName} has left the {$entityLabel} \"{$teamName}\".")
            ->line("They held the following positions: {$rolesText}")
            ->line("They no longer have access to this {$entityLabel} and its data.")
            ->line("If you need to invite them back, you can do so from the team management page.");

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
        $rolesText = empty($this->memberRoles) ? 'No specific roles' : implode(', ', $this->memberRoles);
        $message = "{$this->memberWhoLeft->name} has left the team. They held the following positions: {$rolesText}";
        
        return [
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'member_name' => $this->memberWhoLeft->name,
            'member_id' => $this->memberWhoLeft->id,
            'member_roles' => $this->memberRoles,
            'roles_text' => $rolesText,
            'message' => $message,
            'type' => 'team_member_left',
        ];
    }
}
