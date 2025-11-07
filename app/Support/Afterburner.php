<?php

namespace App\Support;

use App\Traits\HasTeams;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Afterburner
{
    /**
     * Indicates if Afterburner routes will be registered.
     *
     * @var bool
     */
    public static $registersRoutes = true;

    /**
     * The default permissions that should be available to new entities.
     *
     * @var array
     */
    public static $defaultPermissions = [];

    /**
     * The permissions that exist within the application (for API tokens).
     * Note: API features are disabled, so this is empty.
     * 
     * @see Large comment block above for API token system notes
     *
     * @var array
     */
    public static $permissions = [];

    /**
     * The user model that should be used by Afterburner.
     *
     * @var string
     */
    public static $userModel = 'App\\Models\\User';

    /**
     * The team model that should be used by Afterburner.
     *
     * @var string
     */
    public static $teamModel = 'App\\Models\\Team';

    /**
     * The membership model that should be used by Afterburner.
     *
     * @var string
     */
    public static $membershipModel = 'App\\Models\\Membership';

    /**
     * The team invitation model that should be used by Afterburner.
     *
     * @var string
     */
    public static $teamInvitationModel = 'App\\Models\\TeamInvitation';

    /**
     * ============================================================================
     * API TOKEN PERMISSIONS SYSTEM - CURRENTLY DISABLED
     * ============================================================================
     * 
     * NOTE FOR FUTURE: If you enable API features (uncomment Features::api() in
     * config/afterburner.php), these methods and properties are currently STUBS
     * that need proper implementation:
     * 
     * - hasPermissions() - currently always returns false
     * - permissions() - currently a no-op (does nothing)
     * - validPermissions() - currently always returns empty array
     * - $permissions - currently an empty array
     * 
     * These are referenced in:
     * - app/Livewire/Api/ApiTokenManager.php (lines 100, 149)
     * - resources/views/api/api-token-manager.blade.php (lines 21, 26, 78, 129)
     * 
     * To properly enable API tokens, you'll need to:
     * 1. Implement a permissions system (or use Laravel Sanctum's abilities)
     * 2. Update hasPermissions() to check if permissions are registered
     * 3. Update permissions() to store the permission list
     * 4. Update validPermissions() to filter valid permissions
     * 5. Populate $permissions with your actual permission list
     * ============================================================================
     */

    /**
     * Define the default permissions that should be available to new API tokens.
     *
     * @param  array  $permissions
     * @return static
     */
    public static function defaultApiTokenPermissions(array $permissions)
    {
        static::$defaultPermissions = $permissions;

        return new static;
    }

    /**
     * Determine if any permissions have been registered with Afterburner.
     * Note: API features are disabled, so this always returns false.
     * 
     * @see NOTE above - this is a stub that needs implementation if API is enabled
     *
     * @return bool
     */
    public static function hasPermissions()
    {
        return false;
    }

    /**
     * Define the available API token permissions.
     * Note: API features are disabled, so this method is a no-op.
     * 
     * @see NOTE above - this is a stub that needs implementation if API is enabled
     *
     * @param  array  $permissions
     * @return static
     */
    public static function permissions(array $permissions)
    {
        return new static;
    }

    /**
     * Return the permissions in the given list that are actually defined permissions for the application.
     * 
     * For now, if API features are enabled, we filter to only allow common CRUD permissions.
     * In the future, this can be enhanced to validate against a defined permission list.
     *
     * @param  array  $permissions
     * @return array
     */
    public static function validPermissions(array $permissions)
    {
        // If API features are disabled, return empty array
        if (! static::hasApiFeatures()) {
            return [];
        }
        
        // Define valid permissions (common CRUD operations)
        $validPermissions = ['create', 'read', 'update', 'delete'];
        
        // Filter to only include valid permissions
        return array_values(array_intersect($permissions, $validPermissions));
    }

    /**
     * Determine if Afterburner is managing profile photos.
     *
     * @return bool
     */
    public static function managesProfilePhotos()
    {
        return Features::managesProfilePhotos();
    }

    /**
     * Determine if Afterburner is supporting API features.
     *
     * @return bool
     */
    public static function hasApiFeatures()
    {
        return Features::hasApiFeatures();
    }

    /**
     * Determine if Afterburner is supporting team features.
     *
     * @return bool
     */
    public static function hasTeamFeatures()
    {
        return Features::hasTeamFeatures();
    }

    /**
     * Determine if a given user model utilizes the "HasTeams" trait.
     *
     * @param  \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public static function userHasTeamFeatures($user)
    {
        return (array_key_exists(HasTeams::class, class_uses_recursive($user)) ||
                method_exists($user, 'currentTeam')) &&
                static::hasTeamFeatures();
    }

    /**
     * Determine if the application is using the terms confirmation feature.
     *
     * @return bool
     */
    public static function hasTermsAndPrivacyPolicyFeature()
    {
        return Features::hasTermsAndPrivacyPolicyFeature();
    }

    /**
     * Determine if the application is using any account deletion features.
     *
     * @return bool
     */
    public static function hasAccountDeletionFeatures()
    {
        return Features::hasAccountDeletionFeatures();
    }

    /**
     * Determine if the application is using user timezone management features.
     *
     * @return bool
     */
    public static function hasUserTimezoneManagement()
    {
        return Features::hasUserTimezoneManagement();
    }

    /**
     * Determine if the application is using team timezone management features.
     *
     * @return bool
     */
    public static function hasTeamTimezoneManagement()
    {
        return Features::hasTeamTimezoneManagement();
    }

    /**
     * Determine if the application is using any timezone management features.
     * This checks both user and team timezone features for backward compatibility.
     *
     * @return bool
     */
    public static function hasTimezoneManagement()
    {
        return Features::hasTimezoneManagement();
    }

    /**
     * Determine if the application is using biometric authentication features.
     *
     * @return bool
     */
    public static function hasBiometricFeatures()
    {
        return Features::hasBiometricFeatures();
    }

    /**
     * Determine if the application is using two-factor authentication features.
     *
     * @return bool
     */
    public static function hasTwoFactorAuthenticationFeatures()
    {
        return Features::hasTwoFactorAuthenticationFeatures();
    }

    /**
     * Find a user instance by the given ID.
     *
     * @param  int  $id
     * @return mixed
     */
    public static function findUserByIdOrFail($id)
    {
        return static::newUserModel()->where('id', $id)->firstOrFail();
    }

    /**
     * Find a user instance by the given email address or fail.
     *
     * @param  string  $email
     * @return mixed
     */
    public static function findUserByEmailOrFail(string $email)
    {
        return static::newUserModel()->where('email', $email)->firstOrFail();
    }

    /**
     * Get the name of the user model used by the application.
     *
     * @return string
     */
    public static function userModel()
    {
        return static::$userModel;
    }

    /**
     * Get a new instance of the user model.
     *
     * @return mixed
     */
    public static function newUserModel()
    {
        $model = static::userModel();

        return new $model;
    }

    /**
     * Specify the user model that should be used by Afterburner.
     *
     * @param  string  $model
     * @return static
     */
    public static function useUserModel(string $model)
    {
        static::$userModel = $model;

        return new static;
    }

    /**
     * Get the name of the team model used by the application.
     *
     * @return string
     */
    public static function teamModel()
    {
        return static::$teamModel;
    }

    /**
     * Get a new instance of the team model.
     *
     * @return mixed
     */
    public static function newTeamModel()
    {
        $model = static::teamModel();

        return new $model;
    }

    /**
     * Specify the team model that should be used by Afterburner.
     *
     * @param  string  $model
     * @return static
     */
    public static function useTeamModel(string $model)
    {
        static::$teamModel = $model;

        return new static;
    }

    /**
     * Get the name of the membership model used by the application.
     *
     * @return string
     */
    public static function membershipModel()
    {
        return static::$membershipModel;
    }

    /**
     * Specify the membership model that should be used by Afterburner.
     *
     * @param  string  $model
     * @return static
     */
    public static function useMembershipModel(string $model)
    {
        static::$membershipModel = $model;

        return new static;
    }

    /**
     * Get the name of the team invitation model used by the application.
     *
     * @return string
     */
    public static function teamInvitationModel()
    {
        return static::$teamInvitationModel;
    }

    /**
     * Specify the team invitation model that should be used by Afterburner.
     *
     * @param  string  $model
     * @return static
     */
    public static function useTeamInvitationModel(string $model)
    {
        static::$teamInvitationModel = $model;

        return new static;
    }

    /**
     * Find the path to a localized Markdown resource.
     *
     * @param  string  $name
     * @return string|null
     */
    public static function localizedMarkdownPath($name)
    {
        $localName = preg_replace('#(\.md)$#i', '.'.app()->getLocale().'$1', $name);

        return Arr::first([
            resource_path('markdown/'.$localName),
            resource_path('markdown/'.$name),
        ], function ($path) {
            return file_exists($path);
        });
    }

    /**
     * Configure Afterburner to not register its routes.
     *
     * @return static
     */
    public static function ignoreRoutes()
    {
        static::$registersRoutes = false;

        return new static;
    }
}

