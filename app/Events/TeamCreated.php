<?php

namespace App\Events;

use App\Models\Team;

class TeamCreated extends AuditEvent
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
        return 'team.created';
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
            'user_id' => $this->team->user_id,
            'personal_team' => $this->team->personal_team,
        ];
    }

    public function getTeamId(): ?int
    {
        return $this->team->id;
    }
}
