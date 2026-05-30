@props([
    'type' => 'view',
    'title' => '',
    'href' => null,
])

@php
    $baseClass = 'rounded p-1 text-gray-400 transition';

    $hoverClass = match ($type) {
        'view' => 'hover:text-green-600 dark:hover:text-green-400',
        'edit' => 'hover:text-blue-600 dark:hover:text-blue-400',
        'archive' => 'hover:text-gray-600 dark:hover:text-gray-300',
        'delete' => 'hover:text-red-600 dark:hover:text-red-400',
        'download' => 'hover:text-indigo-600 dark:hover:text-indigo-400',
        'move' => 'hover:text-indigo-600 dark:hover:text-indigo-400',
        'mark-read' => 'hover:text-blue-600 dark:hover:text-blue-400',
        default => 'hover:text-gray-600 dark:hover:text-gray-300',
    };

    $class = trim("{$baseClass} {$hoverClass}");

    $icons = [
        'view' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>',
        'edit' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>',
        'archive' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>',
        'delete' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>',
        'download' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"></path>',
        'move' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>',
        'mark-read' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
    ];

    $iconPath = $icons[$type] ?? $icons['view'];
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if ($href)
        href="{{ $href }}"
    @else
        type="button"
    @endif
    {{ $attributes->merge(['class' => $class]) }}
    @if ($title)
        title="{{ $title }}"
    @endif
>
    @if ($title)
        <span class="sr-only">{{ $title }}</span>
    @endif
    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        {!! $iconPath !!}
    </svg>
</{{ $tag }}>
