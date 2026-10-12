<?php

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['services.youversion.key' => 'test-key']);
});

function fakeYouVersion(): void
{
    Http::fake([
        'api.youversion.com/v1/bibles/3034/passages/*' => Http::response([
            'id' => 'JHN.3.16-18',
            'content' => "For God so loved the world...\n",
            'reference' => 'John 3:16-18',
        ]),
        'api.youversion.com/v1/bibles/3034' => Http::response([
            'id' => 3034,
            'abbreviation' => 'BSB',
            'localized_abbreviation' => 'BSB',
            'copyright' => 'Public Domain',
        ]),
    ]);
}

test('it returns the passage, translation and copyright from YouVersion', function () {
    fakeYouVersion();

    $this->actingAs(User::factory()->create());

    $response = $this->get(route('bible-verse-lookup', [
        'reference' => 'John 3:16-18',
        'version_id' => 3034,
        'book' => 'JHN',
        'chapter' => 3,
        'verse_start' => 16,
        'verse_end' => 18,
    ]));

    $response->assertOk();
    $response->assertJson([
        'text' => 'For God so loved the world...',
        'reference' => 'John 3:16-18',
        'translation' => 'BSB',
        'copyright' => 'Public Domain',
        'source' => 'youversion',
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/bibles/3034/passages/JHN.3.16-18')
        && $request['format'] === 'text'
        && $request->header('X-YVP-App-Key') === ['test-key']);
});

test('a single verse is requested without a range', function () {
    fakeYouVersion();

    $this->actingAs(User::factory()->create());

    $this->get(route('bible-verse-lookup', [
        'reference' => 'John 3:16',
        'version_id' => 3034,
        'book' => 'JHN',
        'chapter' => 3,
        'verse_start' => 16,
    ]))->assertOk();

    Http::assertSent(fn ($request) => str_ends_with(parse_url($request->url(), PHP_URL_PATH), '/passages/JHN.3.16'));
});

test('it falls back to the public-domain source without a translation when YouVersion fails', function () {
    Http::fake([
        'api.youversion.com/*' => Http::response(['message' => 'Forbidden'], 403),
        'bible-api.com/*' => Http::response(['text' => "For God so loved the world...\n"]),
    ]);

    $this->actingAs(User::factory()->create());

    $response = $this->get(route('bible-verse-lookup', [
        'reference' => 'John 3:16',
        'version_id' => 111,
        'book' => 'JHN',
        'chapter' => 3,
        'verse_start' => 16,
    ]));

    $response->assertOk();
    $response->assertJson([
        'text' => 'For God so loved the world...',
        'translation' => null,
        'copyright' => null,
        'source' => 'fallback',
    ]);
});

test('it falls back when no app key is configured', function () {
    config(['services.youversion.key' => null]);

    Http::fake([
        'bible-api.com/*' => Http::response(['text' => 'For God so loved the world...']),
    ]);

    $this->actingAs(User::factory()->create());

    $this->get(route('bible-verse-lookup', [
        'reference' => 'John 3:16',
        'version_id' => 3034,
        'book' => 'JHN',
        'chapter' => 3,
        'verse_start' => 16,
    ]))->assertJson(['source' => 'fallback']);

    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'youversion'));
});

test('a bare reference uses the fallback source', function () {
    Http::fake([
        'bible-api.com/*' => Http::response(['text' => "For God so loved the world...\n"]),
    ]);

    $this->actingAs(User::factory()->create());

    $response = $this->get(route('bible-verse-lookup', ['reference' => 'John 3:16']));

    $response->assertOk();
    $response->assertJson(['text' => 'For God so loved the world...', 'source' => 'fallback']);
});

test('it returns a null text instead of erroring when every source fails', function () {
    Http::fake(function () {
        throw new ConnectionException('Connection timed out.');
    });

    $this->actingAs(User::factory()->create());

    $response = $this->get(route('bible-verse-lookup', [
        'reference' => 'John 3:16',
        'version_id' => 3034,
        'book' => 'JHN',
        'chapter' => 3,
        'verse_start' => 16,
    ]));

    $response->assertOk();
    $response->assertJson(['text' => null, 'source' => null]);
});

test('it rejects a malformed selection', function () {
    $this->actingAs(User::factory()->create());

    $this->getJson(route('bible-verse-lookup', [
        'reference' => 'John 3:16',
        'version_id' => 3034,
        'book' => '../etc',
        'chapter' => 3,
        'verse_start' => 18,
        'verse_end' => 16,
    ]))->assertJsonValidationErrors(['book', 'verse_end']);
});

test('guests cannot look up passages', function () {
    $this->get(route('bible-verse-lookup', ['reference' => 'John 3:16']))
        ->assertRedirect(route('login'));
});
