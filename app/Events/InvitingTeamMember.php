<?php

namespace App\Events;

use App\Models\Team;

class InvitingTeamMember extends AuditEvent
{
    public function __construct(
        public Team $team,
        public string $email,
        public ?array $roles = null
    ) {}

    public function getCategory(): string
    {
        return 'team';
    }

    public function getEventName(): string
    {
        return 'team.member.inviting';
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
            'invitee_email' => $this->email,
            'roles' => $this->roles,
        ];
    }

    public function getTeamId(): ?int
    {
        return $this->team->id;
    }
}
