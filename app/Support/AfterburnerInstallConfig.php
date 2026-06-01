<?php

namespace App\Support;

use Illuminate\Support\Str;
use RuntimeException;

class AfterburnerInstallConfig
{
    /**
     * @var list<string>
     */
    public const ENTITY_LABELS = ['team', 'strata', 'company', 'organization'];

    public static function configPath(): string
    {
        return config_path('afterburner.php');
    }

    public static function setEntityLabel(string $entityLabel): void
    {
        if (! in_array($entityLabel, self::ENTITY_LABELS, true)) {
            throw new RuntimeException("Invalid entity label: {$entityLabel}");
        }

        $path = self::configPath();

        if (! is_file($path)) {
            throw new RuntimeException("Afterburner config not found at {$path}");
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException("Unable to read Afterburner config at {$path}");
        }

        if (! preg_match('/\$entityLabel\s*=\s*\'[^\']*\';/', $content)) {
            throw new RuntimeException('Could not find $entityLabel assignment in afterburner config.');
        }

        $slug = $entityLabel === 'strata'
            ? 'strata'
            : Str::plural(strtolower($entityLabel));

        $content = preg_replace(
            '/\$entityLabel\s*=\s*\'[^\']*\';/',
            "\$entityLabel = '{$entityLabel}';",
            $content,
            1
        );

        if (! is_string($content)) {
            throw new RuntimeException('Failed to update entity label in afterburner config.');
        }

        if (preg_match('/\$entityUrlSlug\s*=\s*[^;]+;/', $content)) {
            $content = preg_replace(
                '/\$entityUrlSlug\s*=\s*[^;]+;/',
                "\$entityUrlSlug = '{$slug}';",
                $content,
                1
            );
        }

        if (file_put_contents($path, $content) === false) {
            throw new RuntimeException("Unable to write Afterburner config at {$path}");
        }
    }
}
