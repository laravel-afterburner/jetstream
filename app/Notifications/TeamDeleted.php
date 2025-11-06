<?php

namespace App\Notifications;

use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

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

        return (new MailMessage)
            ->from('donotreply@' . $this->sanitizeEmailDomain($teamName), $teamName)
            ->subject("The {$teamName} {$entityLabel} has been deleted")
            ->viewData(['team' => $this->team])
            ->greeting("Hello {$notifiable->name},")
            ->line("The {$entityLabel} \"{$teamName}\" has been deleted by its owner, {$ownerName}.")
            ->line("You no longer have access to this {$entityLabel} and its data.")
            ->line("If you believe this was done in error, please contact {$ownerName} or a system administrator to restore the {$entityLabel}.")
            ->line("Only the {$entityLabel} owner can request restoration within 30 days of deletion.");
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
        return [
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'owner_id' => $this->team->user_id,
        ];
    }
}