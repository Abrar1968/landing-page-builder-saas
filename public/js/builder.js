// Page Builder AlpineJS Component
function builderApp() {
    return {
        // Core state
        pageId: null,
        elements: [],
        pageSettings: {
            title: '',
            slug: '',
            metaTitle: '',
            metaDescription: ''
        },
        selectedElement: null,
        isDirty: false,
        isSaving: false,
        lastSaved: null,

        // History for undo/redo
        history: [],
        historyIndex: -1,

        // Clipboard
        clipboard: null,

        // Modals
        showPageSettings: false,

        // Sortable instances
        canvasSortable: null,
        paletteSortable: null,

        // Initialize
        init() {
            const pageData = JSON.parse(document.getElementById('page-data').textContent);
            this.pageId = pageData.id;
            this.elements = pageData.elements || [];
            this.pageSettings = {
                title: pageData.title || '',
                slug: pageData.slug || '',
                metaTitle: pageData.settings?.metaTitle || '',
                metaDescription: pageData.settings?.metaDescription || ''
            };

            this.initSortables();
            this.initKeyboardShortcuts();
            this.initAutoSave();
            this.addToHistory();
        },

        // Initialize SortableJS
        initSortables() {
            // Palette sortable (clone elements)
            if (this.$refs.palette) {
                this.paletteSortable = new Sortable(this.$refs.palette, {
                    group: {
                        name: 'builder',
                        pull: 'clone',
                        put: false
                    },
                    sort: false,
                    animation: 200,
                    ghostClass: 'opacity-40'
                });
            }

            // Canvas sortable
            this.initCanvasSortable();
        },

        initCanvasSortable() {
            if (!this.$refs.canvas) return;

            if (this.canvasSortable) {
                this.canvasSortable.destroy();
            }

            this.canvasSortable = new Sortable(this.$refs.canvas, {
                group: {
                    name: 'builder',
                    pull: true,
                    put: true
                },
                animation: 200,
                handle: '.drag-handle',
                ghostClass: 'opacity-40',
                onAdd: (evt) => {
                    const type = evt.item.dataset.type;
                    if (type) {
                        this.addToHistory();
                        const element = this.createNewElement(type);
                        this.elements.splice(evt.newIndex, 0, element);
                        evt.item.remove();
                        this.isDirty = true;
                        this.$nextTick(() => this.initCanvasSortable());
                    }
                },
                onSort: (evt) => {
                    if (evt.oldIndex !== evt.newIndex) {
                        this.addToHistory();
                        this.reorderElements(evt.oldIndex, evt.newIndex);
                        this.isDirty = true;
                    }
                }
            });
        },

        // Create new element with default props
        createNewElement(type) {
            const id = this.generateId();
            const defaults = {
                section: {
                    id, type: 'section', label: 'Section',
                    props: { backgroundColor: '#ffffff', minHeight: '200px' },
                    styles: { padding: '40px 20px' }
                },
                heading: {
                    id, type: 'heading', label: 'Heading',
                    props: { text: 'Heading Text', level: 'h2', color: '#000000', fontSize: '32px', fontWeight: '700' },
                    styles: { margin: '0 0 16px 0' }
                },
                paragraph: {
                    id, type: 'paragraph', label: 'Paragraph',
                    props: { text: 'Enter your text here. Click to edit.', color: '#333333', fontSize: '16px', lineHeight: '1.6' },
                    styles: { margin: '0 0 16px 0' }
                },
                image: {
                    id, type: 'image', label: 'Image',
                    props: { src: '', alt: 'Image description', width: '100%', objectFit: 'cover' },
                    styles: {}
                },
                button: {
                    id, type: 'button', label: 'Button',
                    props: { text: 'Click Me', url: '#', backgroundColor: '#3b82f6', textColor: '#ffffff', borderRadius: '6px' },
                    styles: {}
                },
                video: {
                    id, type: 'video', label: 'Video',
                    props: { src: '', provider: 'youtube', aspectRatio: '16:9' },
                    styles: {}
                },
                divider: {
                    id, type: 'divider', label: 'Divider',
                    props: { color: '#e5e7eb', thickness: '1px', width: '100%' },
                    styles: { margin: '24px 0' }
                },
                spacer: {
                    id, type: 'spacer', label: 'Spacer',
                    props: { height: '40px' },
                    styles: {}
                },
                columns: {
                    id, type: 'columns', label: 'Columns',
                    props: { columns: 2, gap: '24px' },
                    children: [
                        { id: this.generateId(), elements: [] },
                        { id: this.generateId(), elements: [] }
                    ],
                    styles: {}
                },
                form: {
                    id, type: 'form', label: 'Form',
                    props: {
                        fields: [
                            { id: '1', type: 'text', label: 'Name', required: true },
                            { id: '2', type: 'email', label: 'Email', required: true }
                        ],
                        submitText: 'Submit',
                        buttonColor: '#3b82f6'
                    },
                    styles: {}
                },
                html: {
                    id, type: 'html', label: 'Custom HTML',
                    props: { content: '<div>Custom HTML here</div>' },
                    styles: {}
                }
            };
            return defaults[type] || defaults.paragraph;
        },

        generateId() {
            return 'el_' + Math.random().toString(36).substr(2, 9);
        },

        // Add element by clicking
        addElement(type) {
            this.addToHistory();
            const element = this.createNewElement(type);
            this.elements.push(element);
            this.selectedElement = element.id;
            this.isDirty = true;
            this.$nextTick(() => this.initCanvasSortable());
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
            if (!this.selectedElement) return null;
            return this.elements.find(el => el.id === this.selectedElement);
        },

        // Move elements
        moveElementUp(index) {
            if (index <= 0) return;
            this.addToHistory();
            this.reorderElements(index, index - 1);
            this.isDirty = true;
        },

        moveElementDown(index) {
            if (index >= this.elements.length - 1) return;
            this.addToHistory();
            this.reorderElements(index, index + 1);
            this.isDirty = true;
        },

        // Delete element
        deleteElement(id) {
            this.addToHistory();
            this.elements = this.elements.filter(el => el.id !== id);
            if (this.selectedElement === id) {
                this.selectedElement = null;
            }
            this.isDirty = true;
            this.$nextTick(() => this.initCanvasSortable());
        },

        // Duplicate element
        duplicateElement(id) {
            const element = this.elements.find(el => el.id === id);
            if (!element) return;

            this.addToHistory();
            const duplicated = JSON.parse(JSON.stringify(element));
            duplicated.id = this.generateId();

            const index = this.elements.findIndex(el => el.id === id);
            this.elements.splice(index + 1, 0, duplicated);

            this.selectedElement = duplicated.id;
            this.isDirty = true;
            this.$nextTick(() => this.initCanvasSortable());
        },

        // Render element to HTML
        renderElement(element) {
            const renderers = {
                heading: (el) => {
                    const tag = el.props.level || 'h2';
                    return `<${tag} style="color: ${el.props.color}; font-size: ${el.props.fontSize}; font-weight: ${el.props.fontWeight}; margin: 0; padding: 16px;">${this.escapeHtml(el.props.text)}</${tag}>`;
                },
                paragraph: (el) => {
                    return `<p style="color: ${el.props.color}; font-size: ${el.props.fontSize}; line-height: ${el.props.lineHeight}; margin: 0; padding: 16px;">${this.escapeHtml(el.props.text)}</p>`;
                },
                image: (el) => {
                    if (!el.props.src) {
                        return `<div style="padding: 40px; background: #f3f4f6; text-align: center; color: #9ca3af;">Click to add image</div>`;
                    }
                    return `<img src="${el.props.src}" alt="${this.escapeHtml(el.props.alt)}" style="width: ${el.props.width}; object-fit: ${el.props.objectFit}; display: block;">`;
                },
                button: (el) => {
                    return `<div style="padding: 16px; text-align: center;"><a href="${el.props.url || '#'}" style="display: inline-block; background: ${el.props.backgroundColor}; color: ${el.props.textColor}; padding: 12px 24px; border-radius: ${el.props.borderRadius}; text-decoration: none;">${this.escapeHtml(el.props.text)}</a></div>`;
                },
                divider: (el) => {
                    return `<hr style="border: none; border-top: ${el.props.thickness} solid ${el.props.color}; width: ${el.props.width}; margin: ${el.styles?.margin || '24px 0'};">`;
                },
                spacer: (el) => {
                    return `<div style="height: ${el.props.height};"></div>`;
                },
                video: (el) => {
                    if (!el.props.src) {
                        return `<div style="padding: 40px; background: #f3f4f6; text-align: center; color: #9ca3af;">Add video URL</div>`;
                    }
                    const embedUrl = this.getVideoEmbedUrl(el.props.src, el.props.provider);
                    return `<div style="aspect-ratio: 16/9; width: 100%;"><iframe src="${embedUrl}" style="width: 100%; height: 100%; border: none;" allowfullscreen></iframe></div>`;
                },
                form: (el) => {
                    let fieldsHtml = el.props.fields.map(field => `
                        <div style="margin-bottom: 12px;">
                            <label style="display: block; margin-bottom: 4px; font-size: 14px;">${field.label}</label>
                            <input type="${field.type}" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
                        </div>
                    `).join('');
                    return `<div style="padding: 16px;">${fieldsHtml}<button style="background: ${el.props.buttonColor}; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">${el.props.submitText}</button></div>`;
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
            this.$nextTick(() => this.initCanvasSortable());
        },

        redo() {
            if (this.historyIndex >= this.history.length - 1) return;

            this.historyIndex++;
            const state = JSON.parse(this.history[this.historyIndex]);
            this.elements = state.elements;
            this.selectedElement = state.selectedElement;
            this.isDirty = true;
            this.$nextTick(() => this.initCanvasSortable());
        },

        // Clipboard
        copy() {
            if (!this.selectedElement) return;
            const element = this.getSelectedElement();
            if (element) {
                this.clipboard = JSON.parse(JSON.stringify(element));
            }
        },

        paste() {
            if (!this.clipboard) return;

            this.addToHistory();
            const pasted = JSON.parse(JSON.stringify(this.clipboard));
            pasted.id = this.generateId();
            this.elements.push(pasted);
            this.selectedElement = pasted.id;
            this.isDirty = true;
            this.$nextTick(() => this.initCanvasSortable());
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
                if (isCtrlOrCmd && e.key === 'c' && this.selectedElement) {
                    e.preventDefault();
                    this.copy();
                }

                // Paste
                if (isCtrlOrCmd && e.key === 'v' && this.clipboard) {
                    e.preventDefault();
                    this.paste();
                }

                // Cut
                if (isCtrlOrCmd && e.key === 'x' && this.selectedElement) {
                    e.preventDefault();
                    this.cut();
                }

                // Delete
                if ((e.key === 'Delete' || e.key === 'Backspace') && this.selectedElement && !['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
                    e.preventDefault();
                    this.deleteElement(this.selectedElement);
                }

                // Save
                if (isCtrlOrCmd && e.key === 's') {
                    e.preventDefault();
                    this.save();
                }
            });
        },

        // Auto-save
        initAutoSave() {
            setInterval(() => {
                if (this.isDirty && !this.isSaving) {
                    this.save();
                }
            }, 30000); // 30 seconds
        },

        markDirty() {
            this.isDirty = true;
        },

        // Save to server
        async save() {
            if (this.isSaving) return;

            this.isSaving = true;

            try {
                const response = await fetch(`/builder/${this.pageId}/save`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        content: this.elements,
                        settings: {
                            title: this.pageSettings.title,
                            slug: this.pageSettings.slug,
                            metaTitle: this.pageSettings.metaTitle,
                            metaDescription: this.pageSettings.metaDescription
                        }
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.isDirty = false;
                    this.lastSaved = data.saved_at;
                }
            } catch (error) {
                console.error('Save error:', error);
            } finally {
                this.isSaving = false;
            }
        },

        // Publish
        async publish() {
            await this.save();

            try {
                const response = await fetch(`/builder/${this.pageId}/publish`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert('Page published successfully!');
                }
            } catch (error) {
                console.error('Publish error:', error);
            }
        },

        // Preview
        preview() {
            window.open(`/builder/${this.pageId}/preview`, '_blank');
        }
    };
}
