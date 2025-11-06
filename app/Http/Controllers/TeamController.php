<?php

namespace App\Http\Controllers;

use App\Support\Afterburner;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class TeamController extends Controller
{
    /**
     * Show the team creation screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        Gate::authorize('create', Afterburner::newTeamModel());

        return view('teams.create', [
            'user' => $request->user(),
        ]);
    }
}

