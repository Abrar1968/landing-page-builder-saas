<?php

namespace App\Observers;

use App\Models\Media;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaObserver
{
    /**
     * Handle the Media "created" event.
     */
    public function created(Media $media): void
    {
        Log::info('Media uploaded', [
            'media_id' => $media->id,
            'user_id' => $media->user_id,
            'filename' => $media->filename,
            'size' => $media->size,
        ]);
    }

    /**
     * Handle the Media "deleted" event.
     */
    public function deleted(Media $media): void
    {
        // Delete file from storage
        if (Storage::exists($media->path)) {
            Storage::delete($media->path);
        }

        // Delete thumbnails if they exist
        if ($media->thumbnails) {
            foreach ($media->thumbnails as $thumbnail) {
                if (isset($thumbnail['path']) && Storage::exists($thumbnail['path'])) {
                    Storage::delete($thumbnail['path']);
                }
            }
        }

        Log::info('Media deleted', [
            'media_id' => $media->id,
            'user_id' => $media->user_id,
            'filename' => $media->filename,
        ]);
    }
}
