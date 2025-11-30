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
            { name: 'structure', type: 'select', label: 'Structure', options: {
                '100': '1 Column', '50-50': '2 Columns', '33-33-33': '3 Columns'
            }},
            { name: 'content_width', type: 'select', label: 'Content Width', options: { boxed: 'Boxed', full: 'Full Width' } },
            { name: 'min_height', type: 'slider', label: 'Min Height', min: 0, max: 1000, unit: 'px' }
        ],
        style: [
            { name: 'background_color', type: 'color', label: 'Background Color' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' },
            { name: 'css_id', type: 'text', label: 'CSS ID' },
            { name: 'motion_effects', type: 'motion_effects', label: 'Motion Effects' }
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

    function getColumnControls() {
        return columnControls;
    }

    // Actions
    function init(pageData) {
        documentId.value = pageData.id;
        documentTitle.value = pageData.title || '';
        documentSettings.value = pageData.settings || {};

        let loadedContent = pageData.content || pageData.elements || [];

        if (Array.isArray(loadedContent) && loadedContent.length > 0) {
            if (loadedContent[0]?.elType === 'section') {
                content.value = loadedContent;
            } else {
                content.value = [{
                    id: generateId(),
                    elType: 'section',
                    settings: {},
                    elements: [{
                        id: generateId(),
                        elType: 'column',
                        settings: { _column_size: 100 },
                        elements: loadedContent.map(el => ({
                            id: el.id || generateId(),
                            elType: 'widget',
                            widgetType: el.type || el.widgetType,
                            settings: el.settings || {}
                        }))
                    }]
                }];
            }
        } else {
            content.value = [];
        }

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

        const section = {
            id: generateId(),
            elType: 'section',
            settings: { structure: layout, content_width: 'boxed' },
            elements: columns
        };

        addToHistory();
        content.value.push(section);
        selectElement(section.id, 'section');
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

    function clickAddWidget(widgetType) {
        let column = null;

        if (selectedType.value === 'column') {
            column = findElement(selectedElement.value);
        } else if (selectedType.value === 'widget') {
            column = findParent(selectedElement.value);
        } else if (content.value.length > 0 && content.value[0].elements?.length > 0) {
            column = content.value[0].elements[0];
        }

        if (!column) {
            addSection('100');
            if (content.value.length > 0) {
                column = content.value[content.value.length - 1].elements[0];
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
        if (!el) return;

        if (!el.settings) el.settings = {};

        // Update hover state settings
        if (hoverState.value === 'hover') {
            if (!el.hover_settings) el.hover_settings = {};
            el.hover_settings[name] = value;
        }
        // Update responsive device settings
        else if (responsiveDevice.value !== 'desktop') {
            const deviceKey = `${name}_${responsiveDevice.value}`;
            el.settings[deviceKey] = value;
        }
        // Update normal desktop settings
        else {
            el.settings[name] = value;
        }

        // Update settings hash to trigger reactivity
        el.settingsHash = Date.now();

        isDirty.value = true;
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    title: documentTitle.value,
                    content: content.value,
                    settings: documentSettings.value
                })
            });

            if (!response.ok) {
                const errorData = await response.json();
                console.error('Save failed with status:', response.status, errorData);
                alert(`Save failed: ${errorData.message || 'Unknown error'}`);
                return;
            }

            const data = await response.json();
            if (data.success) {
                isDirty.value = false;
                lastSaved.value = data.saved_at || 'just now';
                console.log('Save successful');
            } else {
                console.error('Save failed:', data.message || 'Unknown error');
                alert(`Save failed: ${data.message || 'Unknown error'}`);
            }
        } catch (error) {
            console.error('Save failed with error:', error);
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            const data = await response.json();
            if (data.success && data.url) {
                window.open(data.url, '_blank');
            }
        } catch (error) {
            console.error('Publish failed:', error);
        }
    }

    function preview() {
        const routes = window.builderRoutes || {};
        if (routes.preview) {
            window.open(routes.preview, '_blank');
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
        addWidget,
        clickAddWidget,
        selectElement,
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
