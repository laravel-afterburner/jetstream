<?php

namespace App\Listeners;

use App\Events\AuditEvent;
use App\Services\AuditService;

class AuditEventListener
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function handle(AuditEvent $event): void
    {
        // Check if audit is enabled
        if (!config('audit.enabled', true)) {
            return;
        }

        try {
            $this->auditService->log(
                actionType: 'custom_event',
                category: $event->getCategory(),
                eventName: $event->getEventName(),
                auditable: $event->getAuditable(),
                changes: $event->getChanges(),
                metadata: $event->getMetadata(),
                teamId: $event->getTeamId()
            );
        } catch (\Exception $e) {
            // Don't let audit failures break event processing
            \Illuminate\Support\Facades\Log::error('Audit event logging failed', [
                'error' => $e->getMessage(),
                'event' => get_class($event),
            ]);
        }
    }
}

