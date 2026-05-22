@props(['team' => null])
<x-mail::layout :team="$team">
{{-- Header --}}
<x-slot:header>
@if(isset($header))
{!! $header !!}
@else
<x-mail::header :url="config('app.url')" :team="$team" />
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
© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
