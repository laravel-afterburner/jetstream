<?php

namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ResizeImageHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }
    }

    public function test_resize_profile_photo_scales_large_images(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 2000, 1500);

        $result = resize_profile_photo($file);

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['data']);

        $image = imagecreatefromstring($result['data']);
        $this->assertNotFalse($image);
        $this->assertLessThanOrEqual(512, imagesx($image));
        $this->assertLessThanOrEqual(512, imagesy($image));
        imagedestroy($image);
    }

    public function test_resize_image_rejects_unsupported_format(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $result = resize_image($file);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }
}
