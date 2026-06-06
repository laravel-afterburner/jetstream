@php($entityLabel = entity_title())

<x-app-layout :title="\App\Support\PageHeader::make($entityLabel, detail: 'Details')">
    <x-slot name="header">
        <x-page-header :section="$entityLabel" detail="Details" />
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @if (Gate::check('update', $team))
                @livewire('teams.team-information', ['team' => $team])

                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('teams.team-branding', ['team' => $team])
                </div>

                @if (\App\Support\Features::hasTeamTimezoneManagement())
                    <x-section-border />

                    <div class="mt-10 sm:mt-0">
                        @livewire('teams.update-team-timezone-form', ['team' => $team])
                    </div>
                @endif
            @else
                <x-action-section>
                    <x-slot name="title">
                        {{ $entityLabel }} details
                    </x-slot>

                    <x-slot name="description">
                        {{ __('Name, logo, branding, and timezone for this :entity.', ['entity' => entity_label()]) }}
                    </x-slot>

                    <x-slot name="content">
                        @include('teams.partials.team-details-summary', ['team' => $team])
                    </x-slot>
                </x-action-section>

                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('teams.team-information', ['team' => $team])
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
