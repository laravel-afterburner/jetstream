<?php

namespace Tests\Feature;

use App\Livewire\Profile\UpdateProfileInformationForm;
use App\Models\User;
use App\Support\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfilePhotoResizeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        if (! Features::managesProfilePhotos()) {
            $this->markTestSkipped('Profile photos feature not enabled.');
        }
    }

    public function test_profile_photo_is_resized_before_storage(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(UpdateProfileInformationForm::class)
            ->set('state', ['name' => $user->name, 'email' => $user->email])
            ->set('photo', UploadedFile::fake()->image('avatar.jpg', 1200, 1200))
            ->call('updateProfileInformation');

        $user->refresh();
        $this->assertNotNull($user->profile_photo_path);
        $this->assertStringEndsWith('.jpg', $user->profile_photo_path);

        $stored = Storage::disk('public')->get($user->profile_photo_path);
        $image = imagecreatefromstring($stored);
        $this->assertNotFalse($image);
        $this->assertLessThanOrEqual(512, imagesx($image));
        $this->assertLessThanOrEqual(512, imagesy($image));
        imagedestroy($image);
    }
}
