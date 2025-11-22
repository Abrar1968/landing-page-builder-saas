# Day 10 - Step 1: Analytics & Forms

## Objective
Build analytics tracking and form submission handling.

## Tasks

### 1.1 Track Page Views
```php
// app/Http/Middleware/TrackPageView.php
public function handle(Request $request, Closure $next)
{
    $response = $next($request);

    if ($page = $request->route('page')) {
        PageView::create([
            'page_id' => $page->id,
            'visitor_id' => $request->fingerprint(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
        ]);
    }

    return $response;
}
```

### 1.2 Analytics Dashboard
```php
// app/Http/Controllers/AnalyticsController.php
public function show(Page $page)
{
    $stats = [
        'total_views' => $page->pageViews()->count(),
        'unique_visitors' => $page->pageViews()->distinct('visitor_id')->count(),
        'views_by_day' => $page->pageViews()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->get(),
    ];
    return view('analytics.show', compact('page', 'stats'));
}
```

### 1.3 Form Submission Handler
```php
// app/Http/Controllers/FormSubmissionController.php
public function store(Request $request, Page $page)
{
    FormSubmission::create([
        'page_id' => $page->id,
        'form_id' => $request->input('form_id'),
        'data' => $request->except(['_token', 'form_id']),
        'ip_address' => $request->ip(),
    ]);

    // Send notification email
    // Webhook if configured

    return back()->with('success', 'Form submitted!');
}
```

### 1.4 Chart.js Integration
Display analytics with Chart.js:
- Line chart for views over time
- Pie chart for referrers
- Bar chart for devices

## Reference Documentation
- `docs/features/05-PUBLISHING.md` - Analytics section
- `docs/views/01-DASHBOARD.md` - Analytics display

## Expected Deliverables
- [ ] Page view tracking
- [ ] Analytics dashboard
- [ ] Form submissions stored
- [ ] Email notifications
- [ ] Chart visualizations

## Day 10 Complete - Milestone M3: Feature Complete
→ Proceed to Day 11: Subscription System
