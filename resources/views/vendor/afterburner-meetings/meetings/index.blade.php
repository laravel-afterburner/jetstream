<x-app-layout :title="\App\Support\PageHeader::make('Meetings')">
    <x-slot name="header">
        <x-page-header section="Meetings" />
    </x-slot>

    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        @livewire('meetings.index', ['team' => $team])
    </div>
</x-app-layout>
