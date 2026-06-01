<?php

use App\Http\Middleware\AuthenticateSession;
use App\Support\Features;
use Illuminate\Support\Str;

$entityLabel = 'company';
$entityUrlSlug = $entityLabel === 'strata' ? 'strata' : Str::plural(strtolower($entityLabel));
$appType = 'Management App';

$appName = Str::title($entityLabel).' '.$appType;

return [

    /*
    |--------------------------------------------------------------------------
    | Entity Label
    |--------------------------------------------------------------------------
    |
    | The label used throughout the UI to refer to teams/organizations.
    | Examples: "team", "strata", "company", "organization"
    |
    */

    'entity_label' => $entityLabel,

    /*
    |--------------------------------------------------------------------------
    | Entity URL Slug
    |--------------------------------------------------------------------------
    |
    | The URL segment used for entity routes (e.g. /households/, /teams/).
    | Defaults to the plural form of entity_label. Override for irregular
    | plurals (e.g. strata stays "strata" in URLs).
    |
    */

    'entity_url_slug' => $entityUrlSlug,

    /*
    |--------------------------------------------------------------------------
    | Application Type
    |--------------------------------------------------------------------------
    |
    | The type or description of the application platform. This is combined
    | with the entity_label to create the full application name.
    | Examples: "Management App", "Platform", "System"
    |
    */

    'app_type' => $appType,

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | The full application name used throughout the application. This is
    | dynamically generated from the entity_label and app_type.
    |
    */

    'app_name' => $appName,

    /*
    |--------------------------------------------------------------------------
    | Mail From Address
    |--------------------------------------------------------------------------
    |
    | The email address used as the "from" address for all application emails.
    | This is dynamically generated from the app_name by default.
    |
    */

    'mail_from_address' => 'donotreply@'.Str::snake($appName),

    /*
    |--------------------------------------------------------------------------
    | Mail From Name
    |--------------------------------------------------------------------------
    |
    | The name used as the "from" name for all application emails.
    | This uses the app_name by default.
    |
    */

    'mail_from_name' => $appName,

    /*
    |--------------------------------------------------------------------------
    | Authentication Session Middleware
    |--------------------------------------------------------------------------
    |
    | The middleware class used for authenticating sessions. This is used
    | when setting up route middleware groups.
    |
    */

    'auth_session' => AuthenticateSession::class,

    /*
    |--------------------------------------------------------------------------
    | Afterburner Guard
    |--------------------------------------------------------------------------
    |
    | Here you may specify the authentication guard Afterburner will use while
    | authenticating users. This value should correspond with one of your
    | guards that is already present in your "auth" configuration file.
    |
    */

    'guard' => 'sanctum',

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features. These can also be toggled at runtime
    | via the feature_flags table if the FeatureFlag model is used.
    |
    | Note: Email verification is managed here for unified feature flag control.
    | Other Laravel Fortify features (registration, password reset, etc.) are
    | configured in config/fortify.php.
    |
    */

    'features' => [
        // Teams & Collaboration
        Features::teams(),
        Features::personalTeams(),
        Features::teamTimezone(),
        Features::teamDeletion(),

        // Authentication & Security
        Features::emailVerification(),
        Features::twoFactorAuthentication(),
        Features::biometric(),

        // User Profile & Account
        Features::profilePhotos(),
        Features::userTimezone(),
        Features::accountDeletion(),

        // API & Integration
        Features::api(),

        // Legal & Compliance
        Features::termsAndPrivacyPolicy(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Options
    |--------------------------------------------------------------------------
    |
    | Configure options for specific features. These options control
    | feature behavior beyond simple enable/disable.
    |
    */

    'options' => [
        'two_factor_authentication' => [
            'confirm' => true,
            'confirmPassword' => true,
            // 'window' => 0, // Time window for 2FA codes (optional)
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Profile Photo Disk
    |--------------------------------------------------------------------------
    |
    | This configuration value determines the default disk that will be used
    | when storing profile photos for your application's users. Typically
    | this will be the "public" disk but you may adjust this if needed.
    |
    */

    'profile_photo_disk' => 'public',

    /*
    |--------------------------------------------------------------------------
    | Profile Photo Resize
    |--------------------------------------------------------------------------
    |
    | Images are resized with GD before storage. Uploaded files may be larger
    | than these dimensions; output is always JPEG at the configured quality.
    |
    */

    'profile_photo' => [
        'max_width' => 512,
        'max_height' => 512,
        'quality' => 85,
    ],

    /*
    |--------------------------------------------------------------------------
    | Team Logo Resize
    |--------------------------------------------------------------------------
    */

    'team_logo' => [
        'max_width' => 800,
        'max_height' => 800,
        'quality' => 85,
    ],

    /*
    |--------------------------------------------------------------------------
    | Allow Team Creation
    |--------------------------------------------------------------------------
    |
    | When false, users cannot register or create new organizations. Existing users
    | invited by email are added to that organization automatically. New users still
    | register via invitation links. Users without an organization can access profile,
    | security, and notifications without being prompted to create one.
    |
    */

    'allow_team_creation' => true,

];
