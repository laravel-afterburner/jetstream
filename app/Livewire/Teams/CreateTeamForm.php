<?php

namespace App\Livewire\Teams;

use Illuminate\Support\Facades\Auth;
use App\Actions\Afterburner\CreateTeam;
use App\Traits\RedirectsActions;
use Livewire\Component;

class CreateTeamForm extends Component
{
    use RedirectsActions;

    /**
     * The component's state.
     *
     * @var array
     */
    public $state = [
        'name' => '',
        'timezone' => null,
    ];

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
        if (!\App\Support\Features::hasTeamTimezoneManagement()) {
            // Don't abort, just don't show timezone field
            return;
        }
        
        // Default to user's timezone if available
        $user = Auth::user();
        if ($user && $user->timezone) {
            $this->state['timezone'] = $user->timezone;
        }
    }

    /**
     * Create a new team.
     *
     * @param  \App\Actions\Afterburner\CreateTeam  $creator
     * @return mixed
     */
    public function createTeam(CreateTeam $creator)
    {
        $this->resetErrorBag();

        $team = $creator->create(Auth::user(), $this->state);

        // Flash success banner message for the redirect
        session()->flash('flash', [
            'bannerStyle' => 'success',
            'banner' => __('The :entity ":name" has been created.', [
                'entity' => config('afterburner.entity_label'),
                'name' => $team->name,
            ]),
        ]);

        return $this->redirectPath($creator);
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
            // If no search, return major Canadian cities
            $commonTimezones = [
                'UTC',
                'America/Vancouver',      // Pacific Time
                'America/Edmonton',        // Mountain Time
                'America/Winnipeg',        // Central Time
                'America/Toronto',         // Eastern Time
                'America/Halifax',         // Atlantic Time
                'America/St_Johns',        // Newfoundland Time
            ];

            $results = [];
            foreach ($commonTimezones as $tz) {
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
        usort($matches, function ($a, $b) {
            if ($a['score'] !== $b['score']) {
                return $b['score'] - $a['score'];
            }
            return strcmp($a['display'], $b['display']);
        });

        // Return top 8 matches
        return array_slice($matches, 0, 8);
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
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('teams.create-team-form');
    }
}
