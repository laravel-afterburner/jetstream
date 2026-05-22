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
            
            // Get all favicon URLs for the team
            $faviconUrls = $team->getAllFaviconUrls();
            
            // Share branding data with all views
            view()->share('teamBranding', [
                'logo_url' => $team->logo_url,
                'primary_color' => $team->primary_color,
                'secondary_color' => $team->secondary_color,
                'favicon_url' => $faviconUrls['favicon'],
                'favicon_16' => $faviconUrls['favicon_16'],
                'favicon_32' => $faviconUrls['favicon_32'],
                'apple_touch_icon' => $faviconUrls['apple_touch_icon'],
                'android_chrome_192' => $faviconUrls['android_chrome_192'],
                'android_chrome_512' => $faviconUrls['android_chrome_512'],
                'web_manifest' => $faviconUrls['web_manifest'],
            ]);
        } else {
            // Default branding when no team context
            view()->share('teamBranding', [
                'logo_url' => null,
                'primary_color' => null,
                'secondary_color' => null,
                'favicon_url' => asset('favicon.ico'),
                'favicon_16' => asset('favicon-16x16.png'),
                'favicon_32' => asset('favicon-32x32.png'),
                'apple_touch_icon' => asset('apple-touch-icon.png'),
                'android_chrome_192' => asset('android-chrome-192x192.png'),
                'android_chrome_512' => asset('android-chrome-512x512.png'),
                'web_manifest' => asset('site.webmanifest'),
            ]);
        }

        return $next($request);
    }
}
