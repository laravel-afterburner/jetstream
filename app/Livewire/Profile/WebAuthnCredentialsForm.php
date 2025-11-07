<?php

namespace App\Livewire\Profile;

use App\Support\Features as AfterburnerFeatures;
use App\Traits\InteractsWithBanner;
use Illuminate\Support\Facades\Auth;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Livewire\Component;

class WebAuthnCredentialsForm extends Component
{
    use InteractsWithBanner;

    /**
     * The device name for registration.
     *
     * @var string
     */
    public $deviceName = '';

    /**
     * Indicates if the registration process is in progress.
     *
     * @var bool
     */
    public $registering = false;

    /**
     * The credential being deleted.
     *
     * @var string|null
     */
    public $credentialBeingDeleted = null;

    /**
     * Indicates if the deletion is being confirmed.
     *
     * @var bool
     */
    public $confirmingCredentialDeletion = false;

    /**
     * Mount the component.
     */
    public function mount()
    {
        // Check if biometric feature is enabled
        if (!AfterburnerFeatures::hasBiometricFeatures()) {
            abort(404);
        }
    }

    /**
     * Get a default device name based on user agent.
     */
    protected function getDefaultDeviceName(): string
    {
        $userAgent = request()->userAgent();
        
        if (str_contains($userAgent, 'iPhone')) {
            return 'iPhone';
        } elseif (str_contains($userAgent, 'iPad')) {
            return 'iPad';
        } elseif (str_contains($userAgent, 'Mac')) {
            return 'MacBook';
        } elseif (str_contains($userAgent, 'Windows')) {
            return 'Windows PC';
        } elseif (str_contains($userAgent, 'Android')) {
            return 'Android Device';
        } elseif (str_contains($userAgent, 'Linux')) {
            return 'Linux Device';
        }

        return 'Device';
    }

    /**
     * Register a new WebAuthn credential.
     */
    public function registerDevice()
    {
        // Check if biometric feature is enabled
        if (!AfterburnerFeatures::hasBiometricFeatures()) {
            abort(404);
        }

        $this->resetErrorBag();

        $validated = $this->validate([
            'deviceName' => ['required', 'string', 'max:255'],
        ]);

        $this->registering = true;

        $this->dispatch('webauthn-register', [
            'deviceName' => $validated['deviceName'],
        ]);
    }

    /**
     * Handle successful registration.
     */
    public function handleRegistrationSuccess()
    {
        $this->registering = false;
        $this->deviceName = ''; // Clear the input after successful registration
        
        $this->dispatch('saved');
    }

    /**
     * Handle registration error.
     */
    public function handleRegistrationError($message = null)
    {
        $this->registering = false;
        
        $errorMessage = $message ?? __('Failed to register device. Please try again.');
        
        $this->addError('deviceName', $errorMessage);
    }

    /**
     * Confirm that the user would like to delete the credential.
     */
    public function confirmCredentialDeletion($credentialId)
    {
        $this->resetErrorBag();

        $this->credentialBeingDeleted = $credentialId;

        $this->confirmingCredentialDeletion = true;
    }

    /**
     * Cancel the credential deletion.
     */
    public function cancelCredentialDeletion()
    {
        $this->credentialBeingDeleted = null;
        $this->confirmingCredentialDeletion = false;
    }

    /**
     * Delete a WebAuthn credential.
     */
    public function deleteCredential()
    {
        // Check if biometric feature is enabled
        if (!AfterburnerFeatures::hasBiometricFeatures()) {
            abort(404);
        }

        if (!$this->credentialBeingDeleted) {
            return;
        }

        $credential = Auth::user()->webAuthnCredentials()->find($this->credentialBeingDeleted);

        if (!$credential) {
            session()->flash('flash', [
                'bannerStyle' => 'danger',
                'banner' => __('Credential not found.'),
            ]);
            $this->cancelCredentialDeletion();
            return;
        }

        $credential->delete();

        $this->cancelCredentialDeletion();

        session()->flash('flash', [
            'bannerStyle' => 'success',
            'banner' => __('Device removed successfully.'),
        ]);

        return redirect()->to(request()->header('Referer'));
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('security.webauthn-credentials-form', [
            'credentials' => Auth::user()->webAuthnCredentials()->orderBy('created_at', 'desc')->get(),
        ]);
    }
}


