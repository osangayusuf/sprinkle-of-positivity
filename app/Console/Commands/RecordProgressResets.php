<?php

namespace App\Console\Commands;

use App\Enums\GroupMembershipRole;
use App\Models\Group;
use App\Models\ProgressReset;
use App\Notifications\ChallengeAlert;
use App\Services\ChallengeProgress;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('progress:record-resets')]
#[Description('Record members who missed yesterday and notify them and their accountability partner')]
class RecordProgressResets extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ChallengeProgress $progress): int
    {
        $recorded = 0;
        $yesterday = today()->subDay();

        Group::query()
            ->whereNotNull('starts_on')
            ->whereNotNull('duration_days')
            ->whereDate('starts_on', '<=', $yesterday)
            ->each(function (Group $group) use ($progress, $yesterday, &$recorded) {
                $members = $group->approvedMembers()
                    ->wherePivot('role', GroupMembershipRole::Member->value)
                    ->get();

                $results = $progress->forMembers($group, $members->pluck('id')->all());

                foreach ($members as $member) {
                    foreach ($results[$member->id]['reset_days'] ?? [] as $day) {
                        $missedOn = $group->starts_on->copy()->startOfDay()->addDays($day - 1);

                        $reset = ProgressReset::query()->firstOrCreate([
                            'user_id' => $member->id,
                            'group_id' => $group->id,
                            'missed_on' => $missedOn->toDateString(),
                        ]);

                        if (! $reset->wasRecentlyCreated || ! $missedOn->isSameDay($yesterday)) {
                            continue;
                        }

                        $recorded++;

                        $member->notify(new ChallengeAlert(
                            __('Your progress was reset'),
                            __('You missed :date in :group, so your count starts again from day 1. Keep going.', ['date' => $missedOn->format('M j'), 'group' => $group->name]),
                        ));

                        $member->partnerIn($group)?->notify(new ChallengeAlert(
                            __(':name\'s progress was reset', ['name' => $member->name]),
                            __(':name missed :date in :group and starts again from day 1.', ['name' => $member->name, 'date' => $missedOn->format('M j'), 'group' => $group->name]),
                        ));
                    }
                }
            });

        $this->info("Recorded {$recorded} reset(s).");

        return self::SUCCESS;
    }
}
