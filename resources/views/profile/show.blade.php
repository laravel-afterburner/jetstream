<x-app-layout :title="\App\Support\PageHeader::make(__('Profile'))">
    <x-slot name="header">
        <x-page-header :section="__('Profile')" />
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')

                <x-section-border />
            @endif

            <div class="mt-10 sm:mt-0">
                @livewire('profile.update-color-scheme-form')
            </div>

            <x-section-border />

            @if (App\Support\Afterburner::hasUserTimezoneManagement())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.update-timezone-form')
                </div>

                <x-section-border />
            @endif

            @if (App\Support\Afterburner::hasAccountDeletionFeatures())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.delete-user-form')
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
