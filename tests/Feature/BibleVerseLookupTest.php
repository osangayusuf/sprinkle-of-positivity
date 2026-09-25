<?php

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

test('it returns the verse text on a successful lookup', function () {
    Http::fake([
        'bible-api.com/*' => Http::response(['text' => "For God so loved the world...\n"]),
    ]);

    $this->actingAs(User::factory()->create());

    $response = $this->get(route('bible-verse-lookup', ['reference' => 'John 3:16']));

    $response->assertOk();
    $response->assertJson(['text' => 'For God so loved the world...']);
});

test('it returns a null text instead of erroring when the lookup fails', function () {
    Http::fake(function () {
        throw new ConnectionException('Connection timed out.');
    });

    $this->actingAs(User::factory()->create());

    $response = $this->get(route('bible-verse-lookup', ['reference' => 'John 3:16']));

    $response->assertOk();
    $response->assertJson(['text' => null]);
});
