<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * Vendor publish tags that copy package views into resources/views/vendor/.
     *
     * @var list<string>
     */
    public const VIEW_ASSET_TAGS = [
        'afterburner-documents-assets',
        'afterburner-communications-assets',
        'afterburner-meetings-assets',
        'afterburner-voting-assets',
        'afterburner-subscriptions-assets',
        'afterburner-playbook-assets',
    ];

    protected $signature = 'afterburner:publish
                            {--tag=* : The view asset tag(s) to publish (default: all Afterburner packages)}
                            {--force : Overwrite existing files}';

    protected $description = 'Publish Afterburner package views for intentional host customizations';

    public function handle(): int
    {
        $tags = $this->option('tag') ?: self::VIEW_ASSET_TAGS;

        $this->info('Publishing Afterburner vendor views...');
        $this->comment('Publish only the files you plan to customize. Unchanged copies override package views and create drift — delete them after editing.');

        foreach ($tags as $tag) {
            $this->components->task($tag, function () use ($tag) {
                $this->callSilently('vendor:publish', array_filter([
                    '--tag' => $tag,
                    '--force' => $this->option('force') ? true : null,
                ]));

                return true;
            });
        }

        $this->newLine();
        $this->comment('Customized files under resources/views/vendor/afterburner-* override package views when present.');
        $this->comment('Delete unchanged copies — path-repo package source is the default.');
        $this->comment('Run `php artisan afterburner:audit-integration` after publishing.');

        return Command::SUCCESS;
    }
}
