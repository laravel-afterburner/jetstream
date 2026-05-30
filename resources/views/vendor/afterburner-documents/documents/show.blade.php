<x-app-layout :title="\App\Support\PageHeader::make('Documents')">
    @include('afterburner-documents::components.filepond-assets')

    <x-slot name="header">
        <x-page-header section="Documents" />
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('documents.index', ['team' => $team, 'folder_slug' => $folder_slug ?? null])
        </div>
    </div>
</x-app-layout>
