<div>
    <!-- Register New Device -->
    <x-form-section>
        <x-slot name="title">
            {{ __('Biometric Login (WebAuthn)') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Register your devices to sign in with Face ID, Touch ID, or other biometric methods. You can register multiple devices.') }}
        </x-slot>

        <x-slot name="form">
            <div class="col-span-6 sm:col-span-4">
                <x-label for="deviceName" value="{{ __('Device Name') }}" />
                <x-input 
                    id="deviceName" 
                    type="text" 
                    class="mt-1 block w-full" 
                    wire:model="deviceName"
                    placeholder="{{ __('e.g., iPhone 15, Work MacBook') }}"
                />
                <x-input-error for="deviceName" class="mt-2" />
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Click "Save" and follow your browser\'s prompt to use Face ID, Touch ID, or another biometric method.') }}
                </p>
            </div>
        </x-slot>

        <x-slot name="actions">
            <x-action-message class="me-3" on="saved">
                {{ __('Saved.') }}
            </x-action-message>

            <x-button 
                type="button"
                wire:click="registerDevice"
                wire:loading.attr="disabled"
            >
                {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-form-section>

    <!-- Registered Devices -->
    @if($credentials->count() > 0)
        <x-section-border />
        
        <div class="mt-10 sm:mt-0">
            <x-action-section>
                <x-slot name="title">
                    {{ __('Registered Devices') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('These devices are registered for biometric authentication. You can remove them at any time.') }}
                </x-slot>

                <x-slot name="content">
                    <ul role="list" class="divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden bg-white dark:bg-gray-800 shadow-sm outline outline-1 outline-gray-900/5 dark:outline-gray-700/50 sm:rounded-xl">
                        @foreach($credentials as $credential)
                            <li class="relative flex justify-between gap-x-6 px-4 py-5 hover:bg-gray-50 dark:hover:bg-gray-700 sm:px-6">
                                <div class="flex min-w-0 gap-x-4">
                                    <div class="size-12 flex-none rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-auto">
                                        <div class="text-gray-900 dark:text-white">
                                            {{ $credential->alias ?? __('Unnamed Device') }}
                                        </div>
                                        <div class="text-gray-700 dark:text-gray-300 text-sm mt-1">
                                            {{ __('Registered') }}: {{ $credential->created_at->format('M j, Y') }}
                                            @if($credential->last_used_at)
                                                • {{ __('Last used') }}: {{ \Carbon\Carbon::parse($credential->last_used_at)->diffForHumans() }}
                                            @else
                                                • {{ __('Never used') }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex shrink-0 items-center gap-x-4">
                                    <button 
                                        wire:click="confirmCredentialDeletion('{{ $credential->id }}')"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center p-2.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors duration-200"
                                        title="{{ __('Remove device') }}"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </x-slot>
            </x-action-section>
        </div>
    @endif

    <!-- Delete Credential Confirmation Modal -->
    <x-confirmation-modal wire:model.live="confirmingCredentialDeletion">
    <x-slot name="title">
        {{ __('Remove Device') }}
    </x-slot>

    <x-slot name="content">
        <div class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('Are you sure you want to remove this device? You will no longer be able to sign in using this device\'s biometric authentication.') }}
        </div>
        
        @if($credentialBeingDeleted)
            @php
                $credential = Auth::user()->webAuthnCredentials()->find($credentialBeingDeleted);
            @endphp
            @if($credential)
                <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <div class="text-sm font-medium text-red-800 dark:text-red-200">
                        {{ __('Device') }}: {{ $credential->alias ?? __('Unnamed Device') }}
                    </div>
                    <div class="text-sm text-red-600 dark:text-red-300 mt-1">
                        {{ __('Registered') }}: {{ $credential->created_at->format('M j, Y') }}
                    </div>
                </div>
            @endif
        @endif
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="cancelCredentialDeletion" wire:loading.attr="disabled">
            {{ __('Cancel') }}
        </x-secondary-button>

        <x-danger-button class="ms-3" wire:click="deleteCredential" wire:loading.attr="disabled">
            {{ __('Remove Device') }}
        </x-danger-button>
    </x-slot>
    </x-confirmation-modal>
</div>

<script>
(function() {
    function registerWebAuthnListener() {
        Livewire.on('webauthn-register', async ([params]) => {
            const deviceName = params?.deviceName || '';
            
            try {
                // Check if WebAuthn is supported
                if (!window.PublicKeyCredential) {
                    @this.call('handleRegistrationError', '{{ __("Your browser does not support WebAuthn. Please use a modern browser.") }}');
                    return;
                }

                // Get registration options
                const optionsResponse = await fetch('{{ route("webauthn.register.options") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    credentials: 'same-origin'
                });

                if (!optionsResponse.ok) {
                    throw new Error('Failed to get registration options');
                }

                const options = await optionsResponse.json();

                // Parse the challenge
                const challenge = Uint8Array.from(atob(options.challenge.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));
                
                // Parse user id
                const userId = Uint8Array.from(atob(options.user.id.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));

                // Parse excludeCredentials if present (to prevent duplicate registrations)
                let excludeCredentials = null;
                if (options.excludeCredentials && Array.isArray(options.excludeCredentials)) {
                    excludeCredentials = options.excludeCredentials.map(cred => {
                        // Convert base64url ID to ArrayBuffer
                        const id = Uint8Array.from(atob(cred.id.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));
                        return {
                            ...cred,
                            id: id.buffer
                        };
                    });
                }

                // Create credential
                const publicKeyOptions = {
                    ...options,
                    challenge: challenge,
                    user: {
                        ...options.user,
                        id: userId
                    }
                };
                
                if (excludeCredentials) {
                    publicKeyOptions.excludeCredentials = excludeCredentials;
                }

                const credential = await navigator.credentials.create({
                    publicKey: publicKeyOptions
                });

                // Convert credential to base64
                const credentialId = btoa(String.fromCharCode(...new Uint8Array(credential.rawId)));
                const clientDataJSON = btoa(String.fromCharCode(...new Uint8Array(credential.response.clientDataJSON)));
                const attestationObject = btoa(String.fromCharCode(...new Uint8Array(credential.response.attestationObject)));

                // Send credential to server
                const registerResponse = await fetch('{{ route("webauthn.register") }}', {
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
                            attestationObject: attestationObject
                        },
                        alias: deviceName
                    })
                });

                if (!registerResponse.ok) {
                    const error = await registerResponse.json();
                    throw new Error(error.message || 'Registration failed');
                }

                // Registration successful - now dispatch saved event
                // This happens after the biometric prompt is dismissed
                @this.call('handleRegistrationSuccess');
                
            } catch (error) {
                console.error('WebAuthn registration error:', error);
                
                // Check for specific error types
                let errorMessage = error.message || '{{ __("Failed to register device. Please try again.") }}';
                
                // Handle duplicate credential errors
                if (error.name === 'InvalidStateError' || error.message.includes('excludeCredentials') || error.message.includes('already registered')) {
                    errorMessage = '{{ __("This device is already registered. Please remove the existing registration first if you want to register it again.") }}';
                } else if (error.name === 'NotAllowedError' || error.name === 'NotSupportedError') {
                    errorMessage = '{{ __("Biometric authentication was cancelled or not available. Please try again.") }}';
                } else if (error.message.includes('ArrayBuffer')) {
                    errorMessage = '{{ __("This device is already registered. Please remove the existing registration first if you want to register it again.") }}';
                }
                
                @this.call('handleRegistrationError', errorMessage);
            }
        });
    }

    // Register listener immediately if Livewire is available, otherwise wait for init
    if (window.Livewire) {
        registerWebAuthnListener();
    }
    
    // Also listen for init event as a fallback (in case Livewire loads after this script)
    document.addEventListener('livewire:init', registerWebAuthnListener);
})();
</script>

