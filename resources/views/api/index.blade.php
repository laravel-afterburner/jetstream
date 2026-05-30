<x-app-layout :title="\App\Support\PageHeader::make('API', detail: __('Tokens'))">
    <x-slot name="header">
        <x-page-header section="API" :detail="__('Tokens')" />
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('api.api-token-manager')
        </div>
    </div>
</x-app-layout>
