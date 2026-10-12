<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class YouVersionClient
{
    private const CATALOG_TTL_SECONDS = 86400;

    private const MAX_VERSION_PAGES = 10;

    public function isConfigured(): bool
    {
        return filled(config('services.youversion.key'));
    }

    /**
     * The Bible versions the app key is licensed to read, or null if the
     * API can't be reached.
     *
     * @return array<int, array{id: int, abbreviation: string, title: string}>|null
     */
    public function versions(): ?array
    {
        return $this->remember('versions', function (): ?array {
            $versions = [];
            $pageToken = null;

            for ($page = 0; $page < self::MAX_VERSION_PAGES; $page++) {
                // The API wants repeated `language_ranges[]=en` pairs, not the
                // indexed `language_ranges[0]=en` that a query array produces.
                $query = collect(config('services.youversion.languages'))
                    ->map(fn (string $language) => 'language_ranges[]='.urlencode($language))
                    ->when($pageToken, fn ($pairs) => $pairs->push('page_token='.urlencode($pageToken)))
                    ->implode('&');

                $json = $this->get("bibles?$query");

                if ($json === null) {
                    return null;
                }

                foreach ($json['data'] ?? [] as $version) {
                    $versions[] = [
                        'id' => (int) $version['id'],
                        'abbreviation' => (string) ($version['localized_abbreviation'] ?? $version['abbreviation'] ?? ''),
                        'title' => (string) ($version['localized_title'] ?? $version['title'] ?? ''),
                    ];
                }

                $pageToken = $json['next_page_token'] ?? null;

                if (! $pageToken) {
                    break;
                }
            }

            return $versions;
        });
    }

    /**
     * One version's abbreviation and copyright notice.
     *
     * @return array{abbreviation: string, copyright: string|null}|null
     */
    public function version(int $versionId): ?array
    {
        return $this->remember("version.$versionId", function () use ($versionId): ?array {
            $json = $this->get("bibles/$versionId");

            if ($json === null) {
                return null;
            }

            return [
                'abbreviation' => (string) ($json['localized_abbreviation'] ?? $json['abbreviation'] ?? ''),
                'copyright' => filled($json['copyright'] ?? null) ? (string) $json['copyright'] : null,
            ];
        });
    }

    /**
     * A version's books, each with the verse count of every chapter (the
     * array index is the chapter number minus one). Reduced from the API's
     * full verse listing so the cached copy stays small.
     *
     * @return array<int, array{id: string, title: string, chapters: array<int, int>}>|null
     */
    public function books(int $versionId): ?array
    {
        return $this->remember("books.$versionId", function () use ($versionId): ?array {
            $json = $this->get("bibles/$versionId/books");

            if ($json === null) {
                return null;
            }

            return collect($json['data'] ?? [])
                ->map(fn (array $book) => [
                    'id' => (string) $book['id'],
                    'title' => (string) $book['title'],
                    'chapters' => collect($book['chapters'] ?? [])
                        ->map(fn (array $chapter) => count($chapter['verses'] ?? []))
                        ->values()
                        ->all(),
                ])
                ->values()
                ->all();
        });
    }

    /**
     * A passage as plain text, e.g. "JHN.3.16" or "JHN.3.16-18".
     *
     * @return array{text: string, reference: string}|null
     */
    public function passage(int $versionId, string $usfm): ?array
    {
        $json = $this->get("bibles/$versionId/passages/$usfm", ['format' => 'text']);

        $text = $json['content'] ?? null;

        if (! is_string($text) || trim($text) === '') {
            return null;
        }

        return [
            'text' => trim($text),
            'reference' => (string) ($json['reference'] ?? ''),
        ];
    }

    /**
     * Cache only successful responses, so an outage isn't remembered.
     *
     * @template T
     *
     * @param  callable(): ?T  $resolve
     * @return T|null
     */
    private function remember(string $key, callable $resolve): mixed
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $key = "youversion.$key";

        if (($cached = Cache::get($key)) !== null) {
            return $cached;
        }

        $value = $resolve();

        if ($value !== null) {
            Cache::put($key, $value, self::CATALOG_TTL_SECONDS);
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|null
     */
    private function get(string $path, array $query = []): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $query === [] ? $this->request()->get($path) : $this->request()->get($path, $query);
        } catch (ConnectionException) {
            return null;
        }

        return $response->successful() ? $response->json() : null;
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.youversion.base_url'), '/').'/')
            ->withHeaders(['X-YVP-App-Key' => (string) config('services.youversion.key')])
            ->acceptJson()
            ->timeout(8);
    }
}
