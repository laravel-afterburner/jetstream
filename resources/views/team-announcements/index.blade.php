@php($entityLabel = Str::title(config('afterburner.entity_label')))

<x-app-layout :title="\App\Support\PageHeader::make($entityLabel, detail: 'Announcements')">
    <x-slot name="header">
        <x-page-header :section="$entityLabel" detail="Announcements" />
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('team-announcements.announcement-manager', ['team' => $team])
        </div>
    </div>
</x-app-layout>
