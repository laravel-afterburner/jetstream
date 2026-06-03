<?php

namespace App\Mail;

use App\Support\Afterburner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

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
            config('mail.from.address'),
            $inviter->name ?? $team->name
        )->markdown('emails.team-invitation', [
            'acceptUrl' => route('team-invitations.accept', $this->invitation),
            'team' => $team,
            'primaryColor' => $team->primary_color,
        ])->subject(__('Team Invitation'));
    }

}

