@props([
    'heading' => null,
    'subheading' => null,
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800/80']) }}>
    @if ($heading || $subheading || isset($icon))
        <div class="border-b border-gray-100 px-4 py-5 sm:px-6 dark:border-gray-700">
            <div class="flex min-w-0 gap-4">
                @isset($icon)
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400">
                        {{ $icon }}
                    </div>
                @endisset
                <div class="min-w-0">
                    @if ($heading)
                        <h3 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                            {{ $heading }}
                        </h3>
                    @endif
                    @if ($subheading)
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $subheading }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{ $slot }}
</div>
