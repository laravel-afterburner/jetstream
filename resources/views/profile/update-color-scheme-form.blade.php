<x-form-section submit="updateColorScheme">
    <x-slot name="title">
        {{ __('Appearance') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Choose whether the interface uses a light theme, dark theme, or follows your device settings.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-label for="color-scheme" value="{{ __('Color scheme') }}" />

            <fieldset class="mt-3 space-y-3">
                @foreach($options as $value => $label)
                    <label
                        for="color-scheme-{{ $value }}"
                        class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 px-4 py-3 transition-colors dark:border-gray-700 {{ $colorScheme === $value ? 'border-indigo-500 bg-indigo-50 dark:border-indigo-500 dark:bg-indigo-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50' }}"
                    >
                        <input
                            id="color-scheme-{{ $value }}"
                            type="radio"
                            name="colorScheme"
                            value="{{ $value }}"
                            wire:model.live="colorScheme"
                            class="mt-1 border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:focus:ring-indigo-600"
                        />
                        <span>
                            <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $label }}
                            </span>
                            @if($value === \App\Support\ColorScheme::System)
                                <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('Automatically match your operating system or browser preference.') }}
                                </span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </fieldset>

            <x-input-error for="colorScheme" bag="updateColorScheme" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled">
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>
