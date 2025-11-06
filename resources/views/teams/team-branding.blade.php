<div>
    <!-- Branding Section (Logo + Colors) -->
    <x-form-section submit="updateBranding">
        <x-slot name="title">
            {{ __('Branding') }}
        </x-slot>

        <x-slot name="description">
            Customize your {{ config('afterburner.entity_label') }} branding with a logo and colors. The logo will be displayed in the navigation bar and emails, and colors will be applied to the interface and emails.
        </x-slot>

        <x-slot name="form">
            <!-- Logo Upload -->
            <div x-data="{logoName: null, logoPreview: null}" class="col-span-6 sm:col-span-4">
                <x-label for="logo" value="{{ __('Logo') }}" />

                <!-- Current Logo -->
                <div class="mt-2" x-show="! logoPreview">
                    <img src="{{ $team->getLogoUrl() }}" alt="{{ $team->name }} Logo" class="h-20 object-contain">
                </div>

                <!-- New Logo Preview -->
                <div class="mt-2" x-show="logoPreview" style="display: none;">
                    <img x-bind:src="logoPreview" 
                         alt="Logo Preview" 
                         class="h-20 object-contain">
                </div>

                <!-- Logo File Input -->
                <input type="file" id="logo" class="hidden"
                            wire:model.live="logo"
                            x-ref="logo"
                            accept="image/*"
                            x-on:change="
                                    logoName = $refs.logo.files[0]?.name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        logoPreview = e.target.result;
                                    };
                                    if ($refs.logo.files[0]) {
                                        reader.readAsDataURL($refs.logo.files[0]);
                                    }
                            " />

                <x-secondary-button class="mt-2 me-2" type="button" x-on:click.prevent="$refs.logo.click()">
                    {{ __('Select A New Logo') }}
                </x-secondary-button>

                @if ($team->logo_url)
                    <x-secondary-button type="button" class="mt-2" wire:click="deleteLogo">
                        {{ __('Remove Logo') }}
                    </x-secondary-button>
                @endif

                <x-input-error for="logo" class="mt-2" />
            </div>

            <!-- Colors -->
            <div class="col-span-6 sm:col-span-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Primary Color -->
                    <div>
                        <x-label for="primary_color" value="{{ __('Primary Color') }}" />
                        <div class="mt-1 flex items-center gap-3">
                            <input type="color" 
                                   id="primary_color"
                                   wire:model.live="brandingForm.primary_color"
                                   class="h-10 w-20 flex-shrink-0 rounded border border-gray-300 dark:border-gray-700 cursor-pointer"
                                   :disabled="! Gate::check('update', $team)" />
                            <x-input id="primary_color_text"
                                     type="text"
                                     class="flex-1 min-w-0"
                                     wire:model.live="brandingForm.primary_color"
                                     placeholder="#000000"
                                     :disabled="! Gate::check('update', $team)" />
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Used for primary buttons, links, and accents.</p>
                        <x-input-error for="brandingForm.primary_color" class="mt-2" />
                    </div>

                    <!-- Secondary Color -->
                    <div>
                        <x-label for="secondary_color" value="{{ __('Secondary Color') }}" />
                        <div class="mt-1 flex items-center gap-3">
                            <input type="color" 
                                   id="secondary_color"
                                   wire:model.live="brandingForm.secondary_color"
                                   class="h-10 w-20 flex-shrink-0 rounded border border-gray-300 dark:border-gray-700 cursor-pointer"
                                   :disabled="! Gate::check('update', $team)" />
                            <x-input id="secondary_color_text"
                                     type="text"
                                     class="flex-1 min-w-0"
                                     wire:model.live="brandingForm.secondary_color"
                                     placeholder="#000000"
                                     :disabled="! Gate::check('update', $team)" />
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Used for secondary elements and highlights.</p>
                        <x-input-error for="brandingForm.secondary_color" class="mt-2" />
                    </div>
                </div>
            </div>
        </x-slot>

        @if (Gate::check('update', $team))
            <x-slot name="actions">
                <x-action-message class="me-3" on="saved">
                    {{ __('Saved.') }}
                </x-action-message>

                <x-button>
                    {{ __('Save') }}
                </x-button>
            </x-slot>
        @endif
    </x-form-section>
</div>
