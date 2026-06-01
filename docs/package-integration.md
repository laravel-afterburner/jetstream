# Afterburner package integration

Host applications consume Afterburner packages via Composer (often as local path repos). Package UI must flow from the package source — not parallel copies in the host app.

## Rules

### 1. Package owns UI by default

Use package Livewire components and `afterburner-{package}::` views from the path repo / vendor package.

**Do not** create `resources/views/{feature}/livewire/*.blade.php` copies of package views.

### 2. Customize through extension points

Application-specific behavior belongs in:

| Extension point | Examples |
|---|---|
| `config/afterburner-*.php` | Contract bindings, reference providers, feature flags |
| `App\Policies\*` | Package model policies |
| `App\Support\*` resolvers/providers | Host-specific reference data or eligibility rules |
| Thin Livewire subclasses | Only when adding behavior; still render package namespaced views |
| App-only views | Host-specific exports or pages, not duplicated Livewire UIs |

### 3. Published views (Option A — default)

- **Do not** bulk-publish package views. Path-repo changes apply immediately from `vendor/` / symlink.
- Publish **only** files you intentionally customize into `resources/views/vendor/afterburner-{package}/`.
- `AfterburnerPublishedViews` registers those paths in `AfterburnerServiceProvider` so they override package defaults.
- Delete unchanged published copies; Laravel loads `resources/views/vendor/{namespace}` ahead of package source when that directory exists.

To publish views explicitly:

```bash
php artisan afterburner:publish --tag=afterburner-meetings-assets
# Edit only the files you need under resources/views/vendor/afterburner-meetings/
```

Install publishes **config only** by default:

```bash
php artisan afterburner:install          # config + migrate + seed
php artisan afterburner:install --with-views   # also copy view trees (usually avoid)
```

### 4. Livewire component registration

Do **not** re-register package Livewire components unless the subclass adds real host behavior and still uses package views.

## Audit

```bash
php artisan afterburner:audit-integration
```

Fails when:

- An `App\Livewire\*` class extends `Afterburner\*` and overrides `render()` without calling `parent::render()` or using an `afterburner-*::` package view
- Blade files exist under forbidden app paths like `resources/views/meetings/livewire/`
- Published vendor views exist but are not registered
- Published vendor views are byte-identical to the package (unchanged bulk copies)

## Syncing local packages

When package repos change:

```bash
composer update laravel-afterburner/meetings ...
php artisan migrate --force
php artisan optimize:clear
php artisan queue:restart
```

Do **not** run `afterburner:publish --force` during sync. Republish views **only** when maintaining intentional customized copies. Hard-refresh the browser after UI changes.
