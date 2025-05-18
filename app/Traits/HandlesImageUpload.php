<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesImageUpload
{
        /**
 * Upload file (image/video) and delete the old one if it exists.
 *
 * @param \Illuminate\Http\UploadedFile $file
 * @param string $folder
 * @param string|null $oldFilePath
 * @return string $path
      */
public function uploadFile(UploadedFile $image, string $folder = 'uploads', ?string $oldImagePath = null)
    {
        // Delete old image if path is given
        if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
            Storage::disk('public')->delete($oldImagePath);
        }

        // Generate unique filename
        $filename = Str::random() . '.' . $image->getClientOriginalExtension();

        // Upload new image
        $path = $image->storeAs($folder, $filename, 'public');

        return $path;
    }

    /**
     * Delete image from storage
     *
     * @param string $path
     * @return bool
     */
    public function deleteImage($path)
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }
}
