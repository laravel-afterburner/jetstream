<?php

namespace App\Notifications;

use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

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

        return (new MailMessage)
            ->from('donotreply@' . $this->sanitizeEmailDomain($teamName), $teamName)
            ->subject("Team member left {$teamName}")
            ->viewData(['team' => $this->team])
            ->greeting("Hello {$notifiable->name},")
            ->line("{$memberName} has left the {$entityLabel} \"{$teamName}\".")
            ->line("They held the following positions: {$rolesText}")
            ->line("They no longer have access to this {$entityLabel} and its data.")
            ->line("If you need to invite them back, you can do so from the team management page.");
    }

    /**
     * Sanitize team name for use in email domain.
     * Removes special characters and makes it RFC 2822 compliant.
     */
    protected function sanitizeEmailDomain(string $teamName): string
    {
        // Convert to lowercase, replace spaces with hyphens, remove special characters
        $sanitized = Str::lower($teamName);
        $sanitized = preg_replace('/[^a-z0-9\s-]/', '', $sanitized); // Remove special chars except spaces and hyphens
        $sanitized = preg_replace('/\s+/', '-', $sanitized); // Replace spaces with hyphens
        $sanitized = preg_replace('/-+/', '-', $sanitized); // Replace multiple hyphens with single
        $sanitized = trim($sanitized, '-'); // Remove leading/trailing hyphens
        
        // Ensure it's not empty and has valid characters
        if (empty($sanitized) || !preg_match('/^[a-z0-9-]+$/', $sanitized)) {
            $sanitized = 'team';
        }
        
        return $sanitized;
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
