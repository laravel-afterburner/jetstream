<?php

namespace App\Services;

use App\Jobs\LogAuditEntry;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\Agent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuditService
{
    protected ?string $requestId = null;
    protected array $performanceMetrics = [];
    protected ?float $startTime = null;
    protected ?int $startMemory = null;
    protected ?int $startQueryCount = null;

    public function __construct()
    {
        $this->requestId = Str::uuid()->toString();
        $this->startPerformanceTracking();
    }

    /**
     * Start tracking performance metrics.
     */
    public function startPerformanceTracking(): void
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
        
        // Track database queries if enabled
        if (config('app.debug', false)) {
            $this->startQueryCount = count(DB::getQueryLog());
        } else {
            DB::enableQueryLog();
            $this->startQueryCount = 0;
        }
    }

    /**
     * Get performance metrics since tracking started.
     */
    public function getPerformanceMetrics(): array
    {
        $metrics = [];

        // Execution time
        if ($this->startTime !== null) {
            $metrics['execution_time_ms'] = round((microtime(true) - $this->startTime) * 1000, 2);
        }

        // Memory usage
        if ($this->startMemory !== null) {
            $currentMemory = memory_get_usage(true);
            $peakMemory = memory_get_peak_usage(true);
            $metrics['memory_usage_bytes'] = $currentMemory - $this->startMemory;
            $metrics['memory_peak_bytes'] = $peakMemory;
            $metrics['memory_usage_mb'] = round(($currentMemory - $this->startMemory) / 1024 / 1024, 2);
        }

        // Database queries
        $queryLog = DB::getQueryLog();
        if ($this->startQueryCount !== null && is_array($queryLog)) {
            $totalQueries = count($queryLog) - $this->startQueryCount;
            $metrics['database_queries_count'] = max(0, $totalQueries);
            
            // Calculate total query time
            $queryTime = 0;
            if ($totalQueries > 0 && $this->startQueryCount >= 0) {
                $relevantQueries = array_slice($queryLog, $this->startQueryCount);
                foreach ($relevantQueries as $query) {
                    $queryTime += $query['time'] ?? 0;
                }
            }
            $metrics['database_query_time_ms'] = round($queryTime, 2);
        }

        return $metrics;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function log(
        string $actionType,
        string $category,
        string $eventName,
        ?object $auditable = null,
        ?array $changes = null,
        ?array $metadata = null,
        ?int $teamId = null
    ): ?AuditLog {
        try {
            $user = Auth::user();
            $impersonatedBy = Session::has('impersonator_id') 
                ? User::find(Session::get('impersonator_id')) 
                : null;

            // Build base metadata with request context
            // Use user's timezone preference if user timezone management is enabled and user has a preference
            // Otherwise fall back to app default
            $userTimezone = config('app.timezone', 'UTC');
            if (\App\Support\Features::hasUserTimezoneManagement() && $user) {
                $userTimezone = $user->getTimezone();
            }
            
            $baseMetadata = [
                'request_id' => $this->requestId,
                'timestamp' => now()->setTimezone($userTimezone)->toIso8601String(),
                'timezone' => $userTimezone,
            ];

            // Add request context if available
            if (request()) {
                $baseMetadata['ip'] = request()->ip();
                $baseMetadata['user_agent'] = request()->userAgent();
                $baseMetadata['url'] = request()->fullUrl();
                $baseMetadata['route'] = request()->route()?->getName();
                
                // Add referrer if available
                if ($referrer = request()->header('referer')) {
                    $baseMetadata['referer'] = $referrer;
                }
                
                // Add session ID (useful for tracking user sessions)
                if ($sessionId = session()->getId()) {
                    $baseMetadata['session_id'] = $sessionId;
                }

                // Browser and device detection
                try {
                    $agent = new Agent();
                    $agent->setUserAgent(request()->userAgent());
                    
                    $baseMetadata['browser'] = $agent->browser();
                    $baseMetadata['platform'] = $agent->platform();
                    $baseMetadata['device_type'] = $agent->isMobile() ? 'mobile' : ($agent->isTablet() ? 'tablet' : 'desktop');
                    $baseMetadata['is_mobile'] = $agent->isMobile();
                    $baseMetadata['is_tablet'] = $agent->isTablet();
                    $baseMetadata['is_desktop'] = $agent->isDesktop();
                    
                    // Get browser version if available (MobileDetect has version() method)
                    if (method_exists($agent, 'version')) {
                        $browserVersion = $agent->version($agent->browser() ?? 'Version');
                        if ($browserVersion) {
                            $baseMetadata['browser_version'] = $browserVersion;
                        }
                    }

                    // Add screen resolution if available from headers
                    $screenWidth = request()->header('X-Screen-Width');
                    $screenHeight = request()->header('X-Screen-Height');
                    if ($screenWidth && $screenHeight) {
                        $baseMetadata['screen_resolution'] = [
                            'width' => (int) $screenWidth,
                            'height' => (int) $screenHeight,
                        ];
                    }
                } catch (\Exception $e) {
                    // Silently fail browser detection to not break audit logging
                    Log::debug('Browser detection failed', ['error' => $e->getMessage()]);
                }
            }

            // Add performance metrics
            $performanceMetrics = $this->getPerformanceMetrics();
            if (!empty($performanceMetrics)) {
                $baseMetadata['performance'] = $performanceMetrics;
            }

            // Merge with provided metadata
            $finalMetadata = array_merge($baseMetadata, $metadata ?? []);

            $attributes = [
                'user_id' => $user?->id,
                'impersonated_by' => $impersonatedBy?->id,
                'action_type' => $actionType,
                'category' => $category,
                'event_name' => $eventName,
                'auditable_type' => $auditable ? get_class($auditable) : null,
                'auditable_id' => $auditable?->id,
                'team_id' => $teamId ?? ($user?->currentTeam?->id),
                'changes' => $changes,
                'metadata' => $finalMetadata,
                'request_id' => $this->requestId,
            ];

            // If queuing is enabled, dispatch to queue instead of creating synchronously
            if (config('audit.queue', true)) {
                LogAuditEntry::dispatch($attributes);
                // Return a placeholder instance (not persisted) for backward compatibility
                return new AuditLog($attributes);
            }

            // Create synchronously if queuing is disabled
            return AuditLog::create($attributes);
        } catch (\Exception $e) {
            // Don't let audit failures break the application
            Log::error('Audit log creation failed', [
                'error' => $e->getMessage(),
                'category' => $category,
                'event_name' => $eventName,
            ]);
            return null;
        }
    }

    public function getAuditTrail(
        ?int $userId = null,
        ?int $teamId = null,
        ?string $category = null,
        ?string $modelType = null,
        ?int $modelId = null,
        ?string $since = null
    ) {
        $query = AuditLog::query();

        if ($userId) {
            $query->forUser($userId);
        }

        if ($teamId) {
            $query->forTeam($teamId);
        }

        if ($category) {
            $query->inCategory($category);
        }

        if ($modelType) {
            $query->forModel($modelType, $modelId);
        }

        if ($since) {
            $query->since($since);
        }

        return $query->with(['user', 'team', 'auditable'])
            ->orderBy('created_at', 'desc');
    }
}

