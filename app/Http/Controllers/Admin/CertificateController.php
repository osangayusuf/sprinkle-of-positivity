<?php

namespace App\Http\Controllers\Admin;

use App\Actions\IssueCertificate;
use App\Actions\RestoreCertificate;
use App\Actions\RevokeCertificate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IssueCertificateRequest;
use App\Http\Requests\Admin\RevokeCertificateRequest;
use App\Http\Requests\Admin\UpdateCertificateNameRequest;
use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use App\Models\Group;
use App\Models\User;
use App\Services\ChallengeProgress;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    /**
     * Every group that runs a challenge, as the entry point to its certificates.
     */
    public function index(): Response
    {
        $this->authorize('access-admin');

        $groups = Group::query()
            ->whereNotNull('starts_on')
            ->whereNotNull('duration_days')
            ->withCount(['approvedMembers', 'certificates'])
            ->orderByDesc('starts_on')
            ->get()
            ->map(fn (Group $group) => [
                'id' => $group->id,
                'name' => $group->name,
                'duration_days' => $group->duration_days,
                'starts_on' => $group->starts_on->toDateString(),
                'ends_on' => $group->challengeEndsOn()?->toDateString(),
                'has_ended' => $group->challengeHasEnded(),
                'members_count' => $group->approved_members_count,
                'certificates_count' => $group->certificates_count,
            ]);

        return Inertia::render('admin/certificates/index', ['groups' => $groups]);
    }

    /**
     * One group's members with their progress and certificate status.
     */
    public function show(Group $group, ChallengeProgress $challengeProgress): Response
    {
        $this->authorize('access-admin');

        $members = $group->approvedMembers()->orderBy('name')->get();
        $progress = $challengeProgress->forMembers($group, $members->pluck('id')->all());
        $certificates = $group->certificates()->get()->keyBy('user_id');

        $rows = $members->map(function (User $member) use ($progress, $certificates) {
            $memberProgress = $progress[$member->id] ?? null;
            $certificate = $certificates->get($member->id);

            return [
                'user' => ['id' => $member->id, 'name' => $member->name],
                'completed_count' => $memberProgress['completed_count'] ?? 0,
                'required_count' => count($memberProgress['required_days'] ?? []),
                'current_streak' => $memberProgress['current_streak'] ?? 0,
                'longest_streak' => $memberProgress['longest_streak'] ?? 0,
                'eligible' => $memberProgress['eligible'] ?? false,
                'certificate' => $certificate ? new CertificateResource($certificate) : null,
            ];
        });

        return Inertia::render('admin/certificates/show', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'duration_days' => $group->duration_days,
                'has_ended' => $group->challengeHasEnded(),
            ],
            'members' => $rows,
        ]);
    }

    /**
     * Issue a certificate by hand, overriding the system's eligibility check.
     */
    public function issue(IssueCertificateRequest $request, Group $group, User $user, IssueCertificate $action): RedirectResponse
    {
        abort_unless($group->approvedMembers()->whereKey($user->id)->exists(), 404);

        $existing = $group->certificates()->where('user_id', $user->id)->first();

        if ($existing) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('This member already has a certificate. Restore it instead.')]);

            return back();
        }

        $action->handle($group, $user, $request->user(), $request->string('reason')->value());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Certificate issued.')]);

        return back();
    }

    /**
     * Withdraw a certificate the system issued in error.
     */
    public function revoke(RevokeCertificateRequest $request, Certificate $certificate, RevokeCertificate $action): RedirectResponse
    {
        $action->handle($certificate, $request->user(), $request->string('reason')->value());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Certificate revoked.')]);

        return back();
    }

    /**
     * Reinstate a revoked certificate.
     */
    public function restore(Certificate $certificate, RestoreCertificate $action): RedirectResponse
    {
        $this->authorize('access-admin');

        $action->handle($certificate);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Certificate restored.')]);

        return back();
    }

    /**
     * Correct the name printed on a certificate.
     */
    public function updateName(UpdateCertificateNameRequest $request, Certificate $certificate): RedirectResponse
    {
        $certificate->update(['recipient_name' => $request->string('recipient_name')->value()]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Name updated.')]);

        return back();
    }
}
