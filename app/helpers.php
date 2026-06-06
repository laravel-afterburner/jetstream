<?php

use App\Support\EntityLabel;
use Illuminate\Http\UploadedFile;

if (! function_exists('entity_label')) {
    function entity_label(): string
    {
        return EntityLabel::singular();
    }
}

if (! function_exists('entity_title')) {
    function entity_title(): string
    {
        return EntityLabel::singularTitle();
    }
}

if (! function_exists('entity_plural')) {
    function entity_plural(): string
    {
        return EntityLabel::plural();
    }
}

if (! function_exists('entity_plural_title')) {
    function entity_plural_title(): string
    {
        return EntityLabel::pluralTitle();
    }
}

if (! function_exists('entity_url_slug')) {
    function entity_url_slug(): string
    {
        return EntityLabel::urlSlug();
    }
}

if (! function_exists('entity_path')) {
    /**
     * Build an entity-scoped URL path (leading slash, includes entity_url_slug).
     */
    function entity_path(string $path = ''): string
    {
        $slug = entity_url_slug();
        $path = ltrim($path, '/');

        return $path === '' ? '/'.$slug : '/'.$slug.'/'.$path;
    }
}

if (! function_exists('resize_image')) {
    /**
     * Resize an uploaded image to fit within max dimensions using PHP GD.
     *
     * @return array{success: bool, data?: string, error?: string, details?: array<string, mixed>}
     */
    function resize_image(UploadedFile $file, int $maxWidth = 512, int $maxHeight = 512, int $quality = 85): array
    {
        $path = $file->getRealPath();
        $mime = $file->getMimeType();
        $filename = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $details = [
            'filename' => $filename,
            'mime_type' => $mime,
            'file_size_bytes' => $fileSize,
            'file_size_human' => number_format($fileSize / 1024, 1).' KB',
        ];

        $supportedMimes = ['jpeg', 'jpg', 'png', 'webp', 'gif'];
        $isSupported = collect($supportedMimes)->contains(fn ($m) => str_contains($mime, $m));

        if (! $isSupported) {
            return [
                'success' => false,
                'error' => __('The image format is not supported. Please use JPG, PNG, WebP, or GIF.'),
                'details' => array_merge($details, [
                    'reason' => 'unsupported_format',
                    'supported_formats' => ['JPEG', 'PNG', 'WebP', 'GIF'],
                ]),
            ];
        }

        $image = match (true) {
            str_contains($mime, 'jpeg') || str_contains($mime, 'jpg') => @imagecreatefromjpeg($path),
            str_contains($mime, 'png') => @imagecreatefrompng($path),
            str_contains($mime, 'webp') => @imagecreatefromwebp($path),
            str_contains($mime, 'gif') => @imagecreatefromgif($path),
            default => null,
        };

        if (! $image) {
            return [
                'success' => false,
                'error' => __('The image could not be processed. Try saving it as a new JPG or PNG file, or use a different image.'),
                'details' => array_merge($details, [
                    'reason' => 'gd_decode_failed',
                ]),
            ];
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $details['original_dimensions'] = ['width' => $width, 'height' => $height];

        if ($width <= $maxWidth && $height <= $maxHeight) {
            $newWidth = $width;
            $newHeight = $height;
        } else {
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int) round($width * $ratio);
            $newHeight = (int) round($height * $ratio);
        }

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        if (! $resized) {
            imagedestroy($image);

            return [
                'success' => false,
                'error' => __('The image is too large to process. Try using a smaller image or reduce its dimensions before uploading.'),
                'details' => array_merge($details, [
                    'reason' => 'gd_createtruecolor_failed',
                ]),
            ];
        }

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        ob_start();
        $jpegSuccess = imagejpeg($resized, null, $quality);
        $output = ob_get_clean();
        imagedestroy($resized);

        if (! $jpegSuccess || ! $output) {
            return [
                'success' => false,
                'error' => __('The image could not be processed. Try saving it as a new JPG file.'),
                'details' => array_merge($details, [
                    'reason' => 'gd_encode_failed',
                ]),
            ];
        }

        return [
            'success' => true,
            'data' => $output,
        ];
    }
}

if (! function_exists('resize_profile_photo')) {
    /**
     * Resize an uploaded image for profile photo display (512×512 max by default).
     *
     * @return array{success: bool, data?: string, error?: string, details?: array<string, mixed>}
     */
    function resize_profile_photo(UploadedFile $file, ?int $maxWidth = null, ?int $maxHeight = null, ?int $quality = null): array
    {
        $config = config('afterburner.profile_photo', []);

        return resize_image(
            $file,
            $maxWidth ?? ($config['max_width'] ?? 512),
            $maxHeight ?? ($config['max_height'] ?? 512),
            $quality ?? ($config['quality'] ?? 85),
        );
    }
}

if (! function_exists('resize_team_logo')) {
    /**
     * Resize an uploaded image for team logo display (800×800 max by default).
     *
     * @return array{success: bool, data?: string, error?: string, details?: array<string, mixed>}
     */
    function resize_team_logo(UploadedFile $file, ?int $maxWidth = null, ?int $maxHeight = null, ?int $quality = null): array
    {
        $config = config('afterburner.team_logo', []);

        return resize_image(
            $file,
            $maxWidth ?? ($config['max_width'] ?? 800),
            $maxHeight ?? ($config['max_height'] ?? 800),
            $quality ?? ($config['quality'] ?? 85),
        );
    }
}

if (! function_exists('public_storage_url')) {
    /**
     * Root-relative URL for files on the public disk.
     */
    function public_storage_url(string $path): string
    {
        return '/storage/'.ltrim($path, '/');
    }
}

if (! function_exists('team_mail_message')) {
    /**
     * Create a notification mail message with optional team logo branding.
     */
    function team_mail_message(?object $team = null): \Illuminate\Notifications\Messages\MailMessage
    {
        $class = config('afterburner.mail_message', \Illuminate\Notifications\Messages\MailMessage::class);

        /** @var \Illuminate\Notifications\Messages\MailMessage $message */
        $message = new $class;

        if ($team !== null && method_exists($message, 'forTeam')) {
            $message->forTeam($team);
        }

        return $message;
    }
}

if (! function_exists('format_date_superscript')) {
    /**
     * Format a date with the ordinal suffix in a <sup> tag for HTML display.
     * Returns HTML — use with {!! !!} in Blade. Do not use in plain-text contexts.
     *
     * @param  \Carbon\Carbon|\DateTimeInterface|null  $date
     */
    function format_date_superscript($date, string $format = 'date'): string
    {
        if (! $date) {
            return '';
        }

        $carbon = $date instanceof \Carbon\Carbon
            ? $date
            : \Carbon\Carbon::parse($date);

        $base = $carbon->format('F j').'<sup>'.$carbon->format('S').'</sup>';

        return match ($format) {
            'date_no_year' => $base,
            'datetime_seconds' => $base.' '.$carbon->format('Y').' '.$carbon->format('g:i:s A'),
            'datetime' => $base.' '.$carbon->format('Y').' '.$carbon->format('g:i A'),
            default => $base.' '.$carbon->format('Y'),
        };
    }
}
