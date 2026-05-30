<x-app-layout :title="\App\Support\PageHeader::make('Meetings', detail: $meeting->title)">
    <x-slot name="header">
        <x-page-header section="Meetings" :detail="$meeting->title" />
    </x-slot>

    <div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8">
        @livewire('meetings.show', ['team' => $team, 'meeting' => $meeting])
    </div>
</x-app-layout>
