<?php

namespace Tests\Feature;

use App\Support\AfterburnerPublishedViews;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AfterburnerIntegrationAuditTest extends TestCase
{
    public function test_audit_passes_without_bulk_published_views(): void
    {
        $this->artisan('afterburner:audit-integration')
            ->expectsOutput('Afterburner integration audit passed.')
            ->assertExitCode(0);
    }

    public function test_audit_fails_when_unchanged_published_views_exist(): void
    {
        $namespace = 'afterburner-meetings';
        $packagePath = AfterburnerPublishedViews::packageViewsPath($namespace);
        $publishedPath = resource_path('views/vendor/'.$namespace);

        $this->assertNotNull($packagePath);

        $source = collect(File::allFiles($packagePath))
            ->first(fn ($file) => $file->getExtension() === 'php');

        $this->assertNotNull($source);

        $relativePath = $source->getRelativePathname();
        $target = $publishedPath.'/'.$relativePath;

        File::ensureDirectoryExists(dirname($target));
        File::copy($source->getPathname(), $target);

        try {
            $this->artisan('afterburner:audit-integration')
                ->expectsOutput('Afterburner integration audit failed:')
                ->assertExitCode(1);
        } finally {
            File::delete($target);

            if (File::isDirectory($publishedPath) && File::isEmptyDirectory($publishedPath)) {
                File::deleteDirectory($publishedPath);
            }
        }
    }
}
