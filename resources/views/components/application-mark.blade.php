@php
    $logoUrl = $teamBranding['logo_url'] ?? null;
    $defaultLogo = asset('media/logo.png');
    if (!$logoUrl) {
        $logoUrl = $defaultLogo;
    } elseif (!filter_var($logoUrl, FILTER_VALIDATE_URL)) {
        if (str_starts_with($logoUrl, 'teams/')) {
            $logoUrl = \Storage::url($logoUrl);
        } else {
            $logoUrl = asset($logoUrl);
        }
    }
@endphp
<img id="team-logo-mark" 
     src="{{ $logoUrl }}" 
     title="Logo" 
     style="border: solid 1px darkgreen; border-radius: 10px; width: 50px; height: auto;"
     x-data="{ 
         currentLogo: '{{ $logoUrl }}', 
         defaultLogo: '{{ $defaultLogo }}',
         handleLogoUpdate(event) {
             if (event.detail && event.detail.logoUrl) {
                 const timestamp = new Date().getTime();
                 $el.src = event.detail.logoUrl + '?t=' + timestamp;
                 this.currentLogo = event.detail.logoUrl;
             } else {
                 const timestamp = new Date().getTime();
                 const newSrc = this.currentLogo.includes('?') 
                     ? this.currentLogo.split('?')[0] + '?t=' + timestamp
                     : this.currentLogo + '?t=' + timestamp;
                 $el.src = newSrc;
             }
         }
     }"
     @team-branding-changed.window="handleLogoUpdate($event)">
