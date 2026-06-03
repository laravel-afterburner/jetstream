@props([
    'includeManifest' => true,
])

<link rel="icon" type="image/x-icon" href="{{ $teamBranding['favicon_url'] ?? asset('favicon.ico') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ $teamBranding['favicon_16'] ?? asset('favicon-16x16.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ $teamBranding['favicon_32'] ?? asset('favicon-32x32.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ $teamBranding['apple_touch_icon'] ?? asset('apple-touch-icon.png') }}">
@if ($includeManifest)
    <link rel="manifest" href="{{ $teamBranding['web_manifest'] ?? asset('site.webmanifest') }}">
    <meta name="theme-color" content="#ffffff">
@endif
