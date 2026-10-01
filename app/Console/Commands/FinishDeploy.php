<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('deploy:finish')]
#[Description('Run migrations and rebuild caches once after each FTP deploy')]
class FinishDeploy extends Command
{
    /**
     * The FTP deploy can only copy files, so the GitHub workflow uploads the
     * released commit SHA to `.deploy/revision` as its very last step. The
     * scheduler runs this every minute; it does the post-deploy work once
     * per new revision and records it in storage, which deploys never touch.
     */
    public function handle(): int
    {
        $revisionFile = base_path('.deploy/revision');
        $finishedFile = storage_path('framework/deployed-revision');

        if (! File::exists($revisionFile)) {
            return self::SUCCESS;
        }

        $revision = trim(File::get($revisionFile));

        if ($revision === '' || (File::exists($finishedFile) && trim(File::get($finishedFile)) === $revision)) {
            return self::SUCCESS;
        }

        $this->info("Finishing deploy of {$revision}.");

        $this->call('migrate', ['--force' => true]);
        $this->call('optimize:clear');
        $this->call('optimize');

        if (! File::exists(public_path('storage'))) {
            $this->call('storage:link');
        }

        File::put($finishedFile, $revision);

        return self::SUCCESS;
    }
}
