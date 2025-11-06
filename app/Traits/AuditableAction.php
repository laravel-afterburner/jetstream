<?php

namespace App\Traits;

use App\Services\AuditService;

trait AuditableAction
{
    protected function auditAction(string $eventName, ?array $changes = null, ?array $metadata = null): void
    {
        // Check if audit is enabled
        if (!config('audit.enabled', true)) {
            return;
        }

        $category = $this->getAuditCategory();
        
        app(AuditService::class)->log(
            actionType: 'action_class',
            category: $category,
            eventName: $eventName,
            auditable: $this->getAuditableModel(),
            changes: $changes,
            metadata: $metadata,
            teamId: auth()->user()?->currentTeam?->id
        );
    }

    protected function getAuditCategory(): string
    {
        // Default: extract from class name (e.g., CreateTeam -> 'team')
        $className = class_basename($this);
        // Remove common action prefixes
        $category = preg_replace('/^Create|Update|Delete|Add|Remove|Invite|Accept|Reject|Validate/i', '', $className);
        return strtolower($category);
    }

    protected function getAuditableModel(): ?object
    {
        // Override in action class to return the model being acted upon
        return null;
    }
}

