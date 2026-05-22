# Navigation Menu Registration

Packages can register navigation menu items by calling `\App\Support\Navigation::register()` in their service provider's `boot()` method.

## Example

```php
use App\Support\Navigation;

public function boot(): void
{
    Navigation::register([
        'label' => 'Documents',
        'route' => 'documents.index',
        'route_params' => function() {
            return ['team' => auth()->user()?->currentTeam?->id];
        },
        'icon' => 'document-text',
        'order' => 20,
        'permission' => function ($user) {
            return $user && $user->currentTeam;
        },
        'active' => function () {
            return request()->routeIs('documents.*') ||
                   request()->routeIs('folders.*');
        },
        'badge' => function() {
            // Optional: Return count for badge
            return auth()->user()?->currentTeam?->unreadDocumentsCount() ?? 0;
        },
    ]);
}
```

## Parameters

- **`label`** (required): Display text for the menu item
- **`route`** (required): Route name (without parameters)
- **`route_params`** (optional): Array or callable returning route parameters
- **`icon`** (optional): Heroicon name (without the `heroicon-` prefix)
- **`order`** (optional): Sort order (lower numbers appear first, default: 100)
- **`permission`** (optional): Callable that receives user, returns boolean
- **`active`** (optional): Callable that returns boolean for active state
- **`badge`** (optional): Integer or callable returning count for badge display

## Notes

- **Route params**: Use a callable if you need dynamic values (like team ID) that change per request
- **Icons**: Use Heroicon names (e.g., 'document-text', 'folder', 'credit-card')
- **Active state**: If not provided, defaults to checking if current route matches `{route}.*`
- **Permissions**: Items are automatically filtered based on permission callbacks
- **Order**: Lower numbers appear first (Dashboard is typically 10, so use 20+ for packages)

## Testing

After implementation, test by temporarily registering a test item in `AppServiceProvider`:

```php
use App\Support\Navigation;

public function boot(): void
{
    Navigation::register([
        'label' => 'Test Menu',
        'route' => 'dashboard',
        'order' => 10,
    ]);
}
```

You should see "Test Menu" appear in the navigation between Dashboard and other items.

