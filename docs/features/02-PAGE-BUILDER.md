# Page Builder Feature Documentation

> # ⚠️ DOCUMENTATION OUTDATED - SEE CURRENT IMPLEMENTATION BELOW
>
> ## Actual Implementation (Current)
>
> The page builder has been implemented as a **Vue.js 3 Single Page Application (SPA)** with **Pinia** state management:
>
> - **Backend:** Laravel 12 (PHP 8.2+) - REST API
> - **Frontend:** Vue.js 3 (Composition API) + Pinia + TailwindCSS v4 + Vite 5.x
> - **Architecture:** SPA mounted in Blade template
> - **22+ Widgets:** Elementor-inspired widget system with Content/Style/Advanced tabs
>
> **See Current Documentation:**
> - **[docs/frontend/03-DRAG-DROP-BUILDER.md](../frontend/03-DRAG-DROP-BUILDER.md)** - Complete Vue.js 3 + Pinia architecture
> - **[docs/features/07-WIDGET-SYSTEM.md](./07-WIDGET-SYSTEM.md)** - 28 Elementor widget specifications
> - **[docs/REVISED-WIDGET-IMPLEMENTATION-PLAN.md](../REVISED-WIDGET-IMPLEMENTATION-PLAN.md)** - Implementation roadmap
>
> ---
>
> ## Original Planned Tech Stack (Below - NOT Implemented)
>
> The documentation below describes the original AlpineJS-based approach, which was **NOT implemented**. It is kept for reference only.
>
> - **Backend:** Laravel (PHP)
> - **Frontend:** Blade templates, AlpineJS, TailwindCSS v4
> - **No React, Vue, TypeScript, or other JS frameworks** ← **This changed: Now uses Vue.js 3**

---

## 1. Builder Layout (Blade)

### Main Builder View

```blade
{{-- resources/views/builder/index.blade.php --}}
@extends('layouts.builder')

@section('content')
<div x-data="pageBuilder()"
     x-init="init()"
     @keydown.window="handleKeydown($event)"
     class="h-screen flex flex-col bg-gray-100">

    {{-- Top Toolbar --}}
    <header class="h-14 bg-white border-b border-gray-200 flex items-center justify-between px-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <input type="text"
                   x-model="pageSettings.title"
                   @change="markDirty()"
                   class="text-lg font-semibold bg-transparent border-none focus:ring-0 focus:outline-none"
                   placeholder="Page Title">
        </div>

        <div class="flex items-center gap-3">
            <span x-show="isDirty" class="text-sm text-amber-600">Unsaved changes</span>
            <span x-show="isSaving" class="text-sm text-blue-600">Saving...</span>
            <span x-show="lastSaved && !isDirty && !isSaving" class="text-sm text-gray-500" x-text="'Saved ' + lastSaved"></span>

            <button @click="openPageSettings()" class="px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100 rounded">
                Settings
            </button>
            <button @click="preview()" class="px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100 rounded">
                Preview
            </button>
            <button @click="save()"
                    :disabled="isSaving"
                    class="px-4 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                Save
            </button>
            <button @click="publish()" class="px-4 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700">
                Publish
            </button>
        </div>
    </header>

    {{-- Main Builder Area --}}
    <div class="flex-1 flex overflow-hidden">

        {{-- Left Sidebar - Elements Panel --}}
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
            <div class="p-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Elements</h3>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                @include('builder.partials.elements-panel')
            </div>
        </aside>

        {{-- Canvas Area --}}
        <main class="flex-1 overflow-auto p-8 bg-gray-200">
            <div class="max-w-4xl mx-auto bg-white shadow-lg min-h-full"
                 @click.self="deselectElement()"
                 @drop.prevent="handleDrop($event)"
                 @dragover.prevent="handleDragOver($event)">

                {{-- Rendered Elements --}}
                <template x-for="(element, index) in elements" :key="element.id">
                    <div :class="{'ring-2 ring-blue-500': selectedElementId === element.id}"
                         @click.stop="selectElement(element.id)"
                         @dblclick="editElement(element.id)"
                         draggable="true"
                         @dragstart="handleElementDragStart($event, index)"
                         @dragend="handleElementDragEnd($event)"
                         class="relative group cursor-pointer">

                        {{-- Element Controls --}}
                        <div x-show="selectedElementId === element.id"
                             class="absolute -top-8 left-0 flex items-center gap-1 bg-blue-500 text-white text-xs rounded px-2 py-1">
                            <span x-text="element.type"></span>
                            <button @click.stop="moveElementUp(index)" :disabled="index === 0" class="hover:bg-blue-600 p-0.5 rounded disabled:opacity-50">↑</button>
                            <button @click.stop="moveElementDown(index)" :disabled="index === elements.length - 1" class="hover:bg-blue-600 p-0.5 rounded disabled:opacity-50">↓</button>
                            <button @click.stop="duplicateElement(index)" class="hover:bg-blue-600 p-0.5 rounded">⧉</button>
                            <button @click.stop="deleteElement(index)" class="hover:bg-blue-600 p-0.5 rounded text-red-200">×</button>
                        </div>

                        {{-- Element Content --}}
                        <div x-html="renderElement(element)"></div>
                    </div>
                </template>

                {{-- Empty State --}}
                <div x-show="elements.length === 0" class="p-12 text-center text-gray-500">
                    <p class="text-lg mb-2">Start building your page</p>
                    <p class="text-sm">Drag elements from the left panel or click to add</p>
                </div>
            </div>
        </main>

        {{-- Right Sidebar - Properties Panel --}}
        <aside x-show="selectedElementId"
               x-transition
               class="w-80 bg-white border-l border-gray-200 flex flex-col">
            @include('builder.partials.properties-panel')
        </aside>
    </div>

    {{-- Page Settings Modal --}}
    @include('builder.partials.page-settings-modal')
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/builder.js') }}"></script>
@endpush
```

---

## 2. State Management with Alpine.store()

### Global Store Setup

```javascript
// public/js/builder.js

// Global Alpine Store for shared state
document.addEventListener('alpine:init', () => {
    Alpine.store('builder', {
        // Page data
        pageId: null,
        elements: [],
        pageSettings: {
            title: '',
            slug: '',
            description: '',
            metaTitle: '',
            metaDescription: '',
            customCss: '',
            customJs: ''
        },

        // UI state
        selectedElementId: null,
        isDirty: false,
        isSaving: false,
        lastSaved: null,
        clipboard: null,
        history: [],
        historyIndex: -1,

        // Methods
        init(pageId, initialData) {
            this.pageId = pageId;
            this.elements = initialData.elements || [];
            this.pageSettings = { ...this.pageSettings, ...initialData.settings };
            this.pushHistory();
        },

        markDirty() {
            this.isDirty = true;
        },

        pushHistory() {
            this.history = this.history.slice(0, this.historyIndex + 1);
            this.history.push(JSON.stringify(this.elements));
            this.historyIndex = this.history.length - 1;

            // Limit history size
            if (this.history.length > 50) {
                this.history.shift();
                this.historyIndex--;
            }
        }
    });
});

// Main page builder component
function pageBuilder() {
    return {
        // Local references to store
        get elements() { return Alpine.store('builder').elements; },
        set elements(val) { Alpine.store('builder').elements = val; },
        get selectedElementId() { return Alpine.store('builder').selectedElementId; },
        set selectedElementId(val) { Alpine.store('builder').selectedElementId = val; },
        get pageSettings() { return Alpine.store('builder').pageSettings; },
        get isDirty() { return Alpine.store('builder').isDirty; },
        set isDirty(val) { Alpine.store('builder').isDirty = val; },
        get isSaving() { return Alpine.store('builder').isSaving; },
        set isSaving(val) { Alpine.store('builder').isSaving = val; },
        get lastSaved() { return Alpine.store('builder').lastSaved; },
        set lastSaved(val) { Alpine.store('builder').lastSaved = val; },

        // Auto-save timer
        autoSaveTimer: null,

        init() {
            const pageData = JSON.parse(document.getElementById('page-data').textContent);
            Alpine.store('builder').init(pageData.id, pageData);

            // Setup auto-save
            this.setupAutoSave();
        },

        setupAutoSave() {
            setInterval(() => {
                if (this.isDirty && !this.isSaving) {
                    this.save();
                }
            }, 30000); // Auto-save every 30 seconds
        },

        markDirty() {
            Alpine.store('builder').markDirty();
        },

        // ... more methods below
    };
}
```

### Component-Level State with x-data

```blade
{{-- Alternative: Self-contained component state --}}
<div x-data="{
    elements: @js($page->elements ?? []),
    selectedElementId: null,
    isDirty: false,

    selectElement(id) {
        this.selectedElementId = id;
    },

    addElement(type) {
        const element = this.createElement(type);
        this.elements.push(element);
        this.selectedElementId = element.id;
        this.isDirty = true;
    }
}">
    {{-- Builder content --}}
</div>
```

---

## 3. Element System

### Element Types Configuration

```javascript
// public/js/builder.js

const ELEMENT_TYPES = {
    heading: {
        name: 'Heading',
        icon: 'H',
        category: 'typography',
        defaultProps: {
            content: 'New Heading',
            level: 'h2',
            alignment: 'left',
            color: '#000000',
            fontSize: '2rem',
            fontWeight: 'bold'
        }
    },
    paragraph: {
        name: 'Paragraph',
        icon: '¶',
        category: 'typography',
        defaultProps: {
            content: 'Enter your text here...',
            alignment: 'left',
            color: '#333333',
            fontSize: '1rem',
            lineHeight: '1.6'
        }
    },
    image: {
        name: 'Image',
        icon: '🖼',
        category: 'media',
        defaultProps: {
            src: '',
            alt: '',
            width: '100%',
            height: 'auto',
            objectFit: 'cover',
            borderRadius: '0'
        }
    },
    button: {
        name: 'Button',
        icon: '▢',
        category: 'interactive',
        defaultProps: {
            text: 'Click Me',
            url: '#',
            target: '_self',
            bgColor: '#3B82F6',
            textColor: '#FFFFFF',
            padding: '12px 24px',
            borderRadius: '6px',
            fontSize: '1rem'
        }
    },
    divider: {
        name: 'Divider',
        icon: '—',
        category: 'layout',
        defaultProps: {
            style: 'solid',
            color: '#E5E7EB',
            thickness: '1px',
            width: '100%',
            margin: '24px 0'
        }
    },
    spacer: {
        name: 'Spacer',
        icon: '↕',
        category: 'layout',
        defaultProps: {
            height: '40px'
        }
    },
    columns: {
        name: 'Columns',
        icon: '⊞',
        category: 'layout',
        defaultProps: {
            columns: 2,
            gap: '24px',
            children: [[], []]
        }
    },
    video: {
        name: 'Video',
        icon: '▶',
        category: 'media',
        defaultProps: {
            url: '',
            provider: 'youtube',
            aspectRatio: '16/9'
        }
    },
    form: {
        name: 'Form',
        icon: '📝',
        category: 'interactive',
        defaultProps: {
            fields: [],
            submitText: 'Submit',
            action: '',
            method: 'POST'
        }
    },
    html: {
        name: 'Custom HTML',
        icon: '</>',
        category: 'advanced',
        defaultProps: {
            content: '<div>Custom HTML here</div>'
        }
    }
};

// Element creation and rendering methods
function pageBuilder() {
    return {
        // ... previous code ...

        createElement(type) {
            const config = ELEMENT_TYPES[type];
            return {
                id: 'el_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                type: type,
                props: { ...config.defaultProps }
            };
        },

        renderElement(element) {
            const renderers = {
                heading: (el) => {
                    const tag = el.props.level || 'h2';
                    return `<${tag} style="text-align: ${el.props.alignment}; color: ${el.props.color}; font-size: ${el.props.fontSize}; font-weight: ${el.props.fontWeight}; margin: 0; padding: 16px;">${this.escapeHtml(el.props.content)}</${tag}>`;
                },

                paragraph: (el) => {
                    return `<p style="text-align: ${el.props.alignment}; color: ${el.props.color}; font-size: ${el.props.fontSize}; line-height: ${el.props.lineHeight}; margin: 0; padding: 16px;">${this.escapeHtml(el.props.content)}</p>`;
                },

                image: (el) => {
                    if (!el.props.src) {
                        return `<div style="padding: 40px; background: #f3f4f6; text-align: center; color: #9ca3af;">Click to add image</div>`;
                    }
                    return `<img src="${el.props.src}" alt="${this.escapeHtml(el.props.alt)}" style="width: ${el.props.width}; height: ${el.props.height}; object-fit: ${el.props.objectFit}; border-radius: ${el.props.borderRadius}; display: block;">`;
                },

                button: (el) => {
                    return `<div style="padding: 16px; text-align: center;"><a href="${el.props.url}" target="${el.props.target}" style="display: inline-block; background: ${el.props.bgColor}; color: ${el.props.textColor}; padding: ${el.props.padding}; border-radius: ${el.props.borderRadius}; font-size: ${el.props.fontSize}; text-decoration: none;">${this.escapeHtml(el.props.text)}</a></div>`;
                },

                divider: (el) => {
                    return `<hr style="border: none; border-top: ${el.props.thickness} ${el.props.style} ${el.props.color}; width: ${el.props.width}; margin: ${el.props.margin};">`;
                },

                spacer: (el) => {
                    return `<div style="height: ${el.props.height};"></div>`;
                },

                video: (el) => {
                    if (!el.props.url) {
                        return `<div style="padding: 40px; background: #f3f4f6; text-align: center; color: #9ca3af;">Add video URL</div>`;
                    }
                    const embedUrl = this.getVideoEmbedUrl(el.props.url, el.props.provider);
                    return `<div style="aspect-ratio: ${el.props.aspectRatio}; width: 100%;"><iframe src="${embedUrl}" style="width: 100%; height: 100%; border: none;" allowfullscreen></iframe></div>`;
                },

                html: (el) => {
                    return `<div style="padding: 16px;">${el.props.content}</div>`;
                }
            };

            return renderers[element.type] ? renderers[element.type](element) : '<div>Unknown element</div>';
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        getVideoEmbedUrl(url, provider) {
            if (provider === 'youtube') {
                const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/);
                return match ? `https://www.youtube.com/embed/${match[1]}` : '';
            }
            if (provider === 'vimeo') {
                const match = url.match(/vimeo\.com\/(\d+)/);
                return match ? `https://player.vimeo.com/video/${match[1]}` : '';
            }
            return url;
        }
    };
}
```

### Elements Panel (Blade Partial)

```blade
{{-- resources/views/builder/partials/elements-panel.blade.php --}}
<div x-data="{ activeCategory: 'all' }">
    {{-- Category Tabs --}}
    <div class="flex flex-wrap gap-1 mb-4">
        <button @click="activeCategory = 'all'"
                :class="activeCategory === 'all' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'"
                class="px-2 py-1 text-xs rounded">
            All
        </button>
        <button @click="activeCategory = 'typography'"
                :class="activeCategory === 'typography' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'"
                class="px-2 py-1 text-xs rounded">
            Text
        </button>
        <button @click="activeCategory = 'media'"
                :class="activeCategory === 'media' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'"
                class="px-2 py-1 text-xs rounded">
            Media
        </button>
        <button @click="activeCategory = 'layout'"
                :class="activeCategory === 'layout' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'"
                class="px-2 py-1 text-xs rounded">
            Layout
        </button>
        <button @click="activeCategory = 'interactive'"
                :class="activeCategory === 'interactive' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'"
                class="px-2 py-1 text-xs rounded">
            Interactive
        </button>
    </div>

    {{-- Elements Grid --}}
    <div class="grid grid-cols-2 gap-2">
        <template x-for="(config, type) in $store.builder.elementTypes" :key="type">
            <button x-show="activeCategory === 'all' || activeCategory === config.category"
                    @click="$dispatch('add-element', { type })"
                    draggable="true"
                    @dragstart="$event.dataTransfer.setData('element-type', type)"
                    class="flex flex-col items-center gap-2 p-3 bg-gray-50 hover:bg-gray-100 rounded border border-gray-200 transition-colors">
                <span class="text-lg" x-text="config.icon"></span>
                <span class="text-xs text-gray-600" x-text="config.name"></span>
            </button>
        </template>
    </div>
</div>
```

---

## 4. Properties Panel with Alpine

```blade
{{-- resources/views/builder/partials/properties-panel.blade.php --}}
<div class="h-full flex flex-col" x-show="selectedElementId">
    <div class="p-4 border-b border-gray-200">
        <h3 class="font-semibold text-gray-900">Properties</h3>
    </div>

    <div class="flex-1 overflow-y-auto p-4 space-y-4">
        <template x-if="getSelectedElement()">
            <div>
                {{-- Dynamic property fields based on element type --}}

                {{-- Heading Properties --}}
                <template x-if="getSelectedElement().type === 'heading'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                            <input type="text"
                                   x-model="getSelectedElement().props.content"
                                   @input="markDirty()"
                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                            <select x-model="getSelectedElement().props.level"
                                    @change="markDirty()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="h1">H1</option>
                                <option value="h2">H2</option>
                                <option value="h3">H3</option>
                                <option value="h4">H4</option>
                                <option value="h5">H5</option>
                                <option value="h6">H6</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alignment</label>
                            <div class="flex gap-1">
                                <button @click="getSelectedElement().props.alignment = 'left'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'left' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100'"
                                        class="flex-1 py-2 text-sm rounded">Left</button>
                                <button @click="getSelectedElement().props.alignment = 'center'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'center' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100'"
                                        class="flex-1 py-2 text-sm rounded">Center</button>
                                <button @click="getSelectedElement().props.alignment = 'right'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'right' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100'"
                                        class="flex-1 py-2 text-sm rounded">Right</button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                            <input type="color"
                                   x-model="getSelectedElement().props.color"
                                   @input="markDirty()"
                                   class="w-full h-10 rounded cursor-pointer">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Font Size</label>
                            <input type="text"
                                   x-model="getSelectedElement().props.fontSize"
                                   @input="markDirty()"
                                   placeholder="e.g., 2rem, 24px"
                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </template>

                {{-- Paragraph Properties --}}
                <template x-if="getSelectedElement().type === 'paragraph'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                            <textarea x-model="getSelectedElement().props.content"
                                      @input="markDirty()"
                                      rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alignment</label>
                            <div class="flex gap-1">
                                <button @click="getSelectedElement().props.alignment = 'left'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'left' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100'"
                                        class="flex-1 py-2 text-sm rounded">Left</button>
                                <button @click="getSelectedElement().props.alignment = 'center'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'center' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100'"
                                        class="flex-1 py-2 text-sm rounded">Center</button>
                                <button @click="getSelectedElement().props.alignment = 'right'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'right' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100'"
                                        class="flex-1 py-2 text-sm rounded">Right</button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                            <input type="color"
                                   x-model="getSelectedElement().props.color"
                                   @input="markDirty()"
                                   class="w-full h-10 rounded cursor-pointer">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Line Height</label>
                            <input type="text"
                                   x-model="getSelectedElement().props.lineHeight"
                                   @input="markDirty()"
                                   placeholder="e.g., 1.6"
                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </template>

                {{-- Image Properties --}}
                <template x-if="getSelectedElement().type === 'image'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                            <input type="text"
                                   x-model="getSelectedElement().props.src"
                                   @input="markDirty()"
                                   placeholder="https://..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <button @click="openMediaLibrary()"
                                    class="w-full px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm">
                                Choose from Library
                            </button>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alt Text</label>
                            <input type="text"
                                   x-model="getSelectedElement().props.alt"
                                   @input="markDirty()"
                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Width</label>
                                <input type="text"
                                       x-model="getSelectedElement().props.width"
                                       @input="markDirty()"
                                       placeholder="100%"
                                       class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Height</label>
                                <input type="text"
                                       x-model="getSelectedElement().props.height"
                                       @input="markDirty()"
                                       placeholder="auto"
                                       class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Border Radius</label>
                            <input type="text"
                                   x-model="getSelectedElement().props.borderRadius"
                                   @input="markDirty()"
                                   placeholder="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </template>

                {{-- Button Properties --}}
                <template x-if="getSelectedElement().type === 'button'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                            <input type="text"
                                   x-model="getSelectedElement().props.text"
                                   @input="markDirty()"
                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Link URL</label>
                            <input type="text"
                                   x-model="getSelectedElement().props.url"
                                   @input="markDirty()"
                                   placeholder="https://..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Target</label>
                            <select x-model="getSelectedElement().props.target"
                                    @change="markDirty()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="_self">Same Window</option>
                                <option value="_blank">New Window</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Background</label>
                                <input type="color"
                                       x-model="getSelectedElement().props.bgColor"
                                       @input="markDirty()"
                                       class="w-full h-10 rounded cursor-pointer">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Text Color</label>
                                <input type="color"
                                       x-model="getSelectedElement().props.textColor"
                                       @input="markDirty()"
                                       class="w-full h-10 rounded cursor-pointer">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Padding</label>
                            <input type="text"
                                   x-model="getSelectedElement().props.padding"
                                   @input="markDirty()"
                                   placeholder="12px 24px"
                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Border Radius</label>
                            <input type="text"
                                   x-model="getSelectedElement().props.borderRadius"
                                   @input="markDirty()"
                                   placeholder="6px"
                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </template>

                {{-- Spacer Properties --}}
                <template x-if="getSelectedElement().type === 'spacer'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Height</label>
                            <input type="text"
                                   x-model="getSelectedElement().props.height"
                                   @input="markDirty()"
                                   placeholder="40px"
                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </template>

                {{-- Divider Properties --}}
                <template x-if="getSelectedElement().type === 'divider'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Style</label>
                            <select x-model="getSelectedElement().props.style"
                                    @change="markDirty()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="solid">Solid</option>
                                <option value="dashed">Dashed</option>
                                <option value="dotted">Dotted</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                            <input type="color"
                                   x-model="getSelectedElement().props.color"
                                   @input="markDirty()"
                                   class="w-full h-10 rounded cursor-pointer">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Thickness</label>
                            <input type="text"
                                   x-model="getSelectedElement().props.thickness"
                                   @input="markDirty()"
                                   placeholder="1px"
                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
```

---

## 5. Keyboard Shortcuts

```javascript
// public/js/builder.js

function pageBuilder() {
    return {
        // ... previous code ...

        handleKeydown(event) {
            // Ignore if typing in input/textarea
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName)) {
                return;
            }

            const key = event.key.toLowerCase();
            const ctrl = event.ctrlKey || event.metaKey;
            const shift = event.shiftKey;

            // Save: Ctrl+S
            if (ctrl && key === 's') {
                event.preventDefault();
                this.save();
                return;
            }

            // Undo: Ctrl+Z
            if (ctrl && !shift && key === 'z') {
                event.preventDefault();
                this.undo();
                return;
            }

            // Redo: Ctrl+Shift+Z or Ctrl+Y
            if ((ctrl && shift && key === 'z') || (ctrl && key === 'y')) {
                event.preventDefault();
                this.redo();
                return;
            }

            // Copy: Ctrl+C
            if (ctrl && key === 'c' && this.selectedElementId) {
                event.preventDefault();
                this.copyElement();
                return;
            }

            // Cut: Ctrl+X
            if (ctrl && key === 'x' && this.selectedElementId) {
                event.preventDefault();
                this.cutElement();
                return;
            }

            // Paste: Ctrl+V
            if (ctrl && key === 'v' && Alpine.store('builder').clipboard) {
                event.preventDefault();
                this.pasteElement();
                return;
            }

            // Duplicate: Ctrl+D
            if (ctrl && key === 'd' && this.selectedElementId) {
                event.preventDefault();
                const index = this.getSelectedElementIndex();
                if (index !== -1) {
                    this.duplicateElement(index);
                }
                return;
            }

            // Delete: Delete or Backspace
            if ((key === 'delete' || key === 'backspace') && this.selectedElementId) {
                event.preventDefault();
                const index = this.getSelectedElementIndex();
                if (index !== -1) {
                    this.deleteElement(index);
                }
                return;
            }

            // Deselect: Escape
            if (key === 'escape') {
                event.preventDefault();
                this.deselectElement();
                return;
            }

            // Move up: Ctrl+ArrowUp
            if (ctrl && key === 'arrowup' && this.selectedElementId) {
                event.preventDefault();
                const index = this.getSelectedElementIndex();
                if (index > 0) {
                    this.moveElementUp(index);
                }
                return;
            }

            // Move down: Ctrl+ArrowDown
            if (ctrl && key === 'arrowdown' && this.selectedElementId) {
                event.preventDefault();
                const index = this.getSelectedElementIndex();
                if (index < this.elements.length - 1) {
                    this.moveElementDown(index);
                }
                return;
            }

            // Navigate elements: ArrowUp/ArrowDown
            if (key === 'arrowup' || key === 'arrowdown') {
                event.preventDefault();
                this.navigateElements(key === 'arrowup' ? -1 : 1);
                return;
            }

            // Preview: Ctrl+P
            if (ctrl && key === 'p') {
                event.preventDefault();
                this.preview();
                return;
            }
        },

        navigateElements(direction) {
            if (this.elements.length === 0) return;

            if (!this.selectedElementId) {
                // Select first or last element
                const index = direction === 1 ? 0 : this.elements.length - 1;
                this.selectElement(this.elements[index].id);
                return;
            }

            const currentIndex = this.getSelectedElementIndex();
            const newIndex = currentIndex + direction;

            if (newIndex >= 0 && newIndex < this.elements.length) {
                this.selectElement(this.elements[newIndex].id);
            }
        },

        copyElement() {
            const element = this.getSelectedElement();
            if (element) {
                Alpine.store('builder').clipboard = JSON.parse(JSON.stringify(element));
            }
        },

        cutElement() {
            this.copyElement();
            const index = this.getSelectedElementIndex();
            if (index !== -1) {
                this.deleteElement(index);
            }
        },

        pasteElement() {
            const clipboard = Alpine.store('builder').clipboard;
            if (!clipboard) return;

            const newElement = JSON.parse(JSON.stringify(clipboard));
            newElement.id = 'el_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

            const index = this.getSelectedElementIndex();
            if (index !== -1) {
                this.elements.splice(index + 1, 0, newElement);
            } else {
                this.elements.push(newElement);
            }

            this.selectedElementId = newElement.id;
            this.markDirty();
            Alpine.store('builder').pushHistory();
        },

        undo() {
            const store = Alpine.store('builder');
            if (store.historyIndex > 0) {
                store.historyIndex--;
                store.elements = JSON.parse(store.history[store.historyIndex]);
                this.selectedElementId = null;
                this.markDirty();
            }
        },

        redo() {
            const store = Alpine.store('builder');
            if (store.historyIndex < store.history.length - 1) {
                store.historyIndex++;
                store.elements = JSON.parse(store.history[store.historyIndex]);
                this.selectedElementId = null;
                this.markDirty();
            }
        }
    };
}
```

### Keyboard Shortcuts Help Modal

```blade
{{-- resources/views/builder/partials/shortcuts-modal.blade.php --}}
<div x-data="{ open: false }"
     @keydown.window.shift.?="open = true"
     @keydown.window.escape="open = false">

    <button @click="open = true" class="text-gray-500 hover:text-gray-700" title="Keyboard Shortcuts (Shift+?)">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </button>

    <div x-show="open"
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black opacity-50" @click="open = false"></div>

            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold mb-4">Keyboard Shortcuts</h3>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span>Save</span>
                        <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl+S</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span>Undo</span>
                        <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl+Z</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span>Redo</span>
                        <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl+Shift+Z</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span>Copy Element</span>
                        <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl+C</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span>Cut Element</span>
                        <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl+X</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span>Paste Element</span>
                        <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl+V</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span>Duplicate Element</span>
                        <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl+D</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span>Delete Element</span>
                        <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Delete</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span>Move Element Up</span>
                        <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl+Up</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span>Move Element Down</span>
                        <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl+Down</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span>Deselect</span>
                        <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Escape</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span>Preview</span>
                        <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl+P</kbd>
                    </div>
                </div>

                <button @click="open = false" class="mt-6 w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
```

---

## 6. Auto-save with fetch()

```javascript
// public/js/builder.js

function pageBuilder() {
    return {
        // ... previous code ...

        async save() {
            if (this.isSaving) return;

            this.isSaving = true;

            try {
                const response = await fetch(`/api/pages/${Alpine.store('builder').pageId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        elements: this.elements,
                        settings: this.pageSettings
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                this.isDirty = false;
                this.lastSaved = this.formatTime(new Date());

                // Push to history after successful save
                Alpine.store('builder').pushHistory();

                // Show success notification
                this.showNotification('Page saved successfully', 'success');

                return data;
            } catch (error) {
                console.error('Save failed:', error);
                this.showNotification('Failed to save page', 'error');
                throw error;
            } finally {
                this.isSaving = false;
            }
        },

        async publish() {
            try {
                // Save first
                await this.save();

                const response = await fetch(`/api/pages/${Alpine.store('builder').pageId}/publish`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                this.showNotification('Page published successfully!', 'success');

                // Open published page in new tab
                if (data.url) {
                    window.open(data.url, '_blank');
                }

                return data;
            } catch (error) {
                console.error('Publish failed:', error);
                this.showNotification('Failed to publish page', 'error');
                throw error;
            }
        },

        formatTime(date) {
            const now = new Date();
            const diff = now - date;

            if (diff < 60000) {
                return 'just now';
            } else if (diff < 3600000) {
                const mins = Math.floor(diff / 60000);
                return `${mins} min ago`;
            } else {
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
        },

        showNotification(message, type = 'info') {
            // Dispatch event for notification component
            window.dispatchEvent(new CustomEvent('show-notification', {
                detail: { message, type }
            }));
        },

        // Warn before leaving with unsaved changes
        setupBeforeUnload() {
            window.addEventListener('beforeunload', (e) => {
                if (this.isDirty) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        }
    };
}
```

### Auto-save Configuration

```javascript
// Extended auto-save with debounce
function pageBuilder() {
    return {
        autoSaveDebounceTimer: null,

        setupAutoSave() {
            // Periodic auto-save every 30 seconds
            setInterval(() => {
                if (this.isDirty && !this.isSaving) {
                    this.save();
                }
            }, 30000);

            // Setup beforeunload warning
            this.setupBeforeUnload();
        },

        // Debounced auto-save on changes (saves 3 seconds after last change)
        triggerAutoSave() {
            this.markDirty();

            clearTimeout(this.autoSaveDebounceTimer);
            this.autoSaveDebounceTimer = setTimeout(() => {
                if (this.isDirty && !this.isSaving) {
                    this.save();
                }
            }, 3000);
        }
    };
}
```

---

## 7. Page Settings Modal

```blade
{{-- resources/views/builder/partials/page-settings-modal.blade.php --}}
<div x-data="{ open: false }"
     @open-page-settings.window="open = true"
     @keydown.escape.window="open = false">

    {{-- Modal Backdrop --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black bg-opacity-50"
         @click="open = false"
         style="display: none;">
    </div>

    {{-- Modal Content --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden" @click.stop>
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Page Settings</h2>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="px-6 py-4 overflow-y-auto max-h-[60vh]">
                <div x-data="{ activeTab: 'general' }">
                    {{-- Tabs --}}
                    <div class="flex border-b border-gray-200 mb-4">
                        <button @click="activeTab = 'general'"
                                :class="activeTab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px">
                            General
                        </button>
                        <button @click="activeTab = 'seo'"
                                :class="activeTab === 'seo' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px">
                            SEO
                        </button>
                        <button @click="activeTab = 'advanced'"
                                :class="activeTab === 'advanced' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px">
                            Advanced
                        </button>
                    </div>

                    {{-- General Tab --}}
                    <div x-show="activeTab === 'general'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Page Title</label>
                            <input type="text"
                                   x-model="$store.builder.pageSettings.title"
                                   @input="$parent.markDirty()"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL Slug</label>
                            <div class="flex">
                                <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-500 text-sm">
                                    {{ config('app.url') }}/p/
                                </span>
                                <input type="text"
                                       x-model="$store.builder.pageSettings.slug"
                                       @input="$parent.markDirty()"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-r-md focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea x-model="$store.builder.pageSettings.description"
                                      @input="$parent.markDirty()"
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Favicon</label>
                            <div class="flex items-center gap-3">
                                <img x-show="$store.builder.pageSettings.favicon"
                                     :src="$store.builder.pageSettings.favicon"
                                     class="w-8 h-8 rounded">
                                <button @click="$dispatch('open-media-library', { target: 'favicon' })"
                                        class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm">
                                    Choose Favicon
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- SEO Tab --}}
                    <div x-show="activeTab === 'seo'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                            <input type="text"
                                   x-model="$store.builder.pageSettings.metaTitle"
                                   @input="$parent.markDirty()"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                   maxlength="60">
                            <p class="mt-1 text-xs text-gray-500">
                                <span x-text="($store.builder.pageSettings.metaTitle || '').length"></span>/60 characters
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                            <textarea x-model="$store.builder.pageSettings.metaDescription"
                                      @input="$parent.markDirty()"
                                      rows="3"
                                      maxlength="160"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                <span x-text="($store.builder.pageSettings.metaDescription || '').length"></span>/160 characters
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Social Image (Open Graph)</label>
                            <div class="flex items-center gap-3">
                                <img x-show="$store.builder.pageSettings.ogImage"
                                     :src="$store.builder.pageSettings.ogImage"
                                     class="w-32 h-20 object-cover rounded">
                                <button @click="$dispatch('open-media-library', { target: 'ogImage' })"
                                        class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm">
                                    Choose Image
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Recommended: 1200x630 pixels</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox"
                                   x-model="$store.builder.pageSettings.noIndex"
                                   @change="$parent.markDirty()"
                                   id="noindex"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="noindex" class="text-sm text-gray-700">
                                Hide from search engines (noindex)
                            </label>
                        </div>
                    </div>

                    {{-- Advanced Tab --}}
                    <div x-show="activeTab === 'advanced'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Custom CSS</label>
                            <textarea x-model="$store.builder.pageSettings.customCss"
                                      @input="$parent.markDirty()"
                                      rows="6"
                                      placeholder="/* Your custom CSS here */"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Custom JavaScript</label>
                            <textarea x-model="$store.builder.pageSettings.customJs"
                                      @input="$parent.markDirty()"
                                      rows="6"
                                      placeholder="// Your custom JavaScript here"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"></textarea>
                            <p class="mt-1 text-xs text-gray-500">Warning: Custom JavaScript can affect page functionality</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Header Code</label>
                            <textarea x-model="$store.builder.pageSettings.headerCode"
                                      @input="$parent.markDirty()"
                                      rows="4"
                                      placeholder="<!-- Analytics, fonts, etc -->"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200">
                <button @click="open = false"
                        class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                    Cancel
                </button>
                <button @click="open = false"
                        class="px-4 py-2 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded">
                    Apply
                </button>
            </div>
        </div>
    </div>
</div>
```

---

## 8. API Calls with fetch()

### Complete API Service

```javascript
// public/js/builder.js

const BuilderAPI = {
    baseUrl: '/api',

    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                ...options.headers
            },
            ...options
        };

        const response = await fetch(url, config);

        if (!response.ok) {
            const error = await response.json().catch(() => ({}));
            throw new Error(error.message || `HTTP error! status: ${response.status}`);
        }

        return response.json();
    },

    // Page endpoints
    pages: {
        get(id) {
            return BuilderAPI.request(`/pages/${id}`);
        },

        update(id, data) {
            return BuilderAPI.request(`/pages/${id}`, {
                method: 'PUT',
                body: JSON.stringify(data)
            });
        },

        publish(id) {
            return BuilderAPI.request(`/pages/${id}/publish`, {
                method: 'POST'
            });
        },

        unpublish(id) {
            return BuilderAPI.request(`/pages/${id}/unpublish`, {
                method: 'POST'
            });
        },

        duplicate(id) {
            return BuilderAPI.request(`/pages/${id}/duplicate`, {
                method: 'POST'
            });
        },

        delete(id) {
            return BuilderAPI.request(`/pages/${id}`, {
                method: 'DELETE'
            });
        }
    },

    // Media endpoints
    media: {
        list(params = {}) {
            const query = new URLSearchParams(params).toString();
            return BuilderAPI.request(`/media?${query}`);
        },

        async upload(file, onProgress) {
            const formData = new FormData();
            formData.append('file', file);

            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();

                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable && onProgress) {
                        onProgress(Math.round((e.loaded / e.total) * 100));
                    }
                });

                xhr.addEventListener('load', () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        resolve(JSON.parse(xhr.responseText));
                    } else {
                        reject(new Error(`Upload failed: ${xhr.status}`));
                    }
                });

                xhr.addEventListener('error', () => reject(new Error('Upload failed')));

                xhr.open('POST', `${BuilderAPI.baseUrl}/media`);
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.send(formData);
            });
        },

        delete(id) {
            return BuilderAPI.request(`/media/${id}`, {
                method: 'DELETE'
            });
        }
    },

    // Template endpoints
    templates: {
        list() {
            return BuilderAPI.request('/templates');
        },

        get(id) {
            return BuilderAPI.request(`/templates/${id}`);
        },

        saveAs(pageId, name) {
            return BuilderAPI.request(`/pages/${pageId}/save-as-template`, {
                method: 'POST',
                body: JSON.stringify({ name })
            });
        }
    }
};

// Usage in pageBuilder component
function pageBuilder() {
    return {
        // ... previous code ...

        async loadPage(pageId) {
            try {
                const data = await BuilderAPI.pages.get(pageId);
                Alpine.store('builder').init(pageId, data);
            } catch (error) {
                console.error('Failed to load page:', error);
                this.showNotification('Failed to load page', 'error');
            }
        },

        async save() {
            if (this.isSaving) return;

            this.isSaving = true;

            try {
                await BuilderAPI.pages.update(Alpine.store('builder').pageId, {
                    elements: this.elements,
                    settings: this.pageSettings
                });

                this.isDirty = false;
                this.lastSaved = this.formatTime(new Date());
                Alpine.store('builder').pushHistory();
                this.showNotification('Page saved', 'success');
            } catch (error) {
                console.error('Save failed:', error);
                this.showNotification('Failed to save: ' + error.message, 'error');
            } finally {
                this.isSaving = false;
            }
        },

        async uploadImage(file) {
            try {
                const result = await BuilderAPI.media.upload(file, (progress) => {
                    console.log(`Upload progress: ${progress}%`);
                });

                return result.url;
            } catch (error) {
                console.error('Upload failed:', error);
                this.showNotification('Upload failed', 'error');
                throw error;
            }
        },

        preview() {
            const pageId = Alpine.store('builder').pageId;
            window.open(`/preview/${pageId}`, '_blank');
        }
    };
}
```

### Laravel API Routes

```php
// routes/api.php

use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\TemplateController;

Route::middleware('auth:sanctum')->group(function () {
    // Pages
    Route::get('/pages/{page}', [PageController::class, 'show']);
    Route::put('/pages/{page}', [PageController::class, 'update']);
    Route::post('/pages/{page}/publish', [PageController::class, 'publish']);
    Route::post('/pages/{page}/unpublish', [PageController::class, 'unpublish']);
    Route::post('/pages/{page}/duplicate', [PageController::class, 'duplicate']);
    Route::delete('/pages/{page}', [PageController::class, 'destroy']);
    Route::post('/pages/{page}/save-as-template', [PageController::class, 'saveAsTemplate']);

    // Media
    Route::get('/media', [MediaController::class, 'index']);
    Route::post('/media', [MediaController::class, 'store']);
    Route::delete('/media/{media}', [MediaController::class, 'destroy']);

    // Templates
    Route::get('/templates', [TemplateController::class, 'index']);
    Route::get('/templates/{template}', [TemplateController::class, 'show']);
});
```

### Laravel Page Controller

```php
<?php
// app/Http/Controllers/Api/PageController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function show(Page $page)
    {
        $this->authorize('view', $page);

        return response()->json([
            'id' => $page->id,
            'elements' => $page->elements ?? [],
            'settings' => [
                'title' => $page->title,
                'slug' => $page->slug,
                'description' => $page->description,
                'metaTitle' => $page->meta_title,
                'metaDescription' => $page->meta_description,
                'customCss' => $page->custom_css,
                'customJs' => $page->custom_js,
                'ogImage' => $page->og_image,
                'favicon' => $page->favicon,
                'noIndex' => $page->no_index,
                'headerCode' => $page->header_code,
            ]
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'elements' => 'required|array',
            'settings' => 'required|array',
            'settings.title' => 'required|string|max:255',
            'settings.slug' => 'required|string|max:255',
        ]);

        $page->update([
            'elements' => $validated['elements'],
            'title' => $validated['settings']['title'],
            'slug' => Str::slug($validated['settings']['slug']),
            'description' => $validated['settings']['description'] ?? null,
            'meta_title' => $validated['settings']['metaTitle'] ?? null,
            'meta_description' => $validated['settings']['metaDescription'] ?? null,
            'custom_css' => $validated['settings']['customCss'] ?? null,
            'custom_js' => $validated['settings']['customJs'] ?? null,
            'og_image' => $validated['settings']['ogImage'] ?? null,
            'favicon' => $validated['settings']['favicon'] ?? null,
            'no_index' => $validated['settings']['noIndex'] ?? false,
            'header_code' => $validated['settings']['headerCode'] ?? null,
        ]);

        return response()->json([
            'message' => 'Page saved successfully',
            'page' => $page->fresh()
        ]);
    }

    public function publish(Page $page)
    {
        $this->authorize('update', $page);

        $page->update([
            'is_published' => true,
            'published_at' => now()
        ]);

        return response()->json([
            'message' => 'Page published successfully',
            'url' => route('page.show', $page->slug)
        ]);
    }

    public function unpublish(Page $page)
    {
        $this->authorize('update', $page);

        $page->update(['is_published' => false]);

        return response()->json([
            'message' => 'Page unpublished'
        ]);
    }

    public function duplicate(Page $page)
    {
        $this->authorize('view', $page);

        $newPage = $page->replicate();
        $newPage->title = $page->title . ' (Copy)';
        $newPage->slug = $page->slug . '-' . Str::random(6);
        $newPage->is_published = false;
        $newPage->published_at = null;
        $newPage->save();

        return response()->json([
            'message' => 'Page duplicated',
            'page' => $newPage
        ]);
    }

    public function destroy(Page $page)
    {
        $this->authorize('delete', $page);

        $page->delete();

        return response()->json([
            'message' => 'Page deleted'
        ]);
    }
}
```

---

## 9. Drag and Drop Implementation

```javascript
// public/js/builder.js

function pageBuilder() {
    return {
        // ... previous code ...

        draggedElementType: null,
        draggedElementIndex: null,
        dropTargetIndex: null,

        handleDrop(event) {
            const elementType = event.dataTransfer.getData('element-type');

            if (elementType) {
                // New element from panel
                const element = this.createElement(elementType);

                if (this.dropTargetIndex !== null) {
                    this.elements.splice(this.dropTargetIndex, 0, element);
                } else {
                    this.elements.push(element);
                }

                this.selectedElementId = element.id;
                this.markDirty();
                Alpine.store('builder').pushHistory();
            } else if (this.draggedElementIndex !== null) {
                // Reordering existing element
                if (this.dropTargetIndex !== null && this.dropTargetIndex !== this.draggedElementIndex) {
                    const [element] = this.elements.splice(this.draggedElementIndex, 1);
                    const insertIndex = this.dropTargetIndex > this.draggedElementIndex
                        ? this.dropTargetIndex - 1
                        : this.dropTargetIndex;
                    this.elements.splice(insertIndex, 0, element);
                    this.markDirty();
                    Alpine.store('builder').pushHistory();
                }
            }

            this.draggedElementType = null;
            this.draggedElementIndex = null;
            this.dropTargetIndex = null;
        },

        handleDragOver(event) {
            event.preventDefault();

            // Calculate drop position based on mouse Y
            const canvas = event.currentTarget;
            const rect = canvas.getBoundingClientRect();
            const y = event.clientY - rect.top;

            // Find nearest element to insert before
            const elements = canvas.querySelectorAll('[draggable="true"]');
            let targetIndex = this.elements.length;

            elements.forEach((el, index) => {
                const elRect = el.getBoundingClientRect();
                const elY = elRect.top - rect.top + elRect.height / 2;

                if (y < elY && targetIndex === this.elements.length) {
                    targetIndex = index;
                }
            });

            this.dropTargetIndex = targetIndex;
        },

        handleElementDragStart(event, index) {
            this.draggedElementIndex = index;
            event.dataTransfer.effectAllowed = 'move';
            event.target.classList.add('opacity-50');
        },

        handleElementDragEnd(event) {
            event.target.classList.remove('opacity-50');
            this.draggedElementIndex = null;
            this.dropTargetIndex = null;
        }
    };
}
```

---

## 10. Notification System

```blade
{{-- resources/views/components/notifications.blade.php --}}
<div x-data="notifications()"
     @show-notification.window="add($event.detail)"
     class="fixed bottom-4 right-4 z-50 space-y-2">

    <template x-for="notification in notifications" :key="notification.id">
        <div x-show="notification.visible"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-8"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-x-0"
             x-transition:leave-end="opacity-0 transform translate-x-8"
             :class="{
                 'bg-green-50 border-green-200 text-green-800': notification.type === 'success',
                 'bg-red-50 border-red-200 text-red-800': notification.type === 'error',
                 'bg-blue-50 border-blue-200 text-blue-800': notification.type === 'info',
                 'bg-yellow-50 border-yellow-200 text-yellow-800': notification.type === 'warning'
             }"
             class="px-4 py-3 rounded-lg border shadow-lg flex items-center gap-3 min-w-[300px]">

            <span x-text="notification.message"></span>

            <button @click="remove(notification.id)" class="ml-auto opacity-60 hover:opacity-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>

<script>
function notifications() {
    return {
        notifications: [],

        add({ message, type = 'info', duration = 5000 }) {
            const id = Date.now();

            this.notifications.push({
                id,
                message,
                type,
                visible: true
            });

            if (duration > 0) {
                setTimeout(() => this.remove(id), duration);
            }
        },

        remove(id) {
            const notification = this.notifications.find(n => n.id === id);
            if (notification) {
                notification.visible = false;
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 200);
            }
        }
    };
}
</script>
```

---

## Summary

This page builder implementation uses:
- **Laravel** for backend API and routing
- **Blade templates** for server-side rendering
- **AlpineJS** for reactive UI and state management
- **TailwindCSS v4** for styling
- **Vanilla fetch()** for API calls

No React, Vue, TypeScript, or other JavaScript frameworks are used.
