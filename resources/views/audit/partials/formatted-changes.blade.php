@php
    use Illuminate\Support\Str;
    $humanize = fn (string $key) => Str::title(str_replace('_', ' ', $key));
    $formatValue = function ($value, $inline = false) {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        if (is_null($value)) {
            return '(empty)';
        }
        if (is_array($value) && $inline) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        if (is_array($value)) {
            return null;
        }

        return $value;
    };
@endphp
<dl class="space-y-3">
    @foreach($data as $key => $value)
        <div>
            @if(is_array($value) && array_key_exists('before', $value) && array_key_exists('after', $value))
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $humanize($key) }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    <span class="text-red-600 dark:text-red-400 line-through">{{ $formatValue($value['before'], true) ?? '(empty)' }}</span>
                    <span class="mx-1 text-gray-400 inline-flex">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </span>
                    <span class="text-green-600 dark:text-green-400 font-medium">{{ $formatValue($value['after'], true) ?? '(empty)' }}</span>
                </dd>
            @elseif(is_array($value) && !array_is_list($value))
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $humanize($key) }}</dt>
                <dd class="mt-1 ml-4 pl-3 border-l-2 border-gray-200 dark:border-gray-600">
                    @include('audit.partials.formatted-changes', ['data' => $value])
                </dd>
            @elseif(is_array($value) && array_is_list($value))
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $humanize($key) }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ implode(', ', array_map(fn ($v) => is_scalar($v) ? $v : json_encode($v), $value)) }}
                </dd>
            @else
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $humanize($key) }}</dt>
                <dd class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $formatValue($value) }}</dd>
            @endif
        </div>
    @endforeach
</dl>
