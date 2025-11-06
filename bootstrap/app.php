<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureUserHasTeam;
use App\Http\Middleware\EnsureSystemAdmin;
use App\Http\Middleware\ApplyTeamBranding;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            EnsureUserHasTeam::class,
            ApplyTeamBranding::class,
            \App\Http\Middleware\AuditHttpMiddleware::class,
            \App\Http\Middleware\DetectTimezone::class,
        ]);
        
        $middleware->alias([
            'system.admin' => EnsureSystemAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
