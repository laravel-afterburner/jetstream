<?php

namespace App\Support;

class AfterburnerPublishedViews
{
    /**
     * @var list<string>
     */
    public const PACKAGE_NAMESPACES = [
        'afterburner-meetings',
        'afterburner-voting',
        'afterburner-documents',
        'afterburner-communications',
        'afterburner-subscriptions',
        'afterburner-playbook',
    ];

    /**
     * @return list<string>
     */
    public static function customizedNamespaces(): array
    {
        return array_values(array_filter(
            self::PACKAGE_NAMESPACES,
            fn (string $namespace) => self::hasCustomizedViews(resource_path('views/vendor/'.$namespace)),
        ));
    }

    public static function hasCustomizedViews(string $path): bool
    {
        if (! is_dir($path)) {
            return false;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                return true;
            }
        }

        return false;
    }

    public static function packageViewsPath(string $namespace): ?string
    {
        if (! in_array($namespace, self::PACKAGE_NAMESPACES, true)) {
            return null;
        }

        $package = str_replace('afterburner-', '', $namespace);
        $path = base_path('vendor/laravel-afterburner/'.$package.'/resources/views');

        return is_dir($path) ? $path : null;
    }

    /**
     * Published files that are byte-identical to the package at the same relative path.
     *
     * @return list<string>
     */
    public static function unchangedPublishedFiles(string $namespace): array
    {
        $publishedPath = resource_path('views/vendor/'.$namespace);
        $packagePath = self::packageViewsPath($namespace);

        if ($packagePath === null || ! is_dir($publishedPath)) {
            return [];
        }

        $unchanged = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($publishedPath, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = substr($file->getPathname(), strlen($publishedPath) + 1);
            $packageFile = $packagePath.'/'.$relativePath;

            if (! is_file($packageFile)) {
                continue;
            }

            if (md5_file($file->getPathname()) === md5_file($packageFile)) {
                $unchanged[] = $relativePath;
            }
        }

        sort($unchanged);

        return $unchanged;
    }
}
