@props(['form'])

@php
    $permissionGroups = collect($this->groupedPermissions)
        ->map(fn ($permissions, $groupLabel) => [
            'label' => $groupLabel,
            'permissions' => $permissions->map(fn ($permission) => [
                'name' => $permission->name,
                'slug' => $permission->slug,
            ])->values()->all(),
        ])
        ->values()
        ->all();
@endphp

<div
    x-data="{
        search: '',
        groups: @js($permissionGroups),
        normalize(text) {
            return (text ?? '').toString().toLowerCase();
        },
        query() {
            return this.normalize(this.search).trim();
        },
        matches(...texts) {
            const q = this.query();

            if (q === '') {
                return true;
            }

            return texts.some(text => this.normalize(text).includes(q));
        },
        permissionMatches(permission) {
            return this.matches(permission.name, permission.slug);
        },
        groupVisible(groupLabel, permissions) {
            const q = this.query();

            if (q === '') {
                return true;
            }

            return permissions.some(permission => this.permissionVisible(permission));
        },
        permissionVisible(permission) {
            const q = this.query();

            if (q === '') {
                return true;
            }

            return this.permissionMatches(permission);
        },
        hasResults() {
            const q = this.query();

            if (q === '') {
                return true;
            }

            return this.groups.some(group => this.groupVisible(group.label, group.permissions));
        },
    }"
    class="space-y-2"
>
    <label class="sr-only" for="{{ $form }}_permission_search">Search permissions</label>
    <x-input
        id="{{ $form }}_permission_search"
        type="search"
        class="block w-full"
        x-model="search"
        placeholder="Search permissions…"
    />

    <div class="max-h-60 overflow-y-auto space-y-4 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
        @foreach ($this->groupedPermissions as $groupLabel => $groupPermissions)
            @php
                $permissionSearchData = $groupPermissions->map(fn ($permission) => [
                    'name' => $permission->name,
                    'slug' => $permission->slug,
                ])->values()->all();
            @endphp

            <div x-show="groupVisible(@js($groupLabel), @js($permissionSearchData))" x-cloak>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __($groupLabel) }}</p>
                <div class="mt-2 space-y-2">
                    @foreach ($groupPermissions as $index => $permission)
                        <label
                            x-show="permissionVisible(@js($permissionSearchData[$index]))"
                            x-cloak
                            class="flex items-center"
                        >
                            <input
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:focus:border-indigo-600 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                                wire:model="{{ $form }}.permissions"
                                value="{{ $permission->id }}"
                            >
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

        <p
            x-show="! hasResults()"
            x-cloak
            class="text-sm text-gray-500 dark:text-gray-400"
        >
            No permissions match your search.
        </p>
    </div>
</div>
