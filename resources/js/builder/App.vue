<template>
  <div class="h-screen flex flex-col bg-gray-100">
    <!-- Top Toolbar -->
    <header class="h-14 bg-gray-900 text-white flex items-center justify-between px-4 shrink-0">
      <div class="flex items-center gap-4">
        <a :href="backUrl" class="flex items-center gap-2 text-gray-300 hover:text-white">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
        </a>
        <div class="h-6 w-px bg-gray-700"></div>
        <input type="text" v-model="store.documentTitle" @input="store.isDirty = true"
               class="bg-transparent text-white text-sm font-medium focus:outline-none px-2 py-1 rounded"
               placeholder="Page Title">
      </div>

      <div class="flex items-center gap-2">
        <!-- History -->
        <button @click="store.undo()" :disabled="store.historyIndex <= 0" class="p-2 text-gray-300 hover:text-white disabled:opacity-30" title="Undo (Ctrl+Z)">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
        </button>
        <button @click="store.redo()" :disabled="store.historyIndex >= store.history.length - 1" class="p-2 text-gray-300 hover:text-white disabled:opacity-30" title="Redo (Ctrl+Y)">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"/></svg>
        </button>
        <div class="h-6 w-px bg-gray-700"></div>

        <!-- Preview modes -->
        <button @click="store.previewMode = 'desktop'" :class="store.previewMode === 'desktop' ? 'text-white' : 'text-gray-400'" class="p-2 hover:text-white">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </button>
        <button @click="store.previewMode = 'tablet'" :class="store.previewMode === 'tablet' ? 'text-white' : 'text-gray-400'" class="p-2 hover:text-white">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </button>
        <button @click="store.previewMode = 'mobile'" :class="store.previewMode === 'mobile' ? 'text-white' : 'text-gray-400'" class="p-2 hover:text-white">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </button>
        <div class="h-6 w-px bg-gray-700"></div>

        <!-- Preview button -->
        <a :href="`/builder/${store.documentId}/preview`" target="_blank" class="p-2 text-gray-300 hover:text-white">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        </a>
        <div class="h-6 w-px bg-gray-700"></div>

        <!-- Status -->
        <span class="text-xs text-gray-400">
          {{ store.isDirty ? 'Unsaved' : (store.lastSaved ? `Saved ${store.lastSaved}` : 'Saved') }}
        </span>

        <!-- Save/Publish -->
        <button @click="store.save()" :disabled="store.isSaving" class="px-3 py-1.5 bg-gray-700 text-white text-sm rounded hover:bg-gray-600 disabled:opacity-50">
          {{ store.isSaving ? 'Saving...' : 'Save' }}
        </button>
        <button @click="store.publish()" class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
          Publish
        </button>
      </div>
    </header>

    <!-- Main Content -->
    <div class="flex-1 flex overflow-hidden">
      <!-- Left Panel - Widgets -->
      <aside class="w-72 bg-white border-r flex flex-col shrink-0">
        <!-- Tabs -->
        <div class="flex border-b">
          <button @click="store.leftPanel = 'widgets'" :class="store.leftPanel === 'widgets' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'" class="flex-1 py-3 text-sm font-medium border-b-2">Widgets</button>
          <button @click="store.leftPanel = 'navigator'" :class="store.leftPanel === 'navigator' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'" class="flex-1 py-3 text-sm font-medium border-b-2">Navigator</button>
        </div>

        <!-- Widget Panel -->
        <div v-show="store.leftPanel === 'widgets'" class="flex-1 overflow-y-auto p-4">
          <!-- Search -->
          <input type="text" v-model="store.widgetSearch" placeholder="Search widgets..." class="w-full mb-4 px-3 py-2 text-sm border border-gray-300 rounded-md">

          <!-- Structure buttons -->
          <div class="mb-4">
            <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Structure</div>
            <div class="grid grid-cols-3 gap-2">
              <button @click="store.addSection('100')" class="p-2 border rounded hover:bg-gray-50">
                <div class="h-6 bg-gray-200 rounded"></div>
              </button>
              <button @click="store.addSection('50-50')" class="p-2 border rounded hover:bg-gray-50">
                <div class="h-6 flex gap-0.5"><div class="flex-1 bg-gray-200 rounded"></div><div class="flex-1 bg-gray-200 rounded"></div></div>
              </button>
              <button @click="store.addSection('33-33-33')" class="p-2 border rounded hover:bg-gray-50">
                <div class="h-6 flex gap-0.5"><div class="flex-1 bg-gray-200 rounded"></div><div class="flex-1 bg-gray-200 rounded"></div><div class="flex-1 bg-gray-200 rounded"></div></div>
              </button>
            </div>
          </div>

          <!-- Widgets -->
          <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Basic</div>
          <div class="grid grid-cols-3 gap-2">
            <div v-for="(widget, key) in store.filteredWidgets" :key="key"
                 @click="store.clickAddWidget(key)"
                 draggable="true"
                 @dragstart="onDragStart($event, key)"
                 class="p-3 border rounded text-center cursor-move hover:border-indigo-300 hover:bg-indigo-50">
              <div class="text-lg mb-1">{{ widget.icon }}</div>
              <div class="text-xs text-gray-600">{{ widget.title }}</div>
            </div>
          </div>
        </div>

        <!-- Navigator Panel -->
        <Navigator
          v-show="store.leftPanel === 'navigator'"
          :content="store.content"
          :selectedElement="store.selectedElement"
          @select="(id) => { const el = store.findElement(id); store.selectElement(id, el.elType); }"
          @delete="store.deleteElement"
          @duplicate="store.duplicateElement"
        />
      </aside>

      <!-- Canvas -->
      <main class="flex-1 bg-gray-200 overflow-auto p-8">
        <div :class="store.previewMode === 'desktop' ? 'max-w-5xl' : store.previewMode === 'tablet' ? 'max-w-lg' : 'max-w-sm'" class="mx-auto transition-all">
          <div class="bg-white min-h-[600px] shadow-lg">
            <!-- Empty state -->
            <div v-if="store.content.length === 0" class="flex flex-col items-center justify-center h-96 text-gray-400">
              <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
              <p class="text-lg mb-2">Start building</p>
              <p class="text-sm">Add a structure or drag widgets</p>
            </div>

            <!-- Sections -->
            <div v-for="section in store.content" :key="section.id"
                 @click.stop="store.selectElement(section.id, 'section')"
                 :data-element-id="section.id"
                 :data-element-type="'section'"
                 :class="store.selectedElement === section.id ? 'ring-2 ring-indigo-500' : ''"
                 class="section-container relative group">
              <div class="flex" :style="`min-height: ${section.settings?.min_height || 100}px`">
                <div v-for="column in section.elements" :key="column.id"
                     @click.stop="store.selectElement(column.id, 'column')"
                     :data-element-id="column.id"
                     :data-element-type="'column'"
                     :class="store.selectedElement === column.id ? 'ring-2 ring-blue-500' : ''"
                     :style="`width: ${column.settings?._column_size || 100}%`"
                     class="column-container relative group/col border border-dashed border-transparent hover:border-gray-300"
                     @dragover.prevent
                     @drop="onDrop($event, column.id)">
                  <div class="min-h-[100px] p-4">
                    <!-- Empty column state -->
                    <div v-if="column.elements.length === 0" class="h-24 border-2 border-dashed border-gray-300 rounded flex items-center justify-center text-gray-400 text-sm">+ Widget</div>

                    <!-- Widgets -->
                    <div v-for="widget in column.elements" :key="widget.id"
                         @click.stop="store.selectElement(widget.id, 'widget')"
                         :data-element-id="widget.id"
                         :data-element-type="'widget'"
                         :class="store.selectedElement === widget.id ? 'ring-2 ring-green-500' : ''"
                         class="widget-container relative group/widget mb-4">
                      <!-- Widget toolbar -->
                      <div class="absolute -top-6 left-1/2 -translate-x-1/2 opacity-0 group-hover/widget:opacity-100 z-10">
                        <div class="flex items-center gap-1 bg-green-600 text-white text-xs px-2 py-1 rounded shadow">
                          <span class="capitalize">{{ widget.widgetType }}</span>
                          <button @click.stop="store.duplicateElement(widget.id)" class="hover:text-green-200">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                          </button>
                          <button @click.stop="store.deleteElement(widget.id)" class="hover:text-red-300">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                          </button>
                        </div>
                      </div>
                      <!-- Widget content -->
                      <WidgetRenderer :widget="widget" :key="`widget-${widget.id}-${widget.settingsHash || JSON.stringify(widget.settings)}`" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      <!-- Right Panel - Properties -->
      <aside class="w-80 bg-white border-l flex flex-col shrink-0">
        <div v-if="store.selectedElementData" class="flex flex-col h-full">
          <!-- Header -->
          <div class="p-4 border-b flex items-center justify-between">
            <h3 class="font-medium text-gray-900">
              {{ store.selectedType === 'widget' ? 'Widget Settings' : store.selectedType === 'section' ? 'Section Settings' : 'Column Settings' }}
            </h3>
            <button @click="store.selectedElement = null; store.selectedType = null" class="text-gray-400 hover:text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <!-- Tabs -->
          <div class="flex border-b">
            <button @click="store.activeTab = 'content'" :class="store.activeTab === 'content' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'" class="flex-1 py-2 text-sm font-medium border-b-2">Content</button>
            <button @click="store.activeTab = 'style'" :class="store.activeTab === 'style' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'" class="flex-1 py-2 text-sm font-medium border-b-2">Style</button>
            <button @click="store.activeTab = 'advanced'" :class="store.activeTab === 'advanced' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'" class="flex-1 py-2 text-sm font-medium border-b-2">Advanced</button>
          </div>

          <!-- Hover State & Responsive Controls (Only show in Style tab) -->
          <div v-if="store.activeTab === 'style'" class="p-3 border-b space-y-3 bg-gray-50">
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Hover State</label>
              <HoverStateToggle
                :state="store.hoverState"
                @update:state="store.hoverState = $event"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Responsive</label>
              <ResponsiveToggle
                :device="store.responsiveDevice"
                @update:device="store.responsiveDevice = $event"
              />
            </div>
          </div>

          <!-- Controls -->
          <div class="flex-1 overflow-y-auto p-4">
            <div v-for="control in store.currentControls" :key="control.name">
              <ControlRenderer
                :control="control"
                :modelValue="store.getSetting(control.name) ?? control.default"
                @update:modelValue="store.updateSetting(control.name, $event)"
                @openMedia="store.openMediaLibrary(control.name)"
              />
            </div>
            <div v-if="store.currentControls.length === 0" class="text-sm text-gray-400 text-center py-4">
              No settings for this tab
            </div>
          </div>
        </div>
        <div v-else class="flex-1 flex items-center justify-center text-gray-400">
          <p class="text-sm">Select an element to edit</p>
        </div>
      </aside>
    </div>

    <!-- Media Library Modal -->
    <MediaLibraryModal
      :show="store.showMediaLibrary"
      @close="store.showMediaLibrary = false"
      @select="store.selectMediaItem($event)"
    />

    <!-- Context Menu -->
    <ContextMenu
      :visible="contextMenu.visible"
      :position="contextMenu.position"
      :elementType="contextMenu.elementType"
      :elementData="contextMenu.elementData"
      @close="contextMenu.visible = false"
      @action="handleContextMenuAction"
    />
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, reactive } from 'vue';
import { useBuilderStore } from './stores/builder';
import WidgetRenderer from './components/WidgetRenderer.vue';
import ControlRenderer from './components/ControlRenderer.vue';
import MediaLibraryModal from './components/MediaLibraryModal.vue';
import ContextMenu from './components/ContextMenu.vue';
import HoverStateToggle from './components/HoverStateToggle.vue';
import ResponsiveToggle from './components/ResponsiveToggle.vue';
import Navigator from './components/Navigator.vue';

const store = useBuilderStore();
const backUrl = '/pages';

// Context menu state
const contextMenu = reactive({
  visible: false,
  position: { x: 0, y: 0 },
  elementType: null,
  elementData: null
});

// Initialize from page data
onMounted(() => {
  const pageDataEl = document.getElementById('page-data');
  if (pageDataEl) {
    const pageData = JSON.parse(pageDataEl.textContent);
    store.init(pageData);
  }

  // Keyboard shortcuts
  document.addEventListener('keydown', handleKeydown);

  // Context menu
  document.addEventListener('contextmenu', handleContextMenu);

  // Autosave
  const autosaveInterval = setInterval(() => {
    if (store.isDirty && !store.isSaving) {
      store.save();
    }
  }, 30000);

  onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.removeEventListener('contextmenu', handleContextMenu);
    clearInterval(autosaveInterval);
  });
});

function handleContextMenu(e) {
  // Find if we're right-clicking on a builder element
  const target = e.target.closest('[data-element-id]');

  if (target) {
    e.preventDefault();
    const elementId = target.dataset.elementId;
    const elementType = target.dataset.elementType;
    const elementData = store.findElement(elementId);

    contextMenu.visible = true;
    contextMenu.position = { x: e.clientX, y: e.clientY };
    contextMenu.elementType = elementType;
    contextMenu.elementData = elementData;
  } else if (e.target.closest('.bg-white.min-h-\\[600px\\]')) {
    // Right-click on canvas
    e.preventDefault();
    contextMenu.visible = true;
    contextMenu.position = { x: e.clientX, y: e.clientY };
    contextMenu.elementType = 'canvas';
    contextMenu.elementData = null;
  }
}

function handleContextMenuAction({ action, elementData }) {
  switch (action) {
    case 'edit':
      if (elementData) {
        store.selectElement(elementData.id, elementData.elType);
      }
      break;
    case 'duplicate':
      if (elementData) {
        store.duplicateElement(elementData.id);
      }
      break;
    case 'copy':
      store.copy();
      break;
    case 'paste':
      store.paste();
      break;
    case 'delete':
      if (elementData) {
        store.deleteElement(elementData.id);
      }
      break;
    case 'add_section':
      store.addSection('100');
      break;
    case 'navigator':
      store.leftPanel = 'navigator';
      if (elementData) {
        store.selectElement(elementData.id, elementData.elType);
      }
      break;
    case 'reset_style':
      // TODO: Implement style reset
      break;
    case 'save_global':
    case 'save_template':
      // TODO: Implement save as template
      break;
  }
}

function handleKeydown(e) {
  // Save: Ctrl+S
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault();
    store.save();
  }
  // Undo: Ctrl+Z
  else if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
    e.preventDefault();
    store.undo();
  }
  // Redo: Ctrl+Shift+Z or Ctrl+Y
  else if (((e.ctrlKey || e.metaKey) && e.key === 'z' && e.shiftKey) || ((e.ctrlKey || e.metaKey) && e.key === 'y')) {
    e.preventDefault();
    store.redo();
  }
  // Copy: Ctrl+C
  else if ((e.ctrlKey || e.metaKey) && e.key === 'c') {
    if (!isInputFocused()) {
      e.preventDefault();
      store.copy();
    }
  }
  // Paste: Ctrl+V
  else if ((e.ctrlKey || e.metaKey) && e.key === 'v') {
    if (!isInputFocused()) {
      e.preventDefault();
      store.paste();
    }
  }
  // Cut: Ctrl+X
  else if ((e.ctrlKey || e.metaKey) && e.key === 'x') {
    if (!isInputFocused() && store.selectedElement) {
      e.preventDefault();
      store.cut();
    }
  }
  // Duplicate: Ctrl+D
  else if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
    e.preventDefault();
    if (store.selectedElement) {
      store.duplicateElement(store.selectedElement);
    }
  }
  // Delete: Delete or Backspace
  else if ((e.key === 'Delete' || e.key === 'Backspace') && store.selectedElement && !isInputFocused()) {
    e.preventDefault();
    store.deleteElement(store.selectedElement);
  }
  // Group (select section): Ctrl+G
  else if ((e.ctrlKey || e.metaKey) && e.key === 'g') {
    e.preventDefault();
    if (store.selectedElement) {
      const parent = store.findParent(store.selectedElement);
      if (parent) {
        store.selectElement(parent.id, parent.elType);
      }
    }
  }
  // Escape: Deselect
  else if (e.key === 'Escape') {
    store.selectedElement = null;
    store.selectedType = null;
    contextMenu.visible = false;
  }
  // Enter: Open settings panel
  else if (e.key === 'Enter' && store.selectedElement && !isInputFocused()) {
    e.preventDefault();
    store.activeTab = 'content';
  }
  // Arrow Up: Select previous sibling
  else if (e.key === 'ArrowUp' && store.selectedElement && !isInputFocused()) {
    e.preventDefault();
    selectPreviousSibling();
  }
  // Arrow Down: Select next sibling
  else if (e.key === 'ArrowDown' && store.selectedElement && !isInputFocused()) {
    e.preventDefault();
    selectNextSibling();
  }
  // Arrow Right: Select first child
  else if (e.key === 'ArrowRight' && store.selectedElement && !isInputFocused()) {
    e.preventDefault();
    selectFirstChild();
  }
  // Arrow Left: Select parent
  else if (e.key === 'ArrowLeft' && store.selectedElement && !isInputFocused()) {
    e.preventDefault();
    const parent = store.findParent(store.selectedElement);
    if (parent) {
      store.selectElement(parent.id, parent.elType);
    }
  }
}

// Helper to check if an input/textarea is focused
function isInputFocused() {
  const activeEl = document.activeElement;
  return activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.isContentEditable);
}

// Navigate to previous sibling element
function selectPreviousSibling() {
  const parent = store.findParent(store.selectedElement);
  if (!parent?.elements) return;

  const currentIndex = parent.elements.findIndex(el => el.id === store.selectedElement);
  if (currentIndex > 0) {
    const prevSibling = parent.elements[currentIndex - 1];
    store.selectElement(prevSibling.id, prevSibling.elType);
  }
}

// Navigate to next sibling element
function selectNextSibling() {
  const parent = store.findParent(store.selectedElement);
  if (!parent?.elements) return;

  const currentIndex = parent.elements.findIndex(el => el.id === store.selectedElement);
  if (currentIndex < parent.elements.length - 1) {
    const nextSibling = parent.elements[currentIndex + 1];
    store.selectElement(nextSibling.id, nextSibling.elType);
  }
}

// Navigate to first child element
function selectFirstChild() {
  const current = store.findElement(store.selectedElement);
  if (current?.elements && current.elements.length > 0) {
    const firstChild = current.elements[0];
    store.selectElement(firstChild.id, firstChild.elType);
  }
}

function onDragStart(e, widgetType) {
  e.dataTransfer.setData('widgetType', widgetType);
}

function onDrop(e, columnId) {
  const widgetType = e.dataTransfer.getData('widgetType');
  if (widgetType) {
    store.addWidget(widgetType, columnId);
  }
}
</script>
