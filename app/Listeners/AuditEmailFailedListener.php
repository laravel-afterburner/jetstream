<?php

namespace App\Listeners;

use App\Services\AuditService;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;

class AuditEmailFailedListener
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function handle(JobFailed $event): void
    {
        if (! config('audit.enabled', true)) {
            return;
        }

        $jobName = $event->job->resolveName();
        if ($jobName !== SendQueuedMailable::class) {
            return;
        }

        try {
            $payload = $event->job->payload();
            $command = $payload['data']['command'] ?? null;

            $mailableClass = null;
            $recipients = [];

            if ($command) {
                try {
                    $unserialized = unserialize($command);
                    if ($unserialized instanceof SendQueuedMailable && isset($unserialized->mailable)) {
                        $mailable = $unserialized->mailable;
                        $mailableClass = get_class($mailable);
                        $toAddresses = $mailable->to ?? [];
                        foreach ($toAddresses as $address) {
                            $recipients[] = is_object($address) && method_exists($address, 'getAddress')
                                ? $address->getAddress()
                                : (string) $address;
                        }
                    }
                } catch (\Throwable) {
                    if (preg_match('/O:\d+:"([^"]+)"/', $command, $matches)) {
                        $mailableClass = $matches[1] ?? null;
                    }
                }
            }

            $this->auditService->log(
                actionType: 'custom_event',
                category: 'email',
                eventName: 'email.failed',
                auditable: null,
                changes: [
                    'mailable' => $mailableClass,
                    'recipients' => $recipients,
                    'reason' => $event->exception->getMessage(),
                ],
                metadata: [
                    'failure_reason' => $event->exception->getMessage(),
                    'exception_class' => get_class($event->exception),
                    'connection' => $event->connectionName,
                    'attempts' => $event->job->attempts(),
                ],
                teamId: null,
            );
        } catch (\Exception $e) {
            Log::error('Audit email failed listener error', [
                'error' => $e->getMessage(),
                'original_exception' => $event->exception->getMessage(),
            ]);
        }
    }
}
