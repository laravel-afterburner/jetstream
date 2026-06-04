@php
    use App\Support\Features;
    use App\Support\TimezoneDisplay;

    $entityLabel = Str::title(config('afterburner.entity_label'));
    $timezone = $team->timezone ?? config('app.timezone', 'UTC');
    $currentTime = now()->setTimezone($timezone);
    $primaryColor = $team->primary_color ?? '#4f46e5';
    $secondaryColor = $team->secondary_color ?? '#6366f1';
@endphp

<x-detail.panel>
    <div class="px-4 py-6 sm:px-6">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
            <div class="flex shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                <img
                    src="{{ $team->getLogoUrl() }}"
                    alt="{{ $team->name }} logo"
                    class="h-20 w-auto max-w-[12rem] object-contain"
                />
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    {{ $entityLabel }} name
                </p>
                <p class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                    {{ $team->name }}
                </p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Core profile and branding for this :entity.', ['entity' => config('afterburner.entity_label')]) }}
                </p>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div class="rounded-lg border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-700 dark:bg-gray-900/30">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    {{ __('Primary color') }}
                </p>
                <div class="mt-2 flex items-center gap-3">
                    <span
                        class="h-8 w-8 shrink-0 rounded-md border border-gray-200 dark:border-gray-600"
                        style="background-color: {{ $primaryColor }}"
                    ></span>
                    <span class="font-mono text-sm text-gray-900 dark:text-gray-100">{{ $primaryColor }}</span>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-700 dark:bg-gray-900/30">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    {{ __('Secondary color') }}
                </p>
                <div class="mt-2 flex items-center gap-3">
                    <span
                        class="h-8 w-8 shrink-0 rounded-md border border-gray-200 dark:border-gray-600"
                        style="background-color: {{ $secondaryColor }}"
                    ></span>
                    <span class="font-mono text-sm text-gray-900 dark:text-gray-100">{{ $secondaryColor }}</span>
                </div>
            </div>
        </div>

        @if (Features::hasTeamTimezoneManagement())
            <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-700 dark:bg-gray-900/30">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ __('Timezone') }}
                        </p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ TimezoneDisplay::label($timezone) }}
                            <span class="font-mono text-xs text-gray-500 dark:text-gray-400">({{ $timezone }})</span>
                        </p>
                    </div>
                    <div class="sm:text-end">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ __('Current time') }}
                        </p>
                        <p class="mt-1 text-sm font-medium tabular-nums text-gray-900 dark:text-gray-100">
                            {{ $currentTime->format('g:i A') }}
                            <span class="text-gray-500 dark:text-gray-400">({{ $currentTime->format('T') }})</span>
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-detail.panel>
