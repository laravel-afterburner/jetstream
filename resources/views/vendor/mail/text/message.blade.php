@props(['team' => null])
@php
    // Determine the team name to show in footer
    // Only check for explicitly passed variables (safe for queued emails)
    $teamName = config('app.name'); // Default fallback
    
    // Check if team prop is available (passed explicitly to component or from mail classes)
    if ($team && isset($team->name)) {
        $teamName = $team->name;
    }
@endphp
<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')" :team="$team">
            {{ $teamName }}
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            © {{ date('Y') }} {{ $teamName }}. @lang('All rights reserved.')
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
