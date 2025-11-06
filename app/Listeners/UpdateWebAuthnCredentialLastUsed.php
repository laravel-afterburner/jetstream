<?php

namespace App\Listeners;

use Laragear\WebAuthn\Events\CredentialAsserted;

class UpdateWebAuthnCredentialLastUsed
{
    /**
     * Handle the event.
     */
    public function handle(CredentialAsserted $event): void
    {
        // Update the last_used_at timestamp for the credential
        // Set directly to avoid mass assignment issues
        $event->credential->last_used_at = now();
        $event->credential->save();
    }
}
