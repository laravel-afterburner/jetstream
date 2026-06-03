<?php

namespace App\Mail;

use Illuminate\Notifications\Messages\MailMessage;

class TeamMailMessage extends MailMessage
{
    /**
     * Attach team branding (logo and footer name) for queued notification emails.
     */
    public function forTeam(?object $team): static
    {
        if ($team === null) {
            return $this;
        }

        $this->viewData['team'] = $team;

        if (method_exists($team, 'getLogoUrl')) {
            $this->viewData['teamLogo'] = $team->getLogoUrl();
        }

        return $this;
    }
}
