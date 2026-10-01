<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->revisionFile = base_path('.deploy/revision');
    $this->finishedFile = storage_path('framework/deployed-revision');
});

afterEach(function () {
    File::deleteDirectory(base_path('.deploy'));
    File::delete($this->finishedFile);
    $this->artisan('optimize:clear');
});

test('it runs migrations once for a newly deployed revision', function () {
    File::ensureDirectoryExists(dirname($this->revisionFile));
    File::put($this->revisionFile, "abc123\n");

    $this->artisan('deploy:finish')
        ->expectsOutputToContain('Finishing deploy of abc123.')
        ->assertSuccessful();

    expect(File::get($this->finishedFile))->toBe('abc123');

    $this->artisan('deploy:finish')
        ->doesntExpectOutputToContain('Finishing deploy')
        ->assertSuccessful();
});

test('it does nothing when no deploy has uploaded a revision', function () {
    File::deleteDirectory(base_path('.deploy'));

    $this->artisan('deploy:finish')
        ->doesntExpectOutputToContain('Finishing deploy')
        ->assertSuccessful();

    expect(File::exists($this->finishedFile))->toBeFalse();
});
