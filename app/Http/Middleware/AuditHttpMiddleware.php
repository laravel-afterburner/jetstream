<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuditService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditHttpMiddleware
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Store request ID for later association
        $requestId = $this->auditService->getRequestId();
        $request->attributes->set('audit_request_id', $requestId);

        $response = $next($request);

        // Only audit non-GET requests by default
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->auditRequest($request, $response);
        }

        return $response;
    }

    protected function auditRequest(Request $request, Response $response): void
    {
        // Check if audit is enabled
        if (!config('audit.enabled', true)) {
            return;
        }

        // Skip certain routes
        $skipRoutes = config('audit.skip_routes', []);
        if (in_array($request->route()?->getName(), $skipRoutes)) {
            return;
        }

        // Sanitize sensitive data
        $params = $this->sanitizeParams($request->all());

        // Only log if status is success (2xx, 3xx)
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 400) {
            try {
                $this->auditService->log(
                    actionType: 'http_request',
                    category: $this->getCategoryFromRoute($request),
                    eventName: $this->getEventNameFromRequest($request),
                    auditable: null,
                    changes: null,
                    metadata: [
                        'method' => $request->method(),
                        'uri' => $request->getRequestUri(),
                        'parameters' => $params,
                        'status_code' => $response->getStatusCode(),
                    ],
                    teamId: auth()->user()?->currentTeam?->id
                );
            } catch (\Exception $e) {
                // Don't let audit failures break the request
                Log::error('Audit logging failed', ['error' => $e->getMessage()]);
            }
        }
    }

    protected function sanitizeParams(array $params): array
    {
        $sensitiveFields = config('audit.sensitive_fields', [
            'password',
            'password_confirmation',
            'token',
            'api_token',
            'secret',
            'credit_card',
            'cvv',
        ]);

        foreach ($sensitiveFields as $field) {
            if (isset($params[$field])) {
                $params[$field] = '***REDACTED***';
            }
        }

        return $params;
    }

    protected function getCategoryFromRoute(Request $request): string
    {
        $routeName = $request->route()?->getName();
        if (!$routeName) {
            return 'http';
        }

        // Extract category from route name (e.g., 'teams.create' -> 'team')
        $parts = explode('.', $routeName);
        return $parts[0];
    }

    protected function getEventNameFromRequest(Request $request): string
    {
        $routeName = $request->route()?->getName();
        if (!$routeName) {
            return strtolower($request->method()) . ':' . $request->getPathInfo();
        }

        return $routeName;
    }
}

