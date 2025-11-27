# Drag-and-Drop Builder (Vue.js 3 SPA)

## Overview

The drag-and-drop builder is the core feature of the Landing Page Builder SaaS. It's implemented as a **Vue.js 3 Single Page Application (SPA)** with **Pinia** state management, providing a visual interface for users to create landing pages by dragging widgets onto a canvas and customizing them through a property panel.

## Technology Stack

- **Vue.js 3** - Composition API for reactive components
- **Pinia** - State management (replaces Vuex)
- **Vite 5.x** - Build tool and dev server with hot module replacement
- **TailwindCSS v4** - Utility-first CSS framework
- **Native Drag & Drop API** - Browser drag-and-drop (no external libraries)

**Key Architectural Decision**: Uses Vue.js 3 SPA mounted in a Blade template, not AlpineJS components.

---

## Architecture Overview

### Application Structure

```
resources/js/builder/
├── main.js                 # Vue app entry point
├── App.vue                 # Main builder component
├── stores/
│   └── builder.js          # Pinia store (Composition API)
├── widgets/
│   └── registry.js         # Widget definitions (22+ widgets)
├── components/
│   ├── WidgetRenderer.vue  # Renders widgets in canvas
│   ├── PropertyPanel.vue   # Right sidebar properties
│   ├── MediaLibrary.vue    # Media picker modal
│   └── ...                 # Other UI components
└── utils/
    └── helpers.js          # Utility functions
```

### Data Flow

```
User Action → Vue Component → Pinia Store → State Update → Reactive UI Update
                                    ↓
                                API Call (auto-save)
                                    ↓
                                Laravel Backend
```

---

## Pinia Store (State Management)

The builder uses a single Pinia store defined with the Composition API setup syntax.

### Core State Structure

```javascript
// resources/js/builder/stores/builder.js
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { widgetRegistry } from '../widgets/registry';

export const useBuilderStore = defineStore('builder', () => {
    // Document state
    const documentId = ref(null);
    const documentTitle = ref('');
    const documentSettings = ref({});
    const content = ref([]);  // Main content array (sections, columns, widgets)

    // UI state
    const selectedElement = ref(null);  // Currently selected element ID
    const selectedType = ref(null);
    const activeTab = ref('content');   // 'content' | 'style' | 'advanced'
    const leftPanel = ref('widgets');   // 'widgets' | 'navigator'
    const previewMode = ref('desktop'); // 'desktop' | 'tablet' | 'mobile'

    // History (Undo/Redo)
    const history = ref([]);
    const historyIndex = ref(-1);

    // Clipboard (Copy/Paste)
    const clipboard = ref(null);

    // State flags
    const isDirty = ref(false);
    const isSaving = ref(false);
    const lastSaved = ref(null);

    // Media library
    const showMediaLibrary = ref(false);
    const mediaControlName = ref(null);
    const mediaItems = ref([]);
    const mediaLoading = ref(false);

    // Widget search
    const widgetSearch = ref('');

    // ... actions and mutations

    return {
        // State
        documentId, documentTitle, content, selectedElement,
        activeTab, previewMode, history, clipboard, isDirty,

        // Actions
        addWidget, updateSetting, deleteElement, undo, redo,
        save, publish, loadDocument, /* ... */
    };
});
```

### Content Data Structure

The `content` array stores the page structure as nested JSON:

```json
[
  {
    "id": "section_abc123",
    "elType": "section",
    "settings": {
      "structure": "50-50",
      "background_color": "#ffffff",
      "padding": "60px 0"
    },
    "elements": [
      {
        "id": "column_def456",
        "elType": "column",
        "settings": { "width": 50 },
        "elements": [
          {
            "id": "widget_ghi789",
            "elType": "widget",
            "widgetType": "heading",
            "settings": {
              "title": "Welcome",
              "tag": "h1",
              "color": "#1f2937",
              "typography_font_size": "48px",
              "typography_font_weight": "700"
            }
          }
        ]
      }
    ]
  }
]
```

**Hierarchy**: `Page → Sections → Columns → Widgets`

---

## Main Builder Component (App.vue)

### Template Structure

```vue
<template>
  <div class="h-screen flex flex-col bg-gray-100">
    <!-- Top Toolbar -->
    <header class="h-14 bg-gray-900 text-white flex items-center justify-between px-4">
      <div class="flex items-center gap-4">
        <!-- Back button -->
        <a :href="backUrl" class="text-gray-300 hover:text-white">
          <svg><!-- Back icon --></svg>
        </a>

        <!-- Page title -->
        <input type="text" v-model="store.documentTitle" @input="store.isDirty = true"
               class="bg-transparent text-white"
               placeholder="Page Title">
      </div>

      <div class="flex items-center gap-2">
        <!-- Undo/Redo -->
        <button @click="store.undo()" :disabled="store.historyIndex <= 0">Undo</button>
        <button @click="store.redo()" :disabled="store.historyIndex >= store.history.length - 1">Redo</button>

        <!-- Preview modes (Desktop/Tablet/Mobile) -->
        <button @click="store.previewMode = 'desktop'" :class="{ 'text-white': store.previewMode === 'desktop' }">
          Desktop
        </button>
        <button @click="store.previewMode = 'tablet'">Tablet</button>
        <button @click="store.previewMode = 'mobile'">Mobile</button>

        <!-- Save/Publish -->
        <button @click="store.save()" :disabled="store.isSaving">
          {{ store.isSaving ? 'Saving...' : 'Save' }}
        </button>
        <button @click="store.publish()">Publish</button>
      </div>
    </header>

    <!-- Main Content -->
    <div class="flex-1 flex overflow-hidden">
      <!-- Left Panel - Widgets -->
      <aside class="w-72 bg-white border-r">
        <!-- Tabs: Widgets / Navigator -->
        <div class="flex border-b">
          <button @click="store.leftPanel = 'widgets'"
                  :class="{ 'border-indigo-500': store.leftPanel === 'widgets' }">
            Widgets
          </button>
          <button @click="store.leftPanel = 'navigator'"
                  :class="{ 'border-indigo-500': store.leftPanel === 'navigator' }">
            Navigator
          </button>
        </div>

        <!-- Widget Panel -->
        <div v-show="store.leftPanel === 'widgets'" class="overflow-y-auto p-4">
          <!-- Search -->
          <input type="text" v-model="store.widgetSearch" placeholder="Search widgets...">

          <!-- Structure Buttons -->
          <div class="mb-4">
            <button @click="store.addSection('100')">1 Column</button>
            <button @click="store.addSection('50-50')">2 Columns</button>
            <button @click="store.addSection('33-33-33')">3 Columns</button>
          </div>

          <!-- Widgets Grid -->
          <div class="grid grid-cols-3 gap-2">
            <div v-for="(widget, key) in store.filteredWidgets" :key="key"
                 @click="store.clickAddWidget(key)"
                 draggable="true"
                 @dragstart="onDragStart($event, key)"
                 class="p-3 border rounded cursor-move hover:border-indigo-300">
              <div class="text-lg">{{ widget.icon }}</div>
              <div class="text-xs">{{ widget.title }}</div>
            </div>
          </div>
        </div>

        <!-- Navigator Panel -->
        <div v-show="store.leftPanel === 'navigator'" class="overflow-y-auto p-4">
          <div v-for="section in store.content" :key="section.id">
            <div @click="store.selectElement(section.id)">
              Section
            </div>
            <!-- Nested columns and widgets -->
          </div>
        </div>
      </aside>

      <!-- Center - Canvas -->
      <main class="flex-1 overflow-auto p-6 bg-gray-100">
        <div class="mx-auto bg-white shadow-lg transition-all"
             :style="{ width: canvasWidth }">
          <div class="min-h-screen p-4"
               @drop="onDrop"
               @dragover.prevent>
            <!-- Render sections -->
            <section v-for="section in store.content" :key="section.id"
                     :class="{ 'ring-2 ring-indigo-500': store.selectedElement === section.id }"
                     @click.stop="store.selectElement(section.id)">
              <!-- Render columns -->
              <div v-for="column in section.elements" :key="column.id"
                   :class="{ 'ring-2 ring-indigo-500': store.selectedElement === column.id }"
                   @click.stop="store.selectElement(column.id)">
                <!-- Render widgets -->
                <WidgetRenderer v-for="widget in column.elements" :key="widget.id"
                                :widget="widget"
                                @select="store.selectElement(widget.id)" />
              </div>
            </section>

            <!-- Empty state -->
            <div v-if="store.content.length === 0" class="flex items-center justify-center h-64">
              <p class="text-gray-400">Drag widgets here to start building</p>
            </div>
          </div>
        </div>
      </main>

      <!-- Right Panel - Properties -->
      <aside class="w-80 bg-white border-l overflow-y-auto">
        <PropertyPanel v-if="store.selectedElement" />
        <div v-else class="flex items-center justify-center h-full text-gray-400">
          Select an element to edit properties
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useBuilderStore } from './stores/builder';
import WidgetRenderer from './components/WidgetRenderer.vue';
import PropertyPanel from './components/PropertyPanel.vue';

const store = useBuilderStore();

const backUrl = computed(() => `/dashboard/pages`);

const canvasWidth = computed(() => {
  const widths = { desktop: '100%', tablet: '768px', mobile: '375px' };
  return widths[store.previewMode];
});

function onDragStart(event, widgetType) {
  event.dataTransfer.effectAllowed = 'copy';
  event.dataTransfer.setData('widgetType', widgetType);
}

function onDrop(event) {
  event.preventDefault();
  const widgetType = event.dataTransfer.getData('widgetType');
  if (widgetType) {
    store.clickAddWidget(widgetType);
  }
}
</script>
```

---

## Widget Renderer Component

Renders individual widgets based on their type:

```vue
<!-- resources/js/builder/components/WidgetRenderer.vue -->
<template>
  <div class="relative group"
       :class="{ 'ring-2 ring-indigo-500': isSelected }"
       @click.stop="$emit('select', widget.id)">
    <!-- Widget toolbar (shown when selected) -->
    <div v-if="isSelected" class="absolute -top-8 left-0 bg-indigo-500 text-white text-xs px-2 py-1 rounded-t">
      <span class="cursor-grab">⋮⋮</span>
      <span>{{ widgetConfig.title }}</span>
      <button @click.stop="duplicate">Copy</button>
      <button @click.stop="remove">Delete</button>
    </div>

    <!-- Render based on widget type -->
    <component :is="getWidgetComponent(widget.widgetType)" :settings="widget.settings" />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useBuilderStore } from '../stores/builder';
import { widgetRegistry } from '../widgets/registry';
import HeadingWidget from './widgets/HeadingWidget.vue';
import ImageWidget from './widgets/ImageWidget.vue';
// ... import other widget components

const props = defineProps(['widget']);
const emit = defineEmits(['select']);

const store = useBuilderStore();
const isSelected = computed(() => store.selectedElement === props.widget.id);
const widgetConfig = computed(() => widgetRegistry.get(props.widget.widgetType));

function getWidgetComponent(type) {
  const components = {
    'heading': HeadingWidget,
    'image': ImageWidget,
    'button': ButtonWidget,
    'text-editor': TextEditorWidget,
    // ... 22+ widgets
  };
  return components[type] || 'div';
}

function duplicate() {
  store.duplicateElement(props.widget.id);
}

function remove() {
  store.deleteElement(props.widget.id);
}
</script>
```

---

## Property Panel Component

Displays editable properties for the selected element:

```vue
<!-- resources/js/builder/components/PropertyPanel.vue -->
<template>
  <div class="flex flex-col h-full">
    <!-- Header -->
    <div class="p-4 border-b flex items-center justify-between">
      <h3 class="font-semibold">{{ elementTitle }}</h3>
      <div class="flex gap-2">
        <button @click="store.duplicateElement(store.selectedElement)">Duplicate</button>
        <button @click="store.deleteElement(store.selectedElement)">Delete</button>
      </div>
    </div>

    <!-- Tabs: Content / Style / Advanced -->
    <div class="flex border-b">
      <button @click="store.activeTab = 'content'"
              :class="{ 'border-indigo-500 text-indigo-600': store.activeTab === 'content' }"
              class="flex-1 py-2 border-b-2">
        Content
      </button>
      <button @click="store.activeTab = 'style'"
              :class="{ 'border-indigo-500 text-indigo-600': store.activeTab === 'style' }"
              class="flex-1 py-2 border-b-2">
        Style
      </button>
      <button @click="store.activeTab = 'advanced'"
              :class="{ 'border-indigo-500 text-indigo-600': store.activeTab === 'advanced' }"
              class="flex-1 py-2 border-b-2">
        Advanced
      </button>
    </div>

    <!-- Controls -->
    <div class="flex-1 overflow-y-auto p-4">
      <div v-for="control in store.currentControls" :key="control.name" class="mb-4">
        <!-- Text Input -->
        <div v-if="control.type === 'text'">
          <label class="block text-sm font-medium mb-1">{{ control.label }}</label>
          <input type="text"
                 :value="store.getSetting(control.name)"
                 @input="store.updateSetting(control.name, $event.target.value)"
                 class="w-full p-2 border rounded">
        </div>

        <!-- Textarea -->
        <div v-else-if="control.type === 'textarea'">
          <label class="block text-sm font-medium mb-1">{{ control.label }}</label>
          <textarea :value="store.getSetting(control.name)"
                    @input="store.updateSetting(control.name, $event.target.value)"
                    rows="4"
                    class="w-full p-2 border rounded"></textarea>
        </div>

        <!-- WYSIWYG Editor -->
        <div v-else-if="control.type === 'wysiwyg'">
          <label class="block text-sm font-medium mb-1">{{ control.label }}</label>
          <WysiwygEditor :modelValue="store.getSetting(control.name)"
                         @update:modelValue="store.updateSetting(control.name, $event)" />
        </div>

        <!-- Color Picker -->
        <div v-else-if="control.type === 'color'">
          <label class="block text-sm font-medium mb-1">{{ control.label }}</label>
          <input type="color"
                 :value="store.getSetting(control.name)"
                 @input="store.updateSetting(control.name, $event.target.value)"
                 class="w-full h-10 p-1 border rounded">
        </div>

        <!-- Select Dropdown -->
        <div v-else-if="control.type === 'select'">
          <label class="block text-sm font-medium mb-1">{{ control.label }}</label>
          <select :value="store.getSetting(control.name)"
                  @change="store.updateSetting(control.name, $event.target.value)"
                  class="w-full p-2 border rounded">
            <option v-for="(label, value) in control.options" :key="value" :value="value">
              {{ label }}
            </option>
          </select>
        </div>

        <!-- Slider -->
        <div v-else-if="control.type === 'slider'">
          <label class="block text-sm font-medium mb-1">
            {{ control.label }}: {{ store.getSetting(control.name) }}{{ control.unit || '' }}
          </label>
          <input type="range"
                 :min="control.min"
                 :max="control.max"
                 :step="control.step"
                 :value="store.getSetting(control.name)"
                 @input="store.updateSetting(control.name, Number($event.target.value))"
                 class="w-full">
        </div>

        <!-- Switcher (Toggle) -->
        <div v-else-if="control.type === 'switcher'">
          <label class="flex items-center gap-2">
            <input type="checkbox"
                   :checked="store.getSetting(control.name)"
                   @change="store.updateSetting(control.name, $event.target.checked)"
                   class="rounded border-gray-300">
            <span class="text-sm">{{ control.label }}</span>
          </label>
        </div>

        <!-- Media -->
        <div v-else-if="control.type === 'media'">
          <label class="block text-sm font-medium mb-1">{{ control.label }}</label>
          <div class="border rounded p-2">
            <img v-if="store.getSetting(control.name)"
                 :src="store.getSetting(control.name)"
                 class="w-full h-32 object-cover mb-2">
            <button @click="store.openMediaLibrary(control.name)" class="btn-sm">
              Choose Image
            </button>
          </div>
        </div>

        <!-- Dimensions (margin, padding) -->
        <div v-else-if="control.type === 'dimensions'">
          <label class="block text-sm font-medium mb-1">{{ control.label }}</label>
          <div class="grid grid-cols-4 gap-2">
            <input type="text" placeholder="Top" class="p-2 border rounded text-sm">
            <input type="text" placeholder="Right" class="p-2 border rounded text-sm">
            <input type="text" placeholder="Bottom" class="p-2 border rounded text-sm">
            <input type="text" placeholder="Left" class="p-2 border rounded text-sm">
          </div>
        </div>

        <!-- Typography -->
        <div v-else-if="control.type === 'typography'">
          <label class="block text-sm font-medium mb-2">{{ control.label }}</label>
          <div class="space-y-2">
            <select class="w-full p-2 border rounded text-sm">
              <option>Font Family</option>
            </select>
            <div class="grid grid-cols-2 gap-2">
              <input type="text" placeholder="Size" class="p-2 border rounded text-sm">
              <select class="p-2 border rounded text-sm">
                <option>Weight</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Repeater (for lists like accordion items) -->
        <div v-else-if="control.type === 'repeater'">
          <label class="block text-sm font-medium mb-2">{{ control.label }}</label>
          <div v-for="(item, index) in store.getSetting(control.name) || []" :key="index"
               class="border rounded p-2 mb-2">
            <div v-for="field in control.fields" :key="field.name" class="mb-2">
              <label class="text-xs">{{ field.label }}</label>
              <input type="text"
                     :value="item[field.name]"
                     @input="store.updateRepeaterItem(control.name, index, field.name, $event.target.value)"
                     class="w-full p-1 border rounded text-sm">
            </div>
            <button @click="store.removeRepeaterItem(control.name, index)" class="text-red-500 text-xs">
              Remove
            </button>
          </div>
          <button @click="store.addRepeaterItem(control.name, control.fields)" class="btn-sm">
            Add Item
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useBuilderStore } from '../stores/builder';
import { widgetRegistry } from '../widgets/registry';

const store = useBuilderStore();

const elementTitle = computed(() => {
  const el = store.selectedElementData;
  if (!el) return '';

  if (el.elType === 'widget') {
    const widget = widgetRegistry.get(el.widgetType);
    return widget?.title || 'Widget';
  }

  if (el.elType === 'section') return 'Section';
  if (el.elType === 'column') return 'Column';

  return 'Element';
});
</script>
```

---

## Widget Registry System

Widgets are registered in a central registry with their controls (properties):

```javascript
// resources/js/builder/widgets/registry.js

class WidgetRegistry {
    constructor() {
        this.widgets = {};
    }

    register(name, config) {
        this.widgets[name] = config;
    }

    get(name) {
        return this.widgets[name];
    }

    getAll() {
        return this.widgets;
    }
}

export const widgetRegistry = new WidgetRegistry();

// Register Heading Widget
widgetRegistry.register('heading', {
    title: 'Heading',
    icon: '📝',
    category: 'basic',
    controls: {
        content: [
            { name: 'title', type: 'textarea', label: 'Title', default: 'This is the heading' },
            { name: 'link', type: 'url', label: 'Link', default: '' },
            { name: 'tag', type: 'select', label: 'HTML Tag', options: {
                h1: 'H1', h2: 'H2', h3: 'H3', h4: 'H4', h5: 'H5', h6: 'H6'
            }, default: 'h2' },
            { name: 'align', type: 'choose', label: 'Alignment', options: {
                left: 'Left', center: 'Center', right: 'Right', justify: 'Justify'
            }, default: 'left' }
        ],
        style: [
            { name: 'color', type: 'color', label: 'Text Color', default: '#1f2937' },
            { name: 'typography_font_family', type: 'select', label: 'Font Family', options: {
                '': 'Default', 'Arial': 'Arial', 'Helvetica': 'Helvetica'
            }},
            { name: 'typography_font_size', type: 'slider', label: 'Font Size', min: 10, max: 100, step: 1, unit: 'px', default: 32 },
            { name: 'typography_font_weight', type: 'select', label: 'Font Weight', options: {
                '400': 'Normal', '500': 'Medium', '600': 'Semibold', '700': 'Bold', '800': 'Extra Bold'
            }, default: '700' },
            { name: 'typography_line_height', type: 'slider', label: 'Line Height', min: 1, max: 3, step: 0.1, default: 1.2 },
            { name: 'text_shadow', type: 'text_shadow', label: 'Text Shadow' }
        ],
        advanced: [
            { name: '_margin', type: 'dimensions', label: 'Margin', default: { top: 0, right: 0, bottom: 16, left: 0, unit: 'px' } },
            { name: '_padding', type: 'dimensions', label: 'Padding' },
            { name: '_css_classes', type: 'text', label: 'CSS Classes' },
            { name: '_css_id', type: 'text', label: 'CSS ID' }
        ]
    }
});

// Register Image Widget
widgetRegistry.register('image', {
    title: 'Image',
    icon: '🖼️',
    category: 'basic',
    controls: {
        content: [
            { name: 'image', type: 'media', label: 'Choose Image', default: { url: '/placeholder.jpg' } },
            { name: 'caption', type: 'text', label: 'Caption', default: '' },
            { name: 'link', type: 'url', label: 'Link', default: '' },
            { name: 'link_target', type: 'switcher', label: 'Open in New Tab', default: false },
            { name: 'align', type: 'choose', label: 'Alignment', options: {
                left: 'Left', center: 'Center', right: 'Right'
            }, default: 'center' }
        ],
        style: [
            { name: 'width', type: 'slider', label: 'Width', min: 0, max: 100, step: 1, unit: '%', default: 100 },
            { name: 'height', type: 'slider', label: 'Height', min: 0, max: 1000, step: 10, unit: 'px', default: 300 },
            { name: 'object_fit', type: 'select', label: 'Object Fit', options: {
                cover: 'Cover', contain: 'Contain', fill: 'Fill', none: 'None'
            }, default: 'cover' },
            { name: 'border_radius', type: 'dimensions', label: 'Border Radius' },
            { name: 'box_shadow', type: 'box_shadow', label: 'Box Shadow' },
            { name: 'opacity', type: 'slider', label: 'Opacity', min: 0, max: 1, step: 0.1, default: 1 }
        ],
        advanced: [
            { name: '_margin', type: 'dimensions', label: 'Margin' },
            { name: '_padding', type: 'dimensions', label: 'Padding' }
        ]
    }
});

// ... 22+ widgets total (Button, Text Editor, Video, Divider, Spacer, Icon, etc.)
```

---

## Key Features Implementation

### 1. Undo/Redo

```javascript
// In Pinia store
function addToHistory() {
    const snapshot = JSON.parse(JSON.stringify(content.value));
    history.value = history.value.slice(0, historyIndex.value + 1);
    history.value.push(snapshot);
    historyIndex.value = history.value.length - 1;

    // Limit history to 50 entries
    if (history.value.length > 50) {
        history.value.shift();
        historyIndex.value--;
    }
}

function undo() {
    if (historyIndex.value <= 0) return;
    historyIndex.value--;
    content.value = JSON.parse(JSON.stringify(history.value[historyIndex.value]));
    isDirty.value = true;
}

function redo() {
    if (historyIndex.value >= history.value.length - 1) return;
    historyIndex.value++;
    content.value = JSON.parse(JSON.stringify(history.value[historyIndex.value]));
    isDirty.value = true;
}
```

### 2. Auto-Save

```javascript
// In Pinia store
import { watchDebounced } from '@vueuse/core';

watchDebounced(
    content,
    () => {
        if (isDirty.value && !isSaving.value) {
            save();
        }
    },
    { deep: true, debounce: 3000 }
);

async function save() {
    isSaving.value = true;
    try {
        await fetch(`/api/pages/${documentId.value}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                title: documentTitle.value,
                content: content.value,
                settings: documentSettings.value
            })
        });

        isDirty.value = false;
        lastSaved.value = new Date().toLocaleTimeString();
    } catch (error) {
        console.error('Save failed:', error);
    } finally {
        isSaving.value = false;
    }
}
```

### 3. Copy/Paste

```javascript
// In Pinia store
function copy() {
    if (!selectedElement.value) return;
    const element = findElement(selectedElement.value);
    if (element) {
        clipboard.value = JSON.parse(JSON.stringify(element));
    }
}

function paste() {
    if (!clipboard.value) return;

    const parent = findParent(selectedElement.value);
    if (!parent) return;

    const cloned = deepCloneWithNewIds(clipboard.value);

    const index = parent.elements.findIndex(el => el.id === selectedElement.value);
    parent.elements.splice(index + 1, 0, cloned);

    selectedElement.value = cloned.id;
    addToHistory();
    isDirty.value = true;
}

function deepCloneWithNewIds(element) {
    const cloned = JSON.parse(JSON.stringify(element));
    const assignNewIds = (el) => {
        el.id = generateId();
        if (el.elements) {
            el.elements.forEach(assignNewIds);
        }
    };
    assignNewIds(cloned);
    return cloned;
}
```

### 4. Keyboard Shortcuts

```javascript
// In App.vue
import { onMounted, onUnmounted } from 'vue';

onMounted(() => {
    document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown);
});

function handleKeyDown(event) {
    const isCtrlOrCmd = event.ctrlKey || event.metaKey;

    // Undo: Ctrl+Z
    if (isCtrlOrCmd && event.key === 'z' && !event.shiftKey) {
        event.preventDefault();
        store.undo();
    }

    // Redo: Ctrl+Shift+Z or Ctrl+Y
    if ((isCtrlOrCmd && event.shiftKey && event.key === 'z') || (isCtrlOrCmd && event.key === 'y')) {
        event.preventDefault();
        store.redo();
    }

    // Copy: Ctrl+C
    if (isCtrlOrCmd && event.key === 'c' && store.selectedElement) {
        event.preventDefault();
        store.copy();
    }

    // Paste: Ctrl+V
    if (isCtrlOrCmd && event.key === 'v' && store.clipboard) {
        event.preventDefault();
        store.paste();
    }

    // Delete: Delete or Backspace
    if ((event.key === 'Delete' || event.key === 'Backspace') && store.selectedElement) {
        const target = event.target;
        if (target.tagName !== 'INPUT' && target.tagName !== 'TEXTAREA') {
            event.preventDefault();
            store.deleteElement(store.selectedElement);
        }
    }

    // Save: Ctrl+S
    if (isCtrlOrCmd && event.key === 's') {
        event.preventDefault();
        store.save();
    }

    // Deselect: Escape
    if (event.key === 'Escape') {
        store.selectElement(null);
    }
}
```

---

## Mounting the Vue.js SPA

### Blade Template

```blade
{{-- resources/views/builder/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div id="builder-app"
     data-page-id="{{ $page->id }}"
     data-page-title="{{ $page->title }}"
     data-page-content="{{ json_encode($page->content ?? []) }}"
     data-back-url="{{ route('dashboard.pages.index') }}">
</div>
@endsection

@push('scripts')
@vite(['resources/js/builder/main.js'])
@endpush
```

### Vue App Entry Point

```javascript
// resources/js/builder/main.js
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import { useBuilderStore } from './stores/builder';

const pinia = createPinia();
const app = createApp(App);

app.use(pinia);
app.mount('#builder-app');

// Load initial data from Blade template
const builderEl = document.getElementById('builder-app');
const store = useBuilderStore();

store.documentId = parseInt(builderEl.dataset.pageId);
store.documentTitle = builderEl.dataset.pageTitle;
store.content = JSON.parse(builderEl.dataset.pageContent);
store.addToHistory(); // Initial history snapshot
```

---

## API Integration

### Laravel API Controller

```php
<?php
// app/Http/Controllers/Api/PageController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(Page $page)
    {
        $this->authorize('view', $page);

        return response()->json([
            'id' => $page->id,
            'title' => $page->title,
            'content' => $page->content ?? [],
            'settings' => $page->settings ?? [],
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|array',
            'settings' => 'sometimes|array',
        ]);

        $page->update($validated);

        return response()->json(['success' => true]);
    }

    public function publish(Page $page)
    {
        $this->authorize('update', $page);

        $page->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
```

### API Routes

```php
// routes/api.php
use App\Http\Controllers\Api\PageController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/pages/{page}', [PageController::class, 'show']);
    Route::put('/pages/{page}', [PageController::class, 'update']);
    Route::post('/pages/{page}/publish', [PageController::class, 'publish']);
});
```

---

## Development Workflow

### Starting Development Server

```bash
# Terminal 1: Vite dev server (hot module replacement)
npm run dev

# Terminal 2: Laravel dev server
php artisan serve

# Access builder at: http://localhost:8000/builder/{page_id}/edit
```

### File Watch & Hot Reload

Vite provides instant hot module replacement (HMR) for Vue components. Changes to `.vue` files automatically update in the browser without full page reload.

### Building for Production

```bash
npm run build
```

This compiles the Vue.js SPA into optimized static assets in `public/build/`.

---

## Summary

The Landing Page Builder uses a **Vue.js 3 SPA architecture** with:

1. **Vue.js 3 Composition API**: Modern reactive framework with `<script setup>` syntax
2. **Pinia Store**: Centralized state management for content, history, clipboard, media library
3. **Component-based UI**: WidgetRenderer, PropertyPanel, MediaLibrary as reusable Vue components
4. **Widget Registry**: JavaScript-based system for registering 22+ Elementor-inspired widgets
5. **Native Drag & Drop**: Browser Drag and Drop API (no external libraries)
6. **Real-time Features**: Undo/redo, auto-save, copy/paste, keyboard shortcuts
7. **Three-tab Property System**: Content/Style/Advanced tabs with 15+ control types
8. **Responsive Preview**: Desktop/Tablet/Mobile preview modes
9. **Laravel Integration**: Mounted in Blade template, communicates via REST API
10. **Vite Build System**: Hot module replacement for instant development feedback

**Architecture**: Blade template → Vue.js SPA mount → Pinia state → API calls → Laravel backend

All code uses **Vue.js 3 + Pinia + TailwindCSS v4 + Vite**. No AlpineJS, no React, no external drag-drop libraries.
