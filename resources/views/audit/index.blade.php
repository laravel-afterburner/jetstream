<x-app-layout :title="\App\Support\PageHeader::make('Audit', detail: 'Logs')">
    <x-slot name="header">
        <x-page-header section="Audit" detail="Logs" />
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('audit.audit-trail-viewer')
        </div>
    </div>
</x-app-layout>
