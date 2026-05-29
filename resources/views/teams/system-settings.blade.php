<x-app-layout title="System Settings">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            System Settings
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 space-y-10">
            @foreach (App\Support\SystemSettings::sections() as $section)
                @livewire(
                    $section['component'],
                    value($section['params'] ?? fn () => ['team' => $team], $team),
                    key($section['key'].'-settings-'.$team->id)
                )
            @endforeach
        </div>
    </div>
</x-app-layout>
