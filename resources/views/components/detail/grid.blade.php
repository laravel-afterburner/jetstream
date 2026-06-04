@props([
    'columns' => 'sm:grid-cols-2 lg:grid-cols-4',
])

<dl {{ $attributes->merge(['class' => 'grid gap-px bg-gray-100 dark:bg-gray-700 '.$columns]) }}>
    {{ $slot }}
</dl>
