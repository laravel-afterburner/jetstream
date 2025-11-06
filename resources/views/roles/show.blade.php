<x-app-layout title="{{ Str::title(config('afterburner.entity_label')) }} Role Management">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ Str::title(config('afterburner.entity_label')) }} Role Management
            </h2>
        </div>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('roles.role-manager', ['team' => $team])
        </div>
    </div>
</x-app-layout>