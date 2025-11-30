<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WidgetRegistry;
use Illuminate\Http\JsonResponse;

class WidgetController extends Controller
{
    public function __construct(
        protected WidgetRegistry $widgetRegistry
    ) {}

    /**
     * Get all widgets
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'widgets' => $this->widgetRegistry->all(),
                'count' => $this->widgetRegistry->count(),
                'categories' => $this->widgetRegistry->getCategories(),
            ],
        ]);
    }

    /**
     * Get a specific widget
     */
    public function show(string $type): JsonResponse
    {
        $widget = $this->widgetRegistry->get($type);

        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => "Widget '{$type}' not found",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $widget,
        ]);
    }

    /**
     * Get widgets by category
     */
    public function byCategory(string $category): JsonResponse
    {
        $widgets = $this->widgetRegistry->getByCategory($category);

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category,
                'widgets' => $widgets,
                'count' => count($widgets),
            ],
        ]);
    }
}
