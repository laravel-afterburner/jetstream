import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Detect and send timezone with each request
(function() {
    try {
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        
        // Set timezone in cookie for server-side access
        document.cookie = `timezone=${timezone}; path=/; max-age=${60 * 60 * 24 * 365}; SameSite=Lax`;
        
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
            
            // Add timezone header
            options.headers['X-Timezone'] = timezone;
            
            return originalFetch(url, options);
        };

        // Livewire automatically sends cookies, so the cookie approach should work
        // For Livewire v3, we can also hook into the request to add headers
        document.addEventListener('livewire:init', () => {
            if (window.Livewire) {
                // Hook into Livewire's request to add timezone header
                window.Livewire.hook('request', ({ uri, options, payload, respond, succeed, fail }) => {
                    // Add timezone header to Livewire requests
                    if (!options.headers) {
                        options.headers = {};
                    }
                    options.headers['X-Timezone'] = timezone;
                });
            }
        });
    } catch (e) {
        // Silently fail if timezone detection is not supported
        console.debug('Timezone detection not supported');
    }
})();
