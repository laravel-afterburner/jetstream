@php
    $hasTeamBranding = isset($teamBranding) && !empty($teamBranding['primary_color']);
    $hasSecondaryColor = isset($teamBranding) && !empty($teamBranding['secondary_color']);
    $defaultClasses = 'inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150';
    
    if ($hasTeamBranding) {
        // Use team primary color - CSS will handle the styling including focus ring
        $classes = $defaultClasses . ' btn-team-primary dark:focus:ring-offset-gray-800';
        if ($hasSecondaryColor) {
            $classes .= ' btn-team-secondary-text';
        } else {
            $classes .= ' text-white';
        }
    } else {
        // Use default colors
        $classes = $defaultClasses . ' bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:ring-indigo-500 dark:focus:ring-offset-gray-800';
    }
    
    $showSpinner = !$attributes->has('no-spinner');
    
    // Extract method name from wire:click for scoping spinner
    $wireClick = $attributes->get('wire:click', '');
    $targetMethod = '';
    if ($wireClick && $showSpinner) {
        // Extract method name (everything before the first parenthesis)
        $targetMethod = trim(explode('(', $wireClick)[0]);
    }
@endphp

<button {{ $attributes->except('no-spinner')->merge(['type' => 'submit', 'class' => $classes]) }}>
    @if($showSpinner)
        <!-- Spinner shown when loading, scoped to button's wire:click action if present -->
        <svg 
            wire:loading 
            @if($targetMethod)
                wire:target="{{ $targetMethod }}"
            @endif
            class="animate-spin -ml-1 mr-2 h-4 w-4" 
            xmlns="http://www.w3.org/2000/svg" 
            fill="none" 
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @endif
    
    <!-- Button content -->
    {{ $slot }}
</button>