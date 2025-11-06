<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SecurityController extends Controller
{
    /**
     * Show the security settings screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function show(Request $request)
    {
        return view('security.show', [
            'request' => $request,
            'user' => $request->user(),
        ]);
    }
}

