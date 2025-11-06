<?php

namespace App\Livewire\Audit;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AuditTrailViewer extends Component
{
    use WithPagination;

    public $userId = null;
    public $teamId = null;
    public ?string $category = null;
    public ?string $modelType = null;
    public ?string $eventName = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public ?string $searchQuery = null;

    public bool $showingDetailsModal = false;
    public ?int $selectedLogId = null;

    protected $queryString = [
        'userId' => ['except' => ''],
        'teamId' => ['except' => ''],
        'category' => ['except' => ''],
        'modelType' => ['except' => ''],
        'eventName' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'searchQuery' => ['except' => ''],
    ];

    public function mount()
    {
        // Only system admins can access audit trails
        if (!Auth::user()?->isSystemAdmin()) {
            abort(403, 'Access denied. System admin access required.');
        }

        // Cast query string values to proper types (allow 'system' string)
        if ($this->userId !== null && $this->userId !== '') {
            if ($this->userId !== 'system') {
                $this->userId = (int) $this->userId;
            }
        } else {
            $this->userId = null;
        }

        if ($this->teamId !== null && $this->teamId !== '') {
            $this->teamId = (int) $this->teamId;
        } else {
            $this->teamId = null;
        }
    }

    public function updatingSearchQuery()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatingEventName()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function updatedUserId($value)
    {
        // Allow 'system' as a string value, otherwise cast to int
        if ($value === 'system') {
            $this->userId = 'system';
        } else {
            $this->userId = $value !== '' && $value !== null ? (int) $value : null;
        }
        $this->resetPage();
    }

    public function updatedTeamId($value)
    {
        $this->teamId = $value !== '' && $value !== null ? (int) $value : null;
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->userId = null;
        $this->teamId = null;
        $this->category = null;
        $this->modelType = null;
        $this->eventName = null;
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->searchQuery = null;
        $this->resetPage();
    }

    public function showDetails($logId)
    {
        $this->selectedLogId = $logId;
        $this->showingDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showingDetailsModal = false;
        $this->selectedLogId = null;
    }

    public function render()
    {
        // Ensure userId and teamId are integers or null
        // Special case: 'system' means filter for null user_id
        $userId = null;
        if ($this->userId !== null && $this->userId !== '') {
            if ($this->userId === 'system') {
                // Handle system filter separately
                $userId = 'system';
            } else {
                $userId = (int) $this->userId;
            }
        }
        $teamId = $this->teamId !== null && $this->teamId !== '' ? (int) $this->teamId : null;

        // Build query - handle system filter specially
        if ($userId === 'system') {
            $query = app(AuditService::class)->getAuditTrail(
                userId: null,
                teamId: $teamId,
                category: $this->category,
                modelType: $this->modelType,
                since: $this->dateFrom
            );
            $query->whereNull('user_id');
        } else {
            $query = app(AuditService::class)->getAuditTrail(
                userId: $userId,
                teamId: $teamId,
                category: $this->category,
                modelType: $this->modelType,
                since: $this->dateFrom
            );
        }

        if ($this->eventName) {
            $query->where('event_name', 'like', "%{$this->eventName}%");
        }

        if ($this->dateTo) {
            $query->where('created_at', '<=', $this->dateTo);
        }

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('event_name', 'like', "%{$this->searchQuery}%")
                  ->orWhere('category', 'like', "%{$this->searchQuery}%")
                  ->orWhereHas('user', function ($userQuery) {
                      $userQuery->where('name', 'like', "%{$this->searchQuery}%")
                                ->orWhere('email', 'like', "%{$this->searchQuery}%");
                  });
            });
        }

        $logs = $query->paginate(25);

        $categories = AuditLog::distinct()->pluck('category')->sort()->values();
        $users = User::orderBy('name')->get();
        $eventNames = AuditLog::distinct()->pluck('event_name')->sort()->values();
        $teams = \App\Models\Team::orderBy('name')->get();

        $selectedLog = $this->selectedLogId 
            ? AuditLog::with(['user', 'impersonator', 'team', 'auditable'])->find($this->selectedLogId)
            : null;

        return view('audit.audit-trail-viewer', [
            'logs' => $logs,
            'categories' => $categories,
            'users' => $users,
            'eventNames' => $eventNames,
            'selectedLog' => $selectedLog,
            'teamId' => $teamId,
        ]);
    }
}

