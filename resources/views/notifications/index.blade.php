<x-app-layout :title="\App\Support\PageHeader::make(__('Notifications'))">
    <x-slot name="header">
        <x-page-header :section="__('Notifications')" />
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

            @livewire('notifications.notification-manager')

        </div>
    </div>
</x-app-layout>
