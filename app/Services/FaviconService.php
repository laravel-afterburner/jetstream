<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Imagick;
use ImagickException;

class FaviconService
{
    /**
     * Generate all favicon types from a logo image.
     *
     * @param string $logoPath Path to the logo file (relative to public disk)
     * @param int $teamId Team ID for storage path
     * @return bool Success status
     */
    public function generateFromLogo(string $logoPath, int $teamId): bool
    {
        try {
            $disk = Storage::disk('public');
            $fullLogoPath = $disk->path($logoPath);
            
            if (!file_exists($fullLogoPath)) {
                return false;
            }

            // Ensure directory exists
            $basePath = "teams/{$teamId}";
            $disk->makeDirectory($basePath);

            // Load the original image
            $original = new Imagick($fullLogoPath);
            
            // Generate all favicon sizes
            $this->generateFaviconIco($original, $basePath, $disk);
            $this->generateFaviconPng($original, $basePath, $disk, 16, 'favicon-16x16.png');
            $this->generateFaviconPng($original, $basePath, $disk, 32, 'favicon-32x32.png');
            $this->generateFaviconPng($original, $basePath, $disk, 180, 'apple-touch-icon.png');
            $this->generateFaviconPng($original, $basePath, $disk, 192, 'android-chrome-192x192.png');
            $this->generateFaviconPng($original, $basePath, $disk, 512, 'android-chrome-512x512.png');
            
            // Generate web manifest
            $this->generateWebManifest($basePath, $disk, $teamId);
            
            // Clean up
            $original->clear();
            $original->destroy();
            
            return true;
        } catch (ImagickException $e) {
            Log::error('Failed to generate favicons: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate favicon.ico file.
     *
     * @param Imagick $original Original image
     * @param string $basePath Base storage path
     * @param \Illuminate\Contracts\Filesystem\Filesystem $disk Storage disk
     * @return void
     */
    protected function generateFaviconIco(Imagick $original, string $basePath, $disk): void
    {
        $favicon = clone $original;
        $favicon->resizeImage(32, 32, Imagick::FILTER_LANCZOS, 1, true);
        $favicon->setImageFormat('ico');
        $favicon->setImageBackgroundColor(new \ImagickPixel('transparent'));
        
        $faviconPath = "{$basePath}/favicon.ico";
        $favicon->writeImage($disk->path($faviconPath));
        
        $favicon->clear();
        $favicon->destroy();
    }

    /**
     * Generate PNG favicon at specific size.
     *
     * @param Imagick $original Original image
     * @param string $basePath Base storage path
     * @param \Illuminate\Contracts\Filesystem\Filesystem $disk Storage disk
     * @param int $size Size in pixels
     * @param string $filename Output filename
     * @return void
     */
    protected function generateFaviconPng(Imagick $original, string $basePath, $disk, int $size, string $filename): void
    {
        $icon = clone $original;
        $icon->resizeImage($size, $size, Imagick::FILTER_LANCZOS, 1, true);
        $icon->setImageFormat('png');
        
        // Ensure transparency is preserved
        $icon->setImageBackgroundColor(new \ImagickPixel('transparent'));
        
        $iconPath = "{$basePath}/{$filename}";
        $icon->writeImage($disk->path($iconPath));
        
        $icon->clear();
        $icon->destroy();
    }

    /**
     * Generate web manifest file for the team.
     *
     * @param string $basePath Base storage path
     * @param \Illuminate\Contracts\Filesystem\Filesystem $disk Storage disk
     * @param int $teamId Team ID
     * @return void
     */
    protected function generateWebManifest(string $basePath, $disk, int $teamId): void
    {
        $manifestPath = "{$basePath}/site.webmanifest";
        $baseUrl = rtrim(config('app.url'), '/');
        $storageUrl = $disk->url($basePath);
        
        // Ensure storage URL is fully qualified
        if (!filter_var($storageUrl, FILTER_VALIDATE_URL)) {
            $storageUrl = $baseUrl . '/' . ltrim($storageUrl, '/');
        }
        
        $manifest = [
            'name' => '',
            'short_name' => '',
            'icons' => [
                [
                    'src' => $storageUrl . '/android-chrome-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => $storageUrl . '/android-chrome-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
            'theme_color' => '#ffffff',
            'background_color' => '#ffffff',
            'display' => 'standalone',
        ];
        
        $disk->put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Delete all team favicon files.
     *
     * @param int $teamId Team ID
     * @return void
     */
    public function deleteFavicon(int $teamId): void
    {
        $basePath = "teams/{$teamId}";
        $disk = Storage::disk('public');
        
        $files = [
            "{$basePath}/favicon.ico",
            "{$basePath}/favicon-16x16.png",
            "{$basePath}/favicon-32x32.png",
            "{$basePath}/apple-touch-icon.png",
            "{$basePath}/android-chrome-192x192.png",
            "{$basePath}/android-chrome-512x512.png",
            "{$basePath}/site.webmanifest",
        ];
        
        foreach ($files as $file) {
            $disk->delete($file);
        }
    }

    /**
     * Get the favicon URL for a team (favicon.ico).
     *
     * @param int $teamId Team ID
     * @return string URL to favicon (falls back to default)
     */
    public function getFaviconUrl(int $teamId): string
    {
        return $this->getFaviconFileUrl($teamId, 'favicon.ico', 'favicon.ico');
    }

    /**
     * Get the favicon 16x16 PNG URL for a team.
     *
     * @param int $teamId Team ID
     * @return string URL to favicon (falls back to default)
     */
    public function getFavicon16Url(int $teamId): string
    {
        return $this->getFaviconFileUrl($teamId, 'favicon-16x16.png', 'favicon-16x16.png');
    }

    /**
     * Get the favicon 32x32 PNG URL for a team.
     *
     * @param int $teamId Team ID
     * @return string URL to favicon (falls back to default)
     */
    public function getFavicon32Url(int $teamId): string
    {
        return $this->getFaviconFileUrl($teamId, 'favicon-32x32.png', 'favicon-32x32.png');
    }

    /**
     * Get the Apple Touch Icon URL for a team.
     *
     * @param int $teamId Team ID
     * @return string URL to favicon (falls back to default)
     */
    public function getAppleTouchIconUrl(int $teamId): string
    {
        return $this->getFaviconFileUrl($teamId, 'apple-touch-icon.png', 'apple-touch-icon.png');
    }

    /**
     * Get the Android Chrome 192x192 icon URL for a team.
     *
     * @param int $teamId Team ID
     * @return string URL to favicon (falls back to default)
     */
    public function getAndroidChrome192Url(int $teamId): string
    {
        return $this->getFaviconFileUrl($teamId, 'android-chrome-192x192.png', 'android-chrome-192x192.png');
    }

    /**
     * Get the Android Chrome 512x512 icon URL for a team.
     *
     * @param int $teamId Team ID
     * @return string URL to favicon (falls back to default)
     */
    public function getAndroidChrome512Url(int $teamId): string
    {
        return $this->getFaviconFileUrl($teamId, 'android-chrome-512x512.png', 'android-chrome-512x512.png');
    }

    /**
     * Get the web manifest URL for a team.
     *
     * @param int $teamId Team ID
     * @return string URL to manifest (falls back to default)
     */
    public function getWebManifestUrl(int $teamId): string
    {
        return $this->getFaviconFileUrl($teamId, 'site.webmanifest', 'site.webmanifest');
    }

    /**
     * Get a favicon file URL for a team.
     *
     * @param int $teamId Team ID
     * @param string $teamFilename Filename in team directory
     * @param string $defaultFilename Default filename in public directory
     * @return string URL to favicon file
     */
    protected function getFaviconFileUrl(int $teamId, string $teamFilename, string $defaultFilename): string
    {
        $faviconPath = "teams/{$teamId}/{$teamFilename}";
        $disk = Storage::disk('public');
        
        if ($disk->exists($faviconPath)) {
            return $this->versionedUrl($disk->url($faviconPath), $disk, $faviconPath);
        }

        return $this->versionedUrl(asset($defaultFilename), null, public_path($defaultFilename));
    }

    /**
     * Append a cache-busting query string so browsers and CDNs fetch updated favicons
     * after logo changes (favicon paths stay stable under teams/{id}/).
     */
    protected function versionedUrl(string $url, $disk = null, ?string $path = null): string
    {
        $version = null;

        if ($disk !== null && $path !== null) {
            try {
                $version = $disk->lastModified($path);
            } catch (\Throwable) {
                $version = null;
            }
        } elseif ($path !== null && is_string($path) && file_exists($path)) {
            $version = filemtime($path) ?: null;
        }

        if ($version === null) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.'v='.$version;
    }
}
