<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Media::where('user_id', auth()->id());

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('alt_text', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $media = $query->paginate($request->input('per_page', 24));

        return response()->json([
            'data' => $media->items(),
            'meta' => [
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg,mp4,webm,mp3,wav,pdf|max:10240',
        ]);

        $media = $this->mediaService->upload(
            $request->file('file'),
            auth()->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => $media,
        ], 201);
    }

    public function show(Media $media): JsonResponse
    {
        $this->authorize('view', $media);

        return response()->json([
            'data' => $media,
        ]);
    }

    public function update(Request $request, Media $media): JsonResponse
    {
        $this->authorize('update', $media);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'alt_text' => 'nullable|string|max:500',
            'caption' => 'nullable|string|max:1000',
        ]);

        $media->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'File updated successfully',
            'data' => $media,
        ]);
    }

    public function destroy(Media $media): JsonResponse
    {
        $this->authorize('delete', $media);

        $this->mediaService->delete($media);

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully',
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:media,id',
        ]);

        $media = Media::whereIn('id', $request->input('ids'))
            ->where('user_id', auth()->id())
            ->get();

        foreach ($media as $item) {
            $this->mediaService->delete($item);
        }

        return response()->json([
            'success' => true,
            'message' => count($media) . ' files deleted successfully',
        ]);
    }
}
