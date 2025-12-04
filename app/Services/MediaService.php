<?php

namespace App\Services;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    public function upload(UploadedFile $file, User $user): Media
    {
        $disk = config('media.disk', 'public');
        $basePath = config('media.path', 'media');
        $path = "{$basePath}/{$user->id}";

        $filename = $this->generateFilename($file);
        $filePath = $file->storeAs($path, $filename, $disk);

        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        $thumbnails = [];

        if ($this->isProcessableImage($mimeType)) {
            if (config('media.thumbnails.enabled', true)) {
                $thumbPath = $this->generateThumbnail($disk, $filePath, $path, $filename);
                if ($thumbPath) {
                    $thumbnails['thumb'] = $thumbPath;
                }
            }
        }

        return Media::create([
            'user_id' => $user->id,
            'filename' => $file->getClientOriginalName(),
            'path' => $filePath,
            'mime_type' => $mimeType,
            'size' => $size,
            'thumbnails' => !empty($thumbnails) ? $thumbnails : null,
        ]);
    }

    public function delete(Media $media): void
    {
        $disk = config('media.disk', 'public');
        Storage::disk($disk)->delete($media->path);

        if (!empty($media->thumbnails) && isset($media->thumbnails['thumb'])) {
            Storage::disk($disk)->delete($media->thumbnails['thumb']);
        }

        $media->delete();
    }

    public function getUserStorageUsed(User $user): int
    {
        return $user->media()->sum('size');
    }

    public function getStorageLimit(User $user): int
    {
        $limits = [
            'free' => 100 * 1024 * 1024,      // 100MB
            'pro' => 5 * 1024 * 1024 * 1024,  // 5GB
            'business' => 50 * 1024 * 1024 * 1024, // 50GB
        ];

        return $limits[$user->plan ?? 'free'] ?? $limits['free'];
    }

    protected function generateFilename(UploadedFile $file): string
    {
        return Str::uuid() . '.' . $file->getClientOriginalExtension();
    }

    protected function determineType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) return 'image';
        if (str_starts_with($mimeType, 'video/')) return 'video';
        if (str_starts_with($mimeType, 'audio/')) return 'audio';
        return 'document';
    }

    protected function isProcessableImage(string $mimeType): bool
    {
        return in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    protected function getImageDimensions(string $disk, string $path): ?string
    {
        try {
            $fullPath = Storage::disk($disk)->path($path);
            $imageInfo = getimagesize($fullPath);
            if ($imageInfo) {
                return $imageInfo[0] . 'x' . $imageInfo[1];
            }
        } catch (\Exception $e) {
            // Silent fail
        }
        return null;
    }

    protected function generateThumbnail(string $disk, string $originalPath, string $basePath, string $filename): ?string
    {
        try {
            $width = config('media.thumbnails.width', 300);
            $height = config('media.thumbnails.height', 300);

            $fullPath = Storage::disk($disk)->path($originalPath);
            $imageInfo = getimagesize($fullPath);
            if (!$imageInfo) return null;

            $sourceImage = match ($imageInfo['mime']) {
                'image/jpeg' => imagecreatefromjpeg($fullPath),
                'image/png' => imagecreatefrompng($fullPath),
                'image/gif' => imagecreatefromgif($fullPath),
                'image/webp' => imagecreatefromwebp($fullPath),
                default => null,
            };

            if (!$sourceImage) return null;

            $sourceWidth = imagesx($sourceImage);
            $sourceHeight = imagesy($sourceImage);

            $ratio = min($width / $sourceWidth, $height / $sourceHeight);
            $newWidth = (int)($sourceWidth * $ratio);
            $newHeight = (int)($sourceHeight * $ratio);

            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);

            $thumbnailFilename = 'thumb_' . pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
            $thumbnailPath = "{$basePath}/thumbnails/{$thumbnailFilename}";
            $thumbnailFullPath = Storage::disk($disk)->path($thumbnailPath);

            Storage::disk($disk)->makeDirectory("{$basePath}/thumbnails");
            imagejpeg($thumbnail, $thumbnailFullPath, config('media.thumbnails.quality', 80));

            imagedestroy($sourceImage);
            imagedestroy($thumbnail);

            return $thumbnailPath;
        } catch (\Exception $e) {
            return null;
        }
    }
}
