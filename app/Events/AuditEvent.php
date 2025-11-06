<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class AuditEvent
{
    use Dispatchable, SerializesModels;

    /**
     * Get the category for this audit event.
     */
    abstract public function getCategory(): string;

    /**
     * Get the event name for this audit event.
     */
    abstract public function getEventName(): string;

    /**
     * Get the auditable model (if applicable).
     */
    public function getAuditable(): ?object
    {
        return null;
    }

    /**
     * Get the changes/data to record.
     */
    abstract public function getChanges(): array;

    /**
     * Get additional metadata.
     */
    public function getMetadata(): array
    {
        return [];
    }

    /**
     * Get the team context (if applicable).
     */
    public function getTeamId(): ?int
    {
        return null;
    }
}

