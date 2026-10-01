<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class StorePublicUpload
{
    /**
     * Store an uploaded file on the public disk and return its path.
     * `store()` returns false when the disk write fails; failing loudly here
     * keeps `false` from being saved as an image path.
     */
    public function handle(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'public');

        if ($path === false) {
            throw new RuntimeException("Could not store the uploaded file in [{$directory}].");
        }

        return $path;
    }
}
