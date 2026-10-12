<?php

namespace App\Http\Controllers;

use App\Services\BibleLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BibleVerseLookupController extends Controller
{
    /**
     * Look up a passage's text to prefill (not replace) a manager/admin's
     * verse form. The picker sends a structured selection plus its
     * human-readable reference; a bare `reference` still works and uses the
     * public-domain fallback only.
     */
    public function __invoke(Request $request, BibleLookupService $lookup): JsonResponse
    {
        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:255'],
            'version_id' => ['nullable', 'integer', 'min:1', 'required_with:book'],
            'book' => ['nullable', 'string', 'regex:/^[1-3]?[A-Z]{2,3}$/', 'required_with:version_id'],
            'chapter' => ['nullable', 'integer', 'min:1', 'required_with:book'],
            'verse_start' => ['nullable', 'integer', 'min:1', 'required_with:book'],
            'verse_end' => ['nullable', 'integer', 'min:1', 'gte:verse_start'],
        ]);

        $selection = isset($validated['book']) ? [
            'version_id' => (int) $validated['version_id'],
            'book' => $validated['book'],
            'chapter' => (int) $validated['chapter'],
            'verse_start' => (int) $validated['verse_start'],
            'verse_end' => isset($validated['verse_end']) ? (int) $validated['verse_end'] : null,
        ] : null;

        $result = $lookup->lookup($validated['reference'], $selection);

        return response()->json([
            'text' => $result['text'] ?? null,
            'reference' => $result['reference'] ?? null,
            'translation' => $result['translation'] ?? null,
            'copyright' => $result['copyright'] ?? null,
            'source' => $result['source'] ?? null,
        ]);
    }
}
