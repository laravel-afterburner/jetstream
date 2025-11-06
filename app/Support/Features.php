<?php

namespace App\Support;

use App\Models\FeatureFlag;
use Illuminate\Support\Facades\Schema;

class Features
{
    /**
     * Determine if the given feature is enabled.
     * 
     * Uses hybrid approach: checks database for runtime overrides first,
     * then falls back to config file for deployment-time defaults.
     *
     * All feature keys are normalized to snake_case for database storage.
     *
     * @param  string  $feature
     * @return bool
     */
    public static function enabled(string $feature)
    {
        // Normalize feature key to snake_case (Laravel database convention)
        $normalizedFeature = str_replace('-', '_', $feature);
        
        // Check database for runtime override (if table exists)
        try {
            if (Schema::hasTable('feature_flags')) {
                // Try normalized key first (snake_case)
                $flag = FeatureFlag::where('key', $normalizedFeature)->first();
                
                // If not found, try original key (for backward compatibility during migration)
                if ($flag === null) {
                $flag = FeatureFlag::where('key', $feature)->first();
                }
                
                if ($flag !== null) {
                    return $flag->enabled;
                }
            }
        } catch (\Exception $e) {
            // If table doesn't exist yet (during migrations), fall through to config
        }
        
        // Fallback to config (deployment-time defaults)
        // Config may still use kebab-case, so check both formats
        $configFeatures = config('afterburner.features', []);
        return in_array($feature, $configFeatures) || in_array($normalizedFeature, $configFeatures);
    }

    /**
     * Determine if the feature is enabled and has a given option enabled.
     *
     * @param  string  $feature
     * @param  string  $option
     * @return bool
     */
    public static function optionEnabled(string $feature, string $option)
    {
        // Normalize feature key to snake_case for config lookup
        $normalizedFeature = str_replace('-', '_', $feature);
        return static::enabled($feature) &&
               (config("afterburner.options.{$feature}.{$option}") === true ||
                config("afterburner.options.{$normalizedFeature}.{$option}") === true);
    }

    /**
     * Determine if the application is allowing profile photo uploads.
     *
     * @return bool
     */
    public static function managesProfilePhotos()
    {
        return static::enabled(static::profilePhotos());
    }

    /**
     * Determine if the application is using any API features.
     *
     * @return bool
     */
    public static function hasApiFeatures()
    {
        return static::enabled(static::api());
    }

    /**
     * Determine if the application is using any team features.
     *
     * @return bool
     */
    public static function hasTeamFeatures()
    {
        return static::enabled(static::teams());
    }

    /**
     * Determine if invitations are sent to team members.
     * Invitations are always enabled when teams are enabled.
     *
     * @return bool
     */
    public static function sendsTeamInvitations()
    {
        return static::hasTeamFeatures();
    }

    /**
     * Determine if the application has terms of service / privacy policy confirmation enabled.
     *
     * @return bool
     */
    public static function hasTermsAndPrivacyPolicyFeature()
    {
        return static::enabled(static::termsAndPrivacyPolicy());
    }

    /**
     * Determine if the application is using any account deletion features.
     *
     * @return bool
     */
    public static function hasAccountDeletionFeatures()
    {
        return static::enabled(static::accountDeletion());
    }

    /**
     * Enable the profile photo upload feature.
     *
     * @return string
     */
    public static function profilePhotos()
    {
        return 'profile_photos';
    }

    /**
     * Enable the API feature.
     *
     * @return string
     */
    public static function api()
    {
        return 'api';
    }

    /**
     * Enable the teams feature.
     *
     * @param  array  $options
     * @return string
     */
    public static function teams(array $options = [])
    {
        // Options are now configured in config/afterburner.php under 'options.teams'
        // This parameter is kept for backward compatibility but ignored
        return 'teams';
    }

    /**
     * Enable the terms of service and privacy policy feature.
     *
     * @return string
     */
    public static function termsAndPrivacyPolicy()
    {
        return 'terms_and_privacy_policy';
    }

    /**
     * Enable the account deletion feature.
     *
     * @return string
     */
    public static function accountDeletion()
    {
        return 'account_deletion';
    }

    /**
     * Enable the personal teams feature.
     *
     * @param  array  $options
     * @return string
     */
    public static function personalTeams(array $options = [])
    {
        // Options are now configured in config/afterburner.php under 'options.personal_teams'
        // This parameter is kept for backward compatibility but ignored
        return 'personal_teams';
    }

    /**
     * Determine if the application is using personal teams.
     *
     * @return bool
     */
    public static function hasPersonalTeams()
    {
        return static::enabled(static::personalTeams());
    }

    /**
     * Enable the user timezone management feature.
     *
     * @return string
     */
    public static function userTimezone()
    {
        return 'user_timezone';
    }

    /**
     * Enable the team timezone management feature.
     *
     * @return string
     */
    public static function teamTimezone()
    {
        return 'team_timezone';
    }

    /**
     * Determine if the application is using user timezone management features.
     *
     * @return bool
     */
    public static function hasUserTimezoneManagement()
    {
        return static::enabled(static::userTimezone());
    }

    /**
     * Determine if the application is using team timezone management features.
     *
     * @return bool
     */
    public static function hasTeamTimezoneManagement()
    {
        return static::enabled(static::teamTimezone());
    }

    /**
     * Determine if the application is using any timezone management features.
     * This checks both user and team timezone features for backward compatibility.
     *
     * @return bool
     */
    public static function hasTimezoneManagement()
    {
        return static::hasUserTimezoneManagement() || static::hasTeamTimezoneManagement();
    }

    /**
     * Enable the timezone management feature.
     * @deprecated Use userTimezone() or teamTimezone() instead. This method is kept for backward compatibility.
     *
     * @return string
     */
    public static function timezone()
    {
        return 'timezone';
    }

    /**
     * Enable the biometric authentication (WebAuthn) feature.
     *
     * @return string
     */
    public static function biometric()
    {
        return 'biometric';
    }

    /**
     * Determine if the application is using biometric authentication features.
     *
     * @return bool
     */
    public static function hasBiometricFeatures()
    {
        return static::enabled(static::biometric());
    }

    /**
     * Enable the two-factor authentication feature.
     *
     * @return string
     */
    public static function twoFactorAuthentication()
    {
        return 'two_factor_authentication';
    }

    /**
     * Determine if the application is using two-factor authentication features.
     *
     * @return bool
     */
    public static function hasTwoFactorAuthenticationFeatures()
    {
        return static::enabled(static::twoFactorAuthentication());
    }

    /**
     * Enable the team announcements feature.
     *
     * @return string
     */
    public static function teamAnnouncements()
    {
        return 'team_announcements';
    }

    /**
     * Determine if the application is using team announcements features.
     *
     * @return bool
     */
    public static function hasTeamAnnouncements()
    {
        return static::enabled(static::teamAnnouncements());
    }

    /*
    |--------------------------------------------------------------------------
    | Fortify Email Verification Feature
    |--------------------------------------------------------------------------
    |
    | Email verification is managed through the Afterburner config for unified
    | feature flag control. Other Fortify features remain in config/fortify.php.
    |
    */

    /**
     * Enable the email verification feature.
     *
     * @return string
     */
    public static function emailVerification()
    {
        return 'email_verification';
    }

    /**
     * Determine if the application requires email verification.
     *
     * @return bool
     */
    public static function hasEmailVerification()
    {
        return static::enabled(static::emailVerification());
    }

    /**
     * Get feature options from config.
     * 
     * Returns the options array for a given feature, or an empty array if not set.
     * Feature keys are normalized to snake_case for config lookup.
     *
     * @param  string  $feature
     * @return array
     */
    public static function getOptions(string $feature): array
    {
        // Normalize feature key to snake_case for config lookup
        $normalizedFeature = str_replace('-', '_', $feature);
        $options = config("afterburner.options.{$normalizedFeature}", []);
        // Fallback to original key for backward compatibility
        if (empty($options)) {
            $options = config("afterburner.options.{$feature}", []);
        }
        return $options;
    }

    /**
     * Get all available features grouped by category.
     * Features are organized in logical groups matching the config file.
     * Only includes features that are defined in config/afterburner.php.
     *
     * @return array Array of groups, each containing 'name' and 'features'
     */
    public static function getFeatureGroups(): array
    {
        // Get features from config to ensure we only show what's actually configured
        $configFeatures = config('afterburner.features', []);
        $configFeatureKeys = array_map(function($key) {
            return str_replace('-', '_', $key); // Normalize to snake_case
        }, $configFeatures);
        
        // Define all possible features with their metadata
        $allFeatures = [
            // Teams & Collaboration
            static::teams() => [
                'group' => 'Teams & Collaboration',
                'name' => 'Teams',
                'description' => 'Enable team/organization management features',
            ],
            static::personalTeams() => [
                'group' => 'Teams & Collaboration',
                'name' => 'Personal Teams',
                'description' => 'Enable personal team creation for individual users',
            ],
            static::teamAnnouncements() => [
                'group' => 'Teams & Collaboration',
                'name' => 'Team Announcements',
                'description' => 'Enable team-wide announcements and notifications',
            ],
            static::teamTimezone() => [
                'group' => 'Teams & Collaboration',
                'name' => 'Team Timezone',
                'description' => 'Allow teams to set and manage their timezone preferences',
            ],
            
            // Authentication & Security
            static::emailVerification() => [
                'group' => 'Authentication & Security',
                'name' => 'Email Verification',
                'description' => 'Require users to verify their email addresses',
            ],
            static::twoFactorAuthentication() => [
                'group' => 'Authentication & Security',
                'name' => 'Two-Factor Authentication',
                'description' => 'Enable two-factor authentication for enhanced security',
            ],
            static::biometric() => [
                'group' => 'Authentication & Security',
                'name' => 'Biometric Authentication',
                'description' => 'Enable WebAuthn/biometric authentication (fingerprint, face ID, etc.)',
            ],
            
            // User Profile & Account
            static::profilePhotos() => [
                'group' => 'User Profile & Account',
                'name' => 'Profile Photos',
                'description' => 'Allow users to upload and manage profile photos',
            ],
            static::userTimezone() => [
                'group' => 'User Profile & Account',
                'name' => 'User Timezone',
                'description' => 'Allow users to set and manage their personal timezone preferences',
            ],
            static::accountDeletion() => [
                'group' => 'User Profile & Account',
                'name' => 'Account Deletion',
                'description' => 'Allow users to delete their accounts',
            ],
            
            // API & Integration
            static::api() => [
                'group' => 'API & Integration',
                'name' => 'API',
                'description' => 'Enable API token management and API features',
            ],
            
            // Legal & Compliance
            static::termsAndPrivacyPolicy() => [
                'group' => 'Legal & Compliance',
                'name' => 'Terms & Privacy Policy',
                'description' => 'Require users to accept terms of service and privacy policy',
            ],
        ];
        
        // Filter to only include features that are in config, maintaining config order
        $groups = [];
        $groupOrder = [
            'Teams & Collaboration',
            'Authentication & Security',
            'User Profile & Account',
            'API & Integration',
            'Legal & Compliance',
        ];
        
        // Initialize groups
        foreach ($groupOrder as $groupName) {
            $groups[$groupName] = [
                'name' => $groupName,
                'features' => [],
            ];
        }
        
        // Track which features we've added to prevent duplicates
        $addedKeys = [];
        
        // Add features in config order
        foreach ($configFeatures as $configFeature) {
            $normalizedKey = str_replace('-', '_', $configFeature);
            
            // Skip if already added (prevent duplicates)
            if (in_array($normalizedKey, $addedKeys)) {
                continue;
            }
            
            if (isset($allFeatures[$normalizedKey])) {
                $featureData = $allFeatures[$normalizedKey];
                $groupName = $featureData['group'];
                
                $groups[$groupName]['features'][] = [
                    'key' => $normalizedKey,
                    'name' => $featureData['name'],
                    'description' => $featureData['description'],
                ];
                
                $addedKeys[] = $normalizedKey;
            }
        }
        
        // Remove empty groups and return as indexed array
        return array_values(array_filter($groups, function($group) {
            return !empty($group['features']);
        }));
    }

    /**
     * Get all available features with their status and display information.
     * Features are organized in logical groups matching the config file.
     *
     * @return array
     */
    public static function getAllFeatures(): array
    {
        $groups = static::getFeatureGroups();
        $features = [];
        
        foreach ($groups as $group) {
            foreach ($group['features'] as $feature) {
                $feature['enabled'] = static::enabled($feature['key']);
                $features[] = $feature;
            }
        }
        
        return $features;
    }
}



