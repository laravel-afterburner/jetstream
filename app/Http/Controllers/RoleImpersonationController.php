<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Team;
use App\Support\Features;
use App\Support\RoleImpersonation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class RoleImpersonationController extends Controller
{
    public function start(Team $team, Role $role)
    {
        return $this->begin($role, $team);
    }

    public function startWithoutTeam(Role $role)
    {
        return $this->begin($role, null);
    }

    protected function begin(Role $role, ?Team $team): \Illuminate\Http\RedirectResponse
    {
        if (! Auth::user()->isSystemAdmin()) {
            abort(403);
        }

        if (Session::get('impersonating')) {
            abort(403, 'Stop user impersonation before impersonating a role.');
        }

        if (Features::hasTeamFeatures()) {
            if (! $team) {
                abort(404);
            }

            if (Schema::hasColumn('roles', 'is_system')
                && ! $role->is_system
                && $role->team_id !== $team->id) {
                abort(404);
            }

            Auth::user()->forceFill(['current_team_id' => $team->id])->save();
            $teamId = $team->id;
        } else {
            if (Schema::hasColumn('roles', 'is_system') && ! $role->is_system) {
                abort(404);
            }

            $teamId = null;
        }

        RoleImpersonation::start(Auth::id(), $role->id, $teamId);

        return redirect()->route('dashboard');
    }

    public function stop()
    {
        if (! RoleImpersonation::isActive()) {
            return redirect()->route('dashboard');
        }

        RoleImpersonation::stop();

        return redirect()->route('dashboard');
    }
}
