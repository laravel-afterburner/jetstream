<?php

namespace App\Jobs;

use App\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogAuditEntry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * The time the job was dispatched.
     */
    public $dispatchTime;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $attributes
    ) {
        $this->dispatchTime = microtime(true);
        
        $connection = config('audit.queue_connection', 'default');
        // Only set connection if it's explicitly specified and not 'default'
        // When 'default' is specified, use the system default queue connection
        if ($connection !== 'default') {
            $this->onConnection($connection);
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check if audit is enabled (might have been disabled since job was queued)
        if (!config('audit.enabled', true)) {
            return;
        }

        try {
            // Calculate queue wait time
            $queueWaitTime = null;
            if (isset($this->dispatchTime)) {
                $queueWaitTime = round((microtime(true) - $this->dispatchTime) * 1000, 2);
                
                // Add queue wait time to metadata performance metrics
                if (isset($this->attributes['metadata']['performance'])) {
                    $this->attributes['metadata']['performance']['queue_wait_time_ms'] = $queueWaitTime;
                } elseif (isset($this->attributes['metadata'])) {
                    $this->attributes['metadata']['performance'] = [
                        'queue_wait_time_ms' => $queueWaitTime,
                    ];
                } else {
                    $this->attributes['metadata'] = [
                        'performance' => [
                            'queue_wait_time_ms' => $queueWaitTime,
                        ],
                    ];
                }
            }

            AuditLog::create($this->attributes);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to create audit log entry in queue', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'category' => $this->attributes['category'] ?? 'unknown',
                'event_name' => $this->attributes['event_name'] ?? 'unknown',
            ]);

            // Re-throw to trigger job retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Log the failure after all retries are exhausted
        Log::error('Audit log entry job failed after all retries', [
            'error' => $exception->getMessage(),
            'category' => $this->attributes['category'] ?? 'unknown',
            'event_name' => $this->attributes['event_name'] ?? 'unknown',
            'attributes' => $this->attributes,
        ]);
    }
}
