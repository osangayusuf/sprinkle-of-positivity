<?php

namespace App\Console\Commands;

use App\Enums\GroupMembershipRole;
use App\Models\Group;
use App\Models\User;
use App\Notifications\ChallengeAlert;
use App\Services\ChallengeProgress;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reminders:send')]
#[Description('Remind participants who have not posted today, and tell their partners who is behind')]
class SendDailyReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ChallengeProgress $progress): int
    {
        $reminded = 0;
        $behindByPartner = [];

        Group::query()
            ->whereNotNull('starts_on')
            ->whereNotNull('duration_days')
            ->whereDate('starts_on', '<=', today())
            ->whereHas('verses', fn ($verses) => $verses->whereDate('date', today()))
            ->each(function (Group $group) use ($progress, &$reminded, &$behindByPartner) {
                $memberships = $group->memberships()
                    ->where('role', GroupMembershipRole::Member->value)
                    ->where('status', 'approved')
                    ->with('user')
                    ->get();

                $results = $progress->forMembers($group, $memberships->pluck('user_id')->all());

                foreach ($memberships as $membership) {
                    $result = $results[$membership->user_id] ?? null;

                    if (! $result || $result['completed_today']) {
                        continue;
                    }

                    $membership->user->notify(new ChallengeAlert(
                        __('Don\'t miss today'),
                        $result['current_streak'] > 0
                            ? __('Post today\'s insight in :group before midnight to keep your :days-day run going.', ['group' => $group->name, 'days' => $result['current_streak']])
                            : __('Post today\'s insight in :group before midnight.', ['group' => $group->name]),
                    ));

                    $reminded++;

                    if ($membership->partner_id) {
                        $behindByPartner[$membership->partner_id] = ($behindByPartner[$membership->partner_id] ?? 0) + 1;
                    }
                }
            });

        User::query()->whereIn('id', array_keys($behindByPartner))->each(function (User $partner) use ($behindByPartner) {
            $count = $behindByPartner[$partner->id];

            $partner->notify(new ChallengeAlert(
                __('Participants yet to post'),
                trans_choice(':count participant has not posted today.|:count participants have not posted today.', $count, ['count' => $count]),
            ));
        });

        $this->info("Reminded {$reminded} participant(s).");

        return self::SUCCESS;
    }
}
