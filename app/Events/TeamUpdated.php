<?php

namespace App\Events;

use App\Models\Team;

class TeamUpdated extends AuditEvent
{
    public function __construct(
        public Team $team
    ) {}

    public function getCategory(): string
    {
        return 'team';
    }

    public function getEventName(): string
{
        return 'team.updated';
}

    public function getAuditable(): ?object
    {
        return $this->team;
    }

    public function getChanges(): array
    {
        return [
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'changes' => $this->team->getDirty(),
        ];
    }

    public function getTeamId(): ?int
    {
        return $this->team->id;
    }
}
