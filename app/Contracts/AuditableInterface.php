<?php

namespace App\Contracts;

interface AuditableInterface
{
    /**
     * Get the category for audit logging.
     */
    public function getAuditCategory(): string;

    /**
     * Get additional metadata to include in audit log.
     */
    public function getAuditMetadata(): array;
}

