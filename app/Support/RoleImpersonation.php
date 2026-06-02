<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;

class RoleImpersonation
{
    public const SESSION_ACTIVE = 'role_impersonating';

    public const SESSION_IMPERSONATOR_ID = 'role_impersonator_id';

    public const SESSION_ROLE_ID = 'role_impersonated_role_id';

    public const SESSION_TEAM_ID = 'role_impersonated_team_id';

    public static function isActive(): bool
    {
        return Session::get(self::SESSION_ACTIVE) === true;
    }

    public static function start(int $impersonatorId, int $roleId, ?int $teamId): void
    {
        Session::put([
            self::SESSION_ACTIVE => true,
            self::SESSION_IMPERSONATOR_ID => $impersonatorId,
            self::SESSION_ROLE_ID => $roleId,
            self::SESSION_TEAM_ID => $teamId,
        ]);
    }

    public static function stop(): void
    {
        Session::forget([
            self::SESSION_ACTIVE,
            self::SESSION_IMPERSONATOR_ID,
            self::SESSION_ROLE_ID,
            self::SESSION_TEAM_ID,
        ]);
    }

    public static function roleId(): ?int
    {
        $id = Session::get(self::SESSION_ROLE_ID);

        return $id ? (int) $id : null;
    }

    public static function teamId(): ?int
    {
        if (! Session::has(self::SESSION_TEAM_ID)) {
            return null;
        }

        $teamId = Session::get(self::SESSION_TEAM_ID);

        return $teamId !== null ? (int) $teamId : null;
    }

    public static function shouldBypassOwnerPrivileges(): bool
    {
        return self::isActive();
    }
}
