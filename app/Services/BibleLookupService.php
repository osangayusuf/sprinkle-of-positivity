<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class BibleLookupService
{
    public function __construct(private YouVersionClient $youVersion) {}

    /**
     * Look up a passage to prefill a manager/admin's verse form. Tries the
     * chosen YouVersion version first; if that isn't possible (no app key,
     * API down, version not licensed) it falls back to the public-domain
     * bible-api.com using the human-readable reference. The fallback text is
     * a different translation, so it carries no translation or copyright.
     * Returns null on total failure — this only ever prefills an editable
     * field, never a hard dependency.
     *
     * @param  array{version_id: int, book: string, chapter: int, verse_start: int, verse_end: int|null}|null  $selection
     * @return array{text: string, reference: string, translation: string|null, copyright: string|null, source: string}|null
     */
    public function lookup(string $reference, ?array $selection = null): ?array
    {
        if ($selection !== null && ($found = $this->fromYouVersion($selection)) !== null) {
            return $found;
        }

        $text = $this->fromBibleApi($reference);

        return $text === null ? null : [
            'text' => $text,
            'reference' => $reference,
            'translation' => null,
            'copyright' => null,
            'source' => 'fallback',
        ];
    }

    /**
     * @param  array{version_id: int, book: string, chapter: int, verse_start: int, verse_end: int|null}  $selection
     * @return array{text: string, reference: string, translation: string|null, copyright: string|null, source: string}|null
     */
    private function fromYouVersion(array $selection): ?array
    {
        $usfm = "{$selection['book']}.{$selection['chapter']}.{$selection['verse_start']}";

        if ($selection['verse_end'] !== null && $selection['verse_end'] > $selection['verse_start']) {
            $usfm .= "-{$selection['verse_end']}";
        }

        $passage = $this->youVersion->passage($selection['version_id'], $usfm);

        if ($passage === null) {
            return null;
        }

        $version = $this->youVersion->version($selection['version_id']);

        return [
            'text' => $passage['text'],
            'reference' => $passage['reference'],
            'translation' => $version['abbreviation'] ?? null,
            'copyright' => $version['copyright'] ?? null,
            'source' => 'youversion',
        ];
    }

    private function fromBibleApi(string $reference): ?string
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
