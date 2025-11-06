<?php

namespace App\Traits;

use App\Services\AuditService;

trait AuditableComponent
{
    protected function auditLivewireAction(string $actionName, ?array $changes = null, ?array $metadata = null): void
    {
        // Check if audit is enabled
        if (!config('audit.enabled', true)) {
            return;
        }

        $componentName = class_basename($this);
        $category = strtolower(str_replace(['Component', 'Form'], '', $componentName));

        app(AuditService::class)->log(
            actionType: 'livewire',
            category: $category,
            eventName: "{$category}.{$actionName}",
            auditable: $this->getAuditableModel(),
            changes: $changes,
            metadata: array_merge($metadata ?? [], [
                'component' => $componentName,
                'method' => $actionName,
            ]),
            teamId: auth()->user()?->currentTeam?->id
        );
    }

    protected function getAuditableModel(): ?object
    {
        // Override in component to return the model being acted upon
        return null;
    }
}

