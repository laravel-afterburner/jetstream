<?php

namespace App\Http\Middleware;

use App\Support\Features;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTeamBranding
{
    /**
     * Handle an incoming request.
     *
     * Handle an incoming request and share team branding data with views.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Share team branding with views if user has a current team
        if ($user && Features::hasTeamFeatures() && $user->currentTeam) {
            $team = $user->currentTeam;
            
            // Share branding data with all views
            view()->share('teamBranding', [
                'logo_url' => $team->logo_url,
                'primary_color' => $team->primary_color,
                'secondary_color' => $team->secondary_color,
            ]);
        } else {
            // Default branding when no team context
            view()->share('teamBranding', [
                'logo_url' => null,
                'primary_color' => null,
                'secondary_color' => null,
            ]);
        }

        return $next($request);
    }
}
