<?php

namespace App\Support;

use Illuminate\Support\Collection;

class PermissionGroups
{
    /**
     * Permission slugs grouped for role management UI.
     *
     * @return array<string, array<int, string>>
     */
    public static function definitions(): array
    {
        return [
            'Team Administration' => [
                'manage_users',
                'manage_team_settings',
                'manage_properties',
                'view_audit_log',
            ],
            'Meetings' => [
                'manage_meetings',
            ],
            'Voting' => [
                'vote_resolutions',
                'create_resolutions',
                'manage_ballots',
                'view_ballot_results',
                'manage_proxy_votes',
                'export_ballot_results',
            ],
            'Documents' => [
                'view_documents',
                'create_documents',
                'edit_documents',
                'delete_documents',
                'download_documents',
                'share_documents',
                'manage_document_permissions',
                'view_document_versions',
                'restore_document_versions',
                'manage_folders',
                'manage_folder_permissions',
                'manage_retention_tags',
            ],
            'Finance' => [
                'view_financials',
                'manage_budgets',
                'approve_expenses',
            ],
            'Operations' => [
                'assign_tasks',
                'view_reports',
                'manage_programs',
                'post_announcements',
            ],
        ];
    }

    /**
     * @return array<string, Collection<int, \App\Models\Permission>>
     */
    public static function group(Collection $permissions): array
    {
        $grouped = [];
        $assignedSlugs = [];

        foreach (self::definitions() as $label => $slugs) {
            $groupPermissions = $permissions->filter(
                fn ($permission) => in_array($permission->slug, $slugs, true)
            )->values();

            if ($groupPermissions->isNotEmpty()) {
                $grouped[$label] = $groupPermissions;
                $assignedSlugs = array_merge($assignedSlugs, $groupPermissions->pluck('slug')->all());
            }
        }

        $ungrouped = $permissions->reject(
            fn ($permission) => in_array($permission->slug, $assignedSlugs, true)
        )->values();

        if ($ungrouped->isNotEmpty()) {
            $grouped['Other'] = $ungrouped;
        }

        return $grouped;
    }
}
