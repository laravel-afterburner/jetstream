<?php

namespace App\Livewire\Teams;

use App\Traits\InteractsWithBanner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class UpdateTeamTimezoneForm extends Component
{
    use InteractsWithBanner;

    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * The component's state.
     *
     * @var array
     */
    public $state = [
        'timezone' => null,
    ];

    /**
     * The saved timezone (from database).
     *
     * @var string|null
     */
    public $savedTimezone = null;

    /**
     * The search query for filtering timezones.
     *
     * @var string
     */
    public $searchQuery = '';

    /**
     * Whether the timezone results dropdown is visible.
     *
     * @var bool
     */
    public $showResults = false;

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        if (!\App\Support\Features::hasTeamTimezoneManagement()) {
            abort(403, 'Team timezone management is not enabled.');
        }

        $this->team = $team;
        $this->savedTimezone = $team->timezone ?? config('app.timezone', 'UTC');
        $this->state['timezone'] = $this->savedTimezone;
        
        // Don't populate search query on mount - let placeholder show instead
        $this->searchQuery = '';
    }

    /**
     * Update the search query to match the selected timezone.
     *
     * @return void
     */
    public function updatedStateTimezone()
    {
        $this->updateSearchQuery();
        $this->showResults = false;
    }

    /**
     * Show results when user starts typing.
     *
     * @return void
     */
    public function updatedSearchQuery()
    {
        // Always show results when there's a search query
        // When searchQuery is empty but showResults is true (from wire:focus), 
        // keep it true to show common timezones
        if (!empty($this->searchQuery)) {
            $this->showResults = true;
        }
        // Don't set to false when empty - let wire:focus/showResults handle visibility
    }

    /**
     * Update search query based on selected timezone.
     *
     * @return void
     */
    protected function updateSearchQuery()
    {
        if ($this->state['timezone']) {
            $parts = explode('/', $this->state['timezone']);
            $location = isset($parts[1]) ? str_replace('_', ' ', implode('/', array_slice($parts, 1))) : str_replace('_', ' ', $this->state['timezone']);
            $this->searchQuery = $location;
        }
    }

    /**
     * Update the team's timezone.
     *
     * @return void
     */
    public function updateTimezone()
    {
        $this->resetErrorBag();

        if (! Gate::check('update', $this->team)) {
            return;
        }

        Validator::make($this->state, [
            'timezone' => ['required', 'string', 'timezone'],
        ])->validateWithBag('updateTimezone');

        $this->team->forceFill([
            'timezone' => $this->state['timezone'],
        ])->save();

        // Update saved timezone after save
        $this->savedTimezone = $this->state['timezone'];
        $this->team = $this->team->fresh();

        $this->dispatch('saved');
    }

    /**
     * Select a timezone from search results.
     *
     * @param string $timezone
     * @return void
     */
    public function selectTimezone($timezone)
    {
        $this->state['timezone'] = $timezone;
        $this->updateSearchQuery();
        $this->showResults = false;
    }

    /**
     * Get filtered timezones based on search query.
     *
     * @return array
     */
    public function getFilteredTimezonesProperty()
    {
        if (empty($this->searchQuery)) {
            // If no search, return current timezone plus major Canadian cities
            $commonTimezones = [
                'UTC',
                'America/Vancouver',      // Pacific Time
                'America/Edmonton',        // Mountain Time
                'America/Winnipeg',        // Central Time
                'America/Toronto',         // Eastern Time
                'America/Halifax',         // Atlantic Time
                'America/St_Johns',        // Newfoundland Time
            ];

            // Add saved timezone if not in common list (show saved, not selected)
            $timezonesToShow = $commonTimezones;
            if ($this->savedTimezone && !in_array($this->savedTimezone, $commonTimezones)) {
                array_unshift($timezonesToShow, $this->savedTimezone);
            }

            $results = [];
            foreach ($timezonesToShow as $tz) {
                if (in_array($tz, timezone_identifiers_list())) {
                    $parts = explode('/', $tz);
                    $location = isset($parts[1]) ? str_replace('_', ' ', implode('/', array_slice($parts, 1))) : str_replace('_', ' ', $tz);
                    $results[] = [
                        'timezone' => $tz,
                        'display' => $location,
                        'region' => $parts[0],
                    ];
                }
            }
            return array_slice($results, 0, 8);
        }

        $searchLower = strtolower($this->searchQuery);
        $timezones = timezone_identifiers_list();
        $matches = [];

        foreach ($timezones as $timezone) {
            $parts = explode('/', $timezone);
            $location = isset($parts[1]) ? str_replace('_', ' ', implode('/', array_slice($parts, 1))) : str_replace('_', ' ', $timezone);
            $locationLower = strtolower($location);
            $timezoneLower = strtolower($timezone);

            // Check if search matches location name or timezone identifier
            if (str_contains($locationLower, $searchLower) || str_contains($timezoneLower, $searchLower)) {
                // Calculate relevance score (exact start match is better)
                $score = 0;
                if (str_starts_with($locationLower, $searchLower)) {
                    $score = 1000;
                } elseif (str_starts_with($timezoneLower, $searchLower)) {
                    $score = 500;
                } else {
                    $score = 100 - strpos($locationLower, $searchLower);
                }

                $matches[] = [
                    'timezone' => $timezone,
                    'display' => $location,
                    'region' => $parts[0],
                    'score' => $score,
                ];
            }
        }

        // Sort by relevance score (higher first), then alphabetically
        // Also prioritize current selection if it matches
        usort($matches, function ($a, $b) use ($matches) {
            // Boost current selection to top
            if ($a['timezone'] === $this->state['timezone'] && $b['timezone'] !== $this->state['timezone']) {
                return -1;
            }
            if ($b['timezone'] === $this->state['timezone'] && $a['timezone'] !== $this->state['timezone']) {
                return 1;
            }
            
            if ($a['score'] !== $b['score']) {
                return $b['score'] - $a['score'];
            }
            return strcmp($a['display'], $b['display']);
        });

        // Return top 8 matches
        return array_slice($matches, 0, 8);
    }

    /**
     * Get the display name for the saved timezone.
     *
     * @return string
     */
    public function getSavedTimezoneDisplayProperty()
    {
        if (!$this->savedTimezone) {
            return '';
        }

        $parts = explode('/', $this->savedTimezone);
        return isset($parts[1]) ? str_replace('_', ' ', implode('/', array_slice($parts, 1))) : str_replace('_', ' ', $this->savedTimezone);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('teams.update-team-timezone-form');
    }
}

