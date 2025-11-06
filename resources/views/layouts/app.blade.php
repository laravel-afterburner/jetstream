<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>
            @if(isset($title))
                @php
                    $teamName = null;
                    if (App\Support\Afterburner::hasTeamFeatures() && Auth::check()) {
                        $currentTeam = Auth::user()->currentTeam;
                        if ($currentTeam) {
                            $teamName = $currentTeam->name;
                        }
                    }
                @endphp
                @if($teamName)
                    {{ $teamName }} - {{ $title }}
                @else
                    {{ $title }}
                @endif
            @else
                {{ config('app.name', 'Laravel') }}
            @endif
        </title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
        
        <!-- Team Branding Styles -->
        @if(isset($teamBranding) && ($teamBranding['primary_color'] || $teamBranding['secondary_color']))
            <style>
                :root {
                    @if($teamBranding['primary_color'])
                        --team-primary-color: {{ $teamBranding['primary_color'] }};
                    @endif
                    @if($teamBranding['secondary_color'])
                        --team-secondary-color: {{ $teamBranding['secondary_color'] }};
                    @endif
                }
                
                /* Apply primary color to buttons and backgrounds */
                @if($teamBranding['primary_color'])
                    .btn-primary,
                    button[type="submit"].btn,
                    .bg-indigo-600:not(.banner-icon):not([class*="banner"]) {
                        background-color: var(--team-primary-color) !important;
                        border-color: var(--team-primary-color) !important;
                        color: white !important;
                    }
                    
                    /* Apply primary color to text links only (no background) */
                    .text-indigo-600,
                    a.text-indigo-600,
                    .hover\:text-indigo-600:hover {
                        color: var(--team-primary-color) !important;
                    }
                @endif
                
                /* Apply secondary color where appropriate - exclude banners */
                @if($teamBranding['secondary_color'])
                    .bg-indigo-500:not([class*="banner"]):not([x-data*="banner"]),
                    .bg-secondary {
                        background-color: var(--team-secondary-color) !important;
                    }
                @endif
                
                /* Exclude banners from team branding - use system defaults */
                [x-data*="banner"] .bg-indigo-600 {
                    background-color: #4f46e5 !important; /* indigo-600 */
                }
                [x-data*="banner"] .bg-indigo-500 {
                    background-color: #6366f1 !important; /* indigo-500 */
                }
                [x-data*="banner"] .bg-red-600 {
                    background-color: #dc2626 !important; /* red-600 */
                }
                [x-data*="banner"] .bg-red-700 {
                    background-color: #b91c1c !important; /* red-700 */
                }
                [x-data*="banner"] .bg-yellow-600 {
                    background-color: #ca8a04 !important; /* yellow-600 */
                }
                [x-data*="banner"] .bg-yellow-500 {
                    background-color: #eab308 !important; /* yellow-500 */
                }
                [x-data*="banner"] button[class*="hover:bg-indigo-600"]:hover,
                [x-data*="banner"] button[class*="hover:bg-indigo-600"]:focus {
                    background-color: #4f46e5 !important; /* indigo-600 */
                }
            </style>
        @endif
    </head>
    <body class="font-sans antialiased">
        <x-impersonation-banner />
        <x-banner />
        @if(App\Support\Afterburner::hasUserTimezoneManagement())
            <x-timezone-suggestion-banner 
                :detectedTimezone="session('detected_timezone')" 
                :userTimezone="auth()->check() ? (auth()->user()->timezone ?? config('app.timezone', 'UTC')) : config('app.timezone', 'UTC')"
                :dismissed="session('timezone_suggestion_dismissed', false)" />
        @endif

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @livewire('navigation-menu')
            @livewire('impersonation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScripts
    </body>
</html>
