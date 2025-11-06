<?php

namespace App\Http\Controllers\WebAuthn;

use App\Support\Features as AfterburnerFeatures;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;
use Laravel\Fortify\Contracts\LoginResponse;

use function response;

class WebAuthnLoginController
{
    /**
     * Returns the challenge to assertion.
     */
    public function options(AssertionRequest $request): Responsable
    {
        if (!AfterburnerFeatures::hasBiometricFeatures()) {
            abort(404);
        }

        return $request->toVerify($request->validate(['email' => 'sometimes|email|string']));
    }

    /**
     * Log the user in.
     */
    public function login(AssertedRequest $request): JsonResponse|RedirectResponse
    {
        if (!AfterburnerFeatures::hasBiometricFeatures()) {
            abort(404);
        }

        if (!$request->login()) {
            return response()->json(['message' => 'Authentication failed.'], 422);
        }

        // If the request expects JSON, return JSON response
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Authentication successful.']);
        }

        // Otherwise, redirect to dashboard
        return app(LoginResponse::class);
    }
}
