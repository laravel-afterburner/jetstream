@props([
    'label',
    'value' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white px-4 py-4 sm:px-6 dark:bg-gray-800/80']) }}>
    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
        {{ $label }}
    </dt>
    <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">
        @if (isset($slot) && ! $slot->isEmpty())
            {{ $slot }}
        @else
            {{ filled($value) ? $value : '—' }}
        @endif
    </dd>
</div>
