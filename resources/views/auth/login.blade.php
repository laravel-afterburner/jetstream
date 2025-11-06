<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}" id="login-form">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <!-- WebAuthn Login Button -->
            @if (App\Support\Afterburner::hasBiometricFeatures())
            <div class="mt-4" id="webauthn-login-container" style="display: none;">
                <x-button 
                    type="button" 
                    id="webauthn-login-btn" 
                    class="w-full justify-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    {{ __('Login with Face ID / Touch ID') }}
                </x-button>
                <div class="mt-2 text-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('or') }}</span>
                </div>
            </div>
            @endif

            <div class="mt-4" id="password-login-container">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-between mt-4">
                @if (Route::has('register'))
                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('register') }}">
                        {{ __('Need an account?') }}
                    </a>
                @endif

                <div class="flex items-center">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif

                    <x-button class="ms-4">
                        {{ __('Log in') }}
                    </x-button>
                </div>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>

@if (App\Support\Afterburner::hasBiometricFeatures())
<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const webauthnContainer = document.getElementById('webauthn-login-container');
    const passwordContainer = document.getElementById('password-login-container');
    const webauthnBtn = document.getElementById('webauthn-login-btn');
    
    // Check if WebAuthn is supported
    const webauthnSupported = typeof PublicKeyCredential !== 'undefined';
    
    if (!webauthnSupported) {
        return; // Hide WebAuthn option if not supported
    }
    
    // Check if user has WebAuthn credentials
    async function checkWebAuthnCredentials(email) {
        if (!email || !email.includes('@')) {
            webauthnContainer.style.display = 'none';
            passwordContainer.classList.remove('mt-4');
            passwordContainer.classList.add('mt-4');
            return;
        }
        
        try {
            const response = await fetch('{{ route("webauthn.login.options") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin',
                body: JSON.stringify({ email: email })
            });
            
            if (response.ok) {
                const data = await response.json();
                // If allowCredentials exists and has items, user has WebAuthn credentials
                if (data.allowCredentials && data.allowCredentials.length > 0) {
                    webauthnContainer.style.display = 'block';
                    passwordContainer.classList.remove('mt-4');
                    passwordContainer.classList.add('mt-4');
                } else {
                    webauthnContainer.style.display = 'none';
                }
            } else {
                webauthnContainer.style.display = 'none';
            }
        } catch (error) {
            console.error('Error checking WebAuthn credentials:', error);
            webauthnContainer.style.display = 'none';
        }
    }
    
    // Check on email input
    let checkTimeout;
    emailInput.addEventListener('input', function() {
        clearTimeout(checkTimeout);
        checkTimeout = setTimeout(() => {
            checkWebAuthnCredentials(emailInput.value);
        }, 500);
    });
    
    // Initial check
    if (emailInput.value) {
        checkWebAuthnCredentials(emailInput.value);
    }
    
    // WebAuthn login handler
    webauthnBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        const email = emailInput.value;
        
        if (!email) {
            alert('{{ __("Please enter your email address first.") }}');
            return;
        }
        
        webauthnBtn.disabled = true;
        webauthnBtn.innerHTML = '<svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg><span>{{ __("Authenticating...") }}</span>';
        
        try {
            // Get assertion options
            const optionsResponse = await fetch('{{ route("webauthn.login.options") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin',
                body: JSON.stringify({ email: email })
            });
            
            if (!optionsResponse.ok) {
                throw new Error('Failed to get login options');
            }
            
            const options = await optionsResponse.json();
            
            // Parse challenge
            const challenge = Uint8Array.from(atob(options.challenge.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));
            
            // Parse allowCredentials if present
            if (options.allowCredentials) {
                options.allowCredentials = options.allowCredentials.map(cred => ({
                    ...cred,
                    id: Uint8Array.from(atob(cred.id.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0))
                }));
            }
            
            // Get credential
            const credential = await navigator.credentials.get({
                publicKey: {
                    ...options,
                    challenge: challenge
                }
            });
            
            // Convert credential to base64
            const credentialId = btoa(String.fromCharCode(...new Uint8Array(credential.rawId)));
            const clientDataJSON = btoa(String.fromCharCode(...new Uint8Array(credential.response.clientDataJSON)));
            const authenticatorData = btoa(String.fromCharCode(...new Uint8Array(credential.response.authenticatorData)));
            const signature = btoa(String.fromCharCode(...new Uint8Array(credential.response.signature)));
            const userHandle = credential.response.userHandle ? btoa(String.fromCharCode(...new Uint8Array(credential.response.userHandle))) : null;
            
            // Send credential to server
            const loginResponse = await fetch('{{ route("webauthn.login") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    id: credential.id,
                    rawId: credentialId,
                    type: credential.type,
                    response: {
                        clientDataJSON: clientDataJSON,
                        authenticatorData: authenticatorData,
                        signature: signature,
                        userHandle: userHandle
                    }
                })
            });
            
            if (loginResponse.ok) {
                // Redirect to dashboard
                window.location.href = '{{ route("dashboard") }}';
            } else {
                const error = await loginResponse.json();
                alert(error.message || '{{ __("Authentication failed. Please try again.") }}');
                webauthnBtn.disabled = false;
                webauthnBtn.innerHTML = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>{{ __("Login with Face ID / Touch ID") }}';
            }
        } catch (error) {
            console.error('WebAuthn login error:', error);
            alert(error.message || '{{ __("Authentication failed. Please try again or use password login.") }}');
            webauthnBtn.disabled = false;
            webauthnBtn.innerHTML = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>{{ __("Login with Face ID / Touch ID") }}';
        }
    });
});
</script>
@endif