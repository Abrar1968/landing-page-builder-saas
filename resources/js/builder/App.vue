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

    <!-- Global Drag State Indicator -->
    <div v-if="isDraggingWidget || draggedWidget || draggedSectionLayout || draggedSectionIndex !== null"
         class="fixed top-16 left-1/2 -translate-x-1/2 bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 text-sm font-medium animate-bounce">
      {{ isDraggingWidget || draggedWidget ? '📦 Dragging widget - drop in any column' : '📋 Dragging section - drop on canvas' }}
    </div>

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
              <button
                @click="store.addSection('100')"
                draggable="true"
                @dragstart="onDragStartSection($event, '100')"
                class="p-2 border rounded hover:bg-gray-50 cursor-move">
                <div class="h-6 bg-gray-200 rounded"></div>
              </button>
              <button
                @click="store.addSection('50-50')"
                draggable="true"
                @dragstart="onDragStartSection($event, '50-50')"
                class="p-2 border rounded hover:bg-gray-50 cursor-move">
                <div class="h-6 flex gap-0.5"><div class="flex-1 bg-gray-200 rounded"></div><div class="flex-1 bg-gray-200 rounded"></div></div>
              </button>
              <button
                @click="store.addSection('33-33-33')"
                draggable="true"
                @dragstart="onDragStartSection($event, '33-33-33')"
                class="p-2 border rounded hover:bg-gray-50 cursor-move">
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
                 @dragend="onDragEndNewWidget"
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

            <!-- Drop zone for first section on empty canvas -->
            <div
              v-if="store.content.length === 0 && draggedSectionLayout !== null"
              @dragover.prevent
              @drop="onDropSection($event, 0)"
              @dragenter="handleDragEnter($event)"
              @dragleave="handleDragLeave($event)"
              class="h-32 border-4 border-dashed border-indigo-300 rounded-lg flex items-center justify-center text-indigo-500 font-medium bg-indigo-50 hover:border-indigo-500 hover:bg-indigo-100 transition-all mx-8 my-4">
              Drop section here to start
            </div>

            <!-- Sections with drop zones -->
            <template v-for="(section, sectionIndex) in store.content" :key="section.id">
              <!-- Drop zone before section -->
              <div
                v-if="(draggedSectionIndex !== null && draggedSectionIndex !== sectionIndex) || draggedSectionLayout !== null"
                @dragover.prevent
                @drop="onDropSection($event, sectionIndex)"
                @dragenter="handleDragEnter($event)"
                @dragleave="handleDragLeave($event)"
                class="drop-zone-section h-8 border-2 border-dashed border-indigo-300 hover:border-indigo-500 hover:h-16 hover:bg-indigo-100 transition-all flex items-center justify-center text-sm text-indigo-600 rounded-lg">
                <span class="opacity-50 hover:opacity-100">Drop section here</span>
              </div>

              <!-- Section -->
              <div
                @click.stop="store.selectElement(section.id, 'section')"
                :data-element-id="section.id"
                :data-element-type="'section'"
                :class="store.selectedElement === section.id ? 'ring-2 ring-indigo-500' : ''"
                class="section-container relative group"
                draggable="true"
                @dragstart="onDragStartSectionReorder($event, sectionIndex)"
                @dragend="onDragEndSection">
                <!-- Section toolbar -->
                <div class="absolute -top-8 left-0 opacity-0 group-hover:opacity-100 z-10">
                  <div class="flex items-center gap-1 bg-indigo-600 text-white text-xs px-2 py-1 rounded shadow">
                    <svg class="w-3 h-3 cursor-move" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span>Section</span>
                    <button @click.stop="store.duplicateElement(section.id)" class="hover:text-indigo-200">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </button>
                    <button @click.stop="store.deleteElement(section.id)" class="hover:text-red-300">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                  </div>
                </div>

                <div class="flex" :style="getSectionStyles(section)">
                  <!-- Container level -->
                  <div v-for="container in section.elements" :key="container.id"
                       @click.stop="store.selectElement(container.id, 'container')"
                       :data-element-id="container.id"
                       :data-element-type="'container'"
                       :class="[
                         store.selectedElement === container.id ? 'ring-2 ring-purple-500' : '',
                         container.settings?.content_width === 'boxed' ? 'container mx-auto' : 'w-full'
                       ]"
                       class="container-wrapper relative group/container">
                    <!-- Container toolbar -->
                    <div class="absolute -top-8 left-0 opacity-0 group-hover/container:opacity-100 z-10">
                      <div class="flex items-center gap-1 bg-purple-600 text-white text-xs px-2 py-1 rounded shadow">
                        <span>Container</span>
                        <button @click.stop="store.duplicateElement(container.id)" class="hover:text-purple-200">
                          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </button>
                      </div>
                    </div>

                    <!-- Columns inside container -->
                    <div class="flex" :class="getContainerFlexDirection(container)">
                      <div v-for="column in container.elements" :key="column.id"
                           @click.stop="store.selectElement(column.id, 'column')"
                           :data-element-id="column.id"
                           :data-element-type="'column'"
                           :class="[
                             store.selectedElement === column.id ? 'ring-2 ring-blue-500' : '',
                             column.settings?.css_classes || '',
                             getColumnAnimationClasses(column)
                           ]"
                           :style="getColumnStyles(column)"
                           class="column-container relative group/col border border-dashed border-transparent hover:border-gray-300"
                           @dragover.prevent>
                        <div class="min-h-[100px]" :style="getColumnInnerStyles(column)">
                    <!-- Empty column state -->
                    <div
                      v-if="(column.elements || []).length === 0"
                      @dragover.prevent
                      @drop="onDropWidgetToColumn($event, column.id, 0)"
                      @dragenter="handleDragEnter($event)"
                      @dragleave="handleDragLeave($event)"
                      class="h-32 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center text-gray-400 text-sm hover:border-green-500 hover:bg-green-50 transition-all cursor-copy">
                      <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      </svg>
                      <span>Drop widget here</span>
                    </div>

                    <!-- Widgets with drop zones -->
                    <template v-for="(widget, widgetIndex) in (column.elements || [])" :key="widget.id">
                      <!-- Drop zone before widget -->
                      <div
                        v-if="(draggedWidget || isDraggingWidget) && !(draggedWidget?.columnId === column.id && draggedWidget?.index === widgetIndex)"
                        @dragover.prevent
                        @drop="onDropWidgetToColumn($event, column.id, widgetIndex)"
                        @dragenter="handleDragEnter($event)"
                        @dragleave="handleDragLeave($event)"
                        class="drop-zone-widget h-2 border-2 border-dashed border-transparent hover:border-green-400 hover:h-8 hover:bg-green-50 transition-all mb-2">
                      </div>

                      <!-- Widget -->
                      <div
                         @click.stop="store.selectElement(widget.id, 'widget')"
                         :data-element-id="widget.id"
                         :data-element-type="'widget'"
                         :class="store.selectedElement === widget.id ? 'ring-2 ring-green-500' : ''"
                         class="widget-container relative group/widget mb-4"
                         draggable="true"
                         @dragstart="onDragStartWidget($event, column.id, widgetIndex, widget.id)"
                         @dragend="onDragEndWidget">
                        <!-- Widget toolbar -->
                        <div class="absolute -top-6 left-1/2 -translate-x-1/2 opacity-0 group-hover/widget:opacity-100 z-10">
                          <div class="flex items-center gap-1 bg-green-600 text-white text-xs px-2 py-1 rounded shadow">
                            <svg class="w-3 h-3 cursor-move" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
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

                      <!-- Drop zone after last widget -->
                      <div
                        v-if="widgetIndex === (column.elements || []).length - 1 && (draggedWidget || isDraggingWidget)"
                        @dragover.prevent
                        @drop="onDropWidgetToColumn($event, column.id, (column.elements || []).length)"
                        @dragenter="handleDragEnter($event)"
                        @dragleave="handleDragLeave($event)"
                        class="drop-zone-widget h-2 border-2 border-dashed border-transparent hover:border-green-400 hover:h-8 hover:bg-green-50 transition-all">
                      </div>
                    </template> <!-- close widgets template loop -->
                        </div> <!-- close min-h-[100px] -->
                      </div> <!-- close column v-for div -->
                    </div> <!-- close flex (columns wrapper) -->
                  </div> <!-- close container v-for div -->
                </div> <!-- close flex (section) -->
              </div> <!-- close section-container -->
            </template> <!-- close sections template loop -->

            <!-- Drop zone after last section -->
            <div
              v-if="draggedSectionIndex !== null"
              @dragover.prevent
              @drop="onDropSection($event, store.content.length)"
              @dragenter="handleDragEnter($event)"
              @dragleave="handleDragLeave($event)"
              class="drop-zone-section h-8 border-2 border-dashed border-transparent hover:border-indigo-400 hover:bg-indigo-50 transition-all flex items-center justify-center text-xs text-gray-400">
              <span class="opacity-0 hover:opacity-100">Drop section here</span>
            </div>
          </div> <!-- close bg-white min-h-[600px] shadow-lg -->
        </div> <!-- close max-w-* mx-auto transition-all -->
      </main>

      <!-- Right Panel - Properties -->
      <aside class="w-80 bg-white border-l flex flex-col shrink-0">
        <div v-if="store.selectedElementData" class="flex flex-col h-full">
          <!-- Header -->
          <div class="p-4 border-b flex items-center justify-between">
            <h3 class="font-medium text-gray-900">
              {{
                store.selectedType === 'widget' ? 'Widget Settings' :
                store.selectedType === 'section' ? 'Section Settings' :
                store.selectedType === 'container' ? 'Container Settings' :
                'Column Settings'
              }}
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

    <!-- Floating Add Section Button -->
    <button
      @click="toggleLayoutPicker"
      class="fixed bottom-8 right-8 w-14 h-14 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-lg flex items-center justify-center z-50 transition-all hover:scale-110 group"
      title="Add Section">
      <svg class="w-6 h-6 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
    </button>

    <!-- Layout Picker Modal -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95">
        <div
          v-if="store.showLayoutPicker"
          class="fixed inset-0 z-50 flex items-center justify-center p-4"
          @click.self="store.showLayoutPicker = false">
          <!-- Backdrop -->
          <div class="absolute inset-0 bg-black bg-opacity-50" @click="store.showLayoutPicker = false"></div>

          <!-- Modal Content -->
          <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
            <!-- Header -->
            <div class="p-6 border-b bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
              <div class="flex items-center justify-between">
                <div>
                  <h2 class="text-2xl font-bold">Choose Column Layout</h2>
                  <p class="text-indigo-100 text-sm mt-1">Select a preset layout for your new section</p>
                </div>
                <button
                  @click="store.showLayoutPicker = false"
                  class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Layouts Grid -->
            <div class="p-6 overflow-y-auto max-h-[calc(80vh-120px)]">
              <div class="grid grid-cols-3 gap-4">
                <!-- 1 Column Layouts -->
                <button
                  @click="addSectionWithLayout('100')"
                  class="group p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all bg-white hover:bg-indigo-50">
                  <div class="flex gap-1 h-24 mb-3">
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                  </div>
                  <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">100%</p>
                  <p class="text-xs text-gray-500">Single Column</p>
                </button>

                <!-- 2 Column Layouts -->
                <button
                  @click="addSectionWithLayout('50-50')"
                  class="group p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all bg-white hover:bg-indigo-50">
                  <div class="flex gap-1 h-24 mb-3">
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                  </div>
                  <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">50% + 50%</p>
                  <p class="text-xs text-gray-500">Equal Columns</p>
                </button>

                <button
                  @click="addSectionWithLayout('33-66')"
                  class="group p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all bg-white hover:bg-indigo-50">
                  <div class="flex gap-1 h-24 mb-3">
                    <div class="flex-[33] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-[66] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                  </div>
                  <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">33% + 67%</p>
                  <p class="text-xs text-gray-500">Sidebar Left</p>
                </button>

                <button
                  @click="addSectionWithLayout('66-33')"
                  class="group p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all bg-white hover:bg-indigo-50">
                  <div class="flex gap-1 h-24 mb-3">
                    <div class="flex-[66] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-[33] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                  </div>
                  <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">67% + 33%</p>
                  <p class="text-xs text-gray-500">Sidebar Right</p>
                </button>

                <button
                  @click="addSectionWithLayout('25-75')"
                  class="group p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all bg-white hover:bg-indigo-50">
                  <div class="flex gap-1 h-24 mb-3">
                    <div class="flex-[25] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-[75] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                  </div>
                  <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">25% + 75%</p>
                  <p class="text-xs text-gray-500">Narrow Sidebar Left</p>
                </button>

                <button
                  @click="addSectionWithLayout('75-25')"
                  class="group p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all bg-white hover:bg-indigo-50">
                  <div class="flex gap-1 h-24 mb-3">
                    <div class="flex-[75] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-[25] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                  </div>
                  <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">75% + 25%</p>
                  <p class="text-xs text-gray-500">Narrow Sidebar Right</p>
                </button>

                <!-- 3 Column Layouts -->
                <button
                  @click="addSectionWithLayout('33-33-33')"
                  class="group p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all bg-white hover:bg-indigo-50">
                  <div class="flex gap-1 h-24 mb-3">
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                  </div>
                  <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">33% + 33% + 33%</p>
                  <p class="text-xs text-gray-500">Three Equal</p>
                </button>

                <button
                  @click="addSectionWithLayout('25-50-25')"
                  class="group p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all bg-white hover:bg-indigo-50">
                  <div class="flex gap-1 h-24 mb-3">
                    <div class="flex-[25] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-[50] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-[25] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                  </div>
                  <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">25% + 50% + 25%</p>
                  <p class="text-xs text-gray-500">Center Focus</p>
                </button>

                <button
                  @click="addSectionWithLayout('20-60-20')"
                  class="group p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all bg-white hover:bg-indigo-50">
                  <div class="flex gap-1 h-24 mb-3">
                    <div class="flex-[20] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-[60] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-[20] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                  </div>
                  <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">20% + 60% + 20%</p>
                  <p class="text-xs text-gray-500">Wide Center</p>
                </button>

                <!-- 4 Column Layouts -->
                <button
                  @click="addSectionWithLayout('25-25-25-25')"
                  class="group p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all bg-white hover:bg-indigo-50">
                  <div class="flex gap-1 h-24 mb-3">
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                  </div>
                  <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">25% × 4</p>
                  <p class="text-xs text-gray-500">Four Equal</p>
                </button>

                <button
                  @click="addSectionWithLayout('40-20-20-20')"
                  class="group p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all bg-white hover:bg-indigo-50">
                  <div class="flex gap-1 h-24 mb-3">
                    <div class="flex-[40] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-[20] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-[20] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-[20] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                  </div>
                  <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">40% + 20% × 3</p>
                  <p class="text-xs text-gray-500">Featured Left</p>
                </button>

                <button
                  @click="addSectionWithLayout('20-20-20-40')"
                  class="group p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all bg-white hover:bg-indigo-50">
                  <div class="flex gap-1 h-24 mb-3">
                    <div class="flex-[20] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-[20] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-[20] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-[40] bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                  </div>
                  <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">20% × 3 + 40%</p>
                  <p class="text-xs text-gray-500">Featured Right</p>
                </button>

                <!-- 5 Column Layouts -->
                <button
                  @click="addSectionWithLayout('20-20-20-20-20')"
                  class="group p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all bg-white hover:bg-indigo-50">
                  <div class="flex gap-1 h-24 mb-3">
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                  </div>
                  <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">20% × 5</p>
                  <p class="text-xs text-gray-500">Five Equal</p>
                </button>

                <!-- 6 Column Layouts -->
                <button
                  @click="addSectionWithLayout('16-16-16-16-16-16')"
                  class="group p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all bg-white hover:bg-indigo-50">
                  <div class="flex gap-0.5 h-24 mb-3">
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                    <div class="flex-1 bg-gradient-to-br from-gray-200 to-gray-300 rounded group-hover:from-indigo-200 group-hover:to-indigo-300 transition-colors"></div>
                  </div>
                  <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">16.6% × 6</p>
                  <p class="text-xs text-gray-500">Six Equal (Max)</p>
                </button>
              </div>

              <!-- Info Note -->
              <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex gap-3">
                  <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <div class="text-sm text-blue-800">
                    <p class="font-medium mb-1">Column Limits</p>
                    <ul class="list-disc list-inside space-y-1 text-blue-700">
                      <li>Maximum 6 columns horizontally per container</li>
                      <li>Unlimited vertical stacking - add multiple containers</li>
                      <li>Column widths are percentage-based and responsive</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, reactive, ref } from 'vue';
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

// Drag and drop state
const draggedSectionIndex = ref(null);
const draggedSectionLayout = ref(null);
const draggedWidget = ref(null);
const isDraggingWidget = ref(false);

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

// Helper functions for section styling
function getSectionStyles(section) {
  const settings = section.settings || {};
  const styles = {
    minHeight: `${settings.min_height || 100}px`
  };

  if (settings.background_color) {
    styles.backgroundColor = settings.background_color;
  }

  if (settings.background_image) {
    styles.backgroundImage = `url(${settings.background_image})`;
    styles.backgroundSize = settings.background_size || 'cover';
    styles.backgroundPosition = settings.background_position || 'center';
    styles.backgroundRepeat = settings.background_repeat || 'no-repeat';
  }

  return styles;
}

// Helper functions for column styling
function getColumnStyles(column) {
  const settings = column.settings || {};
  const styles = {
    width: `${settings._column_size || 100}%`
  };

  if (settings.background_color) {
    styles.backgroundColor = settings.background_color;
  }

  // Add motion effects animation duration and delay
  if (settings.motion_effects?.animation_duration) {
    styles.animationDuration = `${settings.motion_effects.animation_duration}ms`;
  }

  if (settings.motion_effects?.animation_delay) {
    styles.animationDelay = `${settings.motion_effects.animation_delay}ms`;
  }

  return styles;
}

function getColumnAnimationClasses(column) {
  const settings = column.settings || {};
  const classes = [];

  // Add entrance animation classes
  if (settings.motion_effects?.entrance_animation) {
    classes.push('animate__animated');
    classes.push(`animate__${settings.motion_effects.entrance_animation}`);
  }

  return classes.join(' ');
}

function getContainerFlexDirection(container) {
  const direction = container.settings?.column_direction || 'row';
  return direction === 'column' ? 'flex-col' : 'flex-row';
}

function getColumnInnerStyles(column) {
  const settings = column.settings || {};
  const styles = {
    padding: '1rem' // Default padding
  };

  if (settings.padding) {
    const p = settings.padding;
    const unit = p.unit || 'px';
    styles.padding = `${p.top || 0}${unit} ${p.right || 0}${unit} ${p.bottom || 0}${unit} ${p.left || 0}${unit}`;
  }

  if (settings.margin) {
    const m = settings.margin;
    const unit = m.unit || 'px';
    styles.margin = `${m.top || 0}${unit} ${m.right || 0}${unit} ${m.bottom || 0}${unit} ${m.left || 0}${unit}`;
  }

  return styles;
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
  e.dataTransfer.effectAllowed = 'copy';
  isDraggingWidget.value = true;

  // Create custom drag image
  const dragImage = document.createElement('div');
  dragImage.className = 'bg-white border-2 border-indigo-500 rounded-lg shadow-lg px-4 py-2 text-sm font-medium';
  dragImage.textContent = widgetType.charAt(0).toUpperCase() + widgetType.slice(1).replace(/-/g, ' ');
  dragImage.style.position = 'absolute';
  dragImage.style.top = '-1000px';
  document.body.appendChild(dragImage);
  e.dataTransfer.setDragImage(dragImage, 0, 0);

  // Clean up after drag
  setTimeout(() => document.body.removeChild(dragImage), 0);
}

function onDragStartWidget(e, columnId, widgetIndex, widgetId) {
  draggedWidget.value = {
    columnId,
    index: widgetIndex,
    widgetId
  };
  e.dataTransfer.effectAllowed = 'move';
  e.target.style.opacity = '0.5';
}

function onDragEndWidget(e) {
  draggedWidget.value = null;
  e.target.style.opacity = '1';
}

function onDragEndNewWidget(e) {
  isDraggingWidget.value = false;
}

function onDropWidgetToColumn(e, targetColumnId, targetIndex) {
  e.preventDefault();
  e.stopPropagation();

  const widgetType = e.dataTransfer.getData('widgetType');

  if (draggedWidget.value) {
    // Moving existing widget
    const { columnId: sourceColumnId, index: sourceIndex, widgetId } = draggedWidget.value;

    // Don't do anything if dropping in the same position
    if (sourceColumnId === targetColumnId && sourceIndex === targetIndex) {
      draggedWidget.value = null;
      return;
    }

    store.moveWidget(widgetId, sourceColumnId, sourceIndex, targetColumnId, targetIndex);
    draggedWidget.value = null;
  } else if (widgetType) {
    // Adding new widget from sidebar
    store.addWidgetAtIndex(widgetType, targetColumnId, targetIndex);
  }
}

function onDragStartSection(e, layout) {
  draggedSectionLayout.value = layout;
  e.dataTransfer.effectAllowed = 'copy';

  // Add drag preview
  const dragImage = document.createElement('div');
  dragImage.className = 'bg-white border-2 border-indigo-500 rounded-lg shadow-lg px-4 py-2 text-sm font-medium';
  dragImage.textContent = `Section (${layout.split('-').length} columns)`;
  dragImage.style.position = 'absolute';
  dragImage.style.top = '-1000px';
  document.body.appendChild(dragImage);
  e.dataTransfer.setDragImage(dragImage, 0, 0);
  setTimeout(() => document.body.removeChild(dragImage), 0);
}

function onDragStartSectionReorder(e, sectionIndex) {
  draggedSectionIndex.value = sectionIndex;
  e.dataTransfer.effectAllowed = 'move';
  // Add visual feedback
  e.target.style.opacity = '0.5';
}

function onDragEndSection(e) {
  draggedSectionIndex.value = null;
  e.target.style.opacity = '1';
}

function onDropSection(e, targetIndex) {
  e.preventDefault();

  if (draggedSectionIndex.value !== null) {
    // Reordering existing section
    const fromIndex = draggedSectionIndex.value;
    if (fromIndex !== targetIndex) {
      store.moveSectionToIndex(fromIndex, targetIndex);
    }
    draggedSectionIndex.value = null;
  } else if (draggedSectionLayout.value) {
    // Adding new section at specific position
    store.addSectionAtIndex(draggedSectionLayout.value, targetIndex);
    draggedSectionLayout.value = null;
  }
}

function handleDragEnter(e) {
  e.currentTarget.classList.add('!border-indigo-400', '!bg-indigo-50');
}

function handleDragLeave(e) {
  e.currentTarget.classList.remove('!border-indigo-400', '!bg-indigo-50');
}

// Layout Picker Functions
function toggleLayoutPicker() {
  store.showLayoutPicker = !store.showLayoutPicker;
}

function addSectionWithLayout(layout) {
  store.addSection(layout);
  store.showLayoutPicker = false;

  // Scroll to the new section
  setTimeout(() => {
    const sections = document.querySelectorAll('.section-container');
    const lastSection = sections[sections.length - 1];
    if (lastSection) {
      lastSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }, 100);
}
</script>
