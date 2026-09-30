<?php

use App\Http\Middleware\RecordPageVisit;
use App\Models\Group;
use App\Models\PageVisit;
use App\Models\User;

test('a guest page view is recorded and the browser gets a visitor cookie', function () {
    $response = $this->get(route('groups.index'));

    $visit = PageVisit::query()->sole();
    expect($visit->user_id)->toBeNull();
    expect($visit->path)->toBe('/groups');
    expect($visit->route_name)->toBe('groups.index');
    $response->assertCookie(RecordPageVisit::VISITOR_COOKIE, $visit->visitor_id);
});

test('a returning browser keeps the same visitor id', function () {
    $visitorId = '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d';

    $this->withCookie(RecordPageVisit::VISITOR_COOKIE, $visitorId)->get(route('home'));
    $this->withCookie(RecordPageVisit::VISITOR_COOKIE, $visitorId)->get(route('groups.index'));

    expect(PageVisit::query()->pluck('visitor_id')->unique()->all())->toBe([$visitorId]);
});

test('a signed-in page view is attributed to the member', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('home'));

    expect(PageVisit::query()->sole()->user_id)->toBe($user->id);
});

test('only referrers from other sites are kept', function () {
    $this->get(route('home'), ['referer' => 'https://www.instagram.com/thebiblestudycommunity']);
    $this->get(route('groups.index'), ['referer' => url('/home')]);

    expect(PageVisit::query()->orderBy('id')->pluck('referrer')->all())
        ->toBe(['https://www.instagram.com/thebiblestudycommunity', null]);
});

test('requests that are not a person viewing a page are not recorded', function (Closure $request) {
    $request($this);

    expect(PageVisit::query()->count())->toBe(0);
})->with([
    'admin area' => fn () => fn ($test) => $test->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard')),
    'form submission' => fn () => fn ($test) => $test->actingAs(User::factory()->create())->post(route('groups.apply', Group::factory()->create())),
    'not found' => fn () => fn ($test) => $test->get('/groups/does-not-exist'),
    'redirect' => fn () => fn ($test) => $test->get(route('leaderboard.index')),
    'bot' => fn () => fn ($test) => $test->get(route('home'), ['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1)']),
    'prefetch' => fn () => fn ($test) => $test->get(route('home'), ['Purpose' => 'prefetch']),
    'inertia partial reload' => fn () => fn ($test) => $test->get(route('home'), ['X-Inertia' => 'true', 'X-Inertia-Partial-Data' => 'verse', 'X-Inertia-Partial-Component' => 'home/index']),
    'json endpoint' => fn () => fn ($test) => $test->get(route('well-known.passkeys')),
]);

test('visits older than the retention period are pruned', function () {
    $old = PageVisit::factory()->create(['created_at' => now()->subMonths(PageVisit::RETENTION_MONTHS)->subDay()]);
    $recent = PageVisit::factory()->create();

    $this->artisan('model:prune', ['--model' => [PageVisit::class]]);

    expect(PageVisit::query()->pluck('id')->all())->toBe([$recent->id]);
    expect(PageVisit::query()->find($old->id))->toBeNull();
});
