<?php

use Illuminate\Database\Schema\Blueprint;
use Laragear\WebAuthn\Models\WebAuthnCredential;

return WebAuthnCredential::migration()->with(function (Blueprint $table) {
    // Add last_used_at timestamp to track when credential was last used
    // Note: alias column is already included in the base migration
    $table->timestamp('last_used_at')->nullable();
});
