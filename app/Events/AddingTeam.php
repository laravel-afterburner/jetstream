<?php

namespace App\Events;

use App\Models\User;

class AddingTeam extends AuditEvent
{
    public function __construct(
        public User $owner
    ) {}

    public function getCategory(): string
    {
        return 'team';
    }

    public function getEventName(): string
    {
        return 'team.adding';
    }

    public function getAuditable(): ?object
    {
        return null; // Team doesn't exist yet
    }

    public function getChanges(): array
    {
        return [
            'user_id' => $this->owner->id,
            'user_name' => $this->owner->name,
            'user_email' => $this->owner->email,
        ];
    }

    public function getTeamId(): ?int
    {
        return null; // Team doesn't exist yet
    }
}
