@props(['detectedTimezone' => null, 'userTimezone' => 'UTC', 'dismissed' => false])

@php
    $sessionDetected = session('detected_timezone');
    $sessionDismissed = session('timezone_suggestion_dismissed', false);
    $shouldShowFromSession = $sessionDetected && !$sessionDismissed;
@endphp

<div x-data="{
    show: @js($shouldShowFromSession),
    detectedTimezone: @js($sessionDetected),
    userTimezone: @js($userTimezone),
    dismissed: @js($sessionDismissed),
    updating: false,
    init() {
        // If banner not shown from session, check cookie client-side
        if (!this.show && !this.dismissed) {
            this.checkCookie();
        }
    },
    checkCookie() {
        // Get timezone from cookie
        const cookieValue = document.cookie
            .split('; ')
            .find(row => row.startsWith('timezone='));
        
        if (cookieValue) {
            const detectedTz = cookieValue.split('=')[1];
            
            // Only show if detected timezone differs from user's saved timezone
            if (detectedTz && detectedTz !== this.userTimezone) {
                this.detectedTimezone = detectedTz;
                this.show = true;
                // The middleware will set the session on the next request automatically
            }
        }
    },
    dismiss() {
        fetch('{{ route('timezone.dismiss') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Content-Type': 'application/json',
            },
        }).then(() => {
            this.show = false;
            this.dismissed = true;
        });
    },
    update() {
        this.updating = true;
        fetch('{{ route('timezone.update') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'X-Update-Timezone': 'true',
                'X-Timezone': this.detectedTimezone,
                'Content-Type': 'application/json',
            },
        }).then(response => response.json())
        .then(data => {
            this.show = false;
            // Reload page to reflect timezone change
            window.location.reload();
        })
        .catch(() => {
            this.updating = false;
        });
    }
}"
@timezone-updated.window="show = false"
x-show="show && detectedTimezone"
x-transition:enter="transition ease-out duration-300"
x-transition:enter-start="opacity-0 transform translate-y-[-10px]"
x-transition:enter-end="opacity-100 translate-y-0"
x-transition:leave="transition ease-in duration-200"
x-transition:leave-start="opacity-100 translate-y-0"
x-transition:leave-end="opacity-0 transform translate-y-[-10px]"
class="bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800">
    <div class="max-w-screen-xl mx-auto py-2 px-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between flex-wrap">
            <div class="w-0 flex-1 flex items-center min-w-0">
                <span class="flex p-2 rounded-lg bg-blue-600">
                    <svg class="size-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>

                <p class="ms-3 font-medium text-sm text-blue-800 dark:text-blue-200">
                    <span x-text="'{{ __('We detected your timezone as :timezone. Would you like to update your timezone preference?', ['timezone' => '']) }}'.replace(':timezone', detectedTimezone)"></span>
                </p>
            </div>

            <div class="shrink-0 sm:ms-3 flex items-center gap-2">
                <button
                    type="button"
                    x-on:click="update()"
                    :disabled="updating"
                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!updating">{{ __('Update') }}</span>
                    <span x-show="updating">{{ __('Updating...') }}</span>
                </button>

                <button
                    type="button"
                    x-on:click="dismiss()"
                    class="-me-1 flex p-2 rounded-md hover:bg-blue-100 dark:hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                    aria-label="Dismiss">
                    <svg class="size-5 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
