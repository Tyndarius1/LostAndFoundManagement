<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait HandlesUploads
{
    protected function storeImage($file, string $directory): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store($directory, 'public');
    }

    protected function replaceImage($file, ?string $oldPath, string $directory): ?string
    {
        if (! $file) {
            return $oldPath;
        }

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return $file->store($directory, 'public');
    }

    protected function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}