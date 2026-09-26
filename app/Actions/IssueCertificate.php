<?php

namespace App\Actions;

use App\Models\Certificate;
use App\Models\Group;
use App\Models\User;
use App\Notifications\CertificateIssued;
use App\Services\ChallengeProgress;

class IssueCertificate
{
    public function __construct(private ChallengeProgress $progress) {}

    /**
     * Issue a member's certificate for a group's challenge. Idempotent: an
     * existing certificate, including a revoked one, is returned untouched.
     *
     * Without `$issuedBy` the system only issues when the member completed
     * every required day of a finished challenge. An admin passes themself
     * and a reason to override that check.
     */
    public function handle(Group $group, User $member, ?User $issuedBy = null, ?string $overrideReason = null): ?Certificate
    {
        $existing = Certificate::query()
            ->where('group_id', $group->id)
            ->where('user_id', $member->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        if ($issuedBy === null) {
            $progress = $this->progress->forMember($group, $member);

            if (! $progress || ! $progress['eligible']) {
                return null;
            }
        }

        $certificate = new Certificate(['recipient_name' => $member->name]);
        $certificate->user_id = $member->id;
        $certificate->group_id = $group->id;
        $certificate->code = Certificate::generateCode();
        $certificate->duration_days = (int) $group->duration_days;
        $certificate->year = (int) ($group->challengeEndsOn() ?? now())->format('Y');
        $certificate->issued_at = now();
        $certificate->issued_by = $issuedBy?->id;
        $certificate->override_reason = $overrideReason;
        $certificate->save();

        $member->notify(new CertificateIssued($certificate));

        return $certificate;
    }
}
