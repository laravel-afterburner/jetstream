<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\CurrentTeamController;
use App\Http\Controllers\DeleteTeamController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PrivacyPolicyController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\TermsOfServiceController;
use App\Http\Controllers\TimezoneController;
use App\Http\Controllers\UserProfileController;
use App\Models\Team;
use App\Support\Afterburner;

Route::get('/', function () {
    return view('welcome');
});

// Terms and Privacy Policy
if (Afterburner::hasTermsAndPrivacyPolicyFeature()) {
    Route::get('/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms.show');
    Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('policy.show');
}

// Team Invitation Routes (public, must come before authenticated routes)
if (Afterburner::hasTeamFeatures()) {
    Route::get('/team-invitations/{invitation}', [TeamInvitationController::class, 'accept'])
        ->middleware(['signed'])
        ->name('team-invitations.accept');
}

// WebAuthn Routes (public for login, protected for registration)
if (Afterburner::hasBiometricFeatures()) {
    Route::middleware(['web'])->group(function () {
        Route::post('/webauthn/login/options', [\App\Http\Controllers\WebAuthn\WebAuthnLoginController::class, 'options'])->name('webauthn.login.options');
        Route::post('/webauthn/login', [\App\Http\Controllers\WebAuthn\WebAuthnLoginController::class, 'login'])->name('webauthn.login');
    });
}

// User Profile (requires auth)
$authMiddleware = config('afterburner.guard') ? 'auth:'.config('afterburner.guard') : 'auth';
$authSessionMiddleware = config('afterburner.auth_session', false) ? config('afterburner.auth_session') : null;

Route::middleware(array_values(array_filter([$authMiddleware, $authSessionMiddleware])))->group(function () {
    Route::get('/user/profile', [UserProfileController::class, 'show'])->name('profile.show');
    Route::get('/user/security', [\App\Http\Controllers\SecurityController::class, 'show'])->name('security.show');
    
    // WebAuthn Registration Routes (requires auth)
    if (Afterburner::hasBiometricFeatures()) {
        Route::post('/webauthn/register/options', [\App\Http\Controllers\WebAuthn\WebAuthnRegisterController::class, 'options'])->name('webauthn.register.options');
        Route::post('/webauthn/register', [\App\Http\Controllers\WebAuthn\WebAuthnRegisterController::class, 'register'])->name('webauthn.register');
    }
});

// Authenticated Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // API Tokens
    if (Afterburner::hasApiFeatures()) {
        Route::get('/user/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    }

    // Teams
    if (Afterburner::hasTeamFeatures()) {
        Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
        Route::put('/current-team', [CurrentTeamController::class, 'update'])->name('current-team.update');
        Route::delete('/team-invitations/{invitation}', [TeamInvitationController::class, 'destroy'])
            ->name('team-invitations.destroy');
        Route::delete('/teams/{team}', [DeleteTeamController::class, 'destroy'])
            ->name('teams.destroy');
        Route::get('/teams/{team}/information', function (Team $team) {
            return view('teams.information', ['team' => $team]);
        })->name('teams.information');
        Route::get('/teams/{team}/members', function (Team $team) {
            return view('teams.members', ['team' => $team]);
        })->name('teams.members');
        Route::get('/teams/{team}/roles', function (Team $team) {
            return view('roles.show', ['team' => $team]);
        })->middleware('can:createRole,team')->name('roles.show');
    }

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{notification}/accept-invitation', [NotificationController::class, 'acceptInvitation'])->name('notifications.accept-invitation');
    Route::post('/notifications/{notification}/decline-invitation', [NotificationController::class, 'declineInvitation'])->name('notifications.decline-invitation');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // User Timezone Management
    if (Afterburner::hasUserTimezoneManagement()) {
        Route::post('/timezone/update', [TimezoneController::class, 'update'])->name('timezone.update');
        Route::post('/timezone/dismiss', [TimezoneController::class, 'dismiss'])->name('timezone.dismiss');
    }

    // Impersonation
    Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');
    Route::middleware('system.admin')->group(function () {
        Route::post('/impersonate/{user}', [ImpersonationController::class, 'start'])->name('impersonate.start');
    });

    // Audit Trail (System Admin only)
    Route::middleware('system.admin')->group(function () {
        Route::get('/audit', function () {
            return view('audit.index');
        })->name('audit.index');
    });

    // Team Announcements (requires feature)
    if (Afterburner::hasTeamFeatures() && \App\Support\Features::hasTeamAnnouncements()) {
        // Combined view for all team members (handles both creation and viewing)
        Route::get('/teams/{team}/announcements', function (Team $team) {
            return view('team-announcements.index', ['team' => $team]);
        })->middleware('can:view,team')->name('team-announcements.index');
    }

    // Email Preview Route (development only)
    if (app()->environment('local', 'development')) {
        Route::get('/preview-email/team-announcement', function () {
            $team = \App\Models\Team::first();
            $announcement = \App\Models\TeamAnnouncement::first();
            
            if (!$announcement && $team) {
                $announcement = new \App\Models\TeamAnnouncement([
                    'title' => 'New Announcement',
                    'message' => 'Here is a brand new announcement.',
                    'team_id' => $team->id,
                    'creator_id' => $team->user_id,
                ]);
            }
            
            if (!$team || !$announcement) {
                return 'No team or announcement found. Please create test data first.';
            }
            
            return new \App\Mail\TeamAnnouncementMail($announcement);
        });
    }
});
