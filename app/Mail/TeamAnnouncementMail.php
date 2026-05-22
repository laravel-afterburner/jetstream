<?php

namespace App\Mail;

use App\Models\TeamAnnouncement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamAnnouncementMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The team announcement instance.
     *
     * @var \App\Models\TeamAnnouncement
     */
    public $announcement;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\TeamAnnouncement  $announcement
     * @return void
     */
    public function __construct(TeamAnnouncement $announcement)
    {
        $this->announcement = $announcement;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $team = $this->announcement->team;
        $creator = $this->announcement->creator;
        
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                $creator->name ?? $team->name
            ),
            subject: $this->announcement->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $team = $this->announcement->team;
        
        return new Content(
            markdown: 'emails.team-announcement',
            with: [
                'team' => $team,
                'teamLogo' => $team->getLogoUrl(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

}
