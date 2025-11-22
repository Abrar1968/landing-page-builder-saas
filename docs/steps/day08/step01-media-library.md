# Day 8 - Step 1: Media Library

## Objective
Build asset upload system with image optimization.

## Tasks

### 1.1 Media Controller
```php
// app/Http/Controllers/MediaController.php
public function store(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
    ]);

    $file = $request->file('file');
    $path = $file->store('media/' . auth()->id(), 'public');

    $media = auth()->user()->media()->create([
        'filename' => $file->getClientOriginalName(),
        'path' => $path,
        'mime_type' => $file->getMimeType(),
        'size' => $file->getSize(),
    ]);

    return response()->json([
        'success' => true,
        'url' => Storage::url($path),
    ]);
}
```

### 1.2 Media Library View
- Grid view of uploaded files
- Drag-and-drop upload zone
- Folder organization
- Search and filter

### 1.3 Image Optimization
```php
// app/Services/MediaService.php
public function optimizeImage(string $path): void
{
    // Resize large images
    // Generate thumbnails
    // Convert to WebP (optional)
}
```

### 1.4 Storage Limits by Plan
- Free: 100MB
- Pro: 5GB
- Business: 50GB

## Reference Documentation
- `docs/features/04-MEDIA-LIBRARY.md` - Complete media specification
- `docs/04-IMPLEMENTATION-FLOW.md` - Day 8 media code

## Expected Deliverables
- [x] Upload working
- [x] Media grid view
- [x] Delete functionality
- [x] Storage tracking
- [x] Image optimization

## Day 8 Complete
→ Proceed to Day 9: Publishing & Domains
