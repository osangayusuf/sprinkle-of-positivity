<?php

use App\Actions\StorePublicUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('it returns the stored path on the public disk', function () {
    Storage::fake('public');

    $path = app(StorePublicUpload::class)->handle(UploadedFile::fake()->image('cover.jpg'), 'groups');

    expect($path)->toStartWith('groups/');
    Storage::disk('public')->assertExists($path);
});

test('it throws instead of returning false when the disk write fails', function () {
    $disk = Mockery::mock();
    $disk->shouldReceive('putFileAs')->andReturn(false);
    Storage::shouldReceive('disk')->with('public')->andReturn($disk);

    app(StorePublicUpload::class)->handle(UploadedFile::fake()->image('cover.jpg'), 'groups');
})->throws(RuntimeException::class, 'Could not store the uploaded file in [groups].');
