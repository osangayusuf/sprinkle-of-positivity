<?php

namespace App\Http\Controllers;

use App\Services\YouVersionClient;
use Illuminate\Http\JsonResponse;

class BibleCatalogController extends Controller
{
    /**
     * The Bible versions the verse picker offers. Empty when YouVersion is
     * unavailable, which makes the picker fall back to free-text entry.
     */
    public function versions(YouVersionClient $youVersion): JsonResponse
    {
        return response()->json([
            'versions' => $youVersion->versions() ?? [],
            'default_version_id' => config('services.youversion.default_version'),
        ]);
    }

    /**
     * A version's books, each with the verse count of every chapter.
     */
    public function books(YouVersionClient $youVersion, int $version): JsonResponse
    {
        return response()->json([
            'books' => $youVersion->books($version) ?? [],
        ]);
    }
}
