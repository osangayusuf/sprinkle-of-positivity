<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['services.youversion.key' => 'test-key']);
});

test('it lists the versions the app key can read', function () {
    Http::fake([
        'api.youversion.com/v1/bibles?*' => Http::response([
            'data' => [
                ['id' => 3034, 'abbreviation' => 'BSB', 'localized_abbreviation' => 'BSB', 'title' => 'Berean Standard Bible', 'localized_title' => 'Berean Standard Bible'],
                ['id' => 12, 'abbreviation' => 'ASV', 'localized_abbreviation' => 'ASV', 'title' => 'American Standard Version', 'localized_title' => 'American Standard Version'],
            ],
            'next_page_token' => null,
        ]),
    ]);

    $this->actingAs(User::factory()->create());

    $this->getJson(route('bible.versions'))
        ->assertOk()
        ->assertJsonPath('versions.0', ['id' => 3034, 'abbreviation' => 'BSB', 'title' => 'Berean Standard Bible'])
        ->assertJsonCount(2, 'versions')
        ->assertJsonPath('default_version_id', 3034);

    Http::assertSent(fn ($request) => $request->header('X-YVP-App-Key') === ['test-key']
        && str_contains(urldecode($request->url()), 'language_ranges[]=en'));
});

test('it caches the version list so a second request does not hit the API', function () {
    Http::fake([
        'api.youversion.com/v1/bibles?*' => Http::response(['data' => [['id' => 12, 'abbreviation' => 'ASV', 'title' => 'American Standard Version']]]),
    ]);

    $this->actingAs(User::factory()->create());

    $this->getJson(route('bible.versions'))->assertJsonCount(1, 'versions');
    $this->getJson(route('bible.versions'))->assertJsonCount(1, 'versions');

    Http::assertSentCount(1);
});

test('it returns no versions rather than an error when YouVersion is unavailable', function () {
    Http::fake(['api.youversion.com/*' => Http::response(['message' => 'Forbidden'], 403)]);

    $this->actingAs(User::factory()->create());

    $this->getJson(route('bible.versions'))->assertOk()->assertJsonPath('versions', []);

    $this->getJson(route('bible.books', 3034))->assertOk()->assertJsonPath('books', []);
});

test('it returns no versions when no app key is configured', function () {
    config(['services.youversion.key' => null]);
    Http::fake();

    $this->actingAs(User::factory()->create());

    $this->getJson(route('bible.versions'))->assertOk()->assertJsonPath('versions', []);

    Http::assertNothingSent();
});

test('it reduces each book to its chapters verse counts', function () {
    Http::fake([
        'api.youversion.com/v1/bibles/3034/books' => Http::response([
            'data' => [[
                'id' => 'JHN',
                'title' => 'John',
                'chapters' => [
                    ['id' => '1', 'verses' => [['id' => '1'], ['id' => '2'], ['id' => '3']]],
                    ['id' => '2', 'verses' => [['id' => '1'], ['id' => '2']]],
                ],
            ]],
        ]),
    ]);

    $this->actingAs(User::factory()->create());

    $this->getJson(route('bible.books', 3034))
        ->assertOk()
        ->assertExactJson(['books' => [['id' => 'JHN', 'title' => 'John', 'chapters' => [3, 2]]]]);
});

test('guests cannot read the catalog', function () {
    $this->getJson(route('bible.versions'))->assertUnauthorized();
});
