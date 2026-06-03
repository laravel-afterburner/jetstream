@props(['team' => null])
@php
    $teamName = config('app.name');

    if ($team && isset($team->name)) {
        $teamName = $team->name;
    }
@endphp
<x-mail::layout :team="$team">
{{-- Header --}}
<x-slot:header>
@if(isset($header))
{!! $header !!}
@else
<x-mail::header :url="config('app.url')" :team="$team" :teamLogo="$teamLogo ?? null" />
@endif
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ $teamName }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
