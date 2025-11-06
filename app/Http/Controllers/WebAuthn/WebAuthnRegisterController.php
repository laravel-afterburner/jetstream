<?php

namespace App\Http\Controllers\WebAuthn;

use App\Support\Features as AfterburnerFeatures;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;

use function response;

class WebAuthnRegisterController
{
    /**
     * Returns a challenge to be verified by the user device.
     */
    public function options(AttestationRequest $request): Responsable
    {
        if (!AfterburnerFeatures::hasBiometricFeatures()) {
            abort(404);
        }

        return $request
            ->fastRegistration()
//            ->userless()
//            ->allowDuplicates()
            ->toCreate();
    }

    /**
     * Registers a device for further WebAuthn authentication.
     */
    public function register(AttestedRequest $request): JsonResponse|Response
    {
        if (!AfterburnerFeatures::hasBiometricFeatures()) {
            abort(404);
        }

        $validated = $request->validate([
            'alias' => 'sometimes|string|max:255',
        ]);

        // Save credential with alias if provided
        // save() accepts an array of attributes to set on the credential
        $credentialId = $request->save(isset($validated['alias']) ? ['alias' => $validated['alias']] : []);

        if ($request->expectsJson()) {
            // Load the credential to get the alias
            $credential = \Laragear\WebAuthn\Models\WebAuthnCredential::find($credentialId);
            
            return response()->json([
                'message' => 'Device registered successfully.',
                'credential' => [
                    'id' => $credentialId,
                    'name' => $credential->alias ?? $validated['alias'] ?? null,
                ],
            ]);
        }

        return response()->noContent();
    }
}
