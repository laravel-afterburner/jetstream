<?php

use App\Support\Features;
use App\Http\Middleware\AuthenticateSession;
use Illuminate\Support\Str;

$entityLabel = env('AFTERBURNER_ENTITY_LABEL', 'company');
$appType = env('AFTERBURNER_APP_TYPE', 'Management App');
$appName = Str::title($entityLabel) . ' ' . $appType;

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

    'mail_from_address' => 'donotreply@' . Str::snake($appName),

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

    'guard' => env('AFTERBURNER_GUARD', 'sanctum'),

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
        Features::teamAnnouncements(),
        Features::teamTimezone(),

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

    'profile_photo_disk' => env('AFTERBURNER_PROFILE_PHOTO_DISK', 'public'),

];

