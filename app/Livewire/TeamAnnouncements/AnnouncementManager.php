<?php

namespace App\Livewire\TeamAnnouncements;

use App\Mail\TeamAnnouncementMail;
use App\Models\Role;
use App\Models\Team;
use App\Models\TeamAnnouncement;
use App\Models\User;
use App\Support\Features;
use App\Traits\InteractsWithBanner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class AnnouncementManager extends Component
{
    use InteractsWithBanner;
    use WithPagination;

    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * Indicates if the application is editing an announcement.
     *
     * @var bool
     */
    public $editingAnnouncement = false;

    /**
     * The announcement being edited.
     *
     * @var mixed
     */
    public $announcementBeingEdited = null;

    /**
     * Indicates if the application is confirming announcement deletion.
     *
     * @var bool
     */
    public $confirmingAnnouncementDeletion = false;

    /**
     * The announcement being deleted.
     *
     * @var mixed
     */
    public $announcementBeingDeleted = null;

    /**
     * The "create announcement" form state.
     *
     * @var array
     */
    public $createAnnouncementForm = [
        'title' => 'Test Announcement Title',
        'message' => 'This is a test announcement message for testing purposes.',
        'send_email' => false,
        'published_at' => null,
        'target_roles' => [],
    ];

    /**
     * The "edit announcement" form state.
     *
     * @var array
     */
    public $editAnnouncementForm = [
        'title' => '',
        'message' => '',
        'send_email' => false,
        'published_at' => '',
        'target_roles' => [],
    ];


    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        // Handle both model instances and ID strings
        if (is_string($team) || is_numeric($team)) {
            $this->team = Team::findOrFail($team);
        } else {
            $this->team = $team;
        }

        // Check feature is enabled
        if (!Features::hasTeamAnnouncements()) {
            abort(404);
        }

        // Ensure user is a member of this team
        if (!Auth::user()->teams->contains($this->team)) {
            abort(403, 'You are not a member of this team.');
        }

        // Ensure this is the user's current team
        if (Auth::user()->currentTeam->id !== $this->team->id) {
            abort(403, 'You can only view announcements for your current team.');
        }

        // Set default published_at to now
        $this->createAnnouncementForm['published_at'] = '';
        
        // Set default text for title and message
        $this->createAnnouncementForm['title'] = 'Test Announcement Title';
        $this->createAnnouncementForm['message'] = 'This is a test announcement message for testing purposes.';
    }

    /**
     * Store a new announcement.
     *
     * @return void
     */
    public function storeAnnouncement()
    {
        $this->resetErrorBag();

        // Check team ownership
        if (!Gate::check('update', $this->team)) {
            return;
        }

        $this->validate([
            'createAnnouncementForm.title' => 'required|string|max:255',
            'createAnnouncementForm.message' => 'required|string',
            'createAnnouncementForm.send_email' => 'boolean',
            'createAnnouncementForm.published_at' => 'nullable|date',
            'createAnnouncementForm.target_roles' => 'nullable|array',
            'createAnnouncementForm.target_roles.*' => 'exists:roles,slug',
        ], [
            'createAnnouncementForm.title.required' => 'The title field is required.',
            'createAnnouncementForm.message.required' => 'The message field is required.',
            'createAnnouncementForm.published_at.date' => 'The published date must be a valid date.',
            'createAnnouncementForm.target_roles.*.exists' => 'One or more selected roles are invalid.',
        ]);

        // Get user's timezone for datetime-local conversion
        $userTimezone = Auth::user()->timezone ?? request()->cookie('timezone') ?? null;

        $announcement = TeamAnnouncement::create([
            'team_id' => $this->team->id,
            'title' => $this->createAnnouncementForm['title'],
            'message' => $this->createAnnouncementForm['message'],
            'send_email' => $this->createAnnouncementForm['send_email'],
            'published_at' => $this->createAnnouncementForm['published_at'] ? 
                $this->team->fromDateTimeLocal($this->createAnnouncementForm['published_at'], $userTimezone) : null,
            'target_roles' => !empty($this->createAnnouncementForm['target_roles']) ? 
                $this->createAnnouncementForm['target_roles'] : null,
            'created_by' => Auth::id(),
        ]);

        // Mark announcement as read for the creator since they already know about it
        $announcement->markAsReadBy(Auth::user());

        // Send emails if requested and announcement is published
        if ($announcement->send_email && $announcement->isPublished()) {
            $this->sendAnnouncementEmails($announcement);
        }

        $this->resetCreateAnnouncementForm();

        $this->banner(__('Announcement created successfully.'));
        
        // Refresh navigation menu to update announcement badge
        $this->dispatch('refresh-navigation-menu');
    }

    /**
     * Edit an announcement.
     *
     * @param  int  $announcementId
     * @return void
     */
    public function editAnnouncement($announcementId)
    {
        $announcement = TeamAnnouncement::where('team_id', $this->team->id)
            ->findOrFail($announcementId);

        // Check if user is the creator or team owner
        if ($announcement->created_by !== Auth::id() && !Gate::check('update', $this->team)) {
            return;
        }

        $this->announcementBeingEdited = $announcement;
        
        // Get user's timezone for datetime-local conversion
        $userTimezone = Auth::user()->timezone ?? request()->cookie('timezone') ?? null;
        
        $this->editAnnouncementForm = [
            'title' => $announcement->title,
            'message' => $announcement->message,
            'send_email' => $announcement->send_email,
            'published_at' => $announcement->published_at ? 
                $this->team->toDateTimeLocal($announcement->published_at, $userTimezone) : '',
            'target_roles' => $announcement->target_roles ?? [],
        ];

        $this->editingAnnouncement = true;
    }

    /**
     * Update the announcement being edited.
     *
     * @return void
     */
    public function updateAnnouncement()
    {
        $this->resetErrorBag();

        // Check if user is the creator or team owner
        if ($this->announcementBeingEdited->created_by !== Auth::id() && !Gate::check('update', $this->team)) {
            return;
        }

        $this->validate([
            'editAnnouncementForm.title' => 'required|string|max:255',
            'editAnnouncementForm.message' => 'required|string',
            'editAnnouncementForm.send_email' => 'boolean',
            'editAnnouncementForm.published_at' => 'nullable|date',
            'editAnnouncementForm.target_roles' => 'nullable|array',
            'editAnnouncementForm.target_roles.*' => 'exists:roles,slug',
        ], [
            'editAnnouncementForm.title.required' => 'The title field is required.',
            'editAnnouncementForm.message.required' => 'The message field is required.',
            'editAnnouncementForm.published_at.date' => 'The published date must be a valid date.',
            'editAnnouncementForm.target_roles.*.exists' => 'One or more selected roles are invalid.',
        ]);

        $wasPublished = $this->announcementBeingEdited->isPublished();

        // Get user's timezone for datetime-local conversion
        $userTimezone = Auth::user()->timezone ?? request()->cookie('timezone') ?? null;

        $this->announcementBeingEdited->update([
            'title' => $this->editAnnouncementForm['title'],
            'message' => $this->editAnnouncementForm['message'],
            'send_email' => $this->editAnnouncementForm['send_email'],
            'published_at' => $this->editAnnouncementForm['published_at'] ? 
                $this->team->fromDateTimeLocal($this->editAnnouncementForm['published_at'], $userTimezone) : null,
            'target_roles' => !empty($this->editAnnouncementForm['target_roles']) ? 
                $this->editAnnouncementForm['target_roles'] : null,
        ]);

        // Send emails if requested and announcement is now published (wasn't before)
        $isNowPublished = $this->announcementBeingEdited->isPublished();
        if ($this->announcementBeingEdited->send_email && $isNowPublished && !$wasPublished) {
            $this->sendAnnouncementEmails($this->announcementBeingEdited);
        }

        $this->resetEditAnnouncementForm();
        $this->editingAnnouncement = false;

        $this->banner(__('Announcement updated successfully.'));
        
        // Refresh navigation menu to update announcement badge
        $this->dispatch('refresh-navigation-menu');
    }

    /**
     * Confirm announcement deletion.
     *
     * @param  int  $announcementId
     * @return void
     */
    public function confirmAnnouncementDeletion($announcementId)
    {
        $announcement = TeamAnnouncement::where('team_id', $this->team->id)
            ->findOrFail($announcementId);

        // Check if user is the creator or team owner
        if ($announcement->created_by !== Auth::id() && !Gate::check('update', $this->team)) {
            return;
        }

        $this->announcementBeingDeleted = $announcement;
        $this->confirmingAnnouncementDeletion = true;
    }

    /**
     * Delete the announcement.
     *
     * @return void
     */
    public function deleteAnnouncement()
    {
        // Check if user is the creator or team owner
        if ($this->announcementBeingDeleted && $this->announcementBeingDeleted->created_by !== Auth::id() && !Gate::check('update', $this->team)) {
            return;
        }

        if ($this->announcementBeingDeleted) {
            $this->announcementBeingDeleted->delete();
        }

        $this->confirmingAnnouncementDeletion = false;
        $this->announcementBeingDeleted = null;

        $this->banner(__('Announcement deleted successfully.'));
        
        // Refresh navigation menu to update announcement badge
        $this->dispatch('refresh-navigation-menu');
    }

    /**
     * Mark an announcement as read.
     *
     * @param  int  $announcementId
     * @return void
     */
    public function markAsRead($announcementId)
    {
        $announcement = TeamAnnouncement::where('team_id', $this->team->id)
            ->findOrFail($announcementId);

        $announcement->markAsReadBy(Auth::user());

        $this->dispatch('refresh-navigation-menu');
    }

    /**
     * Mark all announcements as read.
     *
     * @return void
     */
    public function markAllAsRead()
    {
        $unreadAnnouncements = TeamAnnouncement::getUnreadForUser(Auth::user());

        foreach ($unreadAnnouncements as $announcement) {
            $announcement->markAsReadBy(Auth::user());
        }

        $this->dispatch('refresh-navigation-menu');
        $this->banner(__('All announcements marked as read.'));
    }

    /**
     * Check if an announcement is unread.
     *
     * @param  \App\Models\TeamAnnouncement  $announcement
     * @return bool
     */
    public function isUnread($announcement)
    {
        return !$announcement->hasBeenReadBy(Auth::user());
    }

    /**
     * Cancel announcement editing.
     *
     * @return void
     */
    public function cancelEditAnnouncement()
    {
        $this->resetErrorBag();
        $this->resetEditAnnouncementForm();
        $this->editingAnnouncement = false;
    }

    /**
     * Cancel announcement deletion.
     *
     * @return void
     */
    public function cancelAnnouncementDeletion()
    {
        $this->confirmingAnnouncementDeletion = false;
        $this->announcementBeingDeleted = null;
    }

    /**
     * Reset the create announcement form.
     *
     * @return void
     */
    public function resetCreateAnnouncementForm()
    {
        $this->resetErrorBag();
        $this->createAnnouncementForm = [
            'title' => 'Test Announcement Title',
            'message' => 'This is a test announcement message for testing purposes.',
            'send_email' => false,
            'published_at' => '',
            'target_roles' => [],
        ];
    }

    /**
     * Reset the edit announcement form.
     *
     * @return void
     */
    public function resetEditAnnouncementForm()
    {
        $this->editAnnouncementForm = [
            'title' => '',
            'message' => '',
            'send_email' => false,
            'published_at' => '',
            'target_roles' => [],
        ];
        $this->announcementBeingEdited = null;
    }

    /**
     * Send announcement emails to eligible users.
     *
     * @param  \App\Models\TeamAnnouncement  $announcement
     * @return void
     */
    protected function sendAnnouncementEmails(TeamAnnouncement $announcement)
    {
        // Get all users in the team
        $users = $this->team->allUsers()->filter(function ($user) {
            return $user && $user->email_verified_at !== null;
        });

        foreach ($users as $user) {
            // Check if user has one of the target roles (or if no roles specified, send to all)
            if ($announcement->target_roles === null || empty($announcement->target_roles)) {
                // Send to all team users
                Mail::to($user)->send(new TeamAnnouncementMail($announcement));
            } else {
                // Check if user has any of the target roles in this team
                $userRoleSlugs = $user->roles()
                    ->where('team_id', $this->team->id)
                    ->pluck('slug')
                    ->toArray();

                if (array_intersect($announcement->target_roles, $userRoleSlugs)) {
                    Mail::to($user)->send(new TeamAnnouncementMail($announcement));
                }
            }
        }
        
        // Mark emails as sent to prevent duplicate sends by the scheduled command
        $announcement->update(['emails_sent_at' => now()]);
    }

    /**
     * Get the announcements.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAnnouncementsProperty()
    {
        $user = Auth::user();
        $isTeamOwner = Gate::check('update', $this->team);
        $teamId = $this->team->id;
        
        if ($isTeamOwner) {
            // Team owners see all announcements (published and drafts)
            return TeamAnnouncement::where('team_id', $teamId)
                ->with(['creator', 'readers', 'team.users' => function($query) use ($teamId) {
                    $query->with(['roles' => function($q) use ($teamId) {
                        $q->where('team_id', $teamId);
                    }]);
                }, 'team.owner' => function($query) use ($teamId) {
                    $query->with(['roles' => function($q) use ($teamId) {
                        $q->where('team_id', $teamId);
                    }]);
                }])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            // Regular members see only published announcements relevant to their roles
            $userRoleSlugs = $user->roles()
                ->where('team_id', $teamId)
                ->pluck('slug')
                ->toArray();

            return TeamAnnouncement::published()
                ->where('team_id', $teamId)
                ->where(function ($query) use ($userRoleSlugs) {
                    $query->whereNull('target_roles')
                          ->orWhere(function ($q) use ($userRoleSlugs) {
                              if (!empty($userRoleSlugs)) {
                                  foreach ($userRoleSlugs as $roleSlug) {
                                      $q->orWhereJsonContains('target_roles', $roleSlug);
                                  }
                              }
                          });
                })
                ->with(['creator', 'readers', 'team.users' => function($query) use ($teamId) {
                    $query->with(['roles' => function($q) use ($teamId) {
                        $q->where('team_id', $teamId);
                    }]);
                }, 'team.owner' => function($query) use ($teamId) {
                    $query->with(['roles' => function($q) use ($teamId) {
                        $q->where('team_id', $teamId);
                    }]);
                }])
                ->orderBy('published_at', 'desc')
                ->paginate(10);
        }
    }

    /**
     * Get the unread count.
     *
     * @return int
     */
    public function getUnreadCountProperty()
    {
        return TeamAnnouncement::getUnreadCountForUser(Auth::user());
    }

    /**
     * Check if user can create announcements.
     *
     * @return bool
     */
    public function canCreateAnnouncements()
    {
        return Gate::check('update', $this->team);
    }

    /**
     * Get the available roles for this team.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRolesProperty()
    {
        return Role::orderBy('hierarchy')->get();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('team-announcements.announcement-manager');
    }
}
