<?php

use App\Models\DailyVerse;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('non-admins cannot bulk-set the daily verse', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.daily-verse.bulk.edit'))->assertForbidden();

    $this->post(route('admin.daily-verse.bulk.store'), [
        'rows' => [
            ['date' => today()->toDateString(), 'reference' => 'John 3:16', 'text' => 'Text.'],
        ],
    ])->assertForbidden();
});

test('admins can bulk-set several verses at once from a grid of rows', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $response = $this->post(route('admin.daily-verse.bulk.store'), [
        'rows' => [
            ['date' => today()->toDateString(), 'reference' => 'John 3:16', 'text' => 'First.'],
            ['date' => today()->addDay()->toDateString(), 'reference' => 'Romans 8:28', 'text' => 'Second.'],
        ],
    ]);

    $response->assertRedirect(route('admin.daily-verse.bulk.edit'));
    $response->assertInertiaFlash('toast');

    expect(DailyVerse::query()->count())->toBe(2);
    expect(DailyVerse::query()->whereDate('date', today())->sole()->reference)->toBe('John 3:16');
});

test('bulk-setting a date that already has a verse updates it instead of duplicating', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);
    DailyVerse::factory()->create(['date' => today(), 'reference' => 'Old Reference']);

    $this->post(route('admin.daily-verse.bulk.store'), [
        'rows' => [
            ['date' => today()->toDateString(), 'reference' => 'John 3:16', 'text' => 'New text.'],
        ],
    ]);

    expect(DailyVerse::query()->count())->toBe(1);
    expect(DailyVerse::query()->sole()->reference)->toBe('John 3:16');
});

test('admins can bulk-set verses from an uploaded CSV', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $csv = "date,reference,text\n"
        .today()->toDateString().",John 3:16,\"For God so loved the world.\"\n"
        .today()->addDay()->toDateString().",Romans 8:28,\"And we know.\"\n";

    $file = UploadedFile::fake()->createWithContent('verses.csv', $csv);

    $response = $this->post(route('admin.daily-verse.bulk.store'), [
        'file' => $file,
    ]);

    $response->assertRedirect(route('admin.daily-verse.bulk.edit'));
    expect(DailyVerse::query()->count())->toBe(2);
    expect(DailyVerse::query()->whereDate('date', today())->sole()->reference)->toBe('John 3:16');
});

test('a grid row can attach an image', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $this->post(route('admin.daily-verse.bulk.store'), [
        'rows' => [
            [
                'date' => today()->toDateString(),
                'reference' => 'John 3:16',
                'text' => 'Text.',
                'image' => UploadedFile::fake()->image('verse.jpg'),
            ],
            [
                'date' => today()->addDay()->toDateString(),
                'reference' => 'Romans 8:28',
                'text' => 'Second.',
            ],
        ],
    ]);

    $withImage = DailyVerse::query()->whereDate('date', today())->sole();
    $withoutImage = DailyVerse::query()->whereDate('date', today()->addDay())->sole();

    expect($withImage->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($withImage->image_path);
    expect($withoutImage->image_path)->toBeNull();
});

test('CSV rows never get an image regardless of the file contents', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $csv = "date,reference,text\n".today()->toDateString().",John 3:16,\"Text.\"\n";
    $file = UploadedFile::fake()->createWithContent('verses.csv', $csv);

    $this->post(route('admin.daily-verse.bulk.store'), ['file' => $file]);

    expect(DailyVerse::query()->sole()->image_path)->toBeNull();
});

test('bulk-set validation rejects malformed rows', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $this->post(route('admin.daily-verse.bulk.store'), [
        'rows' => [
            ['date' => 'not-a-date', 'reference' => '', 'text' => ''],
        ],
    ])->assertInvalid(['rows.0.date', 'rows.0.reference', 'rows.0.text']);

    expect(DailyVerse::query()->count())->toBe(0);
});
