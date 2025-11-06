<?php

namespace App\Mail;

use App\Support\Afterburner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class TeamInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The team invitation instance.
     *
     * @var \App\Models\TeamInvitation
     */
    public $invitation;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\TeamInvitation  $invitation
     * @return void
     */
    public function __construct($invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $team = $this->invitation->team;
        $inviter = $team->owner;
        
        return $this->from(
            'donotreply@' . $this->sanitizeEmailDomain($team->name),
            $inviter->name ?? $team->name
        )->markdown('emails.team-invitation', [
            'acceptUrl' => route('team-invitations.accept', $this->invitation),
            'team' => $team,
            'teamLogo' => $team->getLogoUrl(),
            'primaryColor' => $team->primary_color,
        ])->subject(__('Team Invitation'));
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
}

