<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Audit Logging Enabled
    |--------------------------------------------------------------------------
    |
    | Set to false to disable audit logging globally. Useful for testing or
    | when you want to temporarily disable auditing.
    |
    */
    'enabled' => env('AUDIT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Set to true to queue audit log entries asynchronously. This prevents
    | audit logging from slowing down requests. Make sure you have a queue
    | worker running.
    |
    */
    'queue' => env('AUDIT_QUEUE', true),

    'queue_connection' => env('AUDIT_QUEUE_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Models Configuration
    |--------------------------------------------------------------------------
    |
    | Whitelist: Only these models will be audited (empty = all models)
    | Blacklist: These models will never be audited
    |
    */
    'models_whitelist' => [
        // \App\Models\Team::class,
        // \App\Models\User::class,
        // \App\Models\Role::class,
    ],

    'models_blacklist' => [
        \App\Models\AuditLog::class, // Don't audit audit logs!
    ],

    /*
    |--------------------------------------------------------------------------
    | Models to Auto-Observe
    |--------------------------------------------------------------------------
    |
    | Models listed here will automatically have the AuditModelObserver
    | registered. Models must implement AuditableInterface or use Auditable trait.
    |
    */
    'models' => [
        \App\Models\Team::class,
        \App\Models\User::class,
        \App\Models\Role::class,
        \App\Models\Permission::class,
        \App\Models\Membership::class,
        \App\Models\TeamInvitation::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Sensitive Fields
    |--------------------------------------------------------------------------
    |
    | Fields that should be redacted in audit logs. These will be replaced
    | with '***REDACTED***' in audit entries.
    |
    */
    'sensitive_fields' => [
        'password',
        'password_confirmation',
        'token',
        'api_token',
        'secret',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'credit_card',
        'ccv',
        'cvv',
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes to Skip
    |--------------------------------------------------------------------------
    |
    | Route names that should not be audited via HTTP middleware.
    |
    */
    'skip_routes' => [
        'livewire.upload-file',
        'livewire.update',
        'livewire.message',
        'livewire.preview-file',
        'timezone.update',
        'timezone.dismiss',
    ],

    /*
    |--------------------------------------------------------------------------
    | Package Audit Listeners
    |--------------------------------------------------------------------------
    |
    | Register audit listeners for packages. Each package should provide
    | a listener class that handles their specific audit events.
    |
    | Format: 'package_name' => ListenerClass::class
    |
    */
    'packages' => [
        // Example:
        // 'subscriptions' => \Packages\Subscriptions\Listeners\SubscriptionAuditListener::class,
        // 'documents' => \Packages\Documents\Listeners\DocumentAuditListener::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention Period
    |--------------------------------------------------------------------------
    |
    | Number of days to keep audit logs. Older logs will be archived or
    | deleted. Set to null for unlimited retention.
    |
    */
    'retention_days' => env('AUDIT_RETENTION_DAYS', 365),
];

