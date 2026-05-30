<x-app-layout :title="\App\Support\PageHeader::make(__('Security'))">
    <x-slot name="header">
        <x-page-header :section="__('Security')" />
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.update-password-form')
                </div>

                <x-section-border />
            @endif

            @if (App\Support\Afterburner::hasBiometricFeatures())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.webauthn-credentials-form')
                </div>

                <x-section-border />
            @endif

            @if (App\Support\Afterburner::hasTwoFactorAuthenticationFeatures() && Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.two-factor-authentication-form')
                </div>

                <x-section-border />
            @endif

            <div class="mt-10 sm:mt-0">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>
        </div>
    </div>
</x-app-layout>
