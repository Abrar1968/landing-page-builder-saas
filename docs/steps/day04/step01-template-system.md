# Day 4 - Step 1: Template System

## Objective
Implement template system with service layer and views.

## Tasks

### 1.1 Template Service
```php
// app/Services/TemplateService.php
class TemplateService
{
    public function useTemplate(Template $template, User $user): Page
    {
        return Page::create([
            'user_id' => $user->id,
            'title' => $template->name . ' - Copy',
            'slug' => Str::slug($template->name . '-' . time()),
            'content' => $template->content,
            'template_id' => $template->id,
            'status' => 'draft',
        ]);
    }
}
```

### 1.2 Template Controller
```php
// app/Http/Controllers/TemplateController.php
class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::all()->groupBy('category');
        return view('templates.index', compact('templates'));
    }

    public function use(Template $template)
    {
        $page = $this->service->useTemplate($template, auth()->user());
        return redirect()->route('builder.edit', $page);
    }
}
```

### 1.3 Template Selection View
Create grid view with template cards showing:
- Thumbnail, name, category
- Free/Premium badge
- "Use Template" button

### 1.4 Template Preview
Modal or separate page showing template preview.

## Reference Documentation
- `docs/features/03-TEMPLATES.md` - Complete template specification
- `docs/backend/05-SERVICES.md` - Service patterns
- `docs/04-IMPLEMENTATION-FLOW.md` - Day 7 templates section

## Expected Deliverables
- [ ] TemplateService implemented
- [ ] Template gallery view
- [ ] Template cloning working
- [ ] Category filtering

## Day 4 Complete
→ Proceed to Day 5: Drag-and-Drop Builder Core
