<?php

namespace App\Observers;

use App\Services\AuditService;
use App\Contracts\AuditableInterface;
use App\Support\Agent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AuditModelObserver
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function created(Model $model): void
    {
        if (!$this->shouldAudit($model)) {
            return;
        }

        try {
            $this->auditService->log(
                actionType: 'model_event',
                category: $this->getCategory($model),
                eventName: $this->getEventName($model, 'created'),
                auditable: $model,
                changes: $model->getAttributes(),
                metadata: $this->getMetadata($model),
                teamId: $this->getTeamId($model)
            );
        } catch (\Exception $e) {
            // Don't let audit failures break model operations
            Log::error('Audit logging failed for model created event', [
                'error' => $e->getMessage(),
                'model' => get_class($model),
            ]);
        }
    }

    public function updated(Model $model): void
    {
        if (!$this->shouldAudit($model)) {
            return;
        }

        try {
            $changes = [];
            foreach ($model->getDirty() as $key => $value) {
                $changes[$key] = [
                    'before' => $model->getOriginal($key),
                    'after' => $value,
                ];
            }

            if (empty($changes)) {
                return;
            }

            $this->auditService->log(
                actionType: 'model_event',
                category: $this->getCategory($model),
                eventName: $this->getEventName($model, 'updated'),
                auditable: $model,
                changes: $changes,
                metadata: $this->getMetadata($model),
                teamId: $this->getTeamId($model)
            );
        } catch (\Exception $e) {
            // Don't let audit failures break model operations
            Log::error('Audit logging failed for model updated event', [
                'error' => $e->getMessage(),
                'model' => get_class($model),
            ]);
        }
    }

    public function deleted(Model $model): void
    {
        if (!$this->shouldAudit($model)) {
            return;
        }

        try {
            $this->auditService->log(
                actionType: 'model_event',
                category: $this->getCategory($model),
                eventName: $this->getEventName($model, 'deleted'),
                auditable: $model,
                changes: $model->getAttributes(),
                metadata: $this->getMetadata($model),
                teamId: $this->getTeamId($model)
            );
        } catch (\Exception $e) {
            // Don't let audit failures break model operations
            Log::error('Audit logging failed for model deleted event', [
                'error' => $e->getMessage(),
                'model' => get_class($model),
            ]);
        }
    }

    public function restored(Model $model): void
    {
        if (!$this->shouldAudit($model)) {
            return;
        }

        try {
            $this->auditService->log(
                actionType: 'model_event',
                category: $this->getCategory($model),
                eventName: $this->getEventName($model, 'restored'),
                auditable: $model,
                changes: $model->getAttributes(),
                metadata: $this->getMetadata($model),
                teamId: $this->getTeamId($model)
            );
        } catch (\Exception $e) {
            // Don't let audit failures break model operations
            Log::error('Audit logging failed for model restored event', [
                'error' => $e->getMessage(),
                'model' => get_class($model),
            ]);
        }
    }

    protected function shouldAudit(Model $model): bool
    {
        // Check if audit is enabled
        if (!config('audit.enabled', true)) {
            return false;
        }

        $blacklist = config('audit.models_blacklist', []);
        if (in_array(get_class($model), $blacklist)) {
            return false;
        }

        $whitelist = config('audit.models_whitelist', []);
        if (!empty($whitelist) && !in_array(get_class($model), $whitelist)) {
            return false;
        }

        return true;
    }

    protected function getCategory(Model $model): string
    {
        if ($model instanceof AuditableInterface) {
            return $model->getAuditCategory();
        }

        // Default category based on model class name
        $className = class_basename($model);
        return strtolower($className);
    }

    protected function getEventName(Model $model, string $action): string
    {
        $category = $this->getCategory($model);
        return "{$category}.{$action}";
    }

    protected function getMetadata(Model $model): array
    {
        $metadata = [];

        // Always collect request context if available
        if (request()) {
            $metadata['ip'] = request()->ip();
            $metadata['user_agent'] = request()->userAgent();
            $metadata['url'] = request()->fullUrl();
            $metadata['route'] = request()->route()?->getName();
            
            // Collect referrer if available
            if ($referrer = request()->header('referer')) {
                $metadata['referer'] = $referrer;
            }

            // Browser and device detection
            try {
                $agent = new Agent();
                $agent->setUserAgent(request()->userAgent());
                
                $metadata['browser'] = $agent->browser();
                $metadata['platform'] = $agent->platform();
                $metadata['device_type'] = $agent->isMobile() ? 'mobile' : ($agent->isTablet() ? 'tablet' : 'desktop');
                $metadata['is_mobile'] = $agent->isMobile();
                $metadata['is_tablet'] = $agent->isTablet();
                $metadata['is_desktop'] = $agent->isDesktop();
                
                // Get browser version if available
                if (method_exists($agent, 'version')) {
                    $browserVersion = $agent->version($agent->browser() ?? 'Version');
                    if ($browserVersion) {
                        $metadata['browser_version'] = $browserVersion;
                    }
                }

                // Add screen resolution if available from headers
                $screenWidth = request()->header('X-Screen-Width');
                $screenHeight = request()->header('X-Screen-Height');
                if ($screenWidth && $screenHeight) {
                    $metadata['screen_resolution'] = [
                        'width' => (int) $screenWidth,
                        'height' => (int) $screenHeight,
                    ];
                }
            } catch (\Exception $e) {
                // Silently fail browser detection
                Log::debug('Browser detection failed in observer', ['error' => $e->getMessage()]);
            }

            // Performance metrics from audit service (use existing instance to preserve tracking)
            try {
                $performanceMetrics = $this->auditService->getPerformanceMetrics();
                if (!empty($performanceMetrics)) {
                    $metadata['performance'] = $performanceMetrics;
                }
            } catch (\Exception $e) {
                // Silently fail performance metrics
                Log::debug('Performance metrics failed in observer', ['error' => $e->getMessage()]);
            }
        }

        // If model implements AuditableInterface, merge its metadata
        if ($model instanceof AuditableInterface) {
            $metadata = array_merge($metadata, $model->getAuditMetadata());
        }

        // Collect model-specific metadata
        if ($model->wasRecentlyCreated ?? false) {
            $metadata['was_recently_created'] = true;
        }

        // If model has timestamps, include them
        if ($model->usesTimestamps()) {
            if ($model->created_at) {
                $metadata['model_created_at'] = $model->created_at->toIso8601String();
            }
            if ($model->updated_at) {
                $metadata['model_updated_at'] = $model->updated_at->toIso8601String();
            }
        }

        return $metadata;
    }

    protected function getTeamId(Model $model): ?int
    {
        // Check if model has team_id property
        if (isset($model->team_id)) {
            return $model->team_id;
        }

        // Check if model has team relationship and it's loaded
        if (method_exists($model, 'team') && $model->relationLoaded('team')) {
            return $model->team?->id;
        }

        // Try to get team_id via accessor or relationship
        try {
            if (method_exists($model, 'team')) {
                $team = $model->team;
                return $team?->id;
            }
        } catch (\Exception $e) {
            // Ignore errors, return null
        }

        return null;
    }
}

