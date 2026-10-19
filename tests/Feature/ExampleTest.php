<?php

use App\Models\User;
use Illuminate\Support\Facades\File;

test('guests see the public landing page', function () {
    $this->get(route('root'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('landing'));
});

test('authenticated users are redirected to home', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('root'));

    $response->assertRedirect(route('home'));
});

test('anyone can see the testimonials page', function () {
    $this->get(route('testimonials'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('testimonials'));
});

test('anyone can see the support page', function () {
    $this->get(route('support'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('support'));
});

test('anyone can see the accountability partners gallery', function () {
    $this->get(route('accountability-partners'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('accountability-partners')
            ->has('partners.0', fn ($partner) => $partner
                ->has('name')
                ->has('photo')
            )
        );
});

test('anyone can see the hangout photo gallery', function () {
    $thumbs = public_path('images/hangout/thumb');
    $createdDirectory = ! is_dir($thumbs);
    File::ensureDirectoryExists($thumbs);
    $file = $thumbs.'/zz-test-photo.jpg';
    File::put($file, 'x');

    try {
        $this->get(route('hangout'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('hangout')
                ->where('photos', fn ($photos) => collect($photos)->contains(fn ($photo) => $photo['thumb'] === '/images/hangout/thumb/zz-test-photo.jpg'
                    && $photo['full'] === '/images/hangout/full/zz-test-photo.jpg'))
            );
    } finally {
        File::delete($file);
        if ($createdDirectory && File::isEmptyDirectory($thumbs)) {
            File::deleteDirectory(dirname($thumbs));
        }
    }
});
