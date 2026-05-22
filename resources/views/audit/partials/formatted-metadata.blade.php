@php
    use Illuminate\Support\Str;
    $humanize = fn (string $key) => Str::title(str_replace('_', ' ', $key));
    $formatValue = function ($value) {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        if (is_null($value)) {
            return '(empty)';
        }
        if (is_array($value) && !array_is_list($value)) {
            return null;
        }
        if (is_array($value) && array_is_list($value)) {
            return implode(', ', $value);
        }

        return $value;
    };

    $sections = [
        'Request' => ['request_id', 'timestamp', 'timezone'],
        'Request Context' => ['ip', 'url', 'route', 'referer', 'method'],
        'Session' => ['session_id'],
        'Device & Browser' => ['browser', 'browser_version', 'platform', 'device_type', 'is_mobile', 'is_tablet', 'is_desktop', 'screen_resolution'],
        'Request Headers' => ['user_agent'],
        'Performance' => ['performance'],
        'Event Details' => ['component', 'mailable', 'failure_reason', 'was_recently_created', 'model_created_at', 'model_updated_at'],
        'Other' => [],
    ];

    $assignedKeys = [];
    foreach ($sections as $section => $keys) {
        $assignedKeys = array_merge($assignedKeys, $keys);
    }
@endphp
<div class="space-y-4">
    @foreach($sections as $sectionName => $sectionKeys)
        @php
            $sectionData = [];
            if ($sectionName === 'Other') {
                $sectionData = array_diff_key($metadata ?? [], array_flip($assignedKeys));
            } else {
                foreach ($sectionKeys as $key) {
                    if (isset($metadata[$key])) {
                        $sectionData[$key] = $metadata[$key];
                    }
                }
            }
        @endphp
        @if(!empty($sectionData))
            <div>
                <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">{{ $sectionName }}</h4>
                <dl class="space-y-2">
                    @foreach($sectionData as $key => $value)
                        @if($key === 'performance' && is_array($value))
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Performance Metrics</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <ul class="space-y-1">
                                        @if(isset($value['execution_time_ms']))
                                            <li>Execution time: {{ $value['execution_time_ms'] }} ms</li>
                                        @endif
                                        @if(isset($value['memory_usage_mb']))
                                            <li>Memory usage: {{ $value['memory_usage_mb'] }} MB</li>
                                        @endif
                                        @if(isset($value['database_queries_count']))
                                            <li>Database queries: {{ $value['database_queries_count'] }}</li>
                                        @endif
                                        @if(isset($value['database_query_time_ms']))
                                            <li>Query time: {{ $value['database_query_time_ms'] }} ms</li>
                                        @endif
                                        @if(isset($value['queue_wait_time_ms']))
                                            <li>Queue wait: {{ $value['queue_wait_time_ms'] }} ms</li>
                                        @endif
                                    </ul>
                                </dd>
                            </div>
                        @elseif($key === 'screen_resolution' && is_array($value))
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $humanize($key) }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ ($value['width'] ?? '?') }} × {{ ($value['height'] ?? '?') }}
                                </dd>
                            </div>
                        @elseif(is_array($value) && !array_is_list($value))
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $humanize($key) }}</dt>
                                <dd class="mt-1 ml-4 pl-3 border-l-2 border-gray-200 dark:border-gray-600 text-sm text-gray-900 dark:text-gray-100">
                                    @foreach($value as $k => $v)
                                        <div class="py-0.5">
                                            <span class="text-gray-500 dark:text-gray-400">{{ $humanize($k) }}:</span>
                                            <span class="ml-1">{{ is_scalar($v) ? $v : json_encode($v) }}</span>
                                        </div>
                                    @endforeach
                                </dd>
                            </div>
                        @else
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $humanize($key) }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 break-all">
                                    {{ $formatValue($value) }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </div>
        @endif
    @endforeach
</div>
