<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArchiveAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afterburner:audit-archive 
                            {--older-than= : Number of days (uses config if not provided)}
                            {--delete : Delete instead of archiving}
                            {--export : Export to JSON file before deleting}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive or delete old audit logs based on retention period';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get retention days from option or config
        $days = $this->option('older-than') 
            ? (int) $this->option('older-than')
            : (int) config('audit.retention_days', 365);

        if ($days <= 0) {
            $this->error('Retention days must be greater than 0.');
            return Command::FAILURE;
        }

        $date = now()->subDays($days);
        $count = AuditLog::where('created_at', '<', $date)->count();

        if ($count === 0) {
            $this->info('No audit logs to archive.');
            return Command::SUCCESS;
        }

        $this->info("Found {$count} audit log entries older than {$days} days (before {$date->format('Y-m-d H:i:s')}).");

        if (!$this->option('force')) {
            $action = $this->option('delete') ? 'delete' : 'archive';
            if (!$this->confirm("Do you want to {$action} these {$count} audit log entries?", true)) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        // Export to JSON if requested
        if ($this->option('export')) {
            $this->exportLogs($date);
        }

        // Delete or archive
        if ($this->option('delete')) {
            $this->deleteLogs($date, $count);
        } else {
            $this->archiveLogs($date, $count);
        }

        return Command::SUCCESS;
    }

    /**
     * Export audit logs to JSON file.
     */
    protected function exportLogs($date): void
    {
        $this->info('Exporting audit logs to JSON...');

        $filename = 'audit_logs_' . now()->format('Y-m-d_His') . '.json';
        $path = 'audit-exports/' . $filename;

        // Get logs in chunks to avoid memory issues
        $logs = [];
        AuditLog::where('created_at', '<', $date)
            ->orderBy('created_at')
            ->chunk(1000, function ($chunk) use (&$logs) {
                foreach ($chunk as $log) {
                    $logs[] = [
                        'id' => $log->id,
                        'user_id' => $log->user_id,
                        'impersonated_by' => $log->impersonated_by,
                        'action_type' => $log->action_type,
                        'category' => $log->category,
                        'event_name' => $log->event_name,
                        'auditable_type' => $log->auditable_type,
                        'auditable_id' => $log->auditable_id,
                        'team_id' => $log->team_id,
                        'changes' => $log->changes,
                        'metadata' => $log->metadata,
                        'request_id' => $log->request_id,
                        'created_at' => $log->created_at->toIso8601String(),
                        'updated_at' => $log->updated_at->toIso8601String(),
                    ];
                }
            });

        Storage::put($path, json_encode($logs, JSON_PRETTY_PRINT));

        $this->info("✓ Exported " . count($logs) . " logs to: storage/app/{$path}");
    }

    /**
     * Delete audit logs.
     */
    protected function deleteLogs($date, $count): void
    {
        $this->info("Deleting {$count} audit log entries...");

        $deleted = AuditLog::where('created_at', '<', $date)->delete();

        $this->info("✓ Successfully deleted {$deleted} audit log entries.");
    }

    /**
     * Archive audit logs (move to separate table or mark as archived).
     * For now, we'll use a soft approach: mark them or move to archive table.
     * Since we don't have an archive table, we'll just delete after export option.
     */
    protected function archiveLogs($date, $count): void
    {
        $this->info("Archiving {$count} audit log entries...");
        
        // For now, archive means delete (since we don't have an archive table)
        // In the future, you could:
        // 1. Create an audit_logs_archive table and move records there
        // 2. Add an 'archived' column and mark records
        // 3. Export to external storage (S3, etc.)
        
        $this->warn('Archive functionality: Currently archives by deletion.');
        $this->warn('Consider using --export option to save logs before archiving.');
        
        if (!$this->option('force') && !$this->confirm('Proceed with deletion?', false)) {
            $this->info('Operation cancelled. Use --export to save logs first.');
            return;
        }

        $this->deleteLogs($date, $count);
        $this->info('✓ Archive completed.');
    }
}
