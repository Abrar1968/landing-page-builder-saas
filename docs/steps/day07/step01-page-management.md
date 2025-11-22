# Day 7 - Step 1: Page Management

## Objective
Complete page CRUD, versioning, and auto-save functionality.

## Tasks

### 1.1 Page Service
```php
// app/Services/PageService.php
class PageService
{
    public function create(User $user, array $data): Page
    {
        $data['user_id'] = $user->id;
        $data['slug'] = Str::slug($data['title'] . '-' . time());
        return $this->repository->create($data);
    }

    public function duplicate(Page $page): Page
    {
        return $this->create($page->user, [
            'title' => $page->title . ' (Copy)',
            'content' => $page->content,
            'settings' => $page->settings,
        ]);
    }
}
```

### 1.2 Dashboard Controller
```php
// app/Http/Controllers/DashboardController.php
public function index()
{
    $pages = auth()->user()->pages()->latest()->paginate(10);
    $stats = [
        'total_pages' => auth()->user()->pages()->count(),
        'published_pages' => auth()->user()->pages()->where('status', 'published')->count(),
    ];
    return view('dashboard.index', compact('pages', 'stats'));
}
```

### 1.3 Auto-Save with Debounce
```javascript
// In builder AlpineJS
let saveTimeout;
$watch('elements', () => {
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => this.save(), 3000);
});
```

### 1.4 Page Settings Panel
- SEO title & description
- Social media preview
- Custom slug editor
- Favicon upload

### 1.5 Version History
- List previous versions
- Preview old versions
- Restore functionality

## Reference Documentation
- `docs/features/02-PAGE-BUILDER.md` - Page management, versioning
- `docs/views/01-DASHBOARD.md` - Dashboard layout
- `docs/04-IMPLEMENTATION-FLOW.md` - Day 3 dashboard

## Expected Deliverables
- [x] Page CRUD complete
- [x] Auto-save working (3s debounce)
- [x] Version history viewable
- [x] Page settings panel
- [x] Duplicate functionality

## Day 7 Complete - Milestone M2: Builder MVP
→ Proceed to Day 8: Asset Management & Media
