# Services Documentation

## Overview

This document covers the Service Layer implementation for the Landing Page Builder SaaS application. Services encapsulate business logic, coordinate between repositories, and handle transactions.

**Framework:** Laravel 12
**Pattern:** Service Layer with Repository Pattern

## Architecture

```
Controllers -> Services -> Repositories -> Models
                  |
                  v
            External APIs
```

## Base Service

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseService
{
    protected function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    protected function log(string $message, array $context = []): void
    {
        Log::info("[{$this->getServiceName()}] {$message}", $context);
    }

    protected function logError(string $message, array $context = []): void
    {
        Log::error("[{$this->getServiceName()}] {$message}", $context);
    }

    abstract protected function getServiceName(): string;
}
```

---

## PageBuilderService

Handles page creation, editing, publishing, and auto-save functionality.

```php
<?php

namespace App\Services;

use App\Repositories\PageRepository;
use App\Repositories\PageVersionRepository;
use App\Repositories\UserRepository;
use App\Models\Page;
use App\Models\User;
use App\DTOs\PageData;
use App\Exceptions\PageNotFoundException;
use App\Exceptions\PageLimitExceededException;
use App\Exceptions\PublishException;
use App\Events\PagePublished;
use App\Events\PageCreated;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PageBuilderService extends BaseService
{
    public function __construct(
        private PageRepository $pageRepository,
        private PageVersionRepository $versionRepository,
        private UserRepository $userRepository,
        private SubscriptionService $subscriptionService,
    ) {}

    protected function getServiceName(): string
    {
        return 'PageBuilderService';
    }

    /**
     * Create a new page
     */
    public function create(User $user, PageData $data): Page
    {
        // Check subscription limits
        if (!$this->subscriptionService->checkLimits($user, 'pages')) {
            throw new PageLimitExceededException(
                'You have reached the maximum number of pages for your plan.'
            );
        }

        return $this->transaction(function () use ($user, $data) {
            $page = $this->pageRepository->create([
                'user_id' => $user->id,
                'title' => $data->title,
                'slug' => $this->generateUniqueSlug($data->title, $user->id),
                'content' => $data->content ?? $this->getDefaultContent(),
                'settings' => $data->settings ?? $this->getDefaultSettings(),
                'status' => 'draft',
                'meta_title' => $data->metaTitle,
                'meta_description' => $data->metaDescription,
            ]);

            // Create initial version
            $this->versionRepository->create([
                'page_id' => $page->id,
                'content' => $page->content,
                'settings' => $page->settings,
                'version_number' => 1,
                'created_by' => $user->id,
            ]);

            $this->log('Page created', ['page_id' => $page->id, 'user_id' => $user->id]);

            event(new PageCreated($page));

            return $page;
        });
    }

    /**
     * Update an existing page
     */
    public function update(Page $page, PageData $data, User $user): Page
    {
        return $this->transaction(function () use ($page, $data, $user) {
            $updateData = array_filter([
                'title' => $data->title,
                'content' => $data->content,
                'settings' => $data->settings,
                'meta_title' => $data->metaTitle,
                'meta_description' => $data->metaDescription,
            ], fn($value) => $value !== null);

            if (isset($data->title) && $data->title !== $page->title) {
                $updateData['slug'] = $this->generateUniqueSlug($data->title, $page->user_id, $page->id);
            }

            $page = $this->pageRepository->update($page, $updateData);

            // Create new version if content changed
            if (isset($data->content) && $data->content !== $page->getOriginal('content')) {
                $latestVersion = $this->versionRepository->getLatestVersion($page->id);

                $this->versionRepository->create([
                    'page_id' => $page->id,
                    'content' => $data->content,
                    'settings' => $page->settings,
                    'version_number' => ($latestVersion?->version_number ?? 0) + 1,
                    'created_by' => $user->id,
                ]);
            }

            // Clear cache
            $this->clearPageCache($page);

            $this->log('Page updated', ['page_id' => $page->id]);

            return $page->fresh();
        });
    }

    /**
     * Duplicate an existing page
     */
    public function duplicate(Page $page, User $user, ?string $newTitle = null): Page
    {
        // Check subscription limits
        if (!$this->subscriptionService->checkLimits($user, 'pages')) {
            throw new PageLimitExceededException(
                'You have reached the maximum number of pages for your plan.'
            );
        }

        return $this->transaction(function () use ($page, $user, $newTitle) {
            $title = $newTitle ?? "{$page->title} (Copy)";

            $duplicatedPage = $this->pageRepository->create([
                'user_id' => $user->id,
                'title' => $title,
                'slug' => $this->generateUniqueSlug($title, $user->id),
                'content' => $page->content,
                'settings' => $page->settings,
                'status' => 'draft',
                'meta_title' => $page->meta_title,
                'meta_description' => $page->meta_description,
                'template_id' => $page->template_id,
            ]);

            // Create initial version for duplicated page
            $this->versionRepository->create([
                'page_id' => $duplicatedPage->id,
                'content' => $duplicatedPage->content,
                'settings' => $duplicatedPage->settings,
                'version_number' => 1,
                'created_by' => $user->id,
            ]);

            $this->log('Page duplicated', [
                'original_id' => $page->id,
                'new_id' => $duplicatedPage->id,
            ]);

            return $duplicatedPage;
        });
    }

    /**
     * Publish a page
     */
    public function publish(Page $page, User $user): Page
    {
        return $this->transaction(function () use ($page, $user) {
            // Validate page before publishing
            $this->validateForPublishing($page);

            $page = $this->pageRepository->update($page, [
                'status' => 'published',
                'published_at' => now(),
                'published_by' => $user->id,
            ]);

            // Generate static cache if needed
            $this->generatePublishedCache($page);

            // Clear any existing cache
            $this->clearPageCache($page);

            $this->log('Page published', ['page_id' => $page->id]);

            event(new PagePublished($page));

            return $page->fresh();
        });
    }

    /**
     * Auto-save page content
     */
    public function autoSave(Page $page, array $content, User $user): array
    {
        $cacheKey = "autosave:page:{$page->id}:user:{$user->id}";

        try {
            // Store in cache for quick recovery
            Cache::put($cacheKey, [
                'content' => $content,
                'saved_at' => now()->toIso8601String(),
            ], now()->addHours(24));

            // Update page with autosave flag
            $this->pageRepository->update($page, [
                'content' => $content,
                'last_autosave_at' => now(),
            ]);

            $this->log('Page auto-saved', ['page_id' => $page->id]);

            return [
                'success' => true,
                'saved_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            $this->logError('Auto-save failed', [
                'page_id' => $page->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Auto-save failed',
            ];
        }
    }

    /**
     * Restore from auto-save
     */
    public function restoreAutoSave(Page $page, User $user): ?array
    {
        $cacheKey = "autosave:page:{$page->id}:user:{$user->id}";
        return Cache::get($cacheKey);
    }

    private function generateUniqueSlug(string $title, int $userId, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->pageRepository->slugExists($slug, $userId, $excludeId)) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function validateForPublishing(Page $page): void
    {
        $errors = [];

        if (empty($page->content)) {
            $errors[] = 'Page content cannot be empty';
        }

        if (empty($page->title)) {
            $errors[] = 'Page title is required';
        }

        if (!empty($errors)) {
            throw new PublishException(implode(', ', $errors));
        }
    }

    private function clearPageCache(Page $page): void
    {
        Cache::tags(['pages', "page:{$page->id}"])->flush();
    }

    private function generatePublishedCache(Page $page): void
    {
        $cacheKey = "published:page:{$page->slug}";
        Cache::put($cacheKey, $page->toArray(), now()->addDays(7));
    }

    private function getDefaultContent(): array
    {
        return [
            'sections' => [],
            'globalStyles' => [],
        ];
    }

    private function getDefaultSettings(): array
    {
        return [
            'font' => 'Inter',
            'primaryColor' => '#3B82F6',
            'backgroundColor' => '#FFFFFF',
        ];
    }
}
```

---

## TemplateService

Manages templates for landing pages.

```php
<?php

namespace App\Services;

use App\Repositories\TemplateRepository;
use App\Repositories\PageRepository;
use App\Models\Template;
use App\Models\Page;
use App\Models\User;
use App\DTOs\TemplateData;
use App\Exceptions\TemplateNotFoundException;
use App\Exceptions\TemplateLimitExceededException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TemplateService extends BaseService
{
    public function __construct(
        private TemplateRepository $templateRepository,
        private PageRepository $pageRepository,
        private SubscriptionService $subscriptionService,
    ) {}

    protected function getServiceName(): string
    {
        return 'TemplateService';
    }

    /**
     * List available templates
     */
    public function list(
        User $user,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->templateRepository->query();

        // Include system templates and user's custom templates
        $query->where(function ($q) use ($user) {
            $q->where('is_system', true)
              ->orWhere('user_id', $user->id);
        });

        // Apply filters
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['is_premium'])) {
            $query->where('is_premium', $filters['is_premium']);
        }

        // Filter by user's subscription access
        $subscription = $user->subscription;
        if (!$subscription || $subscription->plan === 'free') {
            $query->where('is_premium', false);
        }

        $query->orderBy('is_featured', 'desc')
              ->orderBy('usage_count', 'desc')
              ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Get template categories
     */
    public function getCategories(): Collection
    {
        return $this->templateRepository->getCategories();
    }

    /**
     * Apply template to a page
     */
    public function apply(Template $template, Page $page, User $user): Page
    {
        // Check if user has access to premium template
        if ($template->is_premium) {
            $subscription = $user->subscription;
            if (!$subscription || !in_array($subscription->plan, ['pro', 'enterprise'])) {
                throw new \App\Exceptions\AccessDeniedException(
                    'Premium templates require a Pro or Enterprise subscription.'
                );
            }
        }

        return $this->transaction(function () use ($template, $page, $user) {
            // Apply template content and settings to page
            $page = $this->pageRepository->update($page, [
                'content' => $template->content,
                'settings' => array_merge(
                    $page->settings ?? [],
                    $template->settings ?? []
                ),
                'template_id' => $template->id,
            ]);

            // Increment template usage count
            $this->templateRepository->incrementUsage($template);

            $this->log('Template applied', [
                'template_id' => $template->id,
                'page_id' => $page->id,
            ]);

            return $page->fresh();
        });
    }

    /**
     * Save page as custom template
     */
    public function saveAs(Page $page, User $user, TemplateData $data): Template
    {
        // Check subscription limits for custom templates
        if (!$this->subscriptionService->checkLimits($user, 'templates')) {
            throw new TemplateLimitExceededException(
                'You have reached the maximum number of custom templates for your plan.'
            );
        }

        return $this->transaction(function () use ($page, $user, $data) {
            // Generate thumbnail from page content
            $thumbnail = $this->generateThumbnail($page);

            $template = $this->templateRepository->create([
                'user_id' => $user->id,
                'name' => $data->name,
                'description' => $data->description,
                'category' => $data->category ?? 'custom',
                'content' => $page->content,
                'settings' => $page->settings,
                'thumbnail' => $thumbnail,
                'is_system' => false,
                'is_premium' => false,
                'is_public' => $data->isPublic ?? false,
            ]);

            $this->log('Template saved', [
                'template_id' => $template->id,
                'from_page_id' => $page->id,
            ]);

            return $template;
        });
    }

    /**
     * Update custom template
     */
    public function update(Template $template, TemplateData $data): Template
    {
        if ($template->is_system) {
            throw new \App\Exceptions\AccessDeniedException(
                'System templates cannot be modified.'
            );
        }

        return $this->transaction(function () use ($template, $data) {
            $updateData = array_filter([
                'name' => $data->name,
                'description' => $data->description,
                'category' => $data->category,
                'is_public' => $data->isPublic,
            ], fn($value) => $value !== null);

            return $this->templateRepository->update($template, $updateData);
        });
    }

    /**
     * Delete custom template
     */
    public function delete(Template $template): bool
    {
        if ($template->is_system) {
            throw new \App\Exceptions\AccessDeniedException(
                'System templates cannot be deleted.'
            );
        }

        $this->log('Template deleted', ['template_id' => $template->id]);

        return $this->templateRepository->delete($template);
    }

    private function generateThumbnail(Page $page): ?string
    {
        // Thumbnail generation logic - could use browser screenshot service
        // For now, return null and handle in a queued job
        return null;
    }
}
```

---

## MediaService

Handles file uploads, deletion, and optimization.

```php
<?php

namespace App\Services;

use App\Repositories\MediaRepository;
use App\Models\Media;
use App\Models\User;
use App\Jobs\OptimizeImage;
use App\Exceptions\MediaUploadException;
use App\Exceptions\StorageLimitExceededException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class MediaService extends BaseService
{
    private const ALLOWED_IMAGES = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    private const ALLOWED_DOCUMENTS = ['pdf'];
    private const MAX_IMAGE_SIZE = 10 * 1024 * 1024; // 10MB

    public function __construct(
        private MediaRepository $mediaRepository,
        private SubscriptionService $subscriptionService,
    ) {}

    protected function getServiceName(): string
    {
        return 'MediaService';
    }

    /**
     * Upload a media file
     */
    public function upload(UploadedFile $file, User $user, array $options = []): Media
    {
        // Validate file
        $this->validateFile($file);

        // Check storage limits
        $fileSize = $file->getSize();
        if (!$this->subscriptionService->checkStorageLimit($user, $fileSize)) {
            throw new StorageLimitExceededException(
                'You have exceeded your storage limit. Please upgrade your plan.'
            );
        }

        return $this->transaction(function () use ($file, $user, $options) {
            $extension = strtolower($file->getClientOriginalExtension());
            $filename = $this->generateFilename($file);
            $path = $this->getStoragePath($user, $extension);
            $fullPath = "{$path}/{$filename}";

            // Store file
            $stored = Storage::disk('s3')->putFileAs(
                $path,
                $file,
                $filename,
                ['visibility' => 'public']
            );

            if (!$stored) {
                throw new MediaUploadException('Failed to upload file to storage.');
            }

            // Get image dimensions if applicable
            $dimensions = $this->getImageDimensions($file);

            // Create media record
            $media = $this->mediaRepository->create([
                'user_id' => $user->id,
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'path' => $fullPath,
                'disk' => 's3',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'width' => $dimensions['width'] ?? null,
                'height' => $dimensions['height'] ?? null,
                'alt_text' => $options['alt_text'] ?? null,
                'folder' => $options['folder'] ?? 'general',
            ]);

            // Queue optimization for images
            if ($this->isImage($extension) && $extension !== 'svg') {
                OptimizeImage::dispatch($media);
            }

            // Update user's storage usage
            $this->subscriptionService->incrementStorageUsage($user, $file->getSize());

            $this->log('Media uploaded', [
                'media_id' => $media->id,
                'filename' => $filename,
                'size' => $file->getSize(),
            ]);

            return $media;
        });
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple(array $files, User $user, array $options = []): array
    {
        $uploaded = [];
        $errors = [];

        foreach ($files as $index => $file) {
            try {
                $uploaded[] = $this->upload($file, $user, $options);
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'uploaded' => $uploaded,
            'errors' => $errors,
        ];
    }

    /**
     * Delete a media file
     */
    public function delete(Media $media, User $user): bool
    {
        return $this->transaction(function () use ($media, $user) {
            // Delete from storage
            Storage::disk($media->disk)->delete($media->path);

            // Delete optimized versions
            $this->deleteOptimizedVersions($media);

            // Update user's storage usage
            $this->subscriptionService->decrementStorageUsage($user, $media->size);

            // Delete record
            $result = $this->mediaRepository->delete($media);

            $this->log('Media deleted', ['media_id' => $media->id]);

            return $result;
        });
    }

    /**
     * Delete multiple media files
     */
    public function deleteMultiple(array $mediaIds, User $user): array
    {
        $deleted = [];
        $errors = [];

        foreach ($mediaIds as $id) {
            try {
                $media = $this->mediaRepository->findById($id);
                if ($media && $media->user_id === $user->id) {
                    $this->delete($media, $user);
                    $deleted[] = $id;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'id' => $id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'deleted' => $deleted,
            'errors' => $errors,
        ];
    }

    /**
     * Optimize an image
     */
    public function optimize(Media $media): Media
    {
        if (!$this->isImage($media->mime_type)) {
            return $media;
        }

        try {
            $disk = Storage::disk($media->disk);
            $content = $disk->get($media->path);

            $image = Image::make($content);

            // Create optimized versions
            $versions = $this->createOptimizedVersions($image, $media);

            // Update original with optimization
            $optimized = $image->encode($this->getOptimalFormat($media), 85);
            $disk->put($media->path, $optimized);

            // Update media record
            $media = $this->mediaRepository->update($media, [
                'size' => strlen($optimized),
                'optimized_at' => now(),
                'versions' => $versions,
            ]);

            $this->log('Media optimized', [
                'media_id' => $media->id,
                'original_size' => $media->getOriginal('size'),
                'optimized_size' => $media->size,
            ]);

            return $media;
        } catch (\Exception $e) {
            $this->logError('Media optimization failed', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
            ]);

            return $media;
        }
    }

    /**
     * Get media URL with optional transformation
     */
    public function getUrl(Media $media, ?string $version = null): string
    {
        if ($version && isset($media->versions[$version])) {
            return Storage::disk($media->disk)->url($media->versions[$version]);
        }

        return Storage::disk($media->disk)->url($media->path);
    }

    private function validateFile(UploadedFile $file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = array_merge(self::ALLOWED_IMAGES, self::ALLOWED_DOCUMENTS);

        if (!in_array($extension, $allowedExtensions)) {
            throw new MediaUploadException(
                "File type '{$extension}' is not allowed."
            );
        }

        if ($file->getSize() > self::MAX_IMAGE_SIZE) {
            throw new MediaUploadException(
                'File size exceeds the maximum allowed size of 10MB.'
            );
        }
    }

    private function generateFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return Str::uuid() . '.' . $extension;
    }

    private function getStoragePath(User $user, string $extension): string
    {
        $type = $this->isImage($extension) ? 'images' : 'documents';
        return "media/{$user->id}/{$type}/" . now()->format('Y/m');
    }

    private function isImage(string $extensionOrMime): bool
    {
        $extension = strtolower($extensionOrMime);
        if (str_contains($extension, '/')) {
            return str_starts_with($extension, 'image/');
        }
        return in_array($extension, self::ALLOWED_IMAGES);
    }

    private function getImageDimensions(UploadedFile $file): array
    {
        if (!$this->isImage($file->getClientOriginalExtension())) {
            return [];
        }

        try {
            $image = Image::make($file);
            return [
                'width' => $image->width(),
                'height' => $image->height(),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function createOptimizedVersions($image, Media $media): array
    {
        $versions = [];
        $disk = Storage::disk($media->disk);
        $pathInfo = pathinfo($media->path);

        $sizes = [
            'thumbnail' => 150,
            'small' => 400,
            'medium' => 800,
            'large' => 1200,
        ];

        foreach ($sizes as $name => $width) {
            if ($image->width() > $width) {
                $resized = clone $image;
                $resized->resize($width, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $versionPath = "{$pathInfo['dirname']}/{$pathInfo['filename']}_{$name}.{$pathInfo['extension']}";
                $disk->put($versionPath, $resized->encode(null, 85));
                $versions[$name] = $versionPath;
            }
        }

        return $versions;
    }

    private function deleteOptimizedVersions(Media $media): void
    {
        if (empty($media->versions)) {
            return;
        }

        $disk = Storage::disk($media->disk);
        foreach ($media->versions as $path) {
            $disk->delete($path);
        }
    }

    private function getOptimalFormat(Media $media): string
    {
        $extension = pathinfo($media->filename, PATHINFO_EXTENSION);
        return in_array($extension, ['png', 'gif']) ? $extension : 'jpg';
    }
}
```

---

## DomainService

Manages custom domains, verification, and SSL.

```php
<?php

namespace App\Services;

use App\Repositories\DomainRepository;
use App\Models\Domain;
use App\Models\User;
use App\DTOs\DomainData;
use App\Jobs\VerifyDomain;
use App\Jobs\ProvisionSSL;
use App\Exceptions\DomainVerificationException;
use App\Exceptions\DomainLimitExceededException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DomainService extends BaseService
{
    private const DNS_VERIFICATION_PREFIX = '_lpb-verify';

    public function __construct(
        private DomainRepository $domainRepository,
        private SubscriptionService $subscriptionService,
    ) {}

    protected function getServiceName(): string
    {
        return 'DomainService';
    }

    /**
     * Add a custom domain
     */
    public function add(User $user, DomainData $data): Domain
    {
        // Check subscription limits
        if (!$this->subscriptionService->checkLimits($user, 'domains')) {
            throw new DomainLimitExceededException(
                'You have reached the maximum number of custom domains for your plan.'
            );
        }

        // Check if domain already exists
        if ($this->domainRepository->existsByDomain($data->domain)) {
            throw new \App\Exceptions\DomainAlreadyExistsException(
                'This domain is already registered in our system.'
            );
        }

        return $this->transaction(function () use ($user, $data) {
            // Generate verification token
            $verificationToken = Str::random(32);

            $domain = $this->domainRepository->create([
                'user_id' => $user->id,
                'domain' => $this->normalizeDomain($data->domain),
                'verification_token' => $verificationToken,
                'verification_method' => $data->verificationMethod ?? 'dns',
                'status' => 'pending',
                'ssl_status' => 'pending',
            ]);

            $this->log('Domain added', [
                'domain_id' => $domain->id,
                'domain' => $domain->domain,
            ]);

            // Queue verification check
            VerifyDomain::dispatch($domain)->delay(now()->addMinutes(2));

            return $domain;
        });
    }

    /**
     * Verify domain ownership
     */
    public function verify(Domain $domain): Domain
    {
        $verified = match ($domain->verification_method) {
            'dns' => $this->verifyDns($domain),
            'file' => $this->verifyFile($domain),
            default => false,
        };

        return $this->transaction(function () use ($domain, $verified) {
            if ($verified) {
                $domain = $this->domainRepository->update($domain, [
                    'status' => 'verified',
                    'verified_at' => now(),
                ]);

                // Start SSL provisioning
                ProvisionSSL::dispatch($domain);

                $this->log('Domain verified', ['domain_id' => $domain->id]);
            } else {
                $domain = $this->domainRepository->update($domain, [
                    'last_verification_attempt' => now(),
                    'verification_attempts' => $domain->verification_attempts + 1,
                ]);

                $this->log('Domain verification failed', [
                    'domain_id' => $domain->id,
                    'attempts' => $domain->verification_attempts,
                ]);
            }

            return $domain->fresh();
        });
    }

    /**
     * Check SSL certificate status
     */
    public function checkSSL(Domain $domain): array
    {
        if ($domain->status !== 'verified') {
            return [
                'valid' => false,
                'error' => 'Domain must be verified before SSL check',
            ];
        }

        try {
            $context = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'verify_peer' => false,
                ],
            ]);

            $stream = @stream_socket_client(
                "ssl://{$domain->domain}:443",
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (!$stream) {
                return [
                    'valid' => false,
                    'error' => 'Could not establish SSL connection',
                ];
            }

            $params = stream_context_get_params($stream);
            $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);

            fclose($stream);

            $validFrom = date('Y-m-d H:i:s', $cert['validFrom_time_t']);
            $validTo = date('Y-m-d H:i:s', $cert['validTo_time_t']);
            $isValid = time() < $cert['validTo_time_t'];
            $daysRemaining = floor(($cert['validTo_time_t'] - time()) / 86400);

            // Update domain record
            $this->domainRepository->update($domain, [
                'ssl_status' => $isValid ? 'active' : 'expired',
                'ssl_expires_at' => $validTo,
                'ssl_checked_at' => now(),
            ]);

            $this->log('SSL checked', [
                'domain_id' => $domain->id,
                'valid' => $isValid,
                'days_remaining' => $daysRemaining,
            ]);

            return [
                'valid' => $isValid,
                'issuer' => $cert['issuer']['O'] ?? 'Unknown',
                'valid_from' => $validFrom,
                'valid_to' => $validTo,
                'days_remaining' => $daysRemaining,
            ];
        } catch (\Exception $e) {
            $this->logError('SSL check failed', [
                'domain_id' => $domain->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get verification instructions
     */
    public function getVerificationInstructions(Domain $domain): array
    {
        return match ($domain->verification_method) {
            'dns' => [
                'type' => 'TXT',
                'name' => self::DNS_VERIFICATION_PREFIX . '.' . $domain->domain,
                'value' => $domain->verification_token,
                'instructions' => 'Add a TXT record to your DNS configuration with the above values.',
            ],
            'file' => [
                'path' => '/.well-known/lpb-verification.txt',
                'content' => $domain->verification_token,
                'instructions' => 'Create a file at the specified path with the verification token as content.',
            ],
            default => [],
        };
    }

    /**
     * Remove a custom domain
     */
    public function remove(Domain $domain): bool
    {
        return $this->transaction(function () use ($domain) {
            // Remove SSL certificate if provisioned
            if ($domain->ssl_status === 'active') {
                $this->revokeSSL($domain);
            }

            $result = $this->domainRepository->delete($domain);

            $this->log('Domain removed', ['domain_id' => $domain->id]);

            return $result;
        });
    }

    private function verifyDns(Domain $domain): bool
    {
        $records = @dns_get_record(
            self::DNS_VERIFICATION_PREFIX . '.' . $domain->domain,
            DNS_TXT
        );

        if (!$records) {
            return false;
        }

        foreach ($records as $record) {
            if (isset($record['txt']) && $record['txt'] === $domain->verification_token) {
                return true;
            }
        }

        return false;
    }

    private function verifyFile(Domain $domain): bool
    {
        try {
            $response = Http::timeout(10)->get(
                "https://{$domain->domain}/.well-known/lpb-verification.txt"
            );

            return $response->successful() &&
                   trim($response->body()) === $domain->verification_token;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');
        return $domain;
    }

    private function revokeSSL(Domain $domain): void
    {
        // Implement SSL certificate revocation with your provider
        // This is provider-specific (e.g., Let's Encrypt, Cloudflare)
    }
}
```

---

## SubscriptionService

Manages subscriptions, plan changes, and usage limits.

```php
<?php

namespace App\Services;

use App\Repositories\SubscriptionRepository;
use App\Repositories\UserRepository;
use App\Models\Subscription;
use App\Models\User;
use App\DTOs\SubscriptionData;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionUpgraded;
use App\Exceptions\SubscriptionException;
use App\Exceptions\PaymentFailedException;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Illuminate\Support\Facades\Cache;

class SubscriptionService extends BaseService
{
    private const PLAN_LIMITS = [
        'free' => [
            'pages' => 3,
            'templates' => 5,
            'domains' => 0,
            'storage' => 100 * 1024 * 1024, // 100MB
            'analytics_days' => 7,
        ],
        'starter' => [
            'pages' => 10,
            'templates' => 20,
            'domains' => 1,
            'storage' => 1024 * 1024 * 1024, // 1GB
            'analytics_days' => 30,
        ],
        'pro' => [
            'pages' => 50,
            'templates' => 100,
            'domains' => 5,
            'storage' => 10 * 1024 * 1024 * 1024, // 10GB
            'analytics_days' => 90,
        ],
        'enterprise' => [
            'pages' => -1, // unlimited
            'templates' => -1,
            'domains' => -1,
            'storage' => 100 * 1024 * 1024 * 1024, // 100GB
            'analytics_days' => 365,
        ],
    ];

    public function __construct(
        private SubscriptionRepository $subscriptionRepository,
        private UserRepository $userRepository,
    ) {}

    protected function getServiceName(): string
    {
        return 'SubscriptionService';
    }

    /**
     * Subscribe user to a plan
     */
    public function subscribe(User $user, SubscriptionData $data): Subscription
    {
        // Check if user already has an active subscription
        if ($user->subscription && $user->subscription->isActive()) {
            throw new SubscriptionException(
                'You already have an active subscription. Please upgrade or cancel first.'
            );
        }

        return $this->transaction(function () use ($user, $data) {
            try {
                // Create Stripe subscription via Cashier
                $stripeSubscription = $user->newSubscription('default', $data->priceId)
                    ->create($data->paymentMethodId);

                // Create local subscription record
                $subscription = $this->subscriptionRepository->create([
                    'user_id' => $user->id,
                    'plan' => $data->plan,
                    'stripe_id' => $stripeSubscription->stripe_id,
                    'stripe_price_id' => $data->priceId,
                    'status' => 'active',
                    'trial_ends_at' => $data->trialDays
                        ? now()->addDays($data->trialDays)
                        : null,
                    'current_period_start' => now(),
                    'current_period_end' => now()->addMonth(),
                ]);

                // Clear cached limits
                $this->clearLimitsCache($user);

                $this->log('Subscription created', [
                    'user_id' => $user->id,
                    'plan' => $data->plan,
                ]);

                event(new SubscriptionCreated($subscription));

                return $subscription;
            } catch (IncompletePayment $e) {
                throw new PaymentFailedException(
                    'Payment requires additional action: ' . $e->payment->clientSecret()
                );
            }
        });
    }

    /**
     * Cancel subscription
     */
    public function cancel(Subscription $subscription, bool $immediately = false): Subscription
    {
        return $this->transaction(function () use ($subscription, $immediately) {
            $user = $subscription->user;

            if ($immediately) {
                // Cancel immediately
                $user->subscription('default')->cancelNow();

                $subscription = $this->subscriptionRepository->update($subscription, [
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'ends_at' => now(),
                ]);
            } else {
                // Cancel at end of billing period
                $user->subscription('default')->cancel();

                $subscription = $this->subscriptionRepository->update($subscription, [
                    'status' => 'cancelling',
                    'cancelled_at' => now(),
                    'ends_at' => $subscription->current_period_end,
                ]);
            }

            $this->clearLimitsCache($user);

            $this->log('Subscription cancelled', [
                'subscription_id' => $subscription->id,
                'immediately' => $immediately,
            ]);

            event(new SubscriptionCancelled($subscription));

            return $subscription->fresh();
        });
    }

    /**
     * Resume a cancelled subscription
     */
    public function resume(Subscription $subscription): Subscription
    {
        if ($subscription->status !== 'cancelling') {
            throw new SubscriptionException('Only cancelling subscriptions can be resumed.');
        }

        return $this->transaction(function () use ($subscription) {
            $user = $subscription->user;
            $user->subscription('default')->resume();

            $subscription = $this->subscriptionRepository->update($subscription, [
                'status' => 'active',
                'cancelled_at' => null,
                'ends_at' => null,
            ]);

            $this->clearLimitsCache($user);

            $this->log('Subscription resumed', ['subscription_id' => $subscription->id]);

            return $subscription->fresh();
        });
    }

    /**
     * Upgrade or downgrade subscription
     */
    public function upgrade(Subscription $subscription, SubscriptionData $data): Subscription
    {
        return $this->transaction(function () use ($subscription, $data) {
            $user = $subscription->user;
            $oldPlan = $subscription->plan;

            // Swap plan via Stripe
            $user->subscription('default')->swap($data->priceId);

            $subscription = $this->subscriptionRepository->update($subscription, [
                'plan' => $data->plan,
                'stripe_price_id' => $data->priceId,
            ]);

            $this->clearLimitsCache($user);

            $this->log('Subscription upgraded', [
                'subscription_id' => $subscription->id,
                'old_plan' => $oldPlan,
                'new_plan' => $data->plan,
            ]);

            event(new SubscriptionUpgraded($subscription, $oldPlan));

            return $subscription->fresh();
        });
    }

    /**
     * Check if user is within limits for a resource
     */
    public function checkLimits(User $user, string $resource): bool
    {
        $limits = $this->getLimits($user);
        $limit = $limits[$resource] ?? 0;

        // -1 means unlimited
        if ($limit === -1) {
            return true;
        }

        $currentUsage = $this->getCurrentUsage($user, $resource);

        return $currentUsage < $limit;
    }

    /**
     * Check storage limit
     */
    public function checkStorageLimit(User $user, int $additionalBytes): bool
    {
        $limits = $this->getLimits($user);
        $storageLimit = $limits['storage'] ?? 0;

        $currentUsage = $user->storage_used ?? 0;

        return ($currentUsage + $additionalBytes) <= $storageLimit;
    }

    /**
     * Get limits for user's current plan
     */
    public function getLimits(User $user): array
    {
        $cacheKey = "user:{$user->id}:limits";

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($user) {
            $plan = $user->subscription?->plan ?? 'free';
            return self::PLAN_LIMITS[$plan] ?? self::PLAN_LIMITS['free'];
        });
    }

    /**
     * Get current usage for all resources
     */
    public function getUsage(User $user): array
    {
        return [
            'pages' => $this->getCurrentUsage($user, 'pages'),
            'templates' => $this->getCurrentUsage($user, 'templates'),
            'domains' => $this->getCurrentUsage($user, 'domains'),
            'storage' => $user->storage_used ?? 0,
        ];
    }

    /**
     * Increment storage usage
     */
    public function incrementStorageUsage(User $user, int $bytes): void
    {
        $this->userRepository->increment($user, 'storage_used', $bytes);
    }

    /**
     * Decrement storage usage
     */
    public function decrementStorageUsage(User $user, int $bytes): void
    {
        $this->userRepository->decrement($user, 'storage_used', $bytes);
    }

    private function getCurrentUsage(User $user, string $resource): int
    {
        return match ($resource) {
            'pages' => $user->pages()->count(),
            'templates' => $user->templates()->count(),
            'domains' => $user->domains()->count(),
            default => 0,
        };
    }

    private function clearLimitsCache(User $user): void
    {
        Cache::forget("user:{$user->id}:limits");
    }
}
```

---

## AnalyticsService

Tracks and retrieves analytics data for landing pages.

```php
<?php

namespace App\Services;

use App\Repositories\AnalyticsRepository;
use App\Repositories\PageRepository;
use App\Models\Page;
use App\Models\User;
use App\DTOs\AnalyticsEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AnalyticsService extends BaseService
{
    public function __construct(
        private AnalyticsRepository $analyticsRepository,
        private PageRepository $pageRepository,
        private SubscriptionService $subscriptionService,
    ) {}

    protected function getServiceName(): string
    {
        return 'AnalyticsService';
    }

    /**
     * Track an analytics event
     */
    public function track(AnalyticsEvent $event): void
    {
        try {
            // Skip tracking for bots
            if ($this->isBot($event->userAgent)) {
                return;
            }

            $this->analyticsRepository->create([
                'page_id' => $event->pageId,
                'event_type' => $event->eventType,
                'session_id' => $event->sessionId,
                'visitor_id' => $event->visitorId,
                'ip_address' => $this->hashIp($event->ipAddress),
                'user_agent' => $event->userAgent,
                'referrer' => $event->referrer,
                'utm_source' => $event->utmSource,
                'utm_medium' => $event->utmMedium,
                'utm_campaign' => $event->utmCampaign,
                'country' => $event->country,
                'city' => $event->city,
                'device_type' => $this->getDeviceType($event->userAgent),
                'browser' => $this->getBrowser($event->userAgent),
                'os' => $this->getOS($event->userAgent),
                'metadata' => $event->metadata,
                'created_at' => $event->timestamp ?? now(),
            ]);

            // Update real-time counters
            $this->updateRealtimeCounters($event);

        } catch (\Exception $e) {
            $this->logError('Failed to track event', [
                'event_type' => $event->eventType,
                'page_id' => $event->pageId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get analytics stats for a page
     */
    public function getStats(Page $page, User $user, array $options = []): array
    {
        // Check analytics access based on subscription
        $limits = $this->subscriptionService->getLimits($user);
        $maxDays = $limits['analytics_days'] ?? 7;

        $startDate = isset($options['start_date'])
            ? Carbon::parse($options['start_date'])
            : now()->subDays(min($options['days'] ?? 30, $maxDays));

        $endDate = isset($options['end_date'])
            ? Carbon::parse($options['end_date'])
            : now();

        // Ensure date range is within allowed limit
        if ($startDate->diffInDays($endDate) > $maxDays) {
            $startDate = $endDate->copy()->subDays($maxDays);
        }

        $cacheKey = "analytics:page:{$page->id}:{$startDate->format('Y-m-d')}:{$endDate->format('Y-m-d')}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($page, $startDate, $endDate) {
            return [
                'summary' => $this->getSummary($page, $startDate, $endDate),
                'traffic' => $this->getTrafficData($page, $startDate, $endDate),
                'sources' => $this->getSourcesData($page, $startDate, $endDate),
                'devices' => $this->getDevicesData($page, $startDate, $endDate),
                'locations' => $this->getLocationsData($page, $startDate, $endDate),
                'conversions' => $this->getConversionsData($page, $startDate, $endDate),
            ];
        });
    }

    /**
     * Get real-time stats
     */
    public function getRealtimeStats(Page $page): array
    {
        return [
            'active_visitors' => $this->getActiveVisitors($page),
            'views_last_hour' => $this->getViewsLastHour($page),
            'conversions_today' => $this->getConversionsToday($page),
        ];
    }

    /**
     * Get stats for all user's pages
     */
    public function getDashboardStats(User $user, int $days = 30): array
    {
        $limits = $this->subscriptionService->getLimits($user);
        $days = min($days, $limits['analytics_days'] ?? 7);

        $startDate = now()->subDays($days);
        $endDate = now();

        $pageIds = $user->pages()->pluck('id');

        return [
            'total_views' => $this->analyticsRepository->getTotalViews($pageIds, $startDate, $endDate),
            'total_visitors' => $this->analyticsRepository->getUniqueVisitors($pageIds, $startDate, $endDate),
            'total_conversions' => $this->analyticsRepository->getTotalConversions($pageIds, $startDate, $endDate),
            'conversion_rate' => $this->analyticsRepository->getConversionRate($pageIds, $startDate, $endDate),
            'top_pages' => $this->analyticsRepository->getTopPages($pageIds, $startDate, $endDate, 5),
            'traffic_chart' => $this->analyticsRepository->getDailyTraffic($pageIds, $startDate, $endDate),
        ];
    }

    private function getSummary(Page $page, Carbon $startDate, Carbon $endDate): array
    {
        $pageViews = $this->analyticsRepository->getPageViews($page->id, $startDate, $endDate);
        $uniqueVisitors = $this->analyticsRepository->getUniqueVisitors([$page->id], $startDate, $endDate);
        $conversions = $this->analyticsRepository->getConversions($page->id, $startDate, $endDate);
        $bounceRate = $this->analyticsRepository->getBounceRate($page->id, $startDate, $endDate);
        $avgTimeOnPage = $this->analyticsRepository->getAvgTimeOnPage($page->id, $startDate, $endDate);

        // Get previous period for comparison
        $periodLength = $startDate->diffInDays($endDate);
        $prevStartDate = $startDate->copy()->subDays($periodLength);
        $prevEndDate = $startDate->copy()->subDay();

        $prevPageViews = $this->analyticsRepository->getPageViews($page->id, $prevStartDate, $prevEndDate);
        $prevUniqueVisitors = $this->analyticsRepository->getUniqueVisitors([$page->id], $prevStartDate, $prevEndDate);

        return [
            'page_views' => $pageViews,
            'page_views_change' => $this->calculateChange($pageViews, $prevPageViews),
            'unique_visitors' => $uniqueVisitors,
            'unique_visitors_change' => $this->calculateChange($uniqueVisitors, $prevUniqueVisitors),
            'conversions' => $conversions,
            'conversion_rate' => $uniqueVisitors > 0 ? round(($conversions / $uniqueVisitors) * 100, 2) : 0,
            'bounce_rate' => round($bounceRate, 2),
            'avg_time_on_page' => $avgTimeOnPage,
        ];
    }

    private function getTrafficData(Page $page, Carbon $startDate, Carbon $endDate): array
    {
        return $this->analyticsRepository->getDailyTraffic([$page->id], $startDate, $endDate);
    }

    private function getSourcesData(Page $page, Carbon $startDate, Carbon $endDate): array
    {
        return [
            'referrers' => $this->analyticsRepository->getTopReferrers($page->id, $startDate, $endDate),
            'utm_sources' => $this->analyticsRepository->getUtmSources($page->id, $startDate, $endDate),
            'utm_campaigns' => $this->analyticsRepository->getUtmCampaigns($page->id, $startDate, $endDate),
        ];
    }

    private function getDevicesData(Page $page, Carbon $startDate, Carbon $endDate): array
    {
        return [
            'device_types' => $this->analyticsRepository->getDeviceTypes($page->id, $startDate, $endDate),
            'browsers' => $this->analyticsRepository->getBrowsers($page->id, $startDate, $endDate),
            'operating_systems' => $this->analyticsRepository->getOperatingSystems($page->id, $startDate, $endDate),
        ];
    }

    private function getLocationsData(Page $page, Carbon $startDate, Carbon $endDate): array
    {
        return [
            'countries' => $this->analyticsRepository->getCountries($page->id, $startDate, $endDate),
            'cities' => $this->analyticsRepository->getCities($page->id, $startDate, $endDate, 10),
        ];
    }

    private function getConversionsData(Page $page, Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total' => $this->analyticsRepository->getConversions($page->id, $startDate, $endDate),
            'by_type' => $this->analyticsRepository->getConversionsByType($page->id, $startDate, $endDate),
            'funnel' => $this->analyticsRepository->getConversionFunnel($page->id, $startDate, $endDate),
        ];
    }

    private function updateRealtimeCounters(AnalyticsEvent $event): void
    {
        $key = "realtime:page:{$event->pageId}:visitors";
        Cache::put("{$key}:{$event->visitorId}", true, now()->addMinutes(5));

        if ($event->eventType === 'page_view') {
            Cache::increment("hourly:page:{$event->pageId}:views:" . now()->format('Y-m-d-H'));
        }
    }

    private function getActiveVisitors(Page $page): int
    {
        // Count visitors with activity in last 5 minutes
        $pattern = "realtime:page:{$page->id}:visitors:*";
        return count(Cache::getRedis()->keys($pattern));
    }

    private function getViewsLastHour(Page $page): int
    {
        $key = "hourly:page:{$page->id}:views:" . now()->format('Y-m-d-H');
        return (int) Cache::get($key, 0);
    }

    private function getConversionsToday(Page $page): int
    {
        return $this->analyticsRepository->getConversions(
            $page->id,
            now()->startOfDay(),
            now()
        );
    }

    private function calculateChange(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function hashIp(string $ip): string
    {
        return hash('sha256', $ip . config('app.key'));
    }

    private function isBot(string $userAgent): bool
    {
        $botPatterns = [
            'bot', 'crawler', 'spider', 'slurp', 'googlebot',
            'bingbot', 'yandex', 'baidu', 'duckduck',
        ];

        $userAgent = strtolower($userAgent);
        foreach ($botPatterns as $pattern) {
            if (str_contains($userAgent, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function getDeviceType(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android')) {
            return 'mobile';
        }
        if (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'tablet';
        }
        return 'desktop';
    }

    private function getBrowser(string $userAgent): string
    {
        if (str_contains($userAgent, 'Chrome')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari')) return 'Safari';
        if (str_contains($userAgent, 'Edge')) return 'Edge';
        if (str_contains($userAgent, 'Opera')) return 'Opera';
        return 'Other';
    }

    private function getOS(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) return 'Windows';
        if (str_contains($userAgent, 'Mac')) return 'macOS';
        if (str_contains($userAgent, 'Linux')) return 'Linux';
        if (str_contains($userAgent, 'Android')) return 'Android';
        if (str_contains($userAgent, 'iOS')) return 'iOS';
        return 'Other';
    }
}
```

---

## ExportService

Handles exporting landing pages to various formats.

```php
<?php

namespace App\Services;

use App\Repositories\PageRepository;
use App\Models\Page;
use App\Models\User;
use App\Jobs\ProcessExport;
use App\Exceptions\ExportException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use ZipArchive;

class ExportService extends BaseService
{
    public function __construct(
        private PageRepository $pageRepository,
        private MediaService $mediaService,
    ) {}

    protected function getServiceName(): string
    {
        return 'ExportService';
    }

    /**
     * Export page as standalone HTML
     */
    public function exportHTML(Page $page, User $user, array $options = []): string
    {
        try {
            // Generate HTML content
            $html = $this->generateHTML($page, $options);

            // Store in temporary location
            $filename = Str::slug($page->title) . '-' . time() . '.html';
            $path = "exports/{$user->id}/{$filename}";

            Storage::disk('local')->put($path, $html);

            $this->log('HTML exported', ['page_id' => $page->id, 'path' => $path]);

            return Storage::disk('local')->path($path);
        } catch (\Exception $e) {
            $this->logError('HTML export failed', [
                'page_id' => $page->id,
                'error' => $e->getMessage(),
            ]);
            throw new ExportException('Failed to export HTML: ' . $e->getMessage());
        }
    }

    /**
     * Export page as ZIP with all assets
     */
    public function exportZip(Page $page, User $user, array $options = []): string
    {
        try {
            $exportId = Str::uuid();
            $exportDir = storage_path("app/exports/{$user->id}/{$exportId}");

            // Create export directory
            if (!mkdir($exportDir, 0755, true) && !is_dir($exportDir)) {
                throw new ExportException('Failed to create export directory');
            }

            // Generate and save HTML
            $html = $this->generateHTML($page, array_merge($options, [
                'inline_assets' => false,
                'relative_paths' => true,
            ]));
            file_put_contents("{$exportDir}/index.html", $html);

            // Download and save assets
            $this->exportAssets($page, $exportDir);

            // Generate CSS file
            $css = $this->generateCSS($page);
            mkdir("{$exportDir}/css", 0755, true);
            file_put_contents("{$exportDir}/css/styles.css", $css);

            // Generate JS file if needed
            if ($options['include_js'] ?? true) {
                $js = $this->generateJS($page);
                mkdir("{$exportDir}/js", 0755, true);
                file_put_contents("{$exportDir}/js/scripts.js", $js);
            }

            // Create ZIP file
            $zipFilename = Str::slug($page->title) . '-' . time() . '.zip';
            $zipPath = storage_path("app/exports/{$user->id}/{$zipFilename}");

            $this->createZip($exportDir, $zipPath);

            // Clean up export directory
            $this->deleteDirectory($exportDir);

            $this->log('ZIP exported', ['page_id' => $page->id, 'path' => $zipPath]);

            return $zipPath;
        } catch (\Exception $e) {
            $this->logError('ZIP export failed', [
                'page_id' => $page->id,
                'error' => $e->getMessage(),
            ]);
            throw new ExportException('Failed to export ZIP: ' . $e->getMessage());
        }
    }

    /**
     * Queue export for large pages
     */
    public function queueExport(Page $page, User $user, string $format, array $options = []): string
    {
        $exportId = Str::uuid();

        ProcessExport::dispatch($exportId, $page, $user, $format, $options);

        $this->log('Export queued', [
            'export_id' => $exportId,
            'page_id' => $page->id,
            'format' => $format,
        ]);

        return $exportId;
    }

    /**
     * Get export status
     */
    public function getExportStatus(string $exportId): array
    {
        $status = cache("export:{$exportId}:status");

        return [
            'status' => $status['status'] ?? 'pending',
            'progress' => $status['progress'] ?? 0,
            'download_url' => $status['download_url'] ?? null,
            'error' => $status['error'] ?? null,
        ];
    }

    private function generateHTML(Page $page, array $options = []): string
    {
        $inlineAssets = $options['inline_assets'] ?? true;
        $relativePaths = $options['relative_paths'] ?? false;

        // Render page content to HTML
        $content = $this->renderContent($page->content);

        // Generate meta tags
        $meta = $this->generateMetaTags($page);

        // Generate styles
        $styles = $inlineAssets
            ? '<style>' . $this->generateCSS($page) . '</style>'
            : '<link rel="stylesheet" href="css/styles.css">';

        // Generate scripts
        $scripts = '';
        if ($options['include_js'] ?? true) {
            $scripts = $inlineAssets
                ? '<script>' . $this->generateJS($page) . '</script>'
                : '<script src="js/scripts.js"></script>';
        }

        // Process images
        if ($inlineAssets) {
            $content = $this->inlineImages($content);
        } elseif ($relativePaths) {
            $content = $this->convertToRelativePaths($content);
        }

        return View::make('exports.html', [
            'title' => $page->title,
            'meta' => $meta,
            'styles' => $styles,
            'content' => $content,
            'scripts' => $scripts,
            'settings' => $page->settings,
        ])->render();
    }

    private function generateCSS(Page $page): string
    {
        $settings = $page->settings ?? [];

        $css = <<<CSS
        :root {
            --primary-color: {$settings['primaryColor'] ?? '#3B82F6'};
            --background-color: {$settings['backgroundColor'] ?? '#FFFFFF'};
            --font-family: {$settings['font'] ?? 'Inter'}, sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--background-color);
            line-height: 1.6;
        }
        CSS;

        // Add section-specific styles
        foreach ($page->content['sections'] ?? [] as $section) {
            $css .= $this->generateSectionCSS($section);
        }

        // Add global styles from page
        foreach ($page->content['globalStyles'] ?? [] as $selector => $styles) {
            $css .= "\n{$selector} { {$styles} }";
        }

        return $css;
    }

    private function generateJS(Page $page): string
    {
        return <<<JS
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });

            // Form handling
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    // Handle form submission
                    const formData = new FormData(this);
                    console.log('Form submitted:', Object.fromEntries(formData));
                    alert('Form submitted successfully!');
                });
            });
        });
        JS;
    }

    private function generateMetaTags(Page $page): string
    {
        $meta = [
            '<meta charset="UTF-8">',
            '<meta name="viewport" content="width=device-width, initial-scale=1.0">',
        ];

        if ($page->meta_title) {
            $meta[] = "<meta name=\"title\" content=\"{$page->meta_title}\">";
        }

        if ($page->meta_description) {
            $meta[] = "<meta name=\"description\" content=\"{$page->meta_description}\">";
        }

        // Open Graph tags
        $meta[] = "<meta property=\"og:title\" content=\"{$page->meta_title ?? $page->title}\">";
        $meta[] = "<meta property=\"og:type\" content=\"website\">";

        if ($page->meta_description) {
            $meta[] = "<meta property=\"og:description\" content=\"{$page->meta_description}\">";
        }

        return implode("\n    ", $meta);
    }

    private function renderContent(array $content): string
    {
        $html = '';

        foreach ($content['sections'] ?? [] as $section) {
            $html .= $this->renderSection($section);
        }

        return $html;
    }

    private function renderSection(array $section): string
    {
        $type = $section['type'] ?? 'container';
        $content = $section['content'] ?? '';
        $styles = $section['styles'] ?? [];
        $id = $section['id'] ?? Str::uuid();

        $styleAttr = $this->arrayToInlineStyle($styles);

        return "<section id=\"{$id}\" class=\"section section-{$type}\" style=\"{$styleAttr}\">{$content}</section>";
    }

    private function generateSectionCSS(array $section): string
    {
        $id = $section['id'] ?? '';
        if (!$id) return '';

        $styles = $section['styles'] ?? [];
        if (empty($styles)) return '';

        $cssProperties = [];
        foreach ($styles as $property => $value) {
            $cssProperties[] = "{$property}: {$value}";
        }

        return "\n#{$id} { " . implode('; ', $cssProperties) . " }";
    }

    private function exportAssets(Page $page, string $exportDir): void
    {
        // Create assets directory
        $assetsDir = "{$exportDir}/assets";
        mkdir($assetsDir, 0755, true);

        // Extract media URLs from content
        $mediaUrls = $this->extractMediaUrls($page->content);

        foreach ($mediaUrls as $url) {
            try {
                $filename = basename(parse_url($url, PHP_URL_PATH));
                $content = file_get_contents($url);
                file_put_contents("{$assetsDir}/{$filename}", $content);
            } catch (\Exception $e) {
                $this->logError('Failed to download asset', [
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function extractMediaUrls(array $content): array
    {
        $urls = [];
        $json = json_encode($content);

        // Extract URLs from content
        preg_match_all('/"(https?:\/\/[^"]+\.(jpg|jpeg|png|gif|webp|svg|pdf))"/', $json, $matches);

        return array_unique($matches[1] ?? []);
    }

    private function inlineImages(string $html): string
    {
        return preg_replace_callback(
            '/<img[^>]+src="([^"]+)"[^>]*>/i',
            function ($matches) {
                $url = $matches[1];
                try {
                    $content = file_get_contents($url);
                    $mime = $this->getMimeType($url);
                    $base64 = base64_encode($content);
                    return str_replace($url, "data:{$mime};base64,{$base64}", $matches[0]);
                } catch (\Exception $e) {
                    return $matches[0];
                }
            },
            $html
        );
    }

    private function convertToRelativePaths(string $html): string
    {
        return preg_replace_callback(
            '/(src|href)="(https?:\/\/[^"]+)"/',
            function ($matches) {
                $filename = basename(parse_url($matches[2], PHP_URL_PATH));
                return "{$matches[1]}=\"assets/{$filename}\"";
            },
            $html
        );
    }

    private function createZip(string $sourceDir, string $zipPath): void
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new ExportException('Failed to create ZIP file');
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($sourceDir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = "{$dir}/{$file}";
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }

    private function arrayToInlineStyle(array $styles): string
    {
        $parts = [];
        foreach ($styles as $property => $value) {
            $parts[] = "{$property}: {$value}";
        }
        return implode('; ', $parts);
    }

    private function getMimeType(string $url): string
    {
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'application/octet-stream',
        };
    }
}
```

---

## Service Provider Registration

Register all services in `AppServiceProvider`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PageBuilderService;
use App\Services\TemplateService;
use App\Services\MediaService;
use App\Services\DomainService;
use App\Services\SubscriptionService;
use App\Services\AnalyticsService;
use App\Services\ExportService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Services are automatically resolved via constructor injection
        // but you can bind interfaces here if needed

        $this->app->singleton(SubscriptionService::class);
        $this->app->singleton(AnalyticsService::class);
    }

    public function boot(): void
    {
        //
    }
}
```

---

## Usage Examples

### Controller Usage

```php
<?php

namespace App\Http\Controllers;

use App\Services\PageBuilderService;
use App\DTOs\PageData;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(
        private PageBuilderService $pageBuilderService,
    ) {}

    public function store(Request $request)
    {
        $data = PageData::from($request->validated());
        $page = $this->pageBuilderService->create($request->user(), $data);

        return response()->json($page, 201);
    }

    public function publish(Page $page, Request $request)
    {
        $page = $this->pageBuilderService->publish($page, $request->user());

        return response()->json($page);
    }
}
```

### Testing Services

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PageBuilderService;
use App\Models\User;
use App\DTOs\PageData;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PageBuilderServiceTest extends TestCase
{
    use RefreshDatabase;

    private PageBuilderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PageBuilderService::class);
    }

    public function test_creates_page_with_initial_version(): void
    {
        $user = User::factory()->create();
        $data = new PageData(
            title: 'Test Page',
            content: ['sections' => []],
        );

        $page = $this->service->create($user, $data);

        $this->assertDatabaseHas('pages', ['id' => $page->id]);
        $this->assertDatabaseHas('page_versions', [
            'page_id' => $page->id,
            'version_number' => 1,
        ]);
    }
}
```

---

## Error Handling

All services use custom exceptions for proper error handling:

```php
<?php

namespace App\Exceptions;

class PageNotFoundException extends \Exception {}
class PageLimitExceededException extends \Exception {}
class PublishException extends \Exception {}
class MediaUploadException extends \Exception {}
class StorageLimitExceededException extends \Exception {}
class DomainVerificationException extends \Exception {}
class DomainLimitExceededException extends \Exception {}
class DomainAlreadyExistsException extends \Exception {}
class SubscriptionException extends \Exception {}
class PaymentFailedException extends \Exception {}
class ExportException extends \Exception {}
class AccessDeniedException extends \Exception {}
class TemplateLimitExceededException extends \Exception {}
class TemplateNotFoundException extends \Exception {}
```

Handle these in your exception handler for proper API responses.
