@php($entityLabel = Str::title(config('afterburner.entity_label')))

<x-app-layout :title="\App\Support\PageHeader::make($entityLabel, detail: 'Details')">
    <x-slot name="header">
        <x-page-header :section="$entityLabel" detail="Details" />
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('teams.team-information', ['team' => $team])

            @if (Gate::check('update', $team))
                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('teams.team-branding', ['team' => $team])
                </div>
            @endif

            @if (\App\Support\Features::hasTeamTimezoneManagement())
                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('teams.update-team-timezone-form', ['team' => $team])
                </div>
            @endif

            @if (App\Support\Afterburner::hasTeamDeletionFeatures() && Gate::check('delete', $team))
                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('teams.delete-team-form', ['team' => $team])
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
