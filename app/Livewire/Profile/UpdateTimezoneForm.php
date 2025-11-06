<?php

namespace App\Livewire\Profile;

use App\Traits\InteractsWithBanner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class UpdateTimezoneForm extends Component
{
    use InteractsWithBanner;

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
     * The detected timezone (from browser/location).
     *
     * @var string|null
     */
    public $detectedTimezone = null;

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
     * Prepare the component.
     *
     * @return void
     */
    public function mount()
    {
        if (!\App\Support\Features::hasUserTimezoneManagement()) {
            abort(403, 'User timezone management is not enabled.');
        }

        $user = Auth::user();
        $this->savedTimezone = $user->timezone ?? config('app.timezone', 'UTC');
        $this->state['timezone'] = $this->savedTimezone;
        
        // Get detected timezone - check session first, then cookie, then request header
        $this->refreshDetectedTimezone();
        
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
        $this->showResults = !empty($this->searchQuery);
    }

    /**
     * Refresh detected timezone when component hydrates (for Livewire updates).
     *
     * @return void
     */
    public function hydrate()
    {
        $this->refreshDetectedTimezone();
    }

    /**
     * Public method to refresh detected timezone (can be called from JavaScript).
     *
     * @return void
     */
    public function refreshDetectedTimezoneAction()
    {
        $this->refreshDetectedTimezone();
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
     * Update the user's timezone.
     *
     * @return void
     */
    public function updateTimezone()
    {
        $this->resetErrorBag();

        Validator::make($this->state, [
            'timezone' => ['required', 'string', 'timezone'],
        ])->validateWithBag('updateTimezone');

        $user = Auth::user();
        $user->forceFill([
            'timezone' => $this->state['timezone'],
        ])->save();

        // Update saved timezone after save
        $this->savedTimezone = $this->state['timezone'];

        // Clear session flags to dismiss the banner since user has updated timezone
        Session::forget('detected_timezone');
        Session::forget('timezone_suggestion_dismissed');

        // Clear detected timezone from component if it matches the saved one
        if ($this->detectedTimezone === $this->savedTimezone) {
            $this->detectedTimezone = null;
        }

        // Dispatch Livewire event to dismiss the timezone suggestion banner
        // JavaScript will bridge this to a browser event for Alpine.js
        $this->dispatch('timezone-updated');

        $this->dispatch('saved');
    }

    /**
     * Update the user's timezone to the detected timezone.
     *
     * @return void
     */
    public function updateToDetectedTimezone()
    {
        if (!$this->detectedTimezone) {
            return;
        }

        $this->resetErrorBag();

        $user = Auth::user();
        $user->forceFill([
            'timezone' => $this->detectedTimezone,
        ])->save();

        // Update saved timezone and state
        $this->savedTimezone = $this->detectedTimezone;
        $this->state['timezone'] = $this->detectedTimezone;
        // Don't update search query - let placeholder show
        $this->searchQuery = '';

        // Clear session flags
        Session::forget('detected_timezone');
        Session::forget('timezone_suggestion_dismissed');

        // Clear detected timezone from component
        $this->detectedTimezone = null;

        // Dispatch Livewire event to dismiss the timezone suggestion banner
        // JavaScript will bridge this to a browser event for Alpine.js
        $this->dispatch('timezone-updated');

        $this->dispatch('saved');
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return Auth::user();
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
     * Refresh the detected timezone from session, cookie, or request.
     *
     * @return void
     */
    public function refreshDetectedTimezone()
    {
        // Check session first (set by middleware)
        $this->detectedTimezone = Session::get('detected_timezone');
        
        // Fallback to cookie if not in session (for display purposes only)
        if (!$this->detectedTimezone && request()) {
            $this->detectedTimezone = request()->cookie('timezone');
        }
        
        // Fallback to request header if available (for Livewire requests)
        if (!$this->detectedTimezone && request() && request()->hasHeader('X-Timezone')) {
            $this->detectedTimezone = request()->header('X-Timezone');
        }
        
        // Validate timezone
        if ($this->detectedTimezone && !in_array($this->detectedTimezone, timezone_identifiers_list(), true)) {
            $this->detectedTimezone = null;
        }
        
        // Note: Do NOT store in session here - let the middleware handle that
        // The middleware properly checks if the timezone differs from user's saved timezone
        // and respects the dismissal flag
    }

    /**
     * Get the display name for the detected timezone.
     *
     * @return string
     */
    public function getDetectedTimezoneDisplayProperty()
    {
        if (!$this->detectedTimezone) {
            return '';
        }

        $parts = explode('/', $this->detectedTimezone);
        return isset($parts[1]) ? str_replace('_', ' ', implode('/', array_slice($parts, 1))) : str_replace('_', ' ', $this->detectedTimezone);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('profile.update-timezone-form');
    }
}

