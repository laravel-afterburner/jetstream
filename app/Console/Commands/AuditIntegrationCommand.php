<?php

namespace App\Console\Commands;

use App\Support\AfterburnerPublishedViews;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

class AuditIntegrationCommand extends Command
{
    protected $signature = 'afterburner:audit-integration';

    protected $description = 'Fail when Afterburner package integration violates host application conventions';

    public function handle(): int
    {
        $failures = [];

        $failures = array_merge($failures, $this->auditLivewireRenderOverrides());
        $failures = array_merge($failures, $this->auditAppLevelViewForks());
        $failures = array_merge($failures, $this->auditPublishedViewRegistration());
        $failures = array_merge($failures, $this->auditUnchangedPublishedCopies());

        if ($failures === []) {
            $this->info('Afterburner integration audit passed.');

            return Command::SUCCESS;
        }

        $this->error('Afterburner integration audit failed:');

        foreach ($failures as $failure) {
            $this->line(" - {$failure}");
        }

        return Command::FAILURE;
    }

    /**
     * @return list<string>
     */
    protected function auditLivewireRenderOverrides(): array
    {
        $failures = [];
        $livewirePath = app_path('Livewire');

        if (! is_dir($livewirePath)) {
            return [];
        }

        foreach (Finder::create()->files()->in($livewirePath)->name('*.php') as $file) {
            $class = 'App\\Livewire\\'.str_replace(
                ['/', '.php'],
                ['\\', ''],
                $file->getRelativePathname(),
            );

            if (! class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if ($reflection->isAbstract() || ! $reflection->isSubclassOf(\Livewire\Component::class)) {
                continue;
            }

            $parent = $reflection->getParentClass();

            if ($parent === false || ! str_starts_with($parent->getName(), 'Afterburner\\')) {
                continue;
            }

            if (! $reflection->hasMethod('render')) {
                continue;
            }

            $render = $reflection->getMethod('render');

            if ($render->getDeclaringClass()->getName() !== $class) {
                continue;
            }

            $source = File::get($reflection->getFileName());

            if (! str_contains($source, 'parent::render(')
                && ! preg_match("/view\(['\"]afterburner-[^'\"]+::/", $source)) {
                $failures[] = "{$class} overrides render() without calling parent::render() or using an afterburner-*:: package view.";
            }
        }

        return $failures;
    }

    /**
     * @return list<string>
     */
    protected function auditAppLevelViewForks(): array
    {
        $failures = [];
        $forbiddenRoots = [
            'meetings/livewire',
            'ballots/livewire',
            'documents/livewire',
            'discussions/livewire',
            'subscriptions/livewire',
            'playbook/livewire',
        ];

        foreach ($forbiddenRoots as $root) {
            $path = resource_path('views/'.$root);

            if (! is_dir($path)) {
                continue;
            }

            foreach (Finder::create()->files()->in($path)->name('*.blade.php') as $file) {
                $failures[] = "App-level package view fork: resources/views/{$root}/{$file->getRelativePathname()}";
            }
        }

        return $failures;
    }

    /**
     * @return list<string>
     */
    protected function auditPublishedViewRegistration(): array
    {
        $failures = [];
        $registered = AfterburnerPublishedViews::customizedNamespaces();

        foreach (AfterburnerPublishedViews::PACKAGE_NAMESPACES as $namespace) {
            $path = resource_path('views/vendor/'.$namespace);

            if (! AfterburnerPublishedViews::hasCustomizedViews($path)) {
                continue;
            }

            if (! in_array($namespace, $registered, true)) {
                $failures[] = "Published views exist for {$namespace} but AfterburnerPublishedViews does not register them.";
            }
        }

        return $failures;
    }

    /**
     * @return list<string>
     */
    protected function auditUnchangedPublishedCopies(): array
    {
        $failures = [];

        foreach (AfterburnerPublishedViews::PACKAGE_NAMESPACES as $namespace) {
            $unchanged = AfterburnerPublishedViews::unchangedPublishedFiles($namespace);

            if ($unchanged === []) {
                continue;
            }

            $count = count($unchanged);
            $failures[] = "{$namespace} has {$count} unchanged published view(s) identical to the package (for example {$unchanged[0]}). Delete them and load from vendor/laravel-afterburner instead.";
        }

        return $failures;
    }
}
