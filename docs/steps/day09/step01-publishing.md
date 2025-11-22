# Day 9 - Step 1: Publishing & Custom Domains

## Objective
Implement page publishing workflow and custom domain support.

## Tasks

### 1.1 Publish Controller
```php
// app/Http/Controllers/PublishController.php
public function publish(Page $page)
{
    $this->authorize('update', $page);
    $page->update([
        'status' => 'published',
        'published_at' => now(),
    ]);
    return back()->with('success', 'Page published!');
}

public function show(string $slug)
{
    $page = Page::where('slug', $slug)
        ->where('status', 'published')
        ->firstOrFail();

    $html = $this->renderer->render($page->content ?? []);
    return view('pages.show', compact('page', 'html'));
}
```

### 1.2 Public Page View
```blade
{{-- resources/views/pages/show.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>{{ $page->settings['seo_title'] ?? $page->title }}</title>
    <meta name="description" content="{{ $page->settings['seo_description'] ?? '' }}">
    @vite(['resources/css/app.css'])
</head>
<body>
    {!! $html !!}
</body>
</html>
```

### 1.3 Custom Domain Controller
```php
public function verify(Page $page)
{
    $records = dns_get_record($page->custom_domain, DNS_CNAME);
    $isValid = collect($records)->contains(fn($r) =>
        $r['target'] === config('app.domain')
    );

    if ($isValid) {
        $page->update(['domain_verified_at' => now()]);
    }
}
```

### 1.4 Domain Middleware
Route custom domains to correct pages.

## Reference Documentation
- `docs/features/05-PUBLISHING.md` - Publishing workflow
- `docs/04-IMPLEMENTATION-FLOW.md` - Day 9-10 publishing

## Expected Deliverables
- [ ] Publish/unpublish working
- [ ] Public page routing
- [ ] Custom domain configuration
- [ ] DNS verification
- [ ] SSL setup guide

## Day 9 Complete
→ Proceed to Day 10: Analytics & Forms
