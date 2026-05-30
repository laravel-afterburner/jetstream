<x-app-layout :title="\App\Support\PageHeader::make('Settings', detail: 'System')">
    <x-slot name="header">
        <x-page-header section="Settings" detail="System" />
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
