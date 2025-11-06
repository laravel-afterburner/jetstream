<?php

namespace App\Traits;

use App\Models\AuditLog;
use App\Contracts\AuditableInterface;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Auditable
{
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    public function getAuditCategory(): string
    {
        // Default implementation - can be overridden in model
        return strtolower(class_basename($this));
    }

    public function getAuditMetadata(): array
    {
        // Default implementation - can be overridden in model
        return [];
    }
}

