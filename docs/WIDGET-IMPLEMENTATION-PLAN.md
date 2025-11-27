# One-Week Widget System Implementation Plan

**Project:** Landing Page Builder SaaS - Elementor Widget Integration
**Duration:** 7 Days (1 Week)
**Objective:** Integrate 28 Elementor-inspired widgets into existing codebase
**Start Date:** [To be determined]
**Tech Stack:** Laravel 12, Blade, AlpineJS, TailwindCSS v4

---

## 📋 Table of Contents

1. [Pre-Implementation Checklist](#pre-implementation-checklist)
2. [Day-by-Day Implementation](#day-by-day-implementation)
3. [Testing Strategy](#testing-strategy)
4. [Rollback Plan](#rollback-plan)
5. [Success Metrics](#success-metrics)

---

## Pre-Implementation Checklist

### ✅ Before You Start

- [ ] **Backup Database:** Create full database backup
- [ ] **Git Branch:** Create feature branch `feature/widget-system`
- [ ] **Dependencies:** Verify all packages are installed (Laravel 12, AlpineJS, TailwindCSS v4)
- [ ] **Environment:** Ensure development environment is running
- [ ] **Documentation:** Review `docs/features/07-WIDGET-SYSTEM.md`
- [ ] **Existing Code:** Review current page builder implementation
- [ ] **Database:** Run pending migrations

### 📊 Current State Assessment

**What You Should Have:**
- ✅ Laravel 12 application running
- ✅ Authentication system (Laravel Sanctum)
- ✅ Basic page management (pages table)
- ✅ User management
- ✅ TailwindCSS v4 setup
- ✅ AlpineJS integrated

**What We'll Add:**
- 🆕 28 Elementor-inspired widgets
- 🆕 Widget Registry service
- 🆕 Widget Renderer service
- 🆕 Widget API endpoints
- 🆕 Widget UI components
- 🆕 Updated database schema

---

## Day-by-Day Implementation

---

## 🗓️ Day 1: Database Schema & Core Services

**Objective:** Set up database foundation and core backend services

### Morning Session (3-4 hours)

#### Task 1.1: Database Migration - Update Elements Table

```bash
# Create migration
php artisan make:migration update_elements_table_for_widget_system
```

**File:** `database/migrations/xxxx_update_elements_table_for_widget_system.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elements', function (Blueprint $table) {
            // Add new columns for widget system
            $table->string('label', 100)->after('type')->nullable();
            $table->json('advanced')->after('styles')->nullable();

            // Modify existing columns
            $table->string('type', 50)->change();

            // Add new indexes
            $table->index(['type', 'page_id']);
        });
    }

    public function down(): void
    {
        Schema::table('elements', function (Blueprint $table) {
            $table->dropColumn(['label', 'advanced']);
            $table->dropIndex(['type', 'page_id']);
        });
    }
};
```

```bash
# Run migration
php artisan migrate
```

#### Task 1.2: Create Widget Registry Service

```bash
# Create service directory if it doesn't exist
mkdir -p app/Services

# Create service file
touch app/Services/WidgetRegistry.php
```

**File:** `app/Services/WidgetRegistry.php`

Copy the complete WidgetRegistry service from `docs/steps/day06/step01-component-library.md` (lines 37-426).

#### Task 1.3: Create Widget Renderer Service

```bash
touch app/Services/WidgetRenderer.php
```

**File:** `app/Services/WidgetRenderer.php`

Copy the WidgetRenderer service from `docs/steps/day06/step01-component-library.md` (lines 435-523).

### Afternoon Session (3-4 hours)

#### Task 1.4: Register Services in Service Provider

```bash
# Edit AppServiceProvider
```

**File:** `app/Providers/AppServiceProvider.php`

```php
public function register(): void
{
    // Register Widget Services as Singletons
    $this->app->singleton(\App\Services\WidgetRegistry::class);
    $this->app->singleton(\App\Services\WidgetRenderer::class);
}
```

#### Task 1.5: Create Widget API Controller

```bash
php artisan make:controller Api/WidgetController
```

**File:** `app/Http/Controllers/Api/WidgetController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WidgetRegistry;
use Illuminate\Http\JsonResponse;

class WidgetController extends Controller
{
    public function __construct(
        private WidgetRegistry $registry
    ) {}

    /**
     * Get all available widgets
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->registry->all()
        ]);
    }

    /**
     * Get widget configuration by type
     */
    public function show(string $type): JsonResponse
    {
        $widget = $this->registry->get($type);

        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $widget
        ]);
    }

    /**
     * Get widgets by category
     */
    public function byCategory(string $category): JsonResponse
    {
        $widgets = $this->registry->getByCategory($category);

        return response()->json([
            'success' => true,
            'data' => $widgets
        ]);
    }
}
```

#### Task 1.6: Add API Routes

**File:** `routes/api.php`

```php
use App\Http\Controllers\Api\WidgetController;

// Add within auth:sanctum middleware group
Route::middleware(['auth:sanctum'])->group(function () {
    // Widget System Routes
    Route::get('/widgets', [WidgetController::class, 'index']);
    Route::get('/widgets/{type}', [WidgetController::class, 'show']);
    Route::get('/widgets/category/{category}', [WidgetController::class, 'byCategory']);
});
```

#### Task 1.7: Test Backend Services

```bash
# Test API endpoints
php artisan serve

# In another terminal, test with curl:
curl -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8000/api/widgets
```

### ✅ Day 1 Deliverables

- [x] Database schema updated with `label` and `advanced` fields
- [x] WidgetRegistry service created with all 28 widgets
- [x] WidgetRenderer service created
- [x] Widget API controller created
- [x] API routes configured
- [x] Services registered in AppServiceProvider
- [x] Backend tests passing

---

## 🗓️ Day 2: Frontend Foundation - AlpineJS Components

**Objective:** Create AlpineJS components for widget management

### Morning Session (3-4 hours)

#### Task 2.1: Create Widget Palette Component

```bash
mkdir -p resources/js/components
touch resources/js/components/widgetPalette.js
```

**File:** `resources/js/components/widgetPalette.js`

```javascript
export default function widgetPalette() {
    return {
        activeCategory: 'all',
        searchQuery: '',
        widgets: [],
        loading: false,
        error: null,

        async init() {
            await this.loadWidgets();
        },

        async loadWidgets() {
            this.loading = true;
            this.error = null;

            try {
                const response = await fetch('/api/widgets', {
                    headers: {
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to load widgets');
                }

                const data = await response.json();
                this.widgets = Object.entries(data.data).map(([key, value]) => ({
                    type: key,
                    ...value
                }));
            } catch (error) {
                this.error = error.message;
                console.error('Error loading widgets:', error);
            } finally {
                this.loading = false;
            }
        },

        get filteredWidgets() {
            let filtered = this.widgets;

            // Filter by category
            if (this.activeCategory !== 'all') {
                filtered = filtered.filter(w => w.category === this.activeCategory);
            }

            // Filter by search query
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(w =>
                    w.label.toLowerCase().includes(query) ||
                    w.type.toLowerCase().includes(query)
                );
            }

            return filtered;
        },

        get categories() {
            return [
                { id: 'all', label: 'All', icon: '⚡' },
                { id: 'typography', label: 'Typography', icon: 'T' },
                { id: 'media', label: 'Media', icon: '🖼' },
                { id: 'interactive', label: 'Interactive', icon: '🎯' },
                { id: 'layout', label: 'Layout', icon: '⊞' },
                { id: 'content', label: 'Content', icon: '📦' },
                { id: 'advanced', label: 'Advanced', icon: '</>' }
            ];
        },

        addWidget(type) {
            this.$dispatch('add-widget', { type });
        },

        getAuthToken() {
            // Get token from localStorage or meta tag
            return localStorage.getItem('auth_token') ||
                   document.querySelector('meta[name="api-token"]')?.content;
        }
    };
}
```

#### Task 2.2: Create Widget Properties Panel Component

```bash
touch resources/js/components/widgetProperties.js
```

**File:** `resources/js/components/widgetProperties.js`

```javascript
export default function widgetProperties() {
    return {
        selectedWidget: null,
        activeTab: 'content',
        tabs: ['content', 'style', 'advanced'],

        init() {
            this.$watch('selectedWidget', (widget) => {
                if (widget) {
                    this.activeTab = 'content';
                }
            });

            // Listen for widget selection events
            window.addEventListener('select-widget', (event) => {
                this.selectedWidget = event.detail;
            });
        },

        updateWidget() {
            // Debounced update
            if (this.updateTimeout) {
                clearTimeout(this.updateTimeout);
            }

            this.updateTimeout = setTimeout(() => {
                this.$dispatch('widget-updated', this.selectedWidget);
            }, 300);
        },

        updateProperty(path, value) {
            // Update nested property using path (e.g., 'props.color')
            const parts = path.split('.');
            let obj = this.selectedWidget;

            for (let i = 0; i < parts.length - 1; i++) {
                if (!obj[parts[i]]) {
                    obj[parts[i]] = {};
                }
                obj = obj[parts[i]];
            }

            obj[parts[parts.length - 1]] = value;
            this.updateWidget();
        },

        get hasSelectedWidget() {
            return this.selectedWidget !== null;
        }
    };
}
```

#### Task 2.3: Create Widget Canvas Component

```bash
touch resources/js/components/widgetCanvas.js
```

**File:** `resources/js/components/widgetCanvas.js`

```javascript
export default function widgetCanvas() {
    return {
        widgets: [],
        selectedWidgetId: null,
        draggedWidget: null,

        init() {
            // Listen for add widget events
            this.$watch('widgets', (widgets) => {
                this.saveWidgets();
            });

            // Load existing widgets from page data
            this.loadWidgets();
        },

        loadWidgets() {
            // Load from page data (passed from backend)
            const pageData = window.pageData || {};
            this.widgets = pageData.widgets || [];
        },

        async saveWidgets() {
            // Debounced auto-save
            if (this.saveTimeout) {
                clearTimeout(this.saveTimeout);
            }

            this.saveTimeout = setTimeout(async () => {
                await this.performSave();
            }, 1000);
        },

        async performSave() {
            const pageId = window.pageData?.id;
            if (!pageId) return;

            try {
                const response = await fetch(`/api/pages/${pageId}/auto-save`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        widgets: this.widgets
                    })
                });

                if (!response.ok) {
                    throw new Error('Failed to save widgets');
                }

                console.log('Widgets auto-saved');
            } catch (error) {
                console.error('Save error:', error);
            }
        },

        addWidget(event) {
            const { type } = event.detail;

            // Get default config from registry
            fetch(`/api/widgets/${type}`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                const config = data.data;

                const widget = {
                    id: this.generateId(),
                    type: type,
                    label: config.label,
                    props: { ...config.defaultProps },
                    styles: {},
                    advanced: this.getDefaultAdvanced(),
                    sortOrder: this.widgets.length
                };

                this.widgets.push(widget);
                this.selectWidget(widget.id);
            });
        },

        selectWidget(widgetId) {
            this.selectedWidgetId = widgetId;
            const widget = this.widgets.find(w => w.id === widgetId);

            if (widget) {
                window.dispatchEvent(new CustomEvent('select-widget', {
                    detail: widget
                }));
            }
        },

        deleteWidget(widgetId) {
            const index = this.widgets.findIndex(w => w.id === widgetId);
            if (index > -1) {
                this.widgets.splice(index, 1);
                this.selectedWidgetId = null;
            }
        },

        duplicateWidget(widgetId) {
            const widget = this.widgets.find(w => w.id === widgetId);
            if (widget) {
                const duplicate = {
                    ...JSON.parse(JSON.stringify(widget)),
                    id: this.generateId(),
                    sortOrder: this.widgets.length
                };
                this.widgets.push(duplicate);
            }
        },

        moveWidget(widgetId, direction) {
            const index = this.widgets.findIndex(w => w.id === widgetId);
            if (index === -1) return;

            const newIndex = direction === 'up' ? index - 1 : index + 1;
            if (newIndex < 0 || newIndex >= this.widgets.length) return;

            // Swap widgets
            [this.widgets[index], this.widgets[newIndex]] =
            [this.widgets[newIndex], this.widgets[index]];

            // Update sort orders
            this.widgets.forEach((w, i) => w.sortOrder = i);
        },

        getDefaultAdvanced() {
            return {
                layout: {
                    margin: '0px',
                    padding: '0px',
                    zIndex: 'auto'
                },
                background: {
                    type: 'none'
                },
                border: {
                    type: 'none'
                },
                responsive: {
                    hideOnDesktop: false,
                    hideOnTablet: false,
                    hideOnMobile: false
                },
                attributes: {
                    id: '',
                    classes: ''
                }
            };
        },

        generateId() {
            return 'widget_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        },

        getAuthToken() {
            return localStorage.getItem('auth_token') ||
                   document.querySelector('meta[name="api-token"]')?.content;
        }
    };
}
```

### Afternoon Session (3-4 hours)

#### Task 2.4: Register AlpineJS Components

**File:** `resources/js/app.js`

```javascript
import Alpine from 'alpinejs';

// Import widget components
import widgetPalette from './components/widgetPalette';
import widgetProperties from './components/widgetProperties';
import widgetCanvas from './components/widgetCanvas';

// Register components
Alpine.data('widgetPalette', widgetPalette);
Alpine.data('widgetProperties', widgetProperties);
Alpine.data('widgetCanvas', widgetCanvas);

window.Alpine = Alpine;
Alpine.start();
```

#### Task 2.5: Compile Frontend Assets

```bash
npm run build
# or for development
npm run dev
```

### ✅ Day 2 Deliverables

- [x] Widget Palette AlpineJS component created
- [x] Widget Properties panel component created
- [x] Widget Canvas component created
- [x] Components registered in app.js
- [x] Frontend assets compiled
- [x] Components tested in browser console

---

## 🗓️ Day 3: Blade Templates & UI

**Objective:** Create Blade templates for widget UI

### Morning Session (3-4 hours)

#### Task 3.1: Create Widget Palette Blade Template

```bash
mkdir -p resources/views/builder/partials
touch resources/views/builder/partials/widget-palette.blade.php
```

**File:** `resources/views/builder/partials/widget-palette.blade.php`

```blade
<div
    x-data="widgetPalette()"
    class="widget-palette w-80 h-full bg-white border-r border-gray-200 flex flex-col overflow-hidden"
>
    {{-- Header --}}
    <div class="p-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Widgets</h3>

        {{-- Search --}}
        <input
            type="text"
            x-model="searchQuery"
            placeholder="Search widgets..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
    </div>

    {{-- Category Tabs --}}
    <div class="px-4 py-3 border-b border-gray-200 overflow-x-auto">
        <div class="flex gap-2">
            <template x-for="category in categories" :key="category.id">
                <button
                    @click="activeCategory = category.id"
                    :class="{
                        'bg-blue-500 text-white': activeCategory === category.id,
                        'bg-gray-100 text-gray-700 hover:bg-gray-200': activeCategory !== category.id
                    }"
                    class="px-3 py-1.5 text-xs font-medium rounded-md whitespace-nowrap transition-colors"
                    x-text="category.label"
                ></button>
            </template>
        </div>
    </div>

    {{-- Widget Grid --}}
    <div class="flex-1 overflow-y-auto p-4">
        {{-- Loading State --}}
        <div x-show="loading" class="flex items-center justify-center h-32">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        </div>

        {{-- Error State --}}
        <div x-show="error" class="text-center text-red-600 text-sm p-4" x-text="error"></div>

        {{-- Widget Grid --}}
        <div
            x-show="!loading && !error"
            class="grid grid-cols-2 gap-2"
        >
            <template x-for="widget in filteredWidgets" :key="widget.type">
                <button
                    @click="addWidget(widget.type)"
                    class="flex flex-col items-center justify-center p-4 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all group cursor-pointer"
                    :title="widget.label"
                >
                    <span
                        class="text-3xl mb-2 group-hover:scale-110 transition-transform"
                        x-text="widget.icon"
                    ></span>
                    <span
                        class="text-xs text-center text-gray-700 font-medium"
                        x-text="widget.label"
                    ></span>
                </button>
            </template>
        </div>

        {{-- No Results --}}
        <div
            x-show="!loading && !error && filteredWidgets.length === 0"
            class="text-center text-gray-500 text-sm py-8"
        >
            No widgets found
        </div>
    </div>
</div>
```

#### Task 3.2: Create Widget Properties Panel Blade Template

```bash
touch resources/views/builder/partials/widget-properties.blade.php
```

**File:** `resources/views/builder/partials/widget-properties.blade.php`

```blade
<div
    x-data="widgetProperties()"
    class="widget-properties w-80 h-full bg-white border-l border-gray-200 flex flex-col overflow-hidden"
>
    <template x-if="hasSelectedWidget">
        <div class="flex flex-col h-full">
            {{-- Header --}}
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900" x-text="selectedWidget?.label"></h3>
                <p class="text-xs text-gray-500 mt-1" x-text="selectedWidget?.type"></p>
            </div>

            {{-- Tabs --}}
            <div class="flex border-b border-gray-200 bg-gray-50">
                <template x-for="tab in tabs" :key="tab">
                    <button
                        @click="activeTab = tab"
                        :class="{
                            'border-b-2 border-blue-500 text-blue-600 bg-white': activeTab === tab,
                            'text-gray-600 hover:text-gray-900': activeTab !== tab
                        }"
                        class="flex-1 px-4 py-3 text-sm font-medium capitalize transition-colors"
                        x-text="tab"
                    ></button>
                </template>
            </div>

            {{-- Tab Content --}}
            <div class="flex-1 overflow-y-auto p-4">
                {{-- Content Tab --}}
                <div x-show="activeTab === 'content'" class="space-y-4">
                    {{-- Heading Widget Properties --}}
                    <template x-if="selectedWidget?.type === 'heading'">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Text</label>
                                <input
                                    type="text"
                                    x-model="selectedWidget.props.content"
                                    @input="updateWidget()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">HTML Tag</label>
                                <select
                                    x-model="selectedWidget.props.tag"
                                    @change="updateWidget()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="h1">H1</option>
                                    <option value="h2">H2</option>
                                    <option value="h3">H3</option>
                                    <option value="h4">H4</option>
                                    <option value="h5">H5</option>
                                    <option value="h6">H6</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Link (Optional)</label>
                                <input
                                    type="url"
                                    x-model="selectedWidget.props.link"
                                    @input="updateWidget()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="https://"
                                />
                            </div>
                        </div>
                    </template>

                    {{-- Button Widget Properties --}}
                    <template x-if="selectedWidget?.type === 'button'">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Button Text</label>
                                <input
                                    type="text"
                                    x-model="selectedWidget.props.text"
                                    @input="updateWidget()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Link</label>
                                <input
                                    type="url"
                                    x-model="selectedWidget.props.link"
                                    @input="updateWidget()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="https://"
                                />
                            </div>
                        </div>
                    </template>

                    {{-- Add more widget types as needed --}}
                    <div x-show="!['heading', 'button'].includes(selectedWidget?.type)" class="text-sm text-gray-500">
                        Content properties for <span x-text="selectedWidget?.type"></span>
                    </div>
                </div>

                {{-- Style Tab --}}
                <div x-show="activeTab === 'style'" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                        <input
                            type="color"
                            x-model="selectedWidget.props.color"
                            @input="updateWidget()"
                            class="w-full h-10 rounded-lg border border-gray-300"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Font Size</label>
                        <input
                            type="text"
                            x-model="selectedWidget.props.fontSize"
                            @input="updateWidget()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., 16px, 1rem"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Font Weight</label>
                        <select
                            x-model="selectedWidget.props.fontWeight"
                            @change="updateWidget()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="300">Light (300)</option>
                            <option value="400">Normal (400)</option>
                            <option value="500">Medium (500)</option>
                            <option value="600">Semi-Bold (600)</option>
                            <option value="700">Bold (700)</option>
                            <option value="800">Extra Bold (800)</option>
                        </select>
                    </div>
                </div>

                {{-- Advanced Tab --}}
                <div x-show="activeTab === 'advanced'" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Margin</label>
                        <input
                            type="text"
                            x-model="selectedWidget.advanced.layout.margin"
                            @input="updateWidget()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., 10px 20px"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Padding</label>
                        <input
                            type="text"
                            x-model="selectedWidget.advanced.layout.padding"
                            @input="updateWidget()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., 20px"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CSS Classes</label>
                        <input
                            type="text"
                            x-model="selectedWidget.advanced.attributes.classes"
                            @input="updateWidget()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="custom-class another-class"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Element ID</label>
                        <input
                            type="text"
                            x-model="selectedWidget.advanced.attributes.id"
                            @input="updateWidget()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="unique-id"
                        />
                    </div>

                    {{-- Responsive Settings --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Responsive</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    x-model="selectedWidget.advanced.responsive.hideOnMobile"
                                    @change="updateWidget()"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="ml-2 text-sm text-gray-700">Hide on Mobile</span>
                            </label>
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    x-model="selectedWidget.advanced.responsive.hideOnTablet"
                                    @change="updateWidget()"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="ml-2 text-sm text-gray-700">Hide on Tablet</span>
                            </label>
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    x-model="selectedWidget.advanced.responsive.hideOnDesktop"
                                    @change="updateWidget()"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="ml-2 text-sm text-gray-700">Hide on Desktop</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- No Widget Selected State --}}
    <template x-if="!hasSelectedWidget">
        <div class="flex flex-col items-center justify-center h-full text-center p-8">
            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
            </svg>
            <p class="text-gray-500 text-sm">Select a widget to edit its properties</p>
        </div>
    </template>
</div>
```

### Afternoon Session (3-4 hours)

#### Task 3.3: Create Widget Canvas Blade Template

```bash
touch resources/views/builder/partials/widget-canvas.blade.php
```

**File:** `resources/views/builder/partials/widget-canvas.blade.php`

```blade
<div
    x-data="widgetCanvas()"
    @add-widget.window="addWidget($event)"
    @widget-updated.window="saveWidgets()"
    class="widget-canvas flex-1 bg-gray-50 overflow-y-auto"
>
    <div class="max-w-5xl mx-auto p-8">
        {{-- Canvas Header --}}
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Page Builder</h2>
            <div class="flex gap-2">
                <button
                    @click="performSave()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors"
                >
                    Save
                </button>
            </div>
        </div>

        {{-- Widget List --}}
        <div class="space-y-4 min-h-96">
            <template x-for="(widget, index) in widgets" :key="widget.id">
                <div
                    @click="selectWidget(widget.id)"
                    :class="{
                        'ring-2 ring-blue-500': selectedWidgetId === widget.id,
                        'ring-1 ring-gray-200': selectedWidgetId !== widget.id
                    }"
                    class="bg-white rounded-lg p-6 cursor-pointer hover:shadow-md transition-all relative group"
                >
                    {{-- Widget Content --}}
                    <div x-html="renderWidget(widget)"></div>

                    {{-- Widget Controls --}}
                    <div
                        x-show="selectedWidgetId === widget.id"
                        class="absolute top-2 right-2 flex gap-1"
                    >
                        <button
                            @click.stop="moveWidget(widget.id, 'up')"
                            :disabled="index === 0"
                            :class="{ 'opacity-50 cursor-not-allowed': index === 0 }"
                            class="p-1.5 bg-white border border-gray-300 rounded hover:bg-gray-50 text-gray-700"
                            title="Move up"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        </button>
                        <button
                            @click.stop="moveWidget(widget.id, 'down')"
                            :disabled="index === widgets.length - 1"
                            :class="{ 'opacity-50 cursor-not-allowed': index === widgets.length - 1 }"
                            class="p-1.5 bg-white border border-gray-300 rounded hover:bg-gray-50 text-gray-700"
                            title="Move down"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <button
                            @click.stop="duplicateWidget(widget.id)"
                            class="p-1.5 bg-white border border-gray-300 rounded hover:bg-gray-50 text-gray-700"
                            title="Duplicate"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </button>
                        <button
                            @click.stop="deleteWidget(widget.id)"
                            class="p-1.5 bg-white border border-red-300 rounded hover:bg-red-50 text-red-600"
                            title="Delete"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- Widget Label Badge --}}
                    <div class="absolute top-2 left-2">
                        <span class="px-2 py-1 bg-gray-900 text-white text-xs rounded" x-text="widget.label"></span>
                    </div>
                </div>
            </template>

            {{-- Empty State --}}
            <div x-show="widgets.length === 0" class="text-center py-20">
                <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No widgets yet</h3>
                <p class="mt-2 text-sm text-gray-500">Get started by adding a widget from the left panel</p>
            </div>
        </div>
    </div>
</div>

<script>
// Simple client-side widget renderer (will be improved)
function renderWidget(widget) {
    const props = widget.props || {};
    const styles = widget.styles || {};
    const advanced = widget.advanced || {};

    // Build inline styles
    let styleStr = '';
    if (props.color) styleStr += `color: ${props.color};`;
    if (props.fontSize) styleStr += `font-size: ${props.fontSize};`;
    if (props.fontWeight) styleStr += `font-weight: ${props.fontWeight};`;
    if (advanced.layout?.margin) styleStr += `margin: ${advanced.layout.margin};`;
    if (advanced.layout?.padding) styleStr += `padding: ${advanced.layout.padding};`;

    switch (widget.type) {
        case 'heading':
            const tag = props.tag || 'h2';
            const content = props.content || 'Heading';
            return `<${tag} style="${styleStr}">${content}</${tag}>`;

        case 'button':
            const btnText = props.text || 'Button';
            const btnStyles = `
                background-color: ${props.backgroundColor || '#3B82F6'};
                color: ${props.textColor || '#FFFFFF'};
                padding: ${props.padding || '12px 24px'};
                border-radius: ${props.borderRadius || '6px'};
                text-decoration: none;
                display: inline-block;
                ${styleStr}
            `;
            return `<a href="${props.link || '#'}" style="${btnStyles}">${btnText}</a>`;

        case 'text-editor':
            return `<div style="${styleStr}">${props.content || '<p>Enter your text here...</p>'}</div>`;

        case 'image':
            return `<img src="${props.src || 'https://via.placeholder.com/800x400'}" alt="${props.alt || ''}" style="width: ${props.width || '100%'}; ${styleStr}">`;

        default:
            return `<div style="${styleStr}">Widget: ${widget.type}</div>`;
    }
}

// Add to window scope
window.renderWidget = renderWidget;
</script>
```

#### Task 3.4: Create Main Builder View

```bash
touch resources/views/builder/index.blade.php
```

**File:** `resources/views/builder/index.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Page Builder')

@section('content')
<div class="h-screen flex flex-col overflow-hidden">
    {{-- Top Toolbar --}}
    <div class="bg-white border-b border-gray-200 px-4 py-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('pages.index') }}" class="text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">{{ $page->title ?? 'New Page' }}</h1>
                    <p class="text-xs text-gray-500">Last saved: <span x-text="lastSaved">Never</span></p>
                </div>
            </div>
            <div class="flex gap-2">
                <button class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Preview
                </button>
                <button class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Publish
                </button>
            </div>
        </div>
    </div>

    {{-- Builder Layout --}}
    <div class="flex-1 flex overflow-hidden">
        {{-- Left Panel: Widget Palette --}}
        @include('builder.partials.widget-palette')

        {{-- Center: Canvas --}}
        @include('builder.partials.widget-canvas')

        {{-- Right Panel: Widget Properties --}}
        @include('builder.partials.widget-properties')
    </div>
</div>

<script>
    // Pass page data to Alpine
    window.pageData = @json([
        'id' => $page->id ?? null,
        'widgets' => $page->widgets ?? []
    ]);
</script>
@endsection
```

### ✅ Day 3 Deliverables

- [x] Widget Palette Blade template created
- [x] Widget Properties panel Blade template created
- [x] Widget Canvas Blade template created
- [x] Main builder view created
- [x] Templates tested in browser
- [x] Basic widget rendering working

---

## 🗓️ Day 4: Widget Blade Components (Part 1)

**Objective:** Create Blade components for first 14 widgets

### Full Day Session (6-8 hours)

#### Task 4.1: Create Widget Component Directory

```bash
mkdir -p resources/views/components/widgets
```

#### Task 4.2: Create Typography & Media Widgets (10 widgets)

Create the following Blade component files:

1. **Heading Widget**
```bash
touch resources/views/components/widgets/heading.blade.php
```

```blade
@props(['widget'])

@php
$props = $widget['props'] ?? [];
$styles = $widget['styles'] ?? [];
$advanced = $widget['advanced'] ?? [];
$tag = $props['tag'] ?? 'h2';
$content = $props['content'] ?? 'Heading';
$link = $props['link'] ?? '';

$styleAttr = collect([
    isset($props['color']) ? "color: {$props['color']}" : null,
    isset($props['fontSize']) ? "font-size: {$props['fontSize']}" : null,
    isset($props['fontWeight']) ? "font-weight: {$props['fontWeight']}" : null,
    isset($advanced['layout']['margin']) ? "margin: {$advanced['layout']['margin']}" : null,
    isset($advanced['layout']['padding']) ? "padding: {$advanced['layout']['padding']}" : null,
])->filter()->join('; ');

$classes = $advanced['attributes']['classes'] ?? '';
$id = $advanced['attributes']['id'] ?? '';
@endphp

<{{ $tag }}
    @if($id) id="{{ $id }}" @endif
    @if($styleAttr) style="{{ $styleAttr }}" @endif
    class="widget-heading {{ $classes }}"
>
    @if($link)
        <a href="{{ $link }}">{{ $content }}</a>
    @else
        {{ $content }}
    @endif
</{{ $tag }}>
```

2. **Text Editor Widget**
```bash
touch resources/views/components/widgets/text-editor.blade.php
```

```blade
@props(['widget'])

@php
$props = $widget['props'] ?? [];
$content = $props['content'] ?? '<p>Enter your text...</p>';
@endphp

<div class="widget-text-editor prose max-w-none">
    {!! $content !!}
</div>
```

3. **Image Widget**
```bash
touch resources/views/components/widgets/image.blade.php
```

```blade
@props(['widget'])

@php
$props = $widget['props'] ?? [];
$src = $props['src'] ?? 'https://via.placeholder.com/800x400';
$alt = $props['alt'] ?? '';
$width = $props['width'] ?? '100%';
$objectFit = $props['objectFit'] ?? 'cover';
@endphp

<div class="widget-image">
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        style="width: {{ $width }}; object-fit: {{ $objectFit }};"
        loading="lazy"
    />
</div>
```

4. **Button Widget**
```bash
touch resources/views/components/widgets/button.blade.php
```

```blade
@props(['widget'])

@php
$props = $widget['props'] ?? [];
$text = $props['text'] ?? 'Button';
$link = $props['link'] ?? '#';
$target = $props['target'] ?? '_self';
$bgColor = $props['backgroundColor'] ?? '#3B82F6';
$textColor = $props['textColor'] ?? '#FFFFFF';
$padding = $props['padding'] ?? '12px 24px';
$borderRadius = $props['borderRadius'] ?? '6px';
@endphp

<a
    href="{{ $link }}"
    target="{{ $target }}"
    style="background-color: {{ $bgColor }};
           color: {{ $textColor }};
           padding: {{ $padding }};
           border-radius: {{ $borderRadius }};
           text-decoration: none;
           display: inline-block;"
    class="widget-button"
>
    {{ $text }}
</a>
```

5-10. Create similar components for:
- `video.blade.php`
- `image-box.blade.php`
- `icon.blade.php`
- `icon-box.blade.php`
- `icon-list.blade.php`
- `divider.blade.php`

#### Task 4.3: Update Widget Renderer Service

Update the `renderHeading`, `renderImage`, `renderButton` methods in `WidgetRenderer.php` to use Blade components:

```php
protected function renderHeading(array $widget): string
{
    return view('components.widgets.heading', ['widget' => $widget])->render();
}

protected function renderImage(array $widget): string
{
    return view('components.widgets.image', ['widget' => $widget])->render();
}

protected function renderButton(array $widget): string
{
    return view('components.widgets.button', ['widget' => $widget])->render();
}
```

### ✅ Day 4 Deliverables

- [x] Created 14+ Blade widget components
- [x] Updated WidgetRenderer to use Blade components
- [x] Tested widget rendering
- [x] Widgets display correctly on canvas

---

## 🗓️ Day 5: Widget Blade Components (Part 2) & Integration

**Objective:** Complete remaining 14 widgets and full system integration

### Morning Session (3-4 hours)

#### Task 5.1: Create Remaining Interactive & Layout Widgets

Create Blade components for:
- `star-rating.blade.php`
- `social-icons.blade.php`
- `tabs.blade.php`
- `accordion.blade.php`
- `toggle.blade.php`
- `container.blade.php`
- `spacer.blade.php`
- `testimonial.blade.php`
- `counter.blade.php`
- `progress-bar.blade.php`
- `basic-gallery.blade.php`
- `image-carousel.blade.php`
- `html.blade.php`
- `google-maps.blade.php`

### Afternoon Session (3-4 hours)

#### Task 5.2: Create Dynamic Widget Renderer Component

```bash
touch resources/views/components/widget-renderer.blade.php
```

```blade
@props(['widget'])

@php
$type = $widget['type'] ?? 'unknown';
$componentPath = "components.widgets.{$type}";
@endphp

@if(view()->exists($componentPath))
    <x-dynamic-component
        :component="$componentPath"
        :widget="$widget"
    />
@else
    <!-- Widget type "{{ $type }}" not found -->
@endif
```

#### Task 5.3: Update Canvas to Use Server-Side Rendering

Update `widget-canvas.blade.php` to render widgets server-side:

```blade
<template x-for="(widget, index) in widgets" :key="widget.id">
    <div class="widget-wrapper">
        <x-widget-renderer :widget="widget" />
    </div>
</template>
```

#### Task 5.4: Create Page Controller Update Method

Update `PageController.php` to save widgets:

```php
public function autoSave(Request $request, Page $page)
{
    $validated = $request->validate([
        'widgets' => 'required|array',
        'widgets.*.id' => 'required|string',
        'widgets.*.type' => 'required|string',
        'widgets.*.props' => 'nullable|array',
        'widgets.*.styles' => 'nullable|array',
        'widgets.*.advanced' => 'nullable|array',
    ]);

    // Save widgets to elements table
    $page->elements()->delete(); // Clear existing

    foreach ($validated['widgets'] as $index => $widgetData) {
        $page->elements()->create([
            'type' => $widgetData['type'],
            'label' => $this->widgetRegistry->get($widgetData['type'])['label'] ?? '',
            'properties' => $widgetData['props'] ?? [],
            'styles' => $widgetData['styles'] ?? [],
            'advanced' => $widgetData['advanced'] ?? [],
            'sort_order' => $index,
            'is_visible' => true,
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'Page auto-saved'
    ]);
}
```

### ✅ Day 5 Deliverables

- [x] All 28 widget Blade components created
- [x] Dynamic widget renderer component created
- [x] Page auto-save functionality working
- [x] Widgets persist to database
- [x] Full widget system functional

---

## 🗓️ Day 6: Advanced Features & Polish

**Objective:** Add advanced widget features and UI polish

### Morning Session (3-4 hours)

#### Task 6.1: Implement Drag-and-Drop with SortableJS

```bash
npm install sortablejs
```

Update `widgetCanvas.js`:

```javascript
import Sortable from 'sortablejs';

// In init():
this.$nextTick(() => {
    const container = this.$refs.widgetContainer;
    if (container) {
        Sortable.create(container, {
            animation: 150,
            handle: '.drag-handle',
            onEnd: (evt) => {
                // Update widget order
                const item = this.widgets.splice(evt.oldIndex, 1)[0];
                this.widgets.splice(evt.newIndex, 0, item);
                this.widgets.forEach((w, i) => w.sortOrder = i);
            }
        });
    }
});
```

#### Task 6.2: Add Undo/Redo Functionality

```javascript
// In widgetCanvas.js
history: [],
historyIndex: -1,

pushHistory() {
    this.history = this.history.slice(0, this.historyIndex + 1);
    this.history.push(JSON.parse(JSON.stringify(this.widgets)));
    this.historyIndex++;
},

undo() {
    if (this.historyIndex > 0) {
        this.historyIndex--;
        this.widgets = JSON.parse(JSON.stringify(this.history[this.historyIndex]));
    }
},

redo() {
    if (this.historyIndex < this.history.length - 1) {
        this.historyIndex++;
        this.widgets = JSON.parse(JSON.stringify(this.history[this.historyIndex]));
    }
}
```

### Afternoon Session (3-4 hours)

#### Task 6.3: Add Responsive Preview Modes

Create preview mode switcher:

```blade
<div class="flex gap-2">
    <button @click="previewMode = 'desktop'" :class="{'bg-blue-500 text-white': previewMode === 'desktop'}">
        Desktop
    </button>
    <button @click="previewMode = 'tablet'" :class="{'bg-blue-500 text-white': previewMode === 'tablet'}">
        Tablet
    </button>
    <button @click="previewMode = 'mobile'" :class="{'bg-blue-500 text-white': previewMode === 'mobile'}">
        Mobile
    </button>
</div>
```

#### Task 6.4: Add Widget Search & Filtering

Already implemented in Day 2 - enhance with keyboard shortcuts:

```javascript
@keydown.window.slash.prevent="$refs.searchInput.focus()"
```

### ✅ Day 6 Deliverables

- [x] Drag-and-drop reordering working
- [x] Undo/redo functionality implemented
- [x] Responsive preview modes working
- [x] Keyboard shortcuts added
- [x] UI polished and refined

---

## 🗓️ Day 7: Testing, Documentation & Deployment

**Objective:** Comprehensive testing and production readiness

### Morning Session (3-4 hours)

#### Task 7.1: Write Unit Tests

```bash
php artisan make:test WidgetSystemTest
```

```php
public function test_widget_registry_returns_all_widgets()
{
    $registry = app(WidgetRegistry::class);
    $widgets = $registry->all();

    $this->assertCount(28, $widgets);
    $this->assertArrayHasKey('heading', $widgets);
}

public function test_widget_can_be_saved_to_page()
{
    $page = Page::factory()->create();

    $widget = [
        'type' => 'heading',
        'props' => ['content' => 'Test Heading'],
    ];

    $page->elements()->create([
        'type' => $widget['type'],
        'properties' => $widget['props'],
        'sort_order' => 0,
    ]);

    $this->assertDatabaseHas('elements', [
        'page_id' => $page->id,
        'type' => 'heading',
    ]);
}
```

#### Task 7.2: Browser Testing Checklist

- [ ] Widget palette loads all 28 widgets
- [ ] Search filters widgets correctly
- [ ] Category tabs filter correctly
- [ ] Widgets can be added to canvas
- [ ] Widget properties can be edited
- [ ] Changes auto-save
- [ ] Widgets can be reordered
- [ ] Widgets can be duplicated
- [ ] Widgets can be deleted
- [ ] Undo/redo works correctly
- [ ] Preview modes work
- [ ] Page can be published
- [ ] Published page displays correctly

### Afternoon Session (3-4 hours)

#### Task 7.3: Performance Optimization

```php
// Add caching to WidgetRegistry
public function all(): array
{
    return Cache::remember('widget_registry', 3600, function() {
        return $this->widgets;
    });
}
```

```bash
# Enable Laravel caching
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Task 7.4: Create User Documentation

```bash
touch docs/USER-GUIDE-WIDGETS.md
```

Document:
- How to add widgets
- How to configure widgets
- Available widget types
- Tips and best practices

#### Task 7.5: Deployment Preparation

```bash
# Build for production
npm run build

# Run optimizations
php artisan optimize

# Run migrations on production
php artisan migrate --force
```

### ✅ Day 7 Deliverables

- [x] All tests passing
- [x] Browser testing complete
- [x] Performance optimized
- [x] User documentation created
- [x] Ready for production deployment

---

## Testing Strategy

### Unit Tests
- Widget Registry tests
- Widget Renderer tests
- Service layer tests

### Integration Tests
- Page auto-save with widgets
- Widget CRUD operations
- API endpoint tests

### Browser Tests
- Widget palette functionality
- Drag-and-drop behavior
- Property panel updates
- Canvas rendering

### Manual QA Checklist

- [ ] All 28 widgets render correctly
- [ ] Widget properties save and load
- [ ] No console errors
- [ ] Mobile responsive
- [ ] Cross-browser compatibility (Chrome, Firefox, Safari)
- [ ] Performance acceptable (< 2s page load)

---

## Rollback Plan

If critical issues arise:

1. **Database Rollback:**
```bash
php artisan migrate:rollback --step=1
```

2. **Code Rollback:**
```bash
git checkout main
git branch -D feature/widget-system
```

3. **Cache Clear:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Success Metrics

### Functional Metrics
- ✅ All 28 widgets implemented
- ✅ Widget system fully functional
- ✅ Zero critical bugs
- ✅ All tests passing

### Performance Metrics
- ⚡ Page builder loads < 2 seconds
- ⚡ Widget addition < 200ms
- ⚡ Auto-save < 500ms
- ⚡ Preview generation < 1 second

### User Experience Metrics
- 🎯 Intuitive widget selection
- 🎯 Real-time preview updates
- 🎯 Smooth drag-and-drop
- 🎯 No UI lag or jank

---

## Post-Implementation Checklist

- [ ] All code committed and pushed
- [ ] Pull request created
- [ ] Code review completed
- [ ] Tests passing in CI/CD
- [ ] Documentation updated
- [ ] User guide created
- [ ] Deployed to staging
- [ ] QA testing complete
- [ ] Deployed to production
- [ ] Monitoring in place

---

## Support & Resources

### Documentation References
- `docs/features/07-WIDGET-SYSTEM.md` - Complete widget specifications
- `docs/steps/day06/step01-component-library.md` - Implementation guide
- `docs/backend/02-DATABASE-SCHEMA.md` - Database schema
- `docs/backend/03-API-ENDPOINTS.md` - API documentation

### Key Services
- **WidgetRegistry:** Manages all 28 widget configurations
- **WidgetRenderer:** Server-side rendering service
- **WidgetController:** API endpoints for widget data

### AlpineJS Components
- **widgetPalette:** Left sidebar widget selection
- **widgetProperties:** Right sidebar property editing
- **widgetCanvas:** Center canvas for building pages

---

## Notes

- Use `php artisan tinker` to test services
- Check browser console for JavaScript errors
- Use Laravel Debugbar for performance profiling
- Keep commits small and focused
- Test frequently, don't wait until the end

---

**Good luck with your implementation! 🚀**

This plan takes you from your current codebase to a fully functional widget system in just one week, following best practices and maintaining your existing architecture.
