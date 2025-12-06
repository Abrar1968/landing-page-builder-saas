import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { widgetRegistry } from '../widgets/registry';

export const useBuilderStore = defineStore('builder', () => {
    // Document state
    const documentId = ref(null);
    const documentTitle = ref('');
    const documentSettings = ref({});
    const content = ref([]);

    // UI state
    const selectedElement = ref(null);
    const selectedType = ref(null);
    const activeTab = ref('content');
    const leftPanel = ref('widgets');
    const previewMode = ref('desktop');
    const hoverState = ref('normal'); // 'normal' or 'hover'
    const responsiveDevice = ref('desktop'); // 'desktop', 'tablet', 'mobile'

    // History
    const history = ref([]);
    const historyIndex = ref(-1);

    // Clipboard
    const clipboard = ref(null);

    // State flags
    const isDirty = ref(false);
    const isSaving = ref(false);
    const lastSaved = ref(null);

    // Modals
    const showMediaLibrary = ref(false);
    const showDeleteConfirm = ref(false);
    const showLayoutPicker = ref(false);
    const layoutPickerPosition = ref({ x: 0, y: 0 });
    const mediaControlName = ref(null);
    const mediaItems = ref([]);
    const mediaLoading = ref(false);

    // Search
    const widgetSearch = ref('');

    // Computed
    const filteredWidgets = computed(() => {
        const widgets = widgetRegistry.getAll();
        if (!widgetSearch.value) return widgets;
        const search = widgetSearch.value.toLowerCase();
        return Object.fromEntries(
            Object.entries(widgets).filter(([key, w]) =>
                w.title.toLowerCase().includes(search) || key.includes(search)
            )
        );
    });

    const selectedElementData = computed(() => {
        if (!selectedElement.value) return null;
        return findElement(selectedElement.value);
    });

    const currentControls = computed(() => {
        const el = selectedElementData.value;
        if (!el) return [];

        if (el.elType === 'widget') {
            const widget = widgetRegistry.get(el.widgetType);
            return widget?.controls?.[activeTab.value] || [];
        }

        if (el.elType === 'section') {
            return getSectionControls()[activeTab.value] || [];
        }

        if (el.elType === 'container') {
            return getContainerControls()[activeTab.value] || [];
        }

        if (el.elType === 'column') {
            return getColumnControls()[activeTab.value] || [];
        }

        return [];
    });

    // Helper functions
    function generateId() {
        return Math.random().toString(36).substr(2, 9);
    }

    function findElement(id, elements = content.value) {
        for (const el of elements) {
            if (el.id === id) return el;
            if (el.elements) {
                const found = findElement(id, el.elements);
                if (found) return found;
            }
        }
        return null;
    }

    function findParent(id, elements = content.value, parent = null) {
        for (const el of elements) {
            if (el.id === id) return parent;
            if (el.elements) {
                const found = findParent(id, el.elements, el);
                if (found !== undefined) return found;
            }
        }
        return undefined;
    }

    // Deep clone helper for proper reactivity
    function deepClone(obj) {
        if (obj === null || typeof obj !== 'object') return obj;
        if (Array.isArray(obj)) return obj.map(item => deepClone(item));
        const cloned = {};
        for (const key in obj) {
            if (Object.prototype.hasOwnProperty.call(obj, key)) {
                cloned[key] = deepClone(obj[key]);
            }
        }
        return cloned;
    }

    // Replace element in nested content structure with new reference
    function replaceElementInContent(id, newElement, elements) {
        for (let i = 0; i < elements.length; i++) {
            if (elements[i].id === id) {
                elements[i] = newElement;
                return true;
            }
            if (elements[i].elements) {
                // Clone the parent element too if child is found
                if (replaceElementInContent(id, newElement, elements[i].elements)) {
                    return true;
                }
            }
        }
        return false;
    }

    function getWidgetDefaults(widgetType) {
        const widget = widgetRegistry.get(widgetType);
        if (!widget) return {};

        const defaults = {};
        ['content', 'style', 'advanced'].forEach(tab => {
            (widget.controls[tab] || []).forEach(control => {
                if (control.default !== undefined) {
                    defaults[control.name] = control.default;
                }
            });
        });
        return defaults;
    }

    function applyDefaultsToContent(elements) {
        if (!elements) return;
        elements.forEach(el => {
            if (!el.settings) el.settings = {};

            if (el.elType === 'widget' && el.widgetType) {
                const widgetConfig = widgetRegistry.get(el.widgetType);
                if (widgetConfig) {
                    ['content', 'style', 'advanced'].forEach(tab => {
                        (widgetConfig.controls[tab] || []).forEach(control => {
                            if (control.default !== undefined && el.settings[control.name] === undefined) {
                                el.settings[control.name] = control.default;
                            }
                        });
                    });
                }
            }

            if (el.elements) {
                applyDefaultsToContent(el.elements);
            }
        });
    }

    // Static control definitions
    const sectionControls = {
        content: [
            { name: 'min_height', type: 'slider', label: 'Min Height', min: 0, max: 1000, unit: 'px' }
        ],
        style: [
            { name: 'background_color', type: 'color', label: 'Background Color' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' },
            { name: 'css_id', type: 'text', label: 'CSS ID' }
        ]
    };

    const containerControls = {
        content: [
            { name: 'content_width', type: 'select', label: 'Content Width', options: { boxed: 'Boxed', full: 'Full Width' }, default: 'boxed' }
        ],
        style: [
            { name: 'column_direction', type: 'choose', label: 'Column Direction',
              options: {
                row: { title: 'Horizontal', icon: '→' },
                column: { title: 'Vertical', icon: '↓' }
              },
              default: 'row' },
            { name: 'background_color', type: 'color', label: 'Background Color' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' }
        ]
    };

    const columnControls = {
        content: [
            { name: '_column_size', type: 'slider', label: 'Column Width', min: 0, max: 100, unit: '%' },
            { name: 'vertical_align', type: 'select', label: 'Vertical Align', options: { top: 'Top', middle: 'Middle', bottom: 'Bottom' } }
        ],
        style: [
            { name: 'background_color', type: 'color', label: 'Background Color' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' },
            { name: 'motion_effects', type: 'motion_effects', label: 'Motion Effects' }
        ]
    };

    function getSectionControls() {
        return sectionControls;
    }

    function getContainerControls() {
        return containerControls;
    }

    function getColumnControls() {
        return columnControls;
    }

    // Normalize widget type property recursively
    function normalizeWidgetTypes(elements) {
        if (!Array.isArray(elements)) return;

        elements.forEach(el => {
            // Convert 'type' to 'widgetType' for widgets
            if (el.elType === 'widget' && el.type && !el.widgetType) {
                el.widgetType = el.type;
            }
            // Ensure both properties exist for backwards compatibility
            if (el.elType === 'widget' && el.widgetType && !el.type) {
                el.type = el.widgetType;
            }
            // Recursively normalize nested elements
            if (el.elements) {
                normalizeWidgetTypes(el.elements);
            }
        });
    }

    // Actions
    function init(pageData) {
        documentId.value = pageData.id;
        documentTitle.value = pageData.title || '';
        documentSettings.value = pageData.settings || {};

        let loadedContent = pageData.content || pageData.elements || [];

        if (Array.isArray(loadedContent) && loadedContent.length > 0) {
            if (loadedContent[0]?.elType === 'section') {
                // Migrate old 2-level structure to 3-level (Section → Container → Column)
                content.value = loadedContent.map(section => {
                    // Check if section has old structure (direct columns) or empty
                    const firstElement = section.elements?.[0];
                    const hasOldStructure = firstElement?.elType === 'column' || section.elements?.length === 0;

                    if (hasOldStructure) {
                        // Ensure all columns have elements array
                        const columns = (section.elements || []).map(col => ({
                            ...col,
                            elements: col.elements || []
                        }));

                        // Create container wrapper
                        const container = {
                            id: generateId(),
                            elType: 'container',
                            settings: {
                                content_width: section.settings?.content_width || 'boxed'
                            },
                            elements: columns
                        };

                        // Remove content_width from section (now in container)
                        const newSectionSettings = { ...section.settings };
                        delete newSectionSettings.content_width;

                        return {
                            ...section,
                            settings: newSectionSettings,
                            elements: [container]
                        };
                    }

                    // Already has new structure
                    return section;
                });
            } else {
                content.value = [{
                    id: generateId(),
                    elType: 'section',
                    settings: {},
                    elements: [{
                        id: generateId(),
                        elType: 'container',
                        settings: { content_width: 'boxed' },
                        elements: [{
                            id: generateId(),
                            elType: 'column',
                            settings: { _column_size: 100 },
                            elements: loadedContent.map(el => ({
                                id: el.id || generateId(),
                                elType: 'widget',
                                widgetType: el.type || el.widgetType,
                                type: el.type || el.widgetType,
                                settings: el.settings || {}
                            }))
                        }]
                    }]
                }];
            }
        } else {
            content.value = [];
        }

        // Normalize all widget types recursively
        normalizeWidgetTypes(content.value);

        applyDefaultsToContent(content.value);
        addToHistory();
    }

    function addSection(layout = '100') {
        const columns = layout.split('-').map(size => ({
            id: generateId(),
            elType: 'column',
            settings: { _column_size: parseInt(size) },
            elements: []
        }));

        const container = {
            id: generateId(),
            elType: 'container',
            settings: { content_width: 'boxed' },
            elements: columns
        };

        const section = {
            id: generateId(),
            elType: 'section',
            settings: { structure: layout },
            elements: [container]
        };

        addToHistory();
        content.value.push(section);
        selectElement(section.id, 'section');
        isDirty.value = true;
    }

    function addSectionAtIndex(layout = '100', index) {
        const columns = layout.split('-').map(size => ({
            id: generateId(),
            elType: 'column',
            settings: { _column_size: parseInt(size) },
            elements: []
        }));

        const container = {
            id: generateId(),
            elType: 'container',
            settings: { content_width: 'boxed' },
            elements: columns
        };

        const section = {
            id: generateId(),
            elType: 'section',
            settings: { structure: layout },
            elements: [container]
        };

        addToHistory();
        // Insert at specific index
        content.value.splice(index, 0, section);
        selectElement(section.id, 'section');
        isDirty.value = true;
    }

    function moveSectionToIndex(fromIndex, toIndex) {
        addToHistory();

        // Remove section from old position
        const [section] = content.value.splice(fromIndex, 1);

        // Adjust target index if we removed an item before it
        const adjustedIndex = fromIndex < toIndex ? toIndex - 1 : toIndex;

        // Insert at new position
        content.value.splice(adjustedIndex, 0, section);

        isDirty.value = true;
    }

    function addWidget(widgetType, columnId) {
        const widget = {
            id: generateId(),
            elType: 'widget',
            widgetType: widgetType,
            type: widgetType, // Add for backwards compatibility
            settings: getWidgetDefaults(widgetType)
        };

        const column = findElement(columnId);
        if (column) {
            addToHistory();
            column.elements.push(widget);
            selectElement(widget.id, 'widget');
            isDirty.value = true;
        }
    }

    function addWidgetAtIndex(widgetType, columnId, index) {
        const widget = {
            id: generateId(),
            elType: 'widget',
            widgetType: widgetType,
            type: widgetType,
            settings: getWidgetDefaults(widgetType)
        };

        const column = findElement(columnId);
        if (column) {
            addToHistory();
            column.elements.splice(index, 0, widget);
            selectElement(widget.id, 'widget');
            isDirty.value = true;
        }
    }

    function moveWidget(widgetId, sourceColumnId, sourceIndex, targetColumnId, targetIndex) {
        addToHistory();

        const sourceColumn = findElement(sourceColumnId);
        const targetColumn = findElement(targetColumnId);

        if (!sourceColumn || !targetColumn) return;

        // Remove widget from source column
        const [widget] = sourceColumn.elements.splice(sourceIndex, 1);

        // If moving within the same column and target is after source, adjust index
        if (sourceColumnId === targetColumnId && targetIndex > sourceIndex) {
            targetIndex--;
        }

        // Insert widget into target column at target index
        targetColumn.elements.splice(targetIndex, 0, widget);

        // Select the moved widget
        selectElement(widgetId, 'widget');
        isDirty.value = true;
    }

    function clickAddWidget(widgetType) {
        let column = null;

        if (selectedType.value === 'column') {
            column = findElement(selectedElement.value);
        } else if (selectedType.value === 'widget') {
            column = findParent(selectedElement.value);
        } else if (content.value.length > 0) {
            // Navigate 3-level structure: Section → Container → Column
            const section = content.value[0];
            const container = section?.elements?.[0];
            column = container?.elements?.[0];
        }

        if (!column) {
            addSection('100');
            if (content.value.length > 0) {
                // Navigate to the column of the newly created section
                const section = content.value[content.value.length - 1];
                const container = section?.elements?.[0];
                column = container?.elements?.[0];
            }
        }

        if (column) {
            addWidget(widgetType, column.id);
        }
    }

    function selectElement(id, type) {
        selectedElement.value = id;
        selectedType.value = type;
        activeTab.value = 'content';
    }

    function clearSelection() {
        selectedElement.value = null;
        selectedType.value = null;
    }

    function getSetting(name) {
        const el = selectedElementData.value;
        if (!el) return undefined;

        // For hover state, check hover_settings first
        if (hoverState.value === 'hover') {
            if (el.hover_settings && el.hover_settings[name] !== undefined) {
                return el.hover_settings[name];
            }
        }

        // For responsive, check device-specific settings
        if (responsiveDevice.value !== 'desktop') {
            const deviceKey = `${name}_${responsiveDevice.value}`;
            if (el.settings && el.settings[deviceKey] !== undefined) {
                return el.settings[deviceKey];
            }
        }

        return el?.settings?.[name];
    }

    function updateSetting(name, value) {
        const el = selectedElementData.value;
        if (!el) {
            console.error('[updateSetting] No element selected!');
            return;
        }

        console.log('[updateSetting] Setting:', name, '=', JSON.stringify(value));
        console.log('[updateSetting] Element ID:', el.id, 'Type:', el.elType);

        addToHistory();

        // Create a new element object with updated settings for proper Vue reactivity
        const newElement = {
            ...el,
            settings: { ...(el.settings || {}) },
            hover_settings: { ...(el.hover_settings || {}) },
            settingsHash: Date.now()
        };

        // Update hover state settings
        if (hoverState.value === 'hover') {
            newElement.hover_settings[name] = value;
        }
        // Update responsive device settings
        else if (responsiveDevice.value !== 'desktop') {
            const deviceKey = `${name}_${responsiveDevice.value}`;
            newElement.settings[deviceKey] = value;
        }
        // Update normal desktop settings
        else {
            newElement.settings[name] = value;
        }

        // If element has nested elements, preserve them
        if (el.elements) {
            newElement.elements = el.elements;
        }

        console.log('[updateSetting] New settings:', JSON.stringify(newElement.settings));
        console.log('[updateSetting] New settingsHash:', newElement.settingsHash);

        // Replace the element in the content tree with the new reference
        replaceElementInContent(el.id, newElement, content.value);

        // Force Vue reactivity by creating new array reference
        content.value = [...content.value];

        isDirty.value = true;

        console.log('[updateSetting] Done. isDirty:', isDirty.value);
    }

    function deleteElement(id) {
        addToHistory();
        deleteRecursive(id, content.value);
        if (selectedElement.value === id) {
            selectedElement.value = null;
            selectedType.value = null;
        }
        isDirty.value = true;
    }

    function deleteRecursive(id, elements) {
        const index = elements.findIndex(el => el.id === id);
        if (index !== -1) {
            elements.splice(index, 1);
            return true;
        }
        for (const el of elements) {
            if (el.elements && deleteRecursive(id, el.elements)) {
                return true;
            }
        }
        return false;
    }

    function duplicateElement(id) {
        const el = findElement(id);
        const parent = findParent(id);
        if (!el || !parent?.elements) return;

        addToHistory();
        const duplicate = JSON.parse(JSON.stringify(el));
        regenerateIds(duplicate);

        const index = parent.elements.findIndex(e => e.id === id);
        parent.elements.splice(index + 1, 0, duplicate);
        selectElement(duplicate.id, duplicate.elType);
        isDirty.value = true;
    }

    function regenerateIds(el) {
        el.id = generateId();
        if (el.elements) {
            el.elements.forEach(child => regenerateIds(child));
        }
    }

    // History
    function addToHistory() {
        const state = JSON.stringify({ content: content.value, settings: documentSettings.value });
        history.value = history.value.slice(0, historyIndex.value + 1);
        history.value.push(state);
        if (history.value.length > 50) history.value.shift();
        historyIndex.value = history.value.length - 1;
    }

    function undo() {
        if (historyIndex.value <= 0) return;
        historyIndex.value--;
        const state = JSON.parse(history.value[historyIndex.value]);
        content.value = state.content;
        documentSettings.value = state.settings;
        isDirty.value = true;
    }

    function redo() {
        if (historyIndex.value >= history.value.length - 1) return;
        historyIndex.value++;
        const state = JSON.parse(history.value[historyIndex.value]);
        content.value = state.content;
        documentSettings.value = state.settings;
        isDirty.value = true;
    }

    // Clipboard
    function copy() {
        const el = selectedElementData.value;
        if (el) {
            clipboard.value = JSON.parse(JSON.stringify(el));
        }
    }

    function paste() {
        if (!clipboard.value) return;
        const pasted = JSON.parse(JSON.stringify(clipboard.value));
        regenerateIds(pasted);

        if (pasted.elType === 'section') {
            addToHistory();
            content.value.push(pasted);
        } else if (pasted.elType === 'widget') {
            const selected = selectedElementData.value;
            let column = null;
            if (selected?.elType === 'column') {
                column = selected;
            } else if (selected?.elType === 'widget') {
                column = findParent(selected.id);
            }
            if (column?.elements) {
                addToHistory();
                column.elements.push(pasted);
            }
        }

        selectElement(pasted.id, pasted.elType);
        isDirty.value = true;
    }

    function cut() {
        copy();
        if (selectedElement.value) {
            deleteElement(selectedElement.value);
        }
    }

    // Save
    async function save() {
        if (isSaving.value) return;
        isSaving.value = true;

        try {
            const routes = window.builderRoutes || {};
            const response = await fetch(routes.save || `/builder/${documentId.value}/save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    title: documentTitle.value,
                    content: content.value,
                    settings: documentSettings.value
                })
            });

            if (!response.ok) {
                // Check content type to handle HTML vs JSON error responses
                const contentType = response.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    const errorData = await response.json();
                    console.error('Save failed:', errorData);
                    alert(`Save failed: ${errorData.message || errorData.errors ? JSON.stringify(errorData.errors) : 'Unknown error'}`);
                } else {
                    console.error('Save failed: Server returned non-JSON response');
                    alert(`Save failed: Server error (${response.status}). Please check if you're still logged in.`);
                }
                return;
            }

            const data = await response.json();
            if (data.success) {
                // Re-normalize widget types after save to ensure consistency
                normalizeWidgetTypes(content.value);

                isDirty.value = false;
                lastSaved.value = data.saved_at || 'just now';
            } else {
                console.error('Save failed:', data.message);
                alert(`Save failed: ${data.message || 'Unknown error'}`);
            }
        } catch (error) {
            console.error('Save error:', error);
            alert(`Save failed: ${error.message || 'Network error'}`);
        } finally {
            isSaving.value = false;
        }
    }

    async function publish() {
        try {
            await save();

            const routes = window.builderRoutes || {};
            const response = await fetch(routes.publish || `/builder/${documentId.value}/publish`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            const data = await response.json();
            if (data.success && data.url) {
                window.open(data.url, '_blank');
            } else {
                console.error('Publish failed:', data);
                alert(`Publish failed: ${data.message || 'Unknown error'}`);
            }
        } catch (error) {
            console.error('Publish error:', error);
            alert(`Publish failed: ${error.message}`);
        }
    }

    function preview() {
        const routes = window.builderRoutes || {};
        if (routes.preview) {
            window.open(routes.preview, '_blank');
        } else {
            console.error('Preview route not configured');
        }
    }

    // Media Library
    async function openMediaLibrary(controlName) {
        mediaControlName.value = controlName;
        showMediaLibrary.value = true;
        mediaLoading.value = true;

        try {
            const routes = window.builderRoutes || {};
            const response = await fetch(routes.media || '/api/media', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            const data = await response.json();
            mediaItems.value = data.data || data || [];
        } catch (error) {
            console.error('Failed to load media:', error);
            mediaItems.value = [];
        } finally {
            mediaLoading.value = false;
        }
    }

    function selectMediaItem(url) {
        if (mediaControlName.value) {
            updateSetting(mediaControlName.value, url);
        }
        showMediaLibrary.value = false;
        mediaControlName.value = null;
    }

    return {
        // State
        documentId,
        documentTitle,
        documentSettings,
        content,
        selectedElement,
        selectedType,
        activeTab,
        leftPanel,
        previewMode,
        hoverState,
        responsiveDevice,
        history,
        historyIndex,
        clipboard,
        isDirty,
        isSaving,
        lastSaved,
        showMediaLibrary,
        showDeleteConfirm,
        showLayoutPicker,
        layoutPickerPosition,
        mediaControlName,
        mediaItems,
        mediaLoading,
        widgetSearch,

        // Computed
        filteredWidgets,
        selectedElementData,
        currentControls,

        // Actions
        init,
        addSection,
        addSectionAtIndex,
        moveSectionToIndex,
        addWidget,
        addWidgetAtIndex,
        moveWidget,
        clickAddWidget,
        selectElement,
        clearSelection,
        getSetting,
        updateSetting,
        deleteElement,
        duplicateElement,
        findElement,
        findParent,
        addToHistory,
        undo,
        redo,
        copy,
        paste,
        cut,
        save,
        publish,
        preview,
        openMediaLibrary,
        selectMediaItem,
        generateId
    };
});
