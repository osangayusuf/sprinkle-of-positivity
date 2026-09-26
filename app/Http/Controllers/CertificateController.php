<?php

namespace App\Http\Controllers;

use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    /**
     * The signed-in member's own certificates.
     */
    public function index(Request $request): Response
    {
        $certificates = Certificate::query()
            ->with('group')
            ->where('user_id', $request->user()->id)
            ->whereNull('revoked_at')
            ->latest('issued_at')
            ->get();

        return Inertia::render('certificates/index', [
            'certificates' => CertificateResource::collection($certificates),
        ]);
    }

    /**
     * A single certificate. Public by its unguessable code so a member can
     * share the link; it doubles as verification. A revoked certificate
     * shows only that it is no longer valid.
     */
    public function show(Certificate $certificate): Response
    {
        return Inertia::render('certificates/show', [
            'certificate' => $certificate->isRevoked()
                ? ['revoked' => true]
                : new CertificateResource($certificate),
            'programme' => config('certificates.programme'),
            'signer' => [
                'name' => config('certificates.signer_name'),
                'title' => config('certificates.signer_title'),
                'signature_url' => config('certificates.signature_path'),
            ],
        ]);
    }
}
