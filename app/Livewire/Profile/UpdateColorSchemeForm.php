<?php

namespace App\Livewire\Profile;

use App\Support\ColorScheme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class UpdateColorSchemeForm extends Component
{
    public string $colorScheme = ColorScheme::System;

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $this->colorScheme = ColorScheme::forUser($user);
    }

    public function updateColorScheme(): void
    {
        $this->resetErrorBag();

        Validator::make(
            ['colorScheme' => $this->colorScheme],
            ['colorScheme' => ['required', 'in:'.implode(',', [ColorScheme::Light, ColorScheme::Dark, ColorScheme::System])]],
        )->validateWithBag('updateColorScheme');

        $user = Auth::user();
        $user->forceFill([
            'color_scheme' => $this->colorScheme,
        ])->save();

        $this->dispatch('color-scheme-updated', scheme: $this->colorScheme);
        $this->dispatch('saved');
    }

    public function render()
    {
        return view('profile.update-color-scheme-form', [
            'options' => ColorScheme::options(),
        ]);
    }
}
