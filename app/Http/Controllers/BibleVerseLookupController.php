<?php

namespace App\Http\Controllers;

use App\Services\BibleLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BibleVerseLookupController extends Controller
{
    /**
     * Look up a verse's text by reference, to prefill (not replace) a
     * manager/admin's verse form.
     */
    public function __invoke(Request $request, BibleLookupService $lookup): JsonResponse
    {
        $request->validate([
            'reference' => ['required', 'string', 'max:255'],
        ]);

        return response()->json([
            'text' => $lookup->fetch($request->string('reference')->value()),
        ]);
    }
}
