<?php

namespace App\Events;

use App\Models\Team;
use App\Models\User;

class RemovingTeamMember extends AuditEvent
{
    public function __construct(
        public Team $team,
        public User $user
    ) {}

    public function getCategory(): string
    {
        return 'team';
    }

    public function getEventName(): string
    {
        return 'team.member.removing';
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
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
        ];
    }

    public function getTeamId(): ?int
    {
        return $this->team->id;
    }
}
