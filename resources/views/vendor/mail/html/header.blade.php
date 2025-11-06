@props(['url', 'team' => null, 'teamLogo' => null])
@php
    // Determine logo URL - check multiple sources for team logo
    // Only check for explicitly passed variables (safe for queued emails)
    $logoUrl = null;
    $defaultLogo = asset('media/logo.png');
    
    // Check if teamLogo prop is available (passed explicitly to component)
    if ($teamLogo) {
        $logoUrl = $teamLogo;
    }
    // Check if teamLogo variable is available (passed from mail classes via with())
    elseif (isset($teamLogo) && $teamLogo) {
        $logoUrl = $teamLogo;
    }
    // Check if team prop is available (passed explicitly to component or from mail classes)
    elseif ($team && method_exists($team, 'getLogoUrl')) {
        $logoUrl = $team->getLogoUrl();
    }
    // Check if teamBranding is available (from middleware/view sharing)
    // Note: Only works for synchronous emails, not queued ones
    elseif (isset($teamBranding) && isset($teamBranding['logo_url']) && $teamBranding['logo_url']) {
        $logoUrl = $teamBranding['logo_url'];
        // Convert storage path to fully qualified URL if needed
        if (!filter_var($logoUrl, FILTER_VALIDATE_URL)) {
            if (str_starts_with($logoUrl, 'teams/')) {
                $relativePath = \Storage::disk('public')->url($logoUrl);
                // Ensure we have a fully qualified URL for emails
                $logoUrl = rtrim(config('app.url'), '/') . '/' . ltrim($relativePath, '/');
            } else {
                $logoUrl = asset($logoUrl);
            }
        }
    }
    
    // Use default logo if no team logo found
    $finalLogoUrl = $logoUrl ?: $defaultLogo;
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ $finalLogoUrl }}" class="logo" alt="Logo">
</a>
</td>
</tr>
