@php
    $logoUrl = $teamBranding['logo_url'] ?? null;
    if (!$logoUrl) {
        $logoUrl = asset('media/logo.png');
    } elseif (!filter_var($logoUrl, FILTER_VALIDATE_URL)) {
        if (str_starts_with($logoUrl, 'teams/')) {
            $logoUrl = \Storage::url($logoUrl);
        } else {
            $logoUrl = asset($logoUrl);
        }
    }
@endphp
<img src="{{ $logoUrl }}" title="Logo" style="border: solid 1px darkgreen; border-radius: 10px; width: 150px; height: auto;">
