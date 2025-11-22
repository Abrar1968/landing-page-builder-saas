<?php

namespace App\Observers;

use App\Models\Page;
use App\Models\PageVersion;
use Illuminate\Support\Facades\Log;

class PageObserver
{
    /**
     * Handle the Page "created" event.
     */
    public function created(Page $page): void
    {
        // Create initial version
        PageVersion::create([
            'page_id' => $page->id,
            'content' => $page->content,
            'version_number' => 1,
            'created_at' => now(),
        ]);

        Log::info('Page created', [
            'page_id' => $page->id,
            'user_id' => $page->user_id,
            'title' => $page->title,
        ]);
    }

    /**
     * Handle the Page "updating" event.
     */
    public function updating(Page $page): void
    {
        // Create version before content changes
        if ($page->isDirty('content')) {
            $latestVersion = PageVersion::where('page_id', $page->id)
                ->max('version_number') ?? 0;

            PageVersion::create([
                'page_id' => $page->id,
                'content' => $page->getOriginal('content'),
                'version_number' => $latestVersion + 1,
                'created_at' => now(),
            ]);

            Log::info('Page version created', [
                'page_id' => $page->id,
                'version' => $latestVersion + 1,
            ]);
        }
    }

    /**
     * Handle the Page "deleted" event.
     */
    public function deleted(Page $page): void
    {
        Log::info('Page deleted', [
            'page_id' => $page->id,
            'user_id' => $page->user_id,
            'title' => $page->title,
        ]);
    }
}
