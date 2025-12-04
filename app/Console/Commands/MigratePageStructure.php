<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MigratePageStructure extends Command
{
    protected $signature = 'pages:migrate-structure';
    protected $description = 'Migrate page content from 2-level (Section→Column) to 3-level (Section→Container→Column) structure';

    public function handle()
    {
        $this->info('Starting page structure migration...');

        $pages = Page::all();
        $migratedCount = 0;

        foreach ($pages as $page) {
            if ($this->needsMigration($page)) {
                $this->info("Migrating page: {$page->title} (ID: {$page->id})");
                $this->migratePage($page);
                $migratedCount++;
            }
        }

        $this->info("Migration complete! Migrated {$migratedCount} pages.");
        return 0;
    }

    protected function needsMigration(Page $page): bool
    {
        $content = $page->content ?? [];

        foreach ($content as $section) {
            if (isset($section['elements']) && is_array($section['elements'])) {
                // Check if first element is a column (old structure)
                $firstElement = $section['elements'][0] ?? null;
                if ($firstElement && ($firstElement['elType'] ?? null) === 'column') {
                    return true;
                }
            }
        }

        return false;
    }

    protected function migratePage(Page $page): void
    {
        $content = $page->content ?? [];
        $migratedContent = [];

        foreach ($content as $section) {
            $migratedSection = [
                'id' => $section['id'] ?? $this->generateId(),
                'elType' => 'section',
                'settings' => $section['settings'] ?? [],
                'elements' => []
            ];

            // Get columns from old structure
            $columns = $section['elements'] ?? [];

            // Create container to wrap columns
            $container = [
                'id' => $this->generateId(),
                'elType' => 'container',
                'settings' => [
                    'content_width' => $section['settings']['content_width'] ?? 'boxed'
                ],
                'elements' => $columns
            ];

            // Remove content_width from section settings (now in container)
            if (isset($migratedSection['settings']['content_width'])) {
                unset($migratedSection['settings']['content_width']);
            }

            $migratedSection['elements'] = [$container];
            $migratedContent[] = $migratedSection;
        }

        $page->content = $migratedContent;
        $page->save();

        $this->info("  ✓ Migrated {$page->title}");
    }

    protected function generateId(): string
    {
        return (string) Str::uuid();
    }
}
