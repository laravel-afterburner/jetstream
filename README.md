# Afterburner Jetstream

A production-ready Laravel application template featuring teams, custom roles & permissions, audit logging, and more. Built as a self-contained Jetstream successor with everything you need out of the box.

## What is Afterburner?

Afterburner Jetstream is a complete Laravel application starter template that provides a powerful multi-tenancy foundation. This template includes everything you need to build team-based applications: authentication, team management, custom roles and permissions, audit logging, team announcements, and more.

Built as a self-contained successor to Laravel Jetstream, Afterburner vendors all necessary functionality directly into your application, giving you complete control without external dependencies. Perfect for SaaS applications, team collaboration tools, or any multi-tenant application requiring fine-grained access control.

## Features

### Authentication & Security

- **Fortify-based Authentication** - Complete login, registration, and password reset system

- **Email Verification** - Built-in email verification workflow

- **Two-Factor Authentication (2FA)** - TOTP-based two-factor authentication

- **WebAuthn/Biometric Authentication** - Passwordless authentication via WebAuthn

- **Session Management** - Secure session handling with device management

- **API Tokens** - Sanctum-powered API token authentication

### Multi-Tenancy & Teams

- **Team/Organization Management** - Full CRUD operations for teams

- **Team Switching** - Users can belong to multiple teams and switch between them

- **Team Invitations** - Email-based invitation system with expiration

- **Team Branding** - Customizable branding per team (logos, colors, etc.)

- **Team Timezone Management** - Per-team timezone configuration

- **Personal Teams** - Optional personal team feature (configurable)

### Roles & Permissions

- **Custom Roles System** - Flexible role management with hierarchy

- **Permission Management** - Fine-grained permission system

- **Default Role Assignment** - Automatic role assignment for new team members

- **Role Templates** - Pre-configured role templates (team, company, strata, organization)

- **Member Limits** - Configurable member limits per role

- **Role Hierarchy** - Hierarchical role system for permission inheritance

### Team Features

- **Team Announcements** - Publishable announcements per team

  - Scheduled publishing

  - Email notifications

  - Role-based targeting

  - Read/unread tracking

- **Team Members Management** - Add, remove, and update team members

- **Member Roles** - Assign multiple roles to team members

### Audit & Logging

- **Comprehensive Audit Logging** - Track all user actions and model changes

- **Audit Categories** - Organized audit logs by category

- **Impersonation Tracking** - Track when admins impersonate users

- **Audit Archiving** - Archive or export old audit logs

- **Model Change Tracking** - Automatic tracking of model changes

- **Request ID Tracking** - Track requests across audit logs

### Feature Flags

- **Runtime Feature Toggles** - Database-driven feature flags

- **Config-Based Defaults** - Fallback to config for deployment-time defaults

- **Hybrid Approach** - Runtime overrides with config fallbacks

### User Management

- **Profile Management** - Update name, email, password

- **Profile Photos** - Upload and manage profile photos

- **Timezone Preferences** - User-level timezone settings

- **Account Deletion** - User-initiated account deletion

- **System Admin** - System administrator functionality with impersonation

### UI & Frontend

- **Livewire 3** - Modern reactive UI components

- **Tailwind CSS** - Utility-first CSS framework

- **Vite** - Next-generation frontend build tool

- **Alpine.js** - Lightweight JavaScript framework

- **Responsive Design** - Mobile-first responsive layouts

### Additional Features

- **Terms & Privacy Policy** - Accept terms and privacy policy on registration

- **Email Notifications** - Team invitations, announcements, and more

- **Flash Messages** - Banner-style flash notifications

- **User Agent Detection** - Enhanced browser and platform detection

- **Queued Jobs** - Background job processing for audit logging

### Developer Experience

- **Full Laravel Application** - Complete Laravel installation ready to use

- **No External Dependencies** - Jetstream functionality vendorized

- **Standard Laravel Structure** - Follows Laravel conventions throughout

- **Artisan Commands** - Helpful commands for common tasks

- **Comprehensive Events** - Event-driven architecture

- **Service Providers** - Well-organized service provider structure

## Quick Start

### Using the Installer (Recommended)

```bash
# Install globally
composer global require laravel-afterburner/installer

# Create new project
afterburner new my-project
```

### Using Composer Directly

```bash
composer create-project laravel-afterburner/jetstream my-project

cd my-project
```

After installation, you'll need to:

1. Copy `.env.example` to `.env` and configure your environment
2. Run migrations: `php artisan migrate`
3. Seed roles (optional): `php artisan db:seed --class=RolesSeeder`

**Note:** If you plan to use WebAuthn/Biometric Authentication, ensure your development site is served over HTTPS. WebAuthn APIs are only available in secure contexts (HTTPS or localhost). If you're testing on a non-localhost domain, you'll need to set up SSL/TLS certificates for your development environment.

## Requirements

- PHP ^8.2

- Laravel ^11.0

- Composer

## Configuration

### Environment Variables

Afterburner uses the following environment variables (see `stubs/.env.example` for details):

- `AFTERBURNER_ENTITY_LABEL` - Label for teams/organizations (default: `organization`)
- `AFTERBURNER_APP_TYPE` - Application type (default: `Management App`)
- `AFTERBURNER_GUARD` - Authentication guard (default: `sanctum`)
- `AFTERBURNER_PROFILE_PHOTO_DISK` - Profile photo storage disk (default: `public`)

### Configuration File

Main configuration is in `config/afterburner.php`. This file controls:

- Entity label and application naming
- Feature flags (including email verification from Laravel Fortify)
- Feature options (e.g., 2FA configuration)
- Authentication settings
- Profile photo storage

### WebAuthn/Biometric Authentication

WebAuthn (biometric authentication) requires a secure context to function:

- **HTTPS**: Production and staging environments must use HTTPS
- **Localhost**: Development on `localhost`, `127.0.0.1`, or `[::1]` works over HTTP
- **Non-localhost HTTP**: WebAuthn APIs are disabled by browsers on non-localhost HTTP connections

If users encounter errors when registering biometric devices, ensure your site is served over HTTPS (or use localhost for development). The application will display a helpful error message if WebAuthn is unavailable due to insecure contexts.

WebAuthn configuration is managed in `config/webauthn.php`:
- `WEBAUTHN_NAME` - Relying party name (defaults to app name)
- `WEBAUTHN_ID` - Relying party ID (domain)
- `WEBAUTHN_ORIGINS` - Additional allowed origins (comma-separated)

### Feature Flags

**Most features are managed in `config/afterburner.php`**, including all Afterburner-specific features and email verification from Laravel Fortify. Other Fortify authentication features (registration, password reset, etc.) are configured in `config/fortify.php`.

This approach provides:

- Unified control for Afterburner and email verification features
- Consistent runtime toggling via the database for managed features
- Standard Laravel Fortify configuration for other authentication features

Feature flags can be managed in two ways:

1. **Config File** (`config/afterburner.php`) - Deployment-time defaults
2. **Database** (`feature_flags` table) - Runtime overrides

The `Features` class provides a unified interface:

```php
use App\Support\Features;

// Afterburner features
if (Features::hasTeamFeatures()) {
    // Team features are enabled
}

if (Features::hasPersonalTeams()) {
    // Personal teams feature is enabled
}

// Email verification (managed through Afterburner)
if (Features::hasEmailVerification()) {
    // Email verification is required
}
```

**Note:** Email verification is controlled through Afterburner config and can be toggled at runtime via the `feature_flags` table. Other Fortify features (registration, password reset, update profile, update passwords) are configured directly in `config/fortify.php`.

## User Model

The template includes a complete `User` model with all necessary traits:

- `HasAfterburnerRoles` - Role and permission management
- `HasTeams` - Team membership and switching
- `HasPermissions` - Permission checking
- `HasProfilePhoto` - Profile photo management
- `HasApiTokens` - API token management
- `TwoFactorAuthenticatable` - 2FA support
- `WebAuthnAuthentication` - Biometric authentication

### Role System

The template includes a default role system that automatically assigns roles to users:

1. **Default Roles**: Identified by `is_default => true` in the `roles` table
2. **Automatic Assignment**: Default role is assigned when:
   - A user registers and a team is created
   - A user is added to a team
   - A user accepts a team invitation
   - A user creates a new team

To set up roles, create a `RolesSeeder`:

```php
// database/seeders/RolesSeeder.php
public function run(): void
{
    Role::create([
        'name' => 'Member',
        'slug' => 'member',
        'description' => 'Default team member role',
        'is_default' => true,
        'hierarchy' => 100,
        'badge_color' => 'gray',
        'max_members' => null,
    ]);
}
```

Then run: `php artisan db:seed --class=RolesSeeder`

## Artisan Commands

Afterburner provides several Artisan commands:

### Installation & Publishing
- `afterburner:install` - Install add-ons into an existing project
- `afterburner:publish` - Publish all Afterburner assets (config, migrations, views)

### Feature Flags
All feature flag commands support the `--disabled` flag to disable features, and `--force` to skip confirmation prompts. By default, commands enable the feature.

- `afterburner:api` - Enable/disable API feature
- `afterburner:account-deletion` - Enable/disable account deletion feature
- `afterburner:biometric` - Enable/disable biometric authentication (WebAuthn) feature
- `afterburner:personal-teams` - Enable/disable personal teams feature
- `afterburner:profile-photos` - Enable/disable profile photos feature
- `afterburner:team-announcements` - Enable/disable team announcements feature
- `afterburner:teams` - Enable/disable teams feature
- `afterburner:terms-and-privacy-policy` - Enable/disable terms and privacy policy feature
- `afterburner:timezone` - Enable/disable timezone management feature
- `afterburner:two-factor-authentication` - Enable/disable two-factor authentication feature

### Other Commands
- `afterburner:audit-archive` - Archive or delete old audit logs

## Testing

Run the test suite:

```bash
php artisan test
```

The template includes comprehensive tests for:
- Team creation and management
- Role and permission system
- User model functionality
- Authentication flows
- API tokens
- Feature flags
- Artisan commands

## Architecture

This template follows Laravel conventions:

- **Namespace**: `App\` (standard Laravel project structure)
- **Type**: `project` (full Laravel application template)
- **No Jetstream dependency**: All Jetstream features are vendorized

## Support Classes

The template includes several utility classes in `App\Support`:

- `Agent` - User agent detection
- `Afterburner` - Facade-like class for Afterburner functionality
- `Features` - Feature flag management
- `OwnerRole` - Helper class for owner role
- `Role` - Support class for role definitions

## Documentation

Full documentation is available in the [docs](docs/) directory of this repository. The documentation includes:

- [Installation Guide](docs/installation.md)

- [Configuration](docs/configuration.md)

- [Teams & Organizations](docs/teams.md)

- [Roles & Permissions](docs/roles-permissions.md)

- [Audit Logging](docs/audit-logging.md)

- [Team Announcements](docs/team-announcements.md)

- [API Documentation](docs/api.md)

- And more...

Start with the [Installation Guide](docs/installation.md) to get up and running quickly.

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Support

- GitHub Issues: [laravel-afterburner/jetstream/issues](https://github.com/laravel-afterburner/jetstream/issues)

## Add-On Packages

Afterburner is designed to be extensible. Check out our add-on packages:

- **Subscriptions** - Stripe subscription management

- **Documents** - Document management (coming soon)

- **Communications** - Enhanced communications (coming soon)

- **Voting** - Polls and voting system (coming soon)

- **Meetings** - Meeting management (coming soon)

Install add-ons via Composer:

```bash
composer require laravel-afterburner/subscriptions
php artisan afterburner:subscriptions:install
```

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Credits

Afterburner is built on the foundation of Laravel, created by Taylor Otwell. Taylor's contributions to the PHP development community have revolutionized how we build web applications, and his work has profoundly impacted countless developers worldwide—myself included. 

This project is inspired by Laravel Jetstream's architecture and user experience, but is implemented as a completely independent, self-contained solution. Afterburner vendors all necessary functionality directly into your application, giving you full control without external dependencies.

Thank you, Taylor, for creating Laravel and the entire ecosystem that makes projects like this possible.
