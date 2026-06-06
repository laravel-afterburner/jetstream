@php($entityLabel = entity_title())

<x-app-layout :title="\App\Support\PageHeader::make($entityLabel, detail: 'Role management')">
    <x-slot name="header">
        <x-page-header :section="$entityLabel" detail="Role management" />
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('roles.role-manager', ['team' => $team])
        </div>
    </div>
</x-app-layout>
