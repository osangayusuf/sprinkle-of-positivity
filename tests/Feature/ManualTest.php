<?php

test('anyone can read the user manual', function () {
    $this->get(route('manual'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('manual')
            ->where('html', fn ($html) => str_contains($html, 'id="part-2--member-guide"')
                && str_contains($html, '<table>')
                && ! str_contains($html, '<script'))
            ->where('contents.0.title', 'Overview')
            ->has('contents', 7)
            ->where('contents.2.title', 'Part 2 — Member guide')
            ->where('contents.2.id', 'part-2--member-guide')
        );
});

test('every contents link points at a heading on the page', function () {
    $this->get(route('manual'))->assertInertia(function ($page) {
        $props = $page->toArray()['props'];

        foreach (collect($props['contents'])->skip(1)->flatMap(fn ($part) => [$part, ...$part['sections']]) as $entry) {
            expect($props['html'])->toContain('id="'.$entry['id'].'"');
        }
    });
});
