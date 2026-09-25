<?php

use App\Models\DailyVerse;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('non-admins cannot set the global daily verse', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.daily-verse.edit'))->assertForbidden();

    $this->put(route('admin.daily-verse.update'), [
        'reference' => 'John 3:16',
        'text' => 'For God so loved the world...',
    ])->assertForbidden();
});

test('admins can set the global daily verse', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $response = $this->put(route('admin.daily-verse.update'), [
        'reference' => 'John 3:16',
        'text' => 'For God so loved the world...',
    ]);

    $response->assertRedirect(route('admin.daily-verse.edit', ['date' => today()->toDateString()]));
    $response->assertInertiaFlash('toast');

    $verse = DailyVerse::query()->sole();
    expect($verse->reference)->toBe('John 3:16');
    expect($verse->date->isToday())->toBeTrue();
    expect($verse->created_by)->toBe($admin->id);
});

test('admins can attach an image when setting the global verse', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $this->put(route('admin.daily-verse.update'), [
        'reference' => 'John 3:16',
        'text' => 'For God so loved the world...',
        'image' => UploadedFile::fake()->image('verse.jpg'),
    ]);

    $verse = DailyVerse::query()->sole();
    expect($verse->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($verse->image_path);
});

test('updating the verse without a new image keeps the existing one', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $this->put(route('admin.daily-verse.update'), [
        'reference' => 'John 3:16',
        'text' => 'First text.',
        'image' => UploadedFile::fake()->image('verse.jpg'),
    ]);

    $imagePath = DailyVerse::query()->sole()->image_path;

    $this->put(route('admin.daily-verse.update'), [
        'reference' => 'John 3:16',
        'text' => 'Second text.',
    ]);

    expect(DailyVerse::query()->sole()->image_path)->toBe($imagePath);
});

test('setting the verse again the same day updates it instead of duplicating', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $this->put(route('admin.daily-verse.update'), [
        'reference' => 'John 3:16',
        'text' => 'First text.',
    ]);

    $this->put(route('admin.daily-verse.update'), [
        'reference' => 'Romans 8:28',
        'text' => 'Second text.',
    ]);

    expect(DailyVerse::query()->count())->toBe(1);
    expect(DailyVerse::query()->sole()->reference)->toBe('Romans 8:28');
});

test('admins can set a future date\'s verse', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);
    $futureDate = today()->addWeek();

    $response = $this->put(route('admin.daily-verse.update', ['date' => $futureDate->toDateString()]), [
        'reference' => 'Romans 8:28',
        'text' => 'And we know that all things work together for good.',
    ]);

    $response->assertRedirect(route('admin.daily-verse.edit', ['date' => $futureDate->toDateString()]));

    $verse = DailyVerse::query()->sole();
    expect($verse->date->isSameDay($futureDate))->toBeTrue();
});

test('admins cannot set a past date\'s verse', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);
    $pastDate = today()->subDay();

    $this->put(route('admin.daily-verse.update', ['date' => $pastDate->toDateString()]), [
        'reference' => 'Romans 8:28',
        'text' => 'And we know that all things work together for good.',
    ])->assertForbidden();

    expect(DailyVerse::query()->count())->toBe(0);
});

test('visiting a past date\'s edit form redirects to today', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $response = $this->get(route('admin.daily-verse.edit', ['date' => today()->subDay()->toDateString()]));

    $response->assertRedirect(route('admin.daily-verse.edit'));
});

test('omitting the date defaults to today', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $response = $this->get(route('admin.daily-verse.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->where('date', today()->toDateString()));
});

test('admins can view the verse history, most recent first', function () {
    $admin = User::factory()->admin()->create();
    DailyVerse::factory()->create(['date' => today()->subDays(2), 'reference' => 'Older']);
    DailyVerse::factory()->create(['date' => today()->subDay(), 'reference' => 'Newer']);
    $this->actingAs($admin);

    $response = $this->get(route('admin.daily-verse.history'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('verses.0.reference', 'Newer')
        ->where('verses.1.reference', 'Older'));
});

test('non-admins cannot view the verse history', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.daily-verse.history'))->assertForbidden();
});
