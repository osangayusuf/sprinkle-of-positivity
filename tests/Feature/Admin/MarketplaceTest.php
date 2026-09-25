<?php

use App\Models\MarketplaceListing;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('an admin can create a marketplace listing', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $response = $this->post(route('admin.marketplace.store'), [
        'title' => 'Book Day Super Sales',
        'description' => 'Limited offer online shop',
        'cta_label' => 'Shop Now',
        'cta_url' => 'https://example.com/sale',
    ]);

    $response->assertRedirect(route('admin.marketplace.index'));

    $listing = MarketplaceListing::query()->sole();
    expect($listing->title)->toBe('Book Day Super Sales');
    expect($listing->created_by)->toBe($admin->id);
    expect($listing->is_active)->toBeTrue();
});

test('an admin can remove a marketplace listing', function () {
    $admin = User::factory()->admin()->create();
    $listing = MarketplaceListing::factory()->create();
    $this->actingAs($admin);

    $response = $this->delete(route('admin.marketplace.destroy', $listing));
    $response->assertRedirect();
    $response->assertInertiaFlash('toast');

    expect(MarketplaceListing::query()->count())->toBe(0);
});

test('an admin can update a marketplace listing', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $listing = MarketplaceListing::factory()->create(['title' => 'Old Title']);
    $this->actingAs($admin);

    $response = $this->put(route('admin.marketplace.update', $listing), [
        'title' => 'New Title',
        'description' => 'Updated description',
        'position' => 2,
        'is_active' => false,
        'image' => UploadedFile::fake()->image('listing.jpg'),
    ]);

    $response->assertRedirect(route('admin.marketplace.index'));
    $response->assertInertiaFlash('toast');

    $listing->refresh();
    expect($listing->title)->toBe('New Title');
    expect($listing->is_active)->toBeFalse();
    expect($listing->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($listing->image_path);
});

test('updating a listing without a new image keeps the existing one', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $listing = MarketplaceListing::factory()->create();
    $listing->image_path = 'marketplace/original.jpg';
    $listing->save();
    Storage::disk('public')->put($listing->image_path, 'fake-contents');
    $this->actingAs($admin);

    $this->put(route('admin.marketplace.update', $listing), [
        'title' => $listing->title,
        'is_active' => true,
    ]);

    expect($listing->fresh()->image_path)->toBe('marketplace/original.jpg');
});

test('a non-admin cannot update a marketplace listing', function () {
    $user = User::factory()->create();
    $listing = MarketplaceListing::factory()->create(['title' => 'Old Title']);
    $this->actingAs($user);

    $this->get(route('admin.marketplace.edit', $listing))->assertForbidden();

    $this->put(route('admin.marketplace.update', $listing), [
        'title' => 'New Title',
        'is_active' => true,
    ])->assertForbidden();

    expect($listing->fresh()->title)->toBe('Old Title');
});

test('a non-admin cannot manage marketplace listings', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.marketplace.index'))->assertForbidden();
    $this->get(route('admin.marketplace.create'))->assertForbidden();
    $this->post(route('admin.marketplace.store'), ['title' => 'X'])->assertForbidden();

    expect(MarketplaceListing::query()->count())->toBe(0);
});
