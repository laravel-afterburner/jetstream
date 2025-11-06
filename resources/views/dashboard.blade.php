<x-app-layout title="{{ __('Dashboard') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @if(Auth::user()->unreadNotifications->count() > 0)
                <div class="mb-6 bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-blue-800">
                                You have {{ Auth::user()->unreadNotifications->count() }} new notification{{ Auth::user()->unreadNotifications->count() > 1 ? 's' : '' }}
                            </h3>
                            <div class="mt-2">
                                <a href="{{ route('notifications') }}" class="text-sm text-blue-600 hover:text-blue-500 underline">
                                    View Notifications
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @php
                $featureGroups = \App\Support\Features::getFeatureGroups();
                $allFeatures = \App\Support\Features::getAllFeatures();
                $enabledCount = collect($allFeatures)->where('enabled', true)->count();
                $totalCount = count($allFeatures);
                
                // Add enabled status to each feature in groups (without using references)
                $featureGroupsWithStatus = [];
                foreach ($featureGroups as $group) {
                    $groupWithStatus = [
                        'name' => $group['name'],
                        'features' => [],
                    ];
                    foreach ($group['features'] as $feature) {
                        $groupWithStatus['features'][] = [
                            'key' => $feature['key'],
                            'name' => $feature['name'],
                            'description' => $feature['description'],
                            'enabled' => \App\Support\Features::enabled($feature['key']),
                        ];
                    }
                    $featureGroupsWithStatus[] = $groupWithStatus;
                }
                $featureGroups = $featureGroupsWithStatus;
            @endphp

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <x-welcome>
                    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Feature Summary
                                </h3>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $enabledCount }} of {{ $totalCount }} enabled
                                </span>
                            </div>
                            
                            <div class="space-y-4">
                                @php
                                    $renderedKeys = [];
                                @endphp
                                @foreach($featureGroups as $group)
                                    @if(!empty($group['features']))
                                        <div>
                                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                                                {{ $group['name'] }}
                                            </h4>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($group['features'] as $feature)
                                                    @if(!in_array($feature['key'], $renderedKeys))
                                                        @php
                                                            $renderedKeys[] = $feature['key'];
                                                        @endphp
                                                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                                            <div class="flex items-center gap-2">
                                                                <h5 class="text-xs font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $feature['name'] }}
                                                                </h5>
                                                                @if($feature['enabled'])
                                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 flex-shrink-0">
                                                                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                        </svg>
                                                                    </span>
                                                                @else
                                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 flex-shrink-0">
                                                                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                                        </svg>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </x-welcome>
            </div>
        </div>
    </div>
</x-app-layout>
