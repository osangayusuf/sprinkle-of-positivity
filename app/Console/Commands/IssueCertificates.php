<?php

namespace App\Console\Commands;

use App\Actions\IssueCertificate;
use App\Models\Group;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('certificates:issue')]
#[Description('Issue certificates to members who completed every day of a finished challenge')]
class IssueCertificates extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(IssueCertificate $issue): int
    {
        $issued = 0;

        Group::query()
            ->whereNotNull('starts_on')
            ->whereNotNull('duration_days')
            ->each(function (Group $group) use ($issue, &$issued) {
                if (! $group->challengeHasEnded()) {
                    return;
                }

                foreach ($group->approvedMembers()->get() as $member) {
                    $existing = $group->certificates()->where('user_id', $member->id)->exists();

                    if (! $existing && $issue->handle($group, $member)) {
                        $issued++;
                    }
                }
            });

        $this->info("Issued {$issued} certificate(s).");

        return self::SUCCESS;
    }
}
