<?php

namespace App\Support;

class RoleTemplates
{
    /**
     * Get all available role templates.
     */
    public static function all(): array
    {
        return [
            'team' => self::team(),
            'company' => self::company(),
            'strata' => self::strata(),
            'organization' => self::organization(),
        ];
    }

    /**
     * Get a specific role template by key.
     */
    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /**
     * Get all template keys.
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * Team role template.
     */
    protected static function team(): array
    {
        return [
            'roles' => [
                [
                    'name' => 'Team Lead',
                    'slug' => 'team_lead',
                    'description' => 'Leads and coordinates team activities',
                    'is_default' => false,
                    'hierarchy' => 1,
                    'badge_color' => 'blue',
                    'max_members' => 1,
                ],
                [
                    'name' => 'Coordinator',
                    'slug' => 'coordinator',
                    'description' => 'Organizes meetings, communications, and logistics',
                    'is_default' => false,
                    'hierarchy' => 2,
                    'badge_color' => 'indigo',
                    'max_members' => 3,
                ],
                [
                    'name' => 'Treasurer',
                    'slug' => 'treasurer',
                    'description' => 'Handles finances, budgets, and expenses',
                    'is_default' => false,
                    'hierarchy' => 3,
                    'badge_color' => 'emerald',
                    'max_members' => 1,
                ],
                [
                    'name' => 'Member',
                    'slug' => 'member',
                    'description' => 'Regular team member',
                    'is_default' => true,
                    'hierarchy' => 4,
                    'badge_color' => 'gray',
                    'max_members' => null,
                ],
                [
                    'name' => 'Volunteer',
                    'slug' => 'volunteer',
                    'description' => 'Occasional or task-specific participation',
                    'is_default' => false,
                    'hierarchy' => 5,
                    'badge_color' => 'slate',
                    'max_members' => null,
                ],
            ],
            'permissions' => [
                ['name' => 'Manage Users', 'slug' => 'manage_users', 'description' => 'Add, remove, and manage team members and their roles'],
                ['name' => 'Manage Team Settings', 'slug' => 'manage_team_settings', 'description' => 'Update team name, change owner, and delete team'],
                ['name' => 'Manage Meetings', 'slug' => 'manage_meetings', 'description' => 'Create and manage team meetings'],
                ['name' => 'View Financials', 'slug' => 'view_financials', 'description' => 'View financial reports and transactions'],
                ['name' => 'Manage Budgets', 'slug' => 'manage_budgets', 'description' => 'Create and manage budgets'],
                ['name' => 'Vote Resolutions', 'slug' => 'vote_resolutions', 'description' => 'Vote on team resolutions'],
                ['name' => 'View Documents', 'slug' => 'view_documents', 'description' => 'View team documents'],
                ['name' => 'Create Documents', 'slug' => 'create_documents', 'description' => 'Create new documents'],
                ['name' => 'Post Announcements', 'slug' => 'post_announcements', 'description' => 'Post announcements to the team'],
            ],
            'permission_map' => [
                'team_lead' => ['manage_users', 'manage_team_settings', 'manage_meetings', 'view_financials', 'manage_budgets', 'vote_resolutions', 'view_documents', 'create_documents', 'post_announcements'],
                'coordinator' => ['manage_meetings', 'view_documents', 'create_documents', 'post_announcements'],
                'treasurer' => ['manage_meetings', 'view_financials', 'manage_budgets', 'view_documents', 'create_documents', 'post_announcements'],
                'member' => ['vote_resolutions', 'view_documents'],
                'volunteer' => ['view_documents'],
            ],
        ];
    }

    /**
     * Company role template.
     */
    protected static function company(): array
    {
        return [
            'roles' => [
                [
                    'name' => 'Owner/Manager',
                    'slug' => 'owner_manager',
                    'description' => 'Ownership and management authority',
                    'is_default' => false,
                    'hierarchy' => 1,
                    'badge_color' => 'amber',
                    'max_members' => null,
                ],
                [
                    'name' => 'Supervisor',
                    'slug' => 'supervisor',
                    'description' => 'Direct supervision of front-line staff',
                    'is_default' => false,
                    'hierarchy' => 2,
                    'badge_color' => 'blue',
                    'max_members' => null,
                ],
                [
                    'name' => 'Department Lead',
                    'slug' => 'department_lead',
                    'description' => 'Department-level management',
                    'is_default' => false,
                    'hierarchy' => 3,
                    'badge_color' => 'indigo',
                    'max_members' => null,
                ],
                [
                    'name' => 'Senior Staff',
                    'slug' => 'senior_staff',
                    'description' => 'Experienced employees with added responsibilities',
                    'is_default' => false,
                    'hierarchy' => 4,
                    'badge_color' => 'purple',
                    'max_members' => null,
                ],
                [
                    'name' => 'Employee',
                    'slug' => 'employee',
                    'description' => 'Regular employee role',
                    'is_default' => true,
                    'hierarchy' => 5,
                    'badge_color' => 'gray',
                    'max_members' => null,
                ],
            ],
            'permissions' => [
                ['name' => 'Manage Users', 'slug' => 'manage_users', 'description' => 'Add, remove, and manage team members and their roles'],
                ['name' => 'Manage Team Settings', 'slug' => 'manage_team_settings', 'description' => 'Update team name, change owner, and delete team'],
                ['name' => 'View Financials', 'slug' => 'view_financials', 'description' => 'View financial reports and transactions'],
                ['name' => 'Manage Budgets', 'slug' => 'manage_budgets', 'description' => 'Create and manage budgets'],
                ['name' => 'Approve Expenses', 'slug' => 'approve_expenses', 'description' => 'Approve expense reports and reimbursements'],
                ['name' => 'Assign Tasks', 'slug' => 'assign_tasks', 'description' => 'Assign tasks to team members'],
                ['name' => 'View Reports', 'slug' => 'view_reports', 'description' => 'View standard reports'],
                ['name' => 'View Documents', 'slug' => 'view_documents', 'description' => 'View team documents'],
                ['name' => 'Create Documents', 'slug' => 'create_documents', 'description' => 'Create new documents'],
                ['name' => 'Post Announcements', 'slug' => 'post_announcements', 'description' => 'Post announcements to the team'],
                ['name' => 'View Audit Log', 'slug' => 'view_audit_log', 'description' => 'View system audit logs'],
            ],
            'permission_map' => [
                'owner_manager' => ['manage_users', 'manage_team_settings', 'view_financials', 'manage_budgets', 'approve_expenses', 'assign_tasks', 'view_reports', 'view_documents', 'create_documents', 'post_announcements', 'view_audit_log'],
                'supervisor' => ['assign_tasks', 'view_reports', 'view_documents', 'create_documents', 'post_announcements'],
                'department_lead' => ['manage_users', 'view_financials', 'manage_budgets', 'approve_expenses', 'assign_tasks', 'view_reports', 'view_documents', 'create_documents', 'post_announcements'],
                'senior_staff' => ['assign_tasks', 'view_reports', 'view_documents', 'create_documents', 'post_announcements'],
                'employee' => ['view_documents'],
            ],
        ];
    }

    /**
     * Strata role template.
     */
    protected static function strata(): array
    {
        return [
            'roles' => [
                [
                    'name' => 'President',
                    'slug' => 'president',
                    'description' => 'Council leadership powers',
                    'is_default' => false,
                    'hierarchy' => 1,
                    'badge_color' => 'amber',
                    'max_members' => 1,
                ],
                [
                    'name' => 'Treasurer',
                    'slug' => 'treasurer',
                    'description' => 'Financial management and reporting',
                    'is_default' => false,
                    'hierarchy' => 2,
                    'badge_color' => 'emerald',
                    'max_members' => 1,
                ],
                [
                    'name' => 'Secretary',
                    'slug' => 'secretary',
                    'description' => 'Record keeping and documentation',
                    'is_default' => false,
                    'hierarchy' => 3,
                    'badge_color' => 'blue',
                    'max_members' => 1,
                ],
                [
                    'name' => 'Council Member',
                    'slug' => 'council_member',
                    'description' => 'Voting and decision-making',
                    'is_default' => false,
                    'hierarchy' => 4,
                    'badge_color' => 'indigo',
                    'max_members' => null,
                ],
                [
                    'name' => 'Strata Owner',
                    'slug' => 'strata_owner',
                    'description' => 'General strata ownership',
                    'is_default' => true,
                    'hierarchy' => 5,
                    'badge_color' => 'gray',
                    'max_members' => null,
                ],
            ],
            'permissions' => [
                ['name' => 'Manage Users', 'slug' => 'manage_users', 'description' => 'Add, remove, and manage team members and their roles'],
                ['name' => 'Manage Team Settings', 'slug' => 'manage_team_settings', 'description' => 'Update team name, change owner, and delete team'],
                ['name' => 'Manage Meetings', 'slug' => 'manage_meetings', 'description' => 'Create and manage team meetings'],
                ['name' => 'View Financials', 'slug' => 'view_financials', 'description' => 'View financial reports and transactions'],
                ['name' => 'Manage Budgets', 'slug' => 'manage_budgets', 'description' => 'Create and manage budgets'],
                ['name' => 'Vote Resolutions', 'slug' => 'vote_resolutions', 'description' => 'Vote on team resolutions'],
                ['name' => 'Create Resolutions', 'slug' => 'create_resolutions', 'description' => 'Create proposals for voting'],
                ['name' => 'View Documents', 'slug' => 'view_documents', 'description' => 'View team documents'],
                ['name' => 'Create Documents', 'slug' => 'create_documents', 'description' => 'Create new documents'],
                ['name' => 'Post Announcements', 'slug' => 'post_announcements', 'description' => 'Post announcements to the team'],
                ['name' => 'View Audit Log', 'slug' => 'view_audit_log', 'description' => 'View system audit logs'],
            ],
            'permission_map' => [
                'president' => ['manage_users', 'manage_team_settings', 'manage_meetings', 'view_financials', 'manage_budgets', 'vote_resolutions', 'create_resolutions', 'view_documents', 'create_documents', 'post_announcements', 'view_audit_log'],
                'treasurer' => ['manage_meetings', 'view_financials', 'manage_budgets', 'vote_resolutions', 'view_documents', 'create_documents', 'post_announcements'],
                'secretary' => ['manage_meetings', 'view_financials', 'vote_resolutions', 'view_documents', 'create_documents', 'post_announcements'],
                'council_member' => ['vote_resolutions', 'create_resolutions', 'view_documents', 'post_announcements'],
                'strata_owner' => ['vote_resolutions', 'view_documents'],
            ],
        ];
    }

    /**
     * Organization role template.
     */
    protected static function organization(): array
    {
        return [
            'roles' => [
                [
                    'name' => 'Executive Director',
                    'slug' => 'executive_director',
                    'description' => 'Executive leadership and oversight',
                    'is_default' => false,
                    'hierarchy' => 1,
                    'badge_color' => 'amber',
                    'max_members' => 1,
                ],
                [
                    'name' => 'Program Manager',
                    'slug' => 'program_manager',
                    'description' => 'Program development and management',
                    'is_default' => false,
                    'hierarchy' => 2,
                    'badge_color' => 'blue',
                    'max_members' => null,
                ],
                [
                    'name' => 'Coordinator',
                    'slug' => 'coordinator',
                    'description' => 'Coordinate programs and activities',
                    'is_default' => false,
                    'hierarchy' => 3,
                    'badge_color' => 'indigo',
                    'max_members' => null,
                ],
                [
                    'name' => 'Board Member',
                    'slug' => 'board_member',
                    'description' => 'Governance and strategic oversight',
                    'is_default' => false,
                    'hierarchy' => 4,
                    'badge_color' => 'purple',
                    'max_members' => null,
                ],
                [
                    'name' => 'Member',
                    'slug' => 'member',
                    'description' => 'General membership',
                    'is_default' => true,
                    'hierarchy' => 5,
                    'badge_color' => 'gray',
                    'max_members' => null,
                ],
            ],
            'permissions' => [
                ['name' => 'Manage Users', 'slug' => 'manage_users', 'description' => 'Add, remove, and manage team members and their roles'],
                ['name' => 'Manage Team Settings', 'slug' => 'manage_team_settings', 'description' => 'Update team name, change owner, and delete team'],
                ['name' => 'Manage Meetings', 'slug' => 'manage_meetings', 'description' => 'Create and manage team meetings'],
                ['name' => 'View Financials', 'slug' => 'view_financials', 'description' => 'View financial reports and transactions'],
                ['name' => 'Manage Budgets', 'slug' => 'manage_budgets', 'description' => 'Create and manage budgets'],
                ['name' => 'Manage Programs', 'slug' => 'manage_programs', 'description' => 'Create and manage organizational programs'],
                ['name' => 'Vote Resolutions', 'slug' => 'vote_resolutions', 'description' => 'Vote on team resolutions'],
                ['name' => 'Create Resolutions', 'slug' => 'create_resolutions', 'description' => 'Create proposals for voting'],
                ['name' => 'View Documents', 'slug' => 'view_documents', 'description' => 'View team documents'],
                ['name' => 'Create Documents', 'slug' => 'create_documents', 'description' => 'Create new documents'],
                ['name' => 'Post Announcements', 'slug' => 'post_announcements', 'description' => 'Post announcements to the team'],
                ['name' => 'View Reports', 'slug' => 'view_reports', 'description' => 'View standard reports'],
                ['name' => 'View Audit Log', 'slug' => 'view_audit_log', 'description' => 'View system audit logs'],
            ],
            'permission_map' => [
                'executive_director' => ['manage_users', 'manage_team_settings', 'manage_meetings', 'view_financials', 'manage_budgets', 'manage_programs', 'vote_resolutions', 'create_resolutions', 'view_documents', 'create_documents', 'post_announcements', 'view_reports', 'view_audit_log'],
                'program_manager' => ['manage_meetings', 'view_financials', 'manage_programs', 'view_documents', 'create_documents', 'post_announcements', 'view_reports'],
                'coordinator' => ['manage_meetings', 'manage_programs', 'view_documents', 'create_documents', 'post_announcements'],
                'board_member' => ['view_financials', 'vote_resolutions', 'create_resolutions', 'view_documents', 'post_announcements', 'view_reports'],
                'member' => ['vote_resolutions', 'view_documents'],
            ],
        ];
    }
}

