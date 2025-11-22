# Drag-and-Drop Builder

## Overview

The drag-and-drop builder is the core feature of the Landing Page Builder SaaS. It provides a visual interface for users to create landing pages by dragging elements onto a canvas and customizing them.

## Technology Stack

- **Laravel Blade** - Templating engine
- **AlpineJS** - Reactive JavaScript
- **SortableJS** - Drag-and-drop library
- **TailwindCSS v4** - Styling
- **Vanilla JS fetch()** - API calls

**FORBIDDEN**: React, Vue, TypeScript, or any JS framework

---

## SortableJS + AlpineJS Integration

### Installation

```bash
npm install sortablejs
```

Include in your main layout:

```blade
{{-- resources/views/layouts/builder.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Page Builder</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div x-data="builderApp()" x-init="init()">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
```

### Core Builder Alpine Component

```blade
{{-- resources/views/builder/index.blade.php --}}
@extends('layouts.builder')

@section('content')
<div class="h-screen flex flex-col">
    {{-- Toolbar --}}
    @include('builder.partials.toolbar')

    {{-- Main Builder Area --}}
    <div class="flex flex-1 overflow-hidden">
        {{-- Element Palette --}}
        @include('builder.partials.palette')

        {{-- Canvas --}}
        @include('builder.partials.canvas')

        {{-- Properties Panel --}}
        @include('builder.partials.properties-panel')
    </div>
</div>
@endsection

@push('scripts')
<script>
function builderApp() {
    return {
        // Core state
        pageId: '{{ $page->id }}',
        elements: @json($page->elements ?? []),
        selectedElement: null,
        isDirty: false,

        // History for undo/redo
        history: [],
        historyIndex: -1,

        // Clipboard
        clipboard: null,

        // Preview mode
        previewMode: 'desktop',

        // Sortable instances
        canvasSortable: null,
        paletteSortable: null,

        // Initialize
        init() {
            this.initSortables();
            this.initKeyboardShortcuts();
            this.initAutoSave();
            this.addToHistory();
        },

        // Initialize SortableJS
        initSortables() {
            // Palette sortable (clone elements)
            this.paletteSortable = new Sortable(this.$refs.palette, {
                group: {
                    name: 'builder',
                    pull: 'clone',
                    put: false
                },
                sort: false,
                animation: 200,
                ghostClass: 'opacity-40',
                onClone: (evt) => {
                    const type = evt.item.dataset.type;
                    evt.item.dataset.element = JSON.stringify(this.createNewElement(type));
                }
            });

            // Canvas sortable
            this.canvasSortable = new Sortable(this.$refs.canvas, {
                group: {
                    name: 'builder',
                    pull: true,
                    put: true
                },
                animation: 200,
                handle: '.drag-handle',
                ghostClass: 'opacity-40',
                chosenClass: 'ring-2 ring-blue-500',
                onAdd: (evt) => {
                    this.addToHistory();
                    const elementData = evt.item.dataset.element;
                    if (elementData) {
                        const element = JSON.parse(elementData);
                        this.elements.splice(evt.newIndex, 0, element);
                        evt.item.remove();
                        this.isDirty = true;
                        this.$nextTick(() => this.reinitCanvasSortable());
                    }
                },
                onSort: (evt) => {
                    this.addToHistory();
                    this.reorderElements(evt.oldIndex, evt.newIndex);
                    this.isDirty = true;
                }
            });
        },

        reinitCanvasSortable() {
            if (this.canvasSortable) {
                this.canvasSortable.destroy();
            }
            this.$nextTick(() => {
                this.canvasSortable = new Sortable(this.$refs.canvas, {
                    group: { name: 'builder', pull: true, put: true },
                    animation: 200,
                    handle: '.drag-handle',
                    ghostClass: 'opacity-40',
                    onAdd: (evt) => {
                        this.addToHistory();
                        const elementData = evt.item.dataset.element;
                        if (elementData) {
                            const element = JSON.parse(elementData);
                            this.elements.splice(evt.newIndex, 0, element);
                            evt.item.remove();
                            this.isDirty = true;
                            this.$nextTick(() => this.reinitCanvasSortable());
                        }
                    },
                    onSort: (evt) => {
                        this.addToHistory();
                        this.reorderElements(evt.oldIndex, evt.newIndex);
                        this.isDirty = true;
                    }
                });
            });
        },

        // Create new element with default props
        createNewElement(type) {
            const id = this.generateId();
            const defaults = {
                section: {
                    id, type: 'section', label: 'Section',
                    props: { backgroundColor: '#ffffff', minHeight: '200px', fullWidth: false },
                    children: [], styles: { padding: '40px 20px' }
                },
                heading: {
                    id, type: 'heading', label: 'Heading',
                    props: { text: 'Heading Text', level: 'h2', color: '#000000', fontSize: '32px', fontWeight: '700' },
                    styles: { margin: '0 0 16px 0' }
                },
                paragraph: {
                    id, type: 'paragraph', label: 'Paragraph',
                    props: { text: 'Enter your text here. Click to edit this paragraph.', color: '#333333', fontSize: '16px', lineHeight: '1.6' },
                    styles: { margin: '0 0 16px 0' }
                },
                image: {
                    id, type: 'image', label: 'Image',
                    props: { src: '/placeholder-image.jpg', alt: 'Image description', width: '100%', objectFit: 'cover' },
                    styles: {}
                },
                button: {
                    id, type: 'button', label: 'Button',
                    props: { text: 'Click Me', variant: 'primary', size: 'md', backgroundColor: '#3b82f6', textColor: '#ffffff', borderRadius: '6px' },
                    styles: {}
                },
                video: {
                    id, type: 'video', label: 'Video',
                    props: { src: '', provider: 'youtube', autoplay: false, muted: false, loop: false, controls: true, aspectRatio: '16:9' },
                    styles: {}
                },
                form: {
                    id, type: 'form', label: 'Form',
                    props: {
                        fields: [
                            { id: '1', type: 'text', label: 'Name', placeholder: 'Your name', required: true },
                            { id: '2', type: 'email', label: 'Email', placeholder: 'your@email.com', required: true }
                        ],
                        submitText: 'Submit',
                        submitAction: '/api/forms/submit',
                        successMessage: 'Thank you!',
                        buttonColor: '#3b82f6',
                        buttonTextColor: '#ffffff'
                    },
                    styles: {}
                },
                columns: {
                    id, type: 'columns', label: 'Columns',
                    props: { columns: 2, gap: '24px', layout: '1:1' },
                    children: [
                        { id: this.generateId(), elements: [] },
                        { id: this.generateId(), elements: [] }
                    ],
                    styles: {}
                }
            };
            return defaults[type] || defaults.paragraph;
        },

        generateId() {
            return 'el_' + Math.random().toString(36).substr(2, 9);
        },

        reorderElements(oldIndex, newIndex) {
            const element = this.elements.splice(oldIndex, 1)[0];
            this.elements.splice(newIndex, 0, element);
        },

        // Element selection
        selectElement(id) {
            this.selectedElement = id;
        },

        deselectElement() {
            this.selectedElement = null;
        },

        getSelectedElement() {
            return this.findElementById(this.elements, this.selectedElement);
        },

        findElementById(elements, id) {
            for (const el of elements) {
                if (el.id === id) return el;
                if (el.children) {
                    if (el.type === 'columns') {
                        for (const col of el.children) {
                            const found = this.findElementById(col.elements || [], id);
                            if (found) return found;
                        }
                    } else {
                        const found = this.findElementById(el.children, id);
                        if (found) return found;
                    }
                }
            }
            return null;
        },

        // Update element properties
        updateElementProps(id, newProps) {
            this.addToHistory();
            this.updateElementInTree(this.elements, id, (el) => {
                el.props = { ...el.props, ...newProps };
            });
            this.isDirty = true;
        },

        updateElementStyles(id, newStyles) {
            this.addToHistory();
            this.updateElementInTree(this.elements, id, (el) => {
                el.styles = { ...el.styles, ...newStyles };
            });
            this.isDirty = true;
        },

        updateElementInTree(elements, id, updater) {
            for (let i = 0; i < elements.length; i++) {
                if (elements[i].id === id) {
                    updater(elements[i]);
                    return true;
                }
                if (elements[i].children) {
                    if (elements[i].type === 'columns') {
                        for (const col of elements[i].children) {
                            if (this.updateElementInTree(col.elements || [], id, updater)) return true;
                        }
                    } else {
                        if (this.updateElementInTree(elements[i].children, id, updater)) return true;
                    }
                }
            }
            return false;
        },

        // Delete element
        deleteElement(id) {
            this.addToHistory();
            this.removeElementFromTree(this.elements, id);
            if (this.selectedElement === id) {
                this.selectedElement = null;
            }
            this.isDirty = true;
        },

        removeElementFromTree(elements, id) {
            for (let i = 0; i < elements.length; i++) {
                if (elements[i].id === id) {
                    elements.splice(i, 1);
                    return true;
                }
                if (elements[i].children) {
                    if (elements[i].type === 'columns') {
                        for (const col of elements[i].children) {
                            if (this.removeElementFromTree(col.elements || [], id)) return true;
                        }
                    } else {
                        if (this.removeElementFromTree(elements[i].children, id)) return true;
                    }
                }
            }
            return false;
        },

        // Duplicate element
        duplicateElement(id) {
            const element = this.findElementById(this.elements, id);
            if (!element) return;

            this.addToHistory();
            const duplicated = this.deepCloneWithNewIds(element);

            const index = this.elements.findIndex(el => el.id === id);
            if (index !== -1) {
                this.elements.splice(index + 1, 0, duplicated);
            }

            this.selectedElement = duplicated.id;
            this.isDirty = true;
        },

        deepCloneWithNewIds(element) {
            const cloned = JSON.parse(JSON.stringify(element));
            const assignNewIds = (el) => {
                el.id = this.generateId();
                if (el.children) {
                    if (Array.isArray(el.children)) {
                        el.children.forEach(child => {
                            if (child.elements) {
                                child.id = this.generateId();
                                child.elements.forEach(assignNewIds);
                            } else {
                                assignNewIds(child);
                            }
                        });
                    }
                }
            };
            assignNewIds(cloned);
            return cloned;
        },

        // History (Undo/Redo)
        addToHistory() {
            const state = JSON.stringify({
                elements: this.elements,
                selectedElement: this.selectedElement
            });

            this.history = this.history.slice(0, this.historyIndex + 1);
            this.history.push(state);

            if (this.history.length > 50) {
                this.history.shift();
            }

            this.historyIndex = this.history.length - 1;
        },

        undo() {
            if (this.historyIndex <= 0) return;

            this.historyIndex--;
            const state = JSON.parse(this.history[this.historyIndex]);
            this.elements = state.elements;
            this.selectedElement = state.selectedElement;
            this.isDirty = true;
            this.$nextTick(() => this.reinitCanvasSortable());
        },

        redo() {
            if (this.historyIndex >= this.history.length - 1) return;

            this.historyIndex++;
            const state = JSON.parse(this.history[this.historyIndex]);
            this.elements = state.elements;
            this.selectedElement = state.selectedElement;
            this.isDirty = true;
            this.$nextTick(() => this.reinitCanvasSortable());
        },

        // Clipboard (Copy/Paste/Cut)
        copy() {
            if (!this.selectedElement) return;
            const element = this.findElementById(this.elements, this.selectedElement);
            if (element) {
                this.clipboard = JSON.parse(JSON.stringify(element));
            }
        },

        paste() {
            if (!this.clipboard) return;

            this.addToHistory();
            const pasted = this.deepCloneWithNewIds(this.clipboard);

            if (this.selectedElement) {
                const index = this.elements.findIndex(el => el.id === this.selectedElement);
                if (index !== -1) {
                    this.elements.splice(index + 1, 0, pasted);
                } else {
                    this.elements.push(pasted);
                }
            } else {
                this.elements.push(pasted);
            }

            this.selectedElement = pasted.id;
            this.isDirty = true;
            this.$nextTick(() => this.reinitCanvasSortable());
        },

        cut() {
            if (!this.selectedElement) return;
            this.copy();
            this.deleteElement(this.selectedElement);
        },

        // Keyboard shortcuts
        initKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                const isCtrlOrCmd = e.ctrlKey || e.metaKey;

                // Undo
                if (isCtrlOrCmd && e.key === 'z' && !e.shiftKey) {
                    e.preventDefault();
                    this.undo();
                }

                // Redo
                if ((isCtrlOrCmd && e.shiftKey && e.key === 'z') || (isCtrlOrCmd && e.key === 'y')) {
                    e.preventDefault();
                    this.redo();
                }

                // Copy
                if (isCtrlOrCmd && e.key === 'c') {
                    if (this.selectedElement) {
                        e.preventDefault();
                        this.copy();
                    }
                }

                // Paste
                if (isCtrlOrCmd && e.key === 'v') {
                    if (this.clipboard) {
                        e.preventDefault();
                        this.paste();
                    }
                }

                // Cut
                if (isCtrlOrCmd && e.key === 'x') {
                    if (this.selectedElement) {
                        e.preventDefault();
                        this.cut();
                    }
                }

                // Delete
                if ((e.key === 'Delete' || e.key === 'Backspace') && this.selectedElement) {
                    const target = e.target;
                    if (target.tagName !== 'INPUT' && target.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                        this.deleteElement(this.selectedElement);
                    }
                }

                // Save
                if (isCtrlOrCmd && e.key === 's') {
                    e.preventDefault();
                    this.savePage();
                }

                // Escape
                if (e.key === 'Escape') {
                    this.deselectElement();
                }
            });
        },

        // Auto-save
        initAutoSave() {
            let saveTimeout = null;

            this.$watch('elements', () => {
                if (saveTimeout) clearTimeout(saveTimeout);
                saveTimeout = setTimeout(() => {
                    if (this.isDirty) {
                        this.savePage();
                    }
                }, 30000);
            }, { deep: true });

            // Warn before leaving with unsaved changes
            window.addEventListener('beforeunload', (e) => {
                if (this.isDirty) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes.';
                    return e.returnValue;
                }
            });
        },

        // Save page
        async savePage() {
            try {
                const response = await fetch(`/api/pages/${this.pageId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ elements: this.elements })
                });

                if (response.ok) {
                    this.isDirty = false;
                    console.log('Saved at', new Date().toLocaleTimeString());
                } else {
                    console.error('Save failed');
                }
            } catch (error) {
                console.error('Save error:', error);
            }
        },

        // Preview mode
        setPreviewMode(mode) {
            this.previewMode = mode;
        },

        getPreviewWidth() {
            const widths = {
                desktop: '100%',
                tablet: '768px',
                mobile: '375px'
            };
            return widths[this.previewMode];
        }
    };
}
</script>
@endpush
```

---

## Builder Elements as Blade Components

### Element Palette

```blade
{{-- resources/views/builder/partials/palette.blade.php --}}
<div class="w-64 bg-white border-r border-gray-200 p-4 overflow-y-auto">
    <h3 class="text-sm font-semibold text-gray-700 mb-4">Elements</h3>

    <div x-ref="palette" class="grid grid-cols-2 gap-2">
        @foreach(['section', 'heading', 'paragraph', 'image', 'button', 'video', 'form', 'columns'] as $type)
        <div
            data-type="{{ $type }}"
            class="flex flex-col items-center p-3 border border-gray-200 rounded-lg cursor-grab hover:border-blue-500 hover:bg-blue-50 transition-colors"
        >
            <x-builder.icons :type="$type" class="w-6 h-6 text-gray-500 mb-1" />
            <span class="text-xs text-gray-600 capitalize">{{ $type }}</span>
        </div>
        @endforeach
    </div>
</div>
```

### Canvas

```blade
{{-- resources/views/builder/partials/canvas.blade.php --}}
<div class="flex-1 overflow-auto p-6 bg-gray-100" @click.self="deselectElement()">
    <div
        class="mx-auto bg-white shadow-lg min-h-[600px] transition-all duration-300"
        :style="{ width: getPreviewWidth() }"
    >
        <div
            x-ref="canvas"
            class="min-h-[400px] p-5"
        >
            <template x-for="element in elements" :key="element.id">
                <div
                    class="relative group my-1"
                    :class="{ 'ring-2 ring-blue-500 ring-offset-2': selectedElement === element.id }"
                    @click.stop="selectElement(element.id)"
                >
                    {{-- Element toolbar --}}
                    <div
                        class="absolute -top-8 left-0 flex items-center gap-2 px-2 py-1 bg-blue-500 text-white text-xs rounded-t"
                        x-show="selectedElement === element.id"
                    >
                        <span class="drag-handle cursor-grab">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
                            </svg>
                        </span>
                        <span x-text="element.label"></span>
                    </div>

                    {{-- Element content --}}
                    <div x-html="renderElement(element)"></div>
                </div>
            </template>

            {{-- Empty state --}}
            <div
                x-show="elements.length === 0"
                class="flex items-center justify-center h-64 border-2 border-dashed border-gray-300 rounded-lg text-gray-400"
            >
                Drag elements here to start building
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Add to builderApp()
function builderApp() {
    return {
        // ... previous code ...

        // Render element to HTML
        renderElement(element) {
            switch (element.type) {
                case 'heading':
                    return this.renderHeading(element);
                case 'paragraph':
                    return this.renderParagraph(element);
                case 'image':
                    return this.renderImage(element);
                case 'button':
                    return this.renderButton(element);
                case 'video':
                    return this.renderVideo(element);
                case 'section':
                    return this.renderSection(element);
                case 'form':
                    return this.renderForm(element);
                case 'columns':
                    return this.renderColumns(element);
                default:
                    return `<div>Unknown element: ${element.type}</div>`;
            }
        },

        renderHeading(el) {
            const tag = el.props.level || 'h2';
            const style = `
                color: ${el.props.color || '#000'};
                font-size: ${el.props.fontSize || '32px'};
                font-weight: ${el.props.fontWeight || '700'};
                ${el.props.fontFamily ? `font-family: ${el.props.fontFamily};` : ''}
                ${el.styles?.margin ? `margin: ${el.styles.margin};` : ''}
                ${el.styles?.textAlign ? `text-align: ${el.styles.textAlign};` : ''}
            `;
            return `<${tag} style="${style}">${this.escapeHtml(el.props.text)}</${tag}>`;
        },

        renderParagraph(el) {
            const style = `
                color: ${el.props.color || '#333'};
                font-size: ${el.props.fontSize || '16px'};
                line-height: ${el.props.lineHeight || '1.6'};
                ${el.props.fontFamily ? `font-family: ${el.props.fontFamily};` : ''}
                ${el.styles?.margin ? `margin: ${el.styles.margin};` : ''}
            `;
            return `<p style="${style}">${this.escapeHtml(el.props.text)}</p>`;
        },

        renderImage(el) {
            const style = `
                width: ${el.props.width || '100%'};
                ${el.props.height ? `height: ${el.props.height};` : ''}
                object-fit: ${el.props.objectFit || 'cover'};
                ${el.styles?.borderRadius ? `border-radius: ${el.styles.borderRadius};` : ''}
            `;
            const img = `<img src="${this.escapeHtml(el.props.src)}" alt="${this.escapeHtml(el.props.alt)}" style="${style}" loading="lazy">`;

            if (el.props.link) {
                return `<a href="${this.escapeHtml(el.props.link)}" target="${el.props.linkTarget || '_self'}">${img}</a>`;
            }
            return img;
        },

        renderButton(el) {
            const sizes = {
                sm: 'padding: 8px 16px; font-size: 14px;',
                md: 'padding: 12px 24px; font-size: 16px;',
                lg: 'padding: 16px 32px; font-size: 18px;'
            };

            let style = `
                background-color: ${el.props.backgroundColor || '#3b82f6'};
                color: ${el.props.textColor || '#fff'};
                border-radius: ${el.props.borderRadius || '6px'};
                ${el.props.fullWidth ? 'width: 100%;' : ''}
                ${sizes[el.props.size || 'md']}
                border: none;
                cursor: pointer;
                display: inline-block;
                text-decoration: none;
                text-align: center;
                font-weight: 600;
            `;

            if (el.props.variant === 'outline') {
                style += `
                    background-color: transparent;
                    color: ${el.props.backgroundColor || '#3b82f6'};
                    border: 2px solid ${el.props.backgroundColor || '#3b82f6'};
                `;
            }

            if (el.props.link) {
                return `<a href="${this.escapeHtml(el.props.link)}" target="${el.props.linkTarget || '_self'}" style="${style}">${this.escapeHtml(el.props.text)}</a>`;
            }
            return `<button style="${style}">${this.escapeHtml(el.props.text)}</button>`;
        },

        renderVideo(el) {
            const ratios = { '16:9': '56.25%', '4:3': '75%', '1:1': '100%' };
            const containerStyle = `
                position: relative;
                padding-bottom: ${ratios[el.props.aspectRatio || '16:9']};
                height: 0;
                overflow: hidden;
            `;
            const mediaStyle = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%;';

            if (el.props.provider === 'youtube') {
                const match = el.props.src.match(/(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/watch\?.+&v=))([^&?]+)/);
                if (match) {
                    const params = new URLSearchParams();
                    if (el.props.autoplay) params.set('autoplay', '1');
                    if (el.props.muted) params.set('mute', '1');
                    return `<div style="${containerStyle}">
                        <iframe src="https://www.youtube.com/embed/${match[1]}?${params}" style="${mediaStyle}" frameborder="0" allowfullscreen></iframe>
                    </div>`;
                }
            }

            return `<div style="${containerStyle}">
                <video src="${this.escapeHtml(el.props.src)}" style="${mediaStyle}" ${el.props.controls ? 'controls' : ''}></video>
            </div>`;
        },

        renderSection(el) {
            const style = `
                background-color: ${el.props.backgroundColor || '#fff'};
                ${el.props.backgroundImage ? `background-image: url(${el.props.backgroundImage});` : ''}
                ${el.props.backgroundSize ? `background-size: ${el.props.backgroundSize};` : ''}
                min-height: ${el.props.minHeight || 'auto'};
                ${el.styles?.padding ? `padding: ${el.styles.padding};` : 'padding: 40px 20px;'}
            `;

            const children = (el.children || []).map(child => this.renderElement(child)).join('');
            return `<section style="${style}">${children || '<div class="border-2 border-dashed border-gray-300 p-8 text-center text-gray-400">Drop elements here</div>'}</section>`;
        },

        renderForm(el) {
            const fields = el.props.fields.map(field => {
                const required = field.required ? 'required' : '';
                let input = '';

                switch (field.type) {
                    case 'textarea':
                        input = `<textarea name="${field.id}" placeholder="${this.escapeHtml(field.placeholder || '')}" ${required} rows="4" class="w-full p-2 border rounded"></textarea>`;
                        break;
                    case 'select':
                        const options = (field.options || []).map(opt => `<option value="${this.escapeHtml(opt)}">${this.escapeHtml(opt)}</option>`).join('');
                        input = `<select name="${field.id}" ${required} class="w-full p-2 border rounded"><option value="">${this.escapeHtml(field.placeholder || 'Select...')}</option>${options}</select>`;
                        break;
                    default:
                        input = `<input type="${field.type}" name="${field.id}" placeholder="${this.escapeHtml(field.placeholder || '')}" ${required} class="w-full p-2 border rounded">`;
                }

                return `<div class="mb-4">
                    <label class="block mb-1 font-medium">${this.escapeHtml(field.label)}${field.required ? '<span class="text-red-500">*</span>' : ''}</label>
                    ${input}
                </div>`;
            }).join('');

            return `<form>
                ${fields}
                <button type="submit" style="background-color: ${el.props.buttonColor}; color: ${el.props.buttonTextColor};" class="px-6 py-3 rounded font-semibold">
                    ${this.escapeHtml(el.props.submitText)}
                </button>
            </form>`;
        },

        renderColumns(el) {
            const layout = el.props.layout || '1:1';
            const ratios = layout.split(':').map(Number);
            const total = ratios.reduce((a, b) => a + b, 0);
            const gridTemplate = ratios.map(r => `${(r / total) * 100}%`).join(' ');

            const columns = el.children.map((col, i) => {
                const content = col.elements.map(child => this.renderElement(child)).join('');
                return `<div class="column">${content || `<div class="border-2 border-dashed border-gray-300 p-4 text-center text-gray-400">Column ${i + 1}</div>`}</div>`;
            }).join('');

            return `<div style="display: grid; grid-template-columns: ${gridTemplate}; gap: ${el.props.gap || '24px'};">${columns}</div>`;
        },

        escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    };
}
</script>
@endpush
```

---

## Element Editing with Alpine x-data

### Properties Panel

```blade
{{-- resources/views/builder/partials/properties-panel.blade.php --}}
<div class="w-80 bg-white border-l border-gray-200 overflow-y-auto">
    <template x-if="selectedElement">
        <div>
            {{-- Panel Header --}}
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-700" x-text="getSelectedElement()?.label + ' Properties'"></h3>
                <div class="flex gap-2">
                    <button
                        @click="duplicateElement(selectedElement)"
                        class="p-1 text-gray-500 hover:text-blue-500"
                        title="Duplicate"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                    <button
                        @click="deleteElement(selectedElement)"
                        class="p-1 text-gray-500 hover:text-red-500"
                        title="Delete"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Properties Content --}}
            <div class="p-4">
                {{-- Heading Properties --}}
                <template x-if="getSelectedElement()?.type === 'heading'">
                    <div x-data="{ el: getSelectedElement() }">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Text</label>
                            <textarea
                                x-model="el.props.text"
                                @input="updateElementProps(el.id, { text: el.props.text })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                                rows="2"
                            ></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                            <select
                                x-model="el.props.level"
                                @change="updateElementProps(el.id, { level: el.props.level })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                            >
                                <option value="h1">H1</option>
                                <option value="h2">H2</option>
                                <option value="h3">H3</option>
                                <option value="h4">H4</option>
                                <option value="h5">H5</option>
                                <option value="h6">H6</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                            <input
                                type="color"
                                x-model="el.props.color"
                                @input="updateElementProps(el.id, { color: el.props.color })"
                                class="w-full h-10 p-1 border border-gray-300 rounded"
                            >
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Font Size</label>
                            <input
                                type="text"
                                x-model="el.props.fontSize"
                                @input="updateElementProps(el.id, { fontSize: el.props.fontSize })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                                placeholder="32px"
                            >
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Font Weight</label>
                            <select
                                x-model="el.props.fontWeight"
                                @change="updateElementProps(el.id, { fontWeight: el.props.fontWeight })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                            >
                                <option value="400">Normal</option>
                                <option value="500">Medium</option>
                                <option value="600">Semibold</option>
                                <option value="700">Bold</option>
                            </select>
                        </div>
                    </div>
                </template>

                {{-- Paragraph Properties --}}
                <template x-if="getSelectedElement()?.type === 'paragraph'">
                    <div x-data="{ el: getSelectedElement() }">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Text</label>
                            <textarea
                                x-model="el.props.text"
                                @input="updateElementProps(el.id, { text: el.props.text })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                                rows="4"
                            ></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                            <input
                                type="color"
                                x-model="el.props.color"
                                @input="updateElementProps(el.id, { color: el.props.color })"
                                class="w-full h-10 p-1 border border-gray-300 rounded"
                            >
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Font Size</label>
                            <input
                                type="text"
                                x-model="el.props.fontSize"
                                @input="updateElementProps(el.id, { fontSize: el.props.fontSize })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                                placeholder="16px"
                            >
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Line Height</label>
                            <input
                                type="text"
                                x-model="el.props.lineHeight"
                                @input="updateElementProps(el.id, { lineHeight: el.props.lineHeight })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                                placeholder="1.6"
                            >
                        </div>
                    </div>
                </template>

                {{-- Image Properties --}}
                <template x-if="getSelectedElement()?.type === 'image'">
                    <div x-data="{ el: getSelectedElement() }">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                            <input
                                type="text"
                                x-model="el.props.src"
                                @input="updateElementProps(el.id, { src: el.props.src })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                                placeholder="https://..."
                            >
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alt Text</label>
                            <input
                                type="text"
                                x-model="el.props.alt"
                                @input="updateElementProps(el.id, { alt: el.props.alt })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                            >
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Width</label>
                            <input
                                type="text"
                                x-model="el.props.width"
                                @input="updateElementProps(el.id, { width: el.props.width })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                                placeholder="100%"
                            >
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Object Fit</label>
                            <select
                                x-model="el.props.objectFit"
                                @change="updateElementProps(el.id, { objectFit: el.props.objectFit })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                            >
                                <option value="cover">Cover</option>
                                <option value="contain">Contain</option>
                                <option value="fill">Fill</option>
                                <option value="none">None</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Link URL</label>
                            <input
                                type="text"
                                x-model="el.props.link"
                                @input="updateElementProps(el.id, { link: el.props.link })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                                placeholder="https://..."
                            >
                        </div>
                    </div>
                </template>

                {{-- Button Properties --}}
                <template x-if="getSelectedElement()?.type === 'button'">
                    <div x-data="{ el: getSelectedElement() }">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Text</label>
                            <input
                                type="text"
                                x-model="el.props.text"
                                @input="updateElementProps(el.id, { text: el.props.text })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                            >
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Link URL</label>
                            <input
                                type="text"
                                x-model="el.props.link"
                                @input="updateElementProps(el.id, { link: el.props.link })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                            >
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Variant</label>
                            <select
                                x-model="el.props.variant"
                                @change="updateElementProps(el.id, { variant: el.props.variant })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                            >
                                <option value="primary">Primary</option>
                                <option value="secondary">Secondary</option>
                                <option value="outline">Outline</option>
                                <option value="ghost">Ghost</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Size</label>
                            <select
                                x-model="el.props.size"
                                @change="updateElementProps(el.id, { size: el.props.size })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                            >
                                <option value="sm">Small</option>
                                <option value="md">Medium</option>
                                <option value="lg">Large</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Background Color</label>
                            <input
                                type="color"
                                x-model="el.props.backgroundColor"
                                @input="updateElementProps(el.id, { backgroundColor: el.props.backgroundColor })"
                                class="w-full h-10 p-1 border border-gray-300 rounded"
                            >
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Text Color</label>
                            <input
                                type="color"
                                x-model="el.props.textColor"
                                @input="updateElementProps(el.id, { textColor: el.props.textColor })"
                                class="w-full h-10 p-1 border border-gray-300 rounded"
                            >
                        </div>
                        <div class="mb-4">
                            <label class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    x-model="el.props.fullWidth"
                                    @change="updateElementProps(el.id, { fullWidth: el.props.fullWidth })"
                                    class="rounded border-gray-300"
                                >
                                <span class="text-sm text-gray-700">Full Width</span>
                            </label>
                        </div>
                    </div>
                </template>

                {{-- Section Properties --}}
                <template x-if="getSelectedElement()?.type === 'section'">
                    <div x-data="{ el: getSelectedElement() }">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Background Color</label>
                            <input
                                type="color"
                                x-model="el.props.backgroundColor"
                                @input="updateElementProps(el.id, { backgroundColor: el.props.backgroundColor })"
                                class="w-full h-10 p-1 border border-gray-300 rounded"
                            >
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Background Image URL</label>
                            <input
                                type="text"
                                x-model="el.props.backgroundImage"
                                @input="updateElementProps(el.id, { backgroundImage: el.props.backgroundImage })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                            >
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Height</label>
                            <input
                                type="text"
                                x-model="el.props.minHeight"
                                @input="updateElementProps(el.id, { minHeight: el.props.minHeight })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                                placeholder="200px"
                            >
                        </div>
                    </div>
                </template>

                {{-- Styles Editor (common for all) --}}
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Styles</h4>
                    <div x-data="{ el: getSelectedElement() }">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Margin</label>
                            <input
                                type="text"
                                x-model="el.styles.margin"
                                @input="updateElementStyles(el.id, { margin: el.styles.margin })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                                placeholder="10px 20px"
                            >
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Padding</label>
                            <input
                                type="text"
                                x-model="el.styles.padding"
                                @input="updateElementStyles(el.id, { padding: el.styles.padding })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                                placeholder="20px"
                            >
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Border Radius</label>
                            <input
                                type="text"
                                x-model="el.styles.borderRadius"
                                @input="updateElementStyles(el.id, { borderRadius: el.styles.borderRadius })"
                                class="w-full p-2 border border-gray-300 rounded text-sm"
                                placeholder="8px"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Empty state --}}
    <template x-if="!selectedElement">
        <div class="flex items-center justify-center h-full text-gray-400 p-4 text-center">
            Select an element to edit its properties
        </div>
    </template>
</div>
```

---

## Toolbar with Preview Modes

```blade
{{-- resources/views/builder/partials/toolbar.blade.php --}}
<div class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-200">
    {{-- Preview Modes --}}
    <div class="flex gap-1">
        <button
            @click="setPreviewMode('desktop')"
            :class="previewMode === 'desktop' ? 'bg-blue-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'"
            class="p-2 border border-gray-200 rounded transition-colors"
            title="Desktop"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </button>
        <button
            @click="setPreviewMode('tablet')"
            :class="previewMode === 'tablet' ? 'bg-blue-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'"
            class="p-2 border border-gray-200 rounded transition-colors"
            title="Tablet"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
        </button>
        <button
            @click="setPreviewMode('mobile')"
            :class="previewMode === 'mobile' ? 'bg-blue-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'"
            class="p-2 border border-gray-200 rounded transition-colors"
            title="Mobile"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
        </button>
    </div>

    {{-- Page Title --}}
    <div class="text-sm font-medium text-gray-600">
        {{ $page->name }}
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-2">
        <button
            @click="undo()"
            class="p-2 text-gray-600 hover:bg-gray-100 rounded"
            title="Undo (Ctrl+Z)"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
            </svg>
        </button>
        <button
            @click="redo()"
            class="p-2 text-gray-600 hover:bg-gray-100 rounded"
            title="Redo (Ctrl+Y)"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"/>
            </svg>
        </button>
        <div class="w-px h-6 bg-gray-200 mx-2"></div>
        <button
            @click="savePage()"
            class="relative px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors text-sm font-medium"
        >
            Save
            <span
                x-show="isDirty"
                class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"
            ></span>
        </button>
        <a
            href="{{ route('pages.preview', $page) }}"
            target="_blank"
            class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 transition-colors text-sm font-medium"
        >
            Preview
        </a>
    </div>
</div>
```

---

## JSON to HTML Renderer in PHP

```php
<?php
// app/Services/PageRenderer.php

namespace App\Services;

use App\Models\Page;

class PageRenderer
{
    public function render(Page $page): string
    {
        $elementsHtml = $this->renderElements($page->elements ?? []);

        return view('builder.render.page', [
            'page' => $page,
            'content' => $elementsHtml,
        ])->render();
    }

    public function renderElements(array $elements): string
    {
        $html = '';
        foreach ($elements as $element) {
            $html .= $this->renderElement($element);
        }
        return $html;
    }

    public function renderElement(array $element): string
    {
        $type = $element['type'] ?? 'unknown';
        $method = 'render' . ucfirst($type);

        if (method_exists($this, $method)) {
            return $this->$method($element);
        }

        return "<!-- Unknown element type: {$type} -->";
    }

    protected function renderHeading(array $el): string
    {
        $tag = $el['props']['level'] ?? 'h2';
        $text = e($el['props']['text'] ?? '');
        $style = $this->buildStyle([
            'color' => $el['props']['color'] ?? '#000',
            'font-size' => $el['props']['fontSize'] ?? null,
            'font-weight' => $el['props']['fontWeight'] ?? null,
            'font-family' => $el['props']['fontFamily'] ?? null,
            'margin' => $el['styles']['margin'] ?? null,
            'text-align' => $el['styles']['textAlign'] ?? null,
        ]);

        return "<{$tag} style=\"{$style}\">{$text}</{$tag}>";
    }

    protected function renderParagraph(array $el): string
    {
        $text = e($el['props']['text'] ?? '');
        $style = $this->buildStyle([
            'color' => $el['props']['color'] ?? '#333',
            'font-size' => $el['props']['fontSize'] ?? '16px',
            'line-height' => $el['props']['lineHeight'] ?? '1.6',
            'font-family' => $el['props']['fontFamily'] ?? null,
            'margin' => $el['styles']['margin'] ?? null,
        ]);

        return "<p style=\"{$style}\">{$text}</p>";
    }

    protected function renderImage(array $el): string
    {
        $src = e($el['props']['src'] ?? '');
        $alt = e($el['props']['alt'] ?? '');
        $style = $this->buildStyle([
            'width' => $el['props']['width'] ?? '100%',
            'height' => $el['props']['height'] ?? null,
            'object-fit' => $el['props']['objectFit'] ?? 'cover',
            'border-radius' => $el['styles']['borderRadius'] ?? null,
        ]);

        $img = "<img src=\"{$src}\" alt=\"{$alt}\" style=\"{$style}\" loading=\"lazy\">";

        if (!empty($el['props']['link'])) {
            $link = e($el['props']['link']);
            $target = $el['props']['linkTarget'] ?? '_self';
            $rel = $target === '_blank' ? 'rel="noopener noreferrer"' : '';
            return "<a href=\"{$link}\" target=\"{$target}\" {$rel}>{$img}</a>";
        }

        return $img;
    }

    protected function renderButton(array $el): string
    {
        $text = e($el['props']['text'] ?? 'Button');
        $sizes = [
            'sm' => 'padding: 8px 16px; font-size: 14px;',
            'md' => 'padding: 12px 24px; font-size: 16px;',
            'lg' => 'padding: 16px 32px; font-size: 18px;',
        ];

        $bgColor = $el['props']['backgroundColor'] ?? '#3b82f6';
        $textColor = $el['props']['textColor'] ?? '#fff';

        $baseStyle = implode(' ', [
            "background-color: {$bgColor};",
            "color: {$textColor};",
            "border-radius: " . ($el['props']['borderRadius'] ?? '6px') . ";",
            $el['props']['fullWidth'] ?? false ? 'width: 100%;' : '',
            $sizes[$el['props']['size'] ?? 'md'],
            'border: none;',
            'cursor: pointer;',
            'display: inline-block;',
            'text-decoration: none;',
            'text-align: center;',
            'font-weight: 600;',
        ]);

        if (($el['props']['variant'] ?? 'primary') === 'outline') {
            $baseStyle .= " background-color: transparent; color: {$bgColor}; border: 2px solid {$bgColor};";
        }

        if (!empty($el['props']['link'])) {
            $link = e($el['props']['link']);
            $target = $el['props']['linkTarget'] ?? '_self';
            return "<a href=\"{$link}\" target=\"{$target}\" style=\"{$baseStyle}\">{$text}</a>";
        }

        return "<button style=\"{$baseStyle}\">{$text}</button>";
    }

    protected function renderVideo(array $el): string
    {
        $ratios = ['16:9' => '56.25%', '4:3' => '75%', '1:1' => '100%'];
        $ratio = $ratios[$el['props']['aspectRatio'] ?? '16:9'];

        $containerStyle = "position: relative; padding-bottom: {$ratio}; height: 0; overflow: hidden;";
        $mediaStyle = "position: absolute; top: 0; left: 0; width: 100%; height: 100%;";

        $src = $el['props']['src'] ?? '';
        $provider = $el['props']['provider'] ?? 'custom';

        if ($provider === 'youtube') {
            preg_match('/(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/watch\?.+&v=))([^&?]+)/', $src, $matches);
            if (!empty($matches[1])) {
                $videoId = $matches[1];
                $params = [];
                if ($el['props']['autoplay'] ?? false) $params[] = 'autoplay=1';
                if ($el['props']['muted'] ?? false) $params[] = 'mute=1';
                if ($el['props']['loop'] ?? false) $params[] = 'loop=1';
                $query = implode('&', $params);

                return "<div style=\"{$containerStyle}\">
                    <iframe src=\"https://www.youtube.com/embed/{$videoId}?{$query}\" style=\"{$mediaStyle}\" frameborder=\"0\" allowfullscreen></iframe>
                </div>";
            }
        }

        if ($provider === 'vimeo') {
            preg_match('/vimeo\.com\/(\d+)/', $src, $matches);
            if (!empty($matches[1])) {
                $videoId = $matches[1];
                return "<div style=\"{$containerStyle}\">
                    <iframe src=\"https://player.vimeo.com/video/{$videoId}\" style=\"{$mediaStyle}\" frameborder=\"0\" allowfullscreen></iframe>
                </div>";
            }
        }

        $attrs = [];
        if ($el['props']['controls'] ?? true) $attrs[] = 'controls';
        if ($el['props']['autoplay'] ?? false) $attrs[] = 'autoplay';
        if ($el['props']['muted'] ?? false) $attrs[] = 'muted';
        if ($el['props']['loop'] ?? false) $attrs[] = 'loop';

        return "<div style=\"{$containerStyle}\">
            <video src=\"" . e($src) . "\" style=\"{$mediaStyle}\" " . implode(' ', $attrs) . "></video>
        </div>";
    }

    protected function renderSection(array $el): string
    {
        $style = $this->buildStyle([
            'background-color' => $el['props']['backgroundColor'] ?? '#fff',
            'background-image' => isset($el['props']['backgroundImage']) ? "url({$el['props']['backgroundImage']})" : null,
            'background-size' => $el['props']['backgroundSize'] ?? null,
            'min-height' => $el['props']['minHeight'] ?? null,
            'padding' => $el['styles']['padding'] ?? '40px 20px',
        ]);

        $children = $this->renderElements($el['children'] ?? []);

        return "<section style=\"{$style}\">{$children}</section>";
    }

    protected function renderForm(array $el): string
    {
        $action = e($el['props']['submitAction'] ?? '');
        $fields = '';

        foreach ($el['props']['fields'] ?? [] as $field) {
            $label = e($field['label'] ?? '');
            $name = e($field['id'] ?? '');
            $placeholder = e($field['placeholder'] ?? '');
            $required = ($field['required'] ?? false) ? 'required' : '';
            $requiredMark = ($field['required'] ?? false) ? '<span class="text-red-500">*</span>' : '';

            $input = match($field['type'] ?? 'text') {
                'textarea' => "<textarea name=\"{$name}\" placeholder=\"{$placeholder}\" {$required} rows=\"4\" class=\"w-full p-2 border rounded\"></textarea>",
                'select' => $this->renderSelectField($field),
                default => "<input type=\"{$field['type']}\" name=\"{$name}\" placeholder=\"{$placeholder}\" {$required} class=\"w-full p-2 border rounded\">",
            };

            $fields .= "<div class=\"mb-4\">
                <label class=\"block mb-1 font-medium\">{$label}{$requiredMark}</label>
                {$input}
            </div>";
        }

        $buttonStyle = "background-color: " . ($el['props']['buttonColor'] ?? '#3b82f6') . "; color: " . ($el['props']['buttonTextColor'] ?? '#fff') . ";";
        $submitText = e($el['props']['submitText'] ?? 'Submit');

        return "<form action=\"{$action}\" method=\"POST\">
            {$fields}
            <button type=\"submit\" style=\"{$buttonStyle}\" class=\"px-6 py-3 rounded font-semibold\">{$submitText}</button>
        </form>";
    }

    protected function renderSelectField(array $field): string
    {
        $name = e($field['id'] ?? '');
        $placeholder = e($field['placeholder'] ?? 'Select...');
        $required = ($field['required'] ?? false) ? 'required' : '';

        $options = "<option value=\"\">{$placeholder}</option>";
        foreach ($field['options'] ?? [] as $option) {
            $opt = e($option);
            $options .= "<option value=\"{$opt}\">{$opt}</option>";
        }

        return "<select name=\"{$name}\" {$required} class=\"w-full p-2 border rounded\">{$options}</select>";
    }

    protected function renderColumns(array $el): string
    {
        $layout = $el['props']['layout'] ?? '1:1';
        $ratios = array_map('intval', explode(':', $layout));
        $total = array_sum($ratios);
        $gridTemplate = implode(' ', array_map(fn($r) => (($r / $total) * 100) . '%', $ratios));

        $gap = $el['props']['gap'] ?? '24px';
        $style = "display: grid; grid-template-columns: {$gridTemplate}; gap: {$gap};";

        $columns = '';
        foreach ($el['children'] ?? [] as $col) {
            $colContent = $this->renderElements($col['elements'] ?? []);
            $columns .= "<div class=\"column\">{$colContent}</div>";
        }

        return "<div style=\"{$style}\">{$columns}</div>";
    }

    protected function buildStyle(array $properties): string
    {
        $styles = [];
        foreach ($properties as $property => $value) {
            if ($value !== null && $value !== '') {
                $styles[] = "{$property}: {$value}";
            }
        }
        return implode('; ', $styles);
    }
}
```

### Page Render Template

```blade
{{-- resources/views/builder/render/page.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->metadata['title'] ?? $page->name }}</title>

    @if($page->metadata['description'] ?? null)
    <meta name="description" content="{{ $page->metadata['description'] }}">
    @endif

    @if($page->metadata['ogTitle'] ?? null)
    <meta property="og:title" content="{{ $page->metadata['ogTitle'] }}">
    @endif

    @if($page->metadata['ogDescription'] ?? null)
    <meta property="og:description" content="{{ $page->metadata['ogDescription'] }}">
    @endif

    @if($page->metadata['ogImage'] ?? null)
    <meta property="og:image" content="{{ $page->metadata['ogImage'] }}">
    @endif

    @if($page->settings['favicon'] ?? null)
    <link rel="icon" href="{{ $page->settings['favicon'] }}">
    @endif

    @vite(['resources/css/app.css'])

    @if($page->settings['customCss'] ?? null)
    <style>{{ $page->settings['customCss'] }}</style>
    @endif
</head>
<body class="{{ $page->settings['bodyClass'] ?? '' }}">
    {!! $content !!}

    @if($page->settings['customJs'] ?? null)
    <script>{{ $page->settings['customJs'] }}</script>
    @endif
</body>
</html>
```

---

## API Controller

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
            'name' => $page->name,
            'elements' => $page->elements ?? [],
            'settings' => $page->settings ?? [],
            'metadata' => $page->metadata ?? [],
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'elements' => 'required|array',
        ]);

        $page->update([
            'elements' => $validated['elements'],
        ]);

        return response()->json(['success' => true]);
    }
}
```

---

## Summary

This drag-and-drop builder implementation provides:

1. **SortableJS + AlpineJS Integration**: Full drag-and-drop with cloning from palette, using `x-data`, `x-init`, and `x-ref` directives
2. **8 Builder Elements**: Section, Heading, Paragraph, Image, Button, Video, Form, Columns - all as Blade templates
3. **Element Editing**: Properties panel with Alpine reactive bindings using `x-model` and `@input`
4. **Canvas with Alpine State**: Real-time preview with reactive element rendering
5. **Copy/Paste/Undo/Redo**: Full clipboard and history support using Alpine state
6. **Auto-Save with fetch()**: Vanilla JavaScript fetch() calls with CSRF token
7. **JSON to HTML Renderer**: PHP service class for server-side rendering

All code uses **Blade + AlpineJS + TailwindCSS v4 + SortableJS** only. No React, Vue, TypeScript, or JS frameworks.
