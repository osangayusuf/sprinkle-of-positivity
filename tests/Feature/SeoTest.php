<?php

test('robots.txt blocks private areas and points to the sitemap', function () {
    $this->get('/robots.txt')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('Disallow: /admin')
        ->assertSee('Sitemap: '.route('sitemap'), false);
});

test('sitemap lists public pages only', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee(route('testimonials'), false)
        ->assertSee(route('hangout'), false)
        ->assertDontSee(route('give'), false)
        ->assertDontSee('/admin', false);
});

test('inertia shares the canonical url without the query string', function () {
    $this->get(route('testimonials', ['utm_source' => 'x']))
        ->assertInertia(fn ($page) => $page
            ->where('canonicalUrl', route('testimonials'))
            ->has('appUrl'));
});
