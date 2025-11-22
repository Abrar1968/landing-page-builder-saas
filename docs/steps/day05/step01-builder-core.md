# Day 5 - Step 1: Drag-and-Drop Builder Core

## Objective
Build AlpineJS drag-and-drop component system with canvas.

## Tasks

### 1.1 Builder Controller
```php
// app/Http/Controllers/BuilderController.php
class BuilderController extends Controller
{
    public function edit(Page $page)
    {
        $this->authorize('update', $page);
        return view('builder.edit', [
            'page' => $page,
            'elements' => config('builder.elements'),
        ]);
    }

    public function save(Request $request, Page $page)
    {
        $validated = $request->validate([
            'content' => 'required|array',
            'settings' => 'nullable|array',
        ]);
        $page->update($validated);
        return response()->json(['success' => true]);
    }
}
```

### 1.2 AlpineJS Builder Component
```javascript
function pageBuilder(initialContent) {
    return {
        elements: initialContent || [],
        selectedElement: null,
        saving: false,

        addElement(type) { /* ... */ },
        selectElement(id) { /* ... */ },
        moveElement(index, direction) { /* ... */ },
        removeElement(index) { /* ... */ },
        async save() { /* ... */ },
    }
}
```

### 1.3 Builder Layout
Three-panel layout:
- Left: Elements sidebar
- Center: Canvas/preview
- Right: Properties panel

### 1.4 Element Types
Initial elements:
- Heading, Text, Image, Button, Hero Section

## Reference Documentation
- `docs/features/02-PAGE-BUILDER.md` - Complete builder specification
- `docs/frontend/03-DRAG-DROP-BUILDER.md` - Builder architecture
- `docs/04-IMPLEMENTATION-FLOW.md` - Day 4-5 builder code

## Expected Deliverables
- [ ] Builder view with three panels
- [ ] AlpineJS state management
- [ ] Add/remove elements working
- [ ] Save to database working

## Day 5 Complete
→ Proceed to Day 6: Component Library
