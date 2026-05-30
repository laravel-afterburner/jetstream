<x-app-layout :title="\App\Support\PageHeader::make('Voting')">
    <x-slot name="header">
        <x-page-header section="Voting" />
    </x-slot>

    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        @livewire('voting.index', ['team' => $team])
    </div>
</x-app-layout>
