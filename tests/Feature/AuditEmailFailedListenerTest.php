<?php

namespace Tests\Feature;

use App\Listeners\AuditEmailFailedListener;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Mail;
use Tests\Support\TestMailable;
use Tests\TestCase;

class AuditEmailFailedListenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_queued_mailable_creates_audit_log_entry(): void
    {
        config(['audit.enabled' => true, 'audit.queue' => false]);

        Mail::fake();

        $mailable = new TestMailable;
        $sendJob = new SendQueuedMailable($mailable);
        $serialized = serialize($sendJob);

        $job = \Mockery::mock('Illuminate\Contracts\Queue\Job');
        $job->shouldReceive('resolveName')->andReturn(SendQueuedMailable::class);
        $job->shouldReceive('payload')->andReturn(['data' => ['command' => $serialized]]);
        $job->shouldReceive('attempts')->andReturn(1);

        $event = new JobFailed('sync', $job, new \Exception('SMTP connection failed'));

        app(AuditEmailFailedListener::class)->handle($event);

        $this->assertDatabaseHas('audit_logs', [
            'event_name' => 'email.failed',
            'category' => 'email',
        ]);

        $log = AuditLog::where('event_name', 'email.failed')->first();
        $this->assertSame('SMTP connection failed', $log->changes['reason'] ?? null);
    }

    public function test_non_mail_job_failure_is_not_audited(): void
    {
        config(['audit.enabled' => true, 'audit.queue' => false]);

        $job = \Mockery::mock('Illuminate\Contracts\Queue\Job');
        $job->shouldReceive('resolveName')->andReturn('App\\Jobs\\SomeOtherJob');

        $event = new JobFailed('sync', $job, new \Exception('Failed'));

        app(AuditEmailFailedListener::class)->handle($event);

        $this->assertDatabaseMissing('audit_logs', [
            'event_name' => 'email.failed',
        ]);
    }
}
