import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Detect and send timezone with each request
(function() {
    try {
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        function requestHeadersInclude(headers, name) {
            if (!headers) {
                return false;
            }

            const lowerName = name.toLowerCase();

            if (headers instanceof Headers) {
                return headers.has(name) || headers.has(lowerName);
            }

            return Object.keys(headers).some(key => key.toLowerCase() === lowerName);
        }

        function shouldAttachTimezoneHeader(url, options) {
            if (requestHeadersInclude(options.headers, 'X-Livewire-Navigate')) {
                return false;
            }

            const urlString = typeof url === 'string' ? url : (url?.url ?? '');

            if (urlString.includes('/livewire/')) {
                return false;
            }

            return true;
        }

        // Set timezone in cookie for server-side access
        document.cookie = `timezone=${encodeURIComponent(timezone)}; path=/; max-age=${60 * 60 * 24 * 365}; SameSite=Lax`;

        // Set timezone header for axios requests
        window.axios.defaults.headers.common['X-Timezone'] = timezone;

        // Also intercept fetch requests to add timezone header
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            const url = args[0];
            let options = args[1] || {};

            // Ensure headers object exists
            if (!options.headers) {
                options.headers = {};
            }

            // Convert Headers object to plain object if needed
            if (options.headers instanceof Headers) {
                const headersObj = {};
                options.headers.forEach((value, key) => {
                    headersObj[key] = value;
                });
                options.headers = headersObj;
            }

            if (shouldAttachTimezoneHeader(url, options)) {
                options.headers['X-Timezone'] = timezone;
            }

            return originalFetch(url, options);
        };

        // Livewire automatically sends cookies, so the cookie approach should work
        // For Livewire v3, we can also hook into the request to add headers
        document.addEventListener('livewire:init', () => {
            if (window.Livewire) {
                window.Livewire.hook('request', ({ uri, options, payload, respond, succeed, fail }) => {
                    if (!options.headers) {
                        options.headers = {};
                    }

                    if (shouldAttachTimezoneHeader(uri, options)) {
                        options.headers['X-Timezone'] = timezone;
                    }
                });
            }
        });
    } catch (e) {
        // Silently fail if timezone detection is not supported
        console.debug('Timezone detection not supported');
    }
})();
