<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class BibleLookupService
{
    /**
     * Look up a verse's text by reference (e.g. "John 3:16") via the free,
     * public-domain bible-api.com. Returns null on any failure — including
     * the request never reaching the network at all — since this is only
     * ever used to prefill an editable field, never a hard dependency.
     */
    public function fetch(string $reference): ?string
    {
        try {
            $response = Http::timeout(5)->get('https://bible-api.com/'.urlencode($reference));
        } catch (ConnectionException) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $text = $response->json('text');

        return is_string($text) && $text !== '' ? trim($text) : null;
    }
}
