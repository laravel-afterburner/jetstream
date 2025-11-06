@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full px-4 py-2 border-l-4 border-indigo-400 dark:border-indigo-600 text-start text-sm leading-5 text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/50 focus:outline-none focus:bg-indigo-100 dark:focus:bg-indigo-900 transition duration-150 ease-in-out'
            : 'block w-full px-4 py-2 border-l-4 border-transparent text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out';

// Only add wire:navigate for actual navigation links (not "#" or wire:click actions)
$href = $attributes->get('href', '');
$shouldNavigate = $href !== '#' && !$attributes->has('wire:click') && !$attributes->has('@click.prevent');
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}@if($shouldNavigate)  wire:navigate @endif>
    {{ $slot }}
</a>
