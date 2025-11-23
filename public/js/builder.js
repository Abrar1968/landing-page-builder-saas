// Professional Page Builder - Elementor-like Implementation
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

        // Panel states
        activeTab: 'content', // content, style, advanced
        leftPanelTab: 'elements', // elements, navigator
        searchQuery: '',

        // History for undo/redo
        history: [],
        historyIndex: -1,

        // Clipboard
        clipboard: null,

        // Modals
        showPageSettings: false,
        showDeleteConfirm: false,

        // Responsive preview mode
        previewMode: 'desktop',

        // Sortable instances
        canvasSortable: null,
        paletteSortable: null,

        // Auto-save timeout
        saveTimeout: null,

        // Font families available
        fonts: [
            'Inter', 'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Poppins',
            'Source Sans Pro', 'Raleway', 'Nunito', 'Ubuntu', 'Merriweather',
            'Playfair Display', 'Georgia', 'Times New Roman', 'Arial', 'Helvetica'
        ],

        // Get canvas width based on preview mode
        get canvasWidth() {
            return {
                'desktop': 'max-w-5xl',
                'tablet': 'max-w-lg',
                'mobile': 'max-w-sm'
            }[this.previewMode];
        },

        // Filter elements by search
        get filteredElements() {
            if (!this.searchQuery) return null;
            const query = this.searchQuery.toLowerCase();
            return Object.entries(window.builderElements || {}).reduce((acc, [category, elements]) => {
                const filtered = elements.filter(el =>
                    el.name.toLowerCase().includes(query) ||
                    el.type.toLowerCase().includes(query)
                );
                if (filtered.length) acc[category] = filtered;
                return acc;
            }, {});
        },

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

            // Initialize element styles if missing
            this.elements.forEach(el => {
                if (!el.styles) el.styles = {};
                if (!el.advanced) el.advanced = this.getDefaultAdvanced();
            });

            this.initSortables();
            this.initKeyboardShortcuts();
            this.initAutoSave();
            this.addToHistory();
        },

        // Default advanced properties
        getDefaultAdvanced() {
            return {
                margin: { top: '0', right: '0', bottom: '0', left: '0', unit: 'px', linked: true },
                padding: { top: '16', right: '16', bottom: '16', left: '16', unit: 'px', linked: true },
                zIndex: '',
                cssClass: '',
                cssId: '',
                hideDesktop: false,
                hideTablet: false,
                hideMobile: false
            };
        },

        // Initialize SortableJS
        initSortables() {
            const paletteEl = document.querySelector('[x-ref="palette"]');
            if (paletteEl) {
                this.paletteSortable = new Sortable(paletteEl, {
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
                        this.selectedElement = element.id;
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

        // Create new element with comprehensive defaults
        createNewElement(type) {
            const id = this.generateId();
            const baseElement = {
                id,
                type,
                styles: {},
                advanced: this.getDefaultAdvanced()
            };

            const defaults = {
                // Typography
                heading: {
                    ...baseElement,
                    label: 'Heading',
                    props: {
                        text: 'Heading Text',
                        level: 'h2',
                        alignment: 'left'
                    },
                    styles: {
                        color: '#1f2937',
                        fontSize: '32',
                        fontSizeUnit: 'px',
                        fontFamily: 'Inter',
                        fontWeight: '700',
                        lineHeight: '1.2',
                        letterSpacing: '0',
                        textTransform: 'none'
                    }
                },
                paragraph: {
                    ...baseElement,
                    label: 'Paragraph',
                    props: {
                        text: 'Enter your text here. Click to edit and add your content.',
                        alignment: 'left'
                    },
                    styles: {
                        color: '#4b5563',
                        fontSize: '16',
                        fontSizeUnit: 'px',
                        fontFamily: 'Inter',
                        fontWeight: '400',
                        lineHeight: '1.6',
                        letterSpacing: '0'
                    }
                },
                // Media
                image: {
                    ...baseElement,
                    label: 'Image',
                    props: {
                        src: '',
                        alt: 'Image description',
                        caption: '',
                        link: '',
                        linkTarget: '_self'
                    },
                    styles: {
                        width: '100',
                        widthUnit: '%',
                        maxWidth: '',
                        height: 'auto',
                        objectFit: 'cover',
                        borderRadius: '0',
                        opacity: '100'
                    }
                },
                video: {
                    ...baseElement,
                    label: 'Video',
                    props: {
                        src: '',
                        provider: 'youtube',
                        autoplay: false,
                        muted: false,
                        loop: false,
                        controls: true
                    },
                    styles: {
                        aspectRatio: '16/9',
                        width: '100',
                        widthUnit: '%',
                        borderRadius: '0'
                    }
                },
                // Interactive
                button: {
                    ...baseElement,
                    label: 'Button',
                    props: {
                        text: 'Click Me',
                        url: '#',
                        target: '_self',
                        icon: '',
                        iconPosition: 'left'
                    },
                    styles: {
                        backgroundColor: '#4f46e5',
                        textColor: '#ffffff',
                        fontSize: '16',
                        fontSizeUnit: 'px',
                        fontWeight: '600',
                        paddingX: '24',
                        paddingY: '12',
                        borderRadius: '6',
                        borderWidth: '0',
                        borderColor: '#4f46e5',
                        hoverBgColor: '#4338ca',
                        hoverTextColor: '#ffffff',
                        alignment: 'center',
                        width: 'auto',
                        boxShadow: 'none'
                    }
                },
                form: {
                    ...baseElement,
                    label: 'Form',
                    props: {
                        fields: [
                            { id: this.generateId(), type: 'text', label: 'Name', placeholder: 'Your name', required: true, width: '100' },
                            { id: this.generateId(), type: 'email', label: 'Email', placeholder: 'your@email.com', required: true, width: '100' },
                            { id: this.generateId(), type: 'textarea', label: 'Message', placeholder: 'Your message', required: false, width: '100' }
                        ],
                        submitText: 'Submit',
                        successMessage: 'Thank you for your submission!',
                        formName: 'contact'
                    },
                    styles: {
                        labelColor: '#374151',
                        inputBgColor: '#ffffff',
                        inputBorderColor: '#d1d5db',
                        inputTextColor: '#1f2937',
                        buttonBgColor: '#4f46e5',
                        buttonTextColor: '#ffffff',
                        spacing: '16'
                    }
                },
                // Layout
                divider: {
                    ...baseElement,
                    label: 'Divider',
                    props: {
                        style: 'solid',
                        alignment: 'center'
                    },
                    styles: {
                        color: '#e5e7eb',
                        thickness: '1',
                        width: '100',
                        widthUnit: '%',
                        gap: '20'
                    }
                },
                spacer: {
                    ...baseElement,
                    label: 'Spacer',
                    props: {},
                    styles: {
                        height: '50',
                        heightUnit: 'px',
                        heightTablet: '40',
                        heightMobile: '30'
                    }
                },
                columns: {
                    ...baseElement,
                    label: 'Columns',
                    props: {
                        columns: 2,
                        gap: '24',
                        verticalAlign: 'top'
                    },
                    children: [
                        { id: this.generateId(), elements: [] },
                        { id: this.generateId(), elements: [] }
                    ],
                    styles: {
                        stackOn: 'mobile'
                    }
                },
                // Sections
                hero: {
                    ...baseElement,
                    label: 'Hero Section',
                    props: {
                        heading: 'Welcome to Our Website',
                        subheading: 'Create amazing landing pages with our powerful builder',
                        buttonText: 'Get Started',
                        buttonUrl: '#',
                        alignment: 'center',
                        showButton: true
                    },
                    styles: {
                        backgroundColor: '#1e3a5f',
                        textColor: '#ffffff',
                        subheadingColor: '#94a3b8',
                        headingSize: '48',
                        subheadingSize: '18',
                        minHeight: '500',
                        backgroundImage: '',
                        backgroundSize: 'cover',
                        backgroundPosition: 'center',
                        overlay: true,
                        overlayColor: 'rgba(0,0,0,0.5)',
                        buttonBgColor: '#4f46e5',
                        buttonTextColor: '#ffffff'
                    }
                },
                features: {
                    ...baseElement,
                    label: 'Features',
                    props: {
                        heading: 'Our Features',
                        columns: 3,
                        items: [
                            { icon: '🚀', title: 'Fast Performance', description: 'Lightning fast load times' },
                            { icon: '🎨', title: 'Beautiful Design', description: 'Stunning visual layouts' },
                            { icon: '📱', title: 'Fully Responsive', description: 'Works on all devices' }
                        ]
                    },
                    styles: {
                        backgroundColor: '#ffffff',
                        headingColor: '#1f2937',
                        titleColor: '#1f2937',
                        textColor: '#6b7280',
                        iconSize: '48'
                    }
                },
                testimonial: {
                    ...baseElement,
                    label: 'Testimonial',
                    props: {
                        quote: '"This product has completely transformed how we work. Highly recommended!"',
                        author: 'John Doe',
                        role: 'CEO, Company Inc.',
                        avatar: '',
                        rating: 5
                    },
                    styles: {
                        backgroundColor: '#f9fafb',
                        quoteColor: '#1f2937',
                        authorColor: '#4b5563',
                        roleColor: '#9ca3af',
                        accentColor: '#4f46e5'
                    }
                },
                pricing: {
                    ...baseElement,
                    label: 'Pricing',
                    props: {
                        heading: 'Simple Pricing',
                        plans: [
                            { name: 'Basic', price: '9', period: 'month', features: ['Feature 1', 'Feature 2', 'Feature 3'], buttonText: 'Get Started', featured: false },
                            { name: 'Pro', price: '29', period: 'month', features: ['Everything in Basic', 'Feature 4', 'Feature 5', 'Feature 6'], buttonText: 'Get Started', featured: true },
                            { name: 'Enterprise', price: '99', period: 'month', features: ['Everything in Pro', 'Feature 7', 'Feature 8', 'Priority Support'], buttonText: 'Contact Us', featured: false }
                        ]
                    },
                    styles: {
                        backgroundColor: '#ffffff',
                        cardBgColor: '#ffffff',
                        featuredBgColor: '#4f46e5',
                        textColor: '#1f2937',
                        priceColor: '#4f46e5',
                        buttonBgColor: '#4f46e5'
                    }
                },
                cta: {
                    ...baseElement,
                    label: 'Call to Action',
                    props: {
                        heading: 'Ready to Get Started?',
                        description: 'Join thousands of satisfied customers today.',
                        buttonText: 'Start Free Trial',
                        buttonUrl: '#',
                        secondaryButtonText: '',
                        secondaryButtonUrl: '#'
                    },
                    styles: {
                        backgroundColor: '#4f46e5',
                        textColor: '#ffffff',
                        buttonBgColor: '#ffffff',
                        buttonTextColor: '#4f46e5'
                    }
                },
                footer: {
                    ...baseElement,
                    label: 'Footer',
                    props: {
                        companyName: 'Your Company',
                        description: 'Building amazing products for the web.',
                        columns: [
                            { title: 'Product', links: [{ text: 'Features', url: '#' }, { text: 'Pricing', url: '#' }] },
                            { title: 'Company', links: [{ text: 'About', url: '#' }, { text: 'Contact', url: '#' }] }
                        ],
                        copyright: '© 2024 Your Company. All rights reserved.',
                        showSocial: true,
                        socialLinks: { facebook: '#', twitter: '#', linkedin: '#' }
                    },
                    styles: {
                        backgroundColor: '#111827',
                        textColor: '#9ca3af',
                        headingColor: '#ffffff',
                        linkColor: '#d1d5db',
                        linkHoverColor: '#ffffff'
                    }
                },
                newsletter: {
                    ...baseElement,
                    label: 'Newsletter',
                    props: {
                        heading: 'Subscribe to Our Newsletter',
                        description: 'Get the latest updates delivered to your inbox.',
                        buttonText: 'Subscribe',
                        placeholder: 'Enter your email'
                    },
                    styles: {
                        backgroundColor: '#f3f4f6',
                        textColor: '#1f2937',
                        inputBgColor: '#ffffff',
                        buttonBgColor: '#4f46e5',
                        buttonTextColor: '#ffffff'
                    }
                },
                // Advanced
                html: {
                    ...baseElement,
                    label: 'Custom HTML',
                    props: {
                        content: '<div class="p-4">\n  <p>Your custom HTML here</p>\n</div>'
                    },
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
            this.activeTab = 'content';
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
            this.activeTab = 'content';
        },

        deselectElement() {
            this.selectedElement = null;
        },

        getSelectedElement() {
            if (!this.selectedElement) return null;
            return this.elements.find(el => el.id === this.selectedElement);
        },

        // Update element property with dot notation support
        updateProp(path, value) {
            const el = this.getSelectedElement();
            if (!el) return;

            const parts = path.split('.');
            let obj = el;
            for (let i = 0; i < parts.length - 1; i++) {
                if (!obj[parts[i]]) obj[parts[i]] = {};
                obj = obj[parts[i]];
            }
            obj[parts[parts.length - 1]] = value;
            this.markDirty();
        },

        // Get element property with dot notation
        getProp(path, defaultValue = '') {
            const el = this.getSelectedElement();
            if (!el) return defaultValue;

            const parts = path.split('.');
            let obj = el;
            for (let i = 0; i < parts.length; i++) {
                if (obj[parts[i]] === undefined) return defaultValue;
                obj = obj[parts[i]];
            }
            return obj;
        },

        // Linked spacing update
        updateLinkedSpacing(type, side, value) {
            const el = this.getSelectedElement();
            if (!el || !el.advanced) return;

            const spacing = el.advanced[type];
            if (spacing.linked) {
                spacing.top = value;
                spacing.right = value;
                spacing.bottom = value;
                spacing.left = value;
            } else {
                spacing[side] = value;
            }
            this.markDirty();
        },

        toggleLinkedSpacing(type) {
            const el = this.getSelectedElement();
            if (!el || !el.advanced) return;
            el.advanced[type].linked = !el.advanced[type].linked;
            this.markDirty();
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

        // Build inline styles from element
        buildStyles(element) {
            const s = element.styles || {};
            const a = element.advanced || {};
            let styles = [];

            // Typography styles
            if (s.color) styles.push(`color: ${s.color}`);
            if (s.fontSize) styles.push(`font-size: ${s.fontSize}${s.fontSizeUnit || 'px'}`);
            if (s.fontFamily) styles.push(`font-family: ${s.fontFamily}, sans-serif`);
            if (s.fontWeight) styles.push(`font-weight: ${s.fontWeight}`);
            if (s.lineHeight) styles.push(`line-height: ${s.lineHeight}`);
            if (s.letterSpacing && s.letterSpacing !== '0') styles.push(`letter-spacing: ${s.letterSpacing}px`);
            if (s.textTransform && s.textTransform !== 'none') styles.push(`text-transform: ${s.textTransform}`);

            // Background
            if (s.backgroundColor) styles.push(`background-color: ${s.backgroundColor}`);

            // Spacing from advanced
            if (a.margin) {
                const m = a.margin;
                styles.push(`margin: ${m.top}${m.unit} ${m.right}${m.unit} ${m.bottom}${m.unit} ${m.left}${m.unit}`);
            }
            if (a.padding) {
                const p = a.padding;
                styles.push(`padding: ${p.top}${p.unit} ${p.right}${p.unit} ${p.bottom}${p.unit} ${p.left}${p.unit}`);
            }

            return styles.join('; ');
        },

        // Render element to HTML
        renderElement(element) {
            const renderers = {
                heading: (el) => {
                    const tag = el.props.level || 'h2';
                    const align = el.props.alignment || 'left';
                    const styles = this.buildStyles(el);
                    return `<${tag} style="${styles}; text-align: ${align};">${this.escapeHtml(el.props.text)}</${tag}>`;
                },
                paragraph: (el) => {
                    const align = el.props.alignment || 'left';
                    const styles = this.buildStyles(el);
                    return `<p style="${styles}; text-align: ${align};">${this.escapeHtml(el.props.text)}</p>`;
                },
                image: (el) => {
                    if (!el.props.src) {
                        return `<div style="padding: 60px 40px; background: #f3f4f6; text-align: center; color: #9ca3af; border: 2px dashed #d1d5db;">
                            <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p>Click to add image URL</p>
                        </div>`;
                    }
                    const s = el.styles;
                    const imgStyles = `width: ${s.width || '100'}${s.widthUnit || '%'}; height: ${s.height || 'auto'}; object-fit: ${s.objectFit || 'cover'}; border-radius: ${s.borderRadius || '0'}px; opacity: ${(s.opacity || 100) / 100};`;
                    return `<img src="${el.props.src}" alt="${this.escapeHtml(el.props.alt)}" style="${imgStyles} display: block; max-width: 100%;">`;
                },
                button: (el) => {
                    const s = el.styles;
                    const btnStyles = `display: inline-block; background: ${s.backgroundColor}; color: ${s.textColor}; font-size: ${s.fontSize}${s.fontSizeUnit || 'px'}; font-weight: ${s.fontWeight}; padding: ${s.paddingY}px ${s.paddingX}px; border-radius: ${s.borderRadius}px; border: ${s.borderWidth}px solid ${s.borderColor}; text-decoration: none; cursor: pointer;`;
                    return `<div style="text-align: ${s.alignment}; padding: 16px;"><a href="${el.props.url || '#'}" target="${el.props.target}" style="${btnStyles}">${this.escapeHtml(el.props.text)}</a></div>`;
                },
                divider: (el) => {
                    const s = el.styles;
                    return `<div style="padding: ${s.gap}px 0; text-align: ${el.props.alignment};"><hr style="border: none; border-top: ${s.thickness}px ${el.props.style} ${s.color}; width: ${s.width}${s.widthUnit}; margin: 0 ${el.props.alignment === 'center' ? 'auto' : '0'};"></div>`;
                },
                spacer: (el) => {
                    const height = el.styles.height || '50';
                    const unit = el.styles.heightUnit || 'px';
                    return `<div style="height: ${height}${unit};"></div>`;
                },
                video: (el) => {
                    if (!el.props.src) {
                        return `<div style="padding: 60px 40px; background: #f3f4f6; text-align: center; color: #9ca3af; border: 2px dashed #d1d5db; aspect-ratio: 16/9; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <svg class="h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <p>Add video URL</p>
                        </div>`;
                    }
                    const embedUrl = this.getVideoEmbedUrl(el.props.src, el.props.provider);
                    const s = el.styles;
                    return `<div style="aspect-ratio: ${s.aspectRatio}; width: ${s.width}${s.widthUnit}; border-radius: ${s.borderRadius}px; overflow: hidden;"><iframe src="${embedUrl}" style="width: 100%; height: 100%; border: none;" allowfullscreen></iframe></div>`;
                },
                hero: (el) => {
                    const s = el.styles;
                    const bgImage = s.backgroundImage ? `url('${s.backgroundImage}')` : 'none';
                    const overlay = s.overlay ? `<div style="position: absolute; inset: 0; background: ${s.overlayColor};"></div>` : '';
                    const button = el.props.showButton ? `<a href="${el.props.buttonUrl}" style="display: inline-block; margin-top: 24px; background: ${s.buttonBgColor}; color: ${s.buttonTextColor}; padding: 14px 32px; border-radius: 6px; text-decoration: none; font-weight: 600;">${this.escapeHtml(el.props.buttonText)}</a>` : '';
                    return `<div style="position: relative; min-height: ${s.minHeight}px; background: ${s.backgroundColor}; background-image: ${bgImage}; background-size: ${s.backgroundSize}; background-position: ${s.backgroundPosition}; display: flex; align-items: center; justify-content: center; text-align: ${el.props.alignment};">
                        ${overlay}
                        <div style="position: relative; z-index: 1; padding: 40px 20px; max-width: 800px;">
                            <h1 style="font-size: ${s.headingSize}px; font-weight: 700; color: ${s.textColor}; margin: 0 0 16px 0;">${this.escapeHtml(el.props.heading)}</h1>
                            <p style="font-size: ${s.subheadingSize}px; color: ${s.subheadingColor}; margin: 0;">${this.escapeHtml(el.props.subheading)}</p>
                            ${button}
                        </div>
                    </div>`;
                },
                features: (el) => {
                    const s = el.styles;
                    const items = el.props.items.map(item => `
                        <div style="text-align: center; padding: 20px;">
                            <div style="font-size: ${s.iconSize}px; margin-bottom: 16px;">${item.icon}</div>
                            <h3 style="font-size: 20px; font-weight: 600; color: ${s.titleColor}; margin: 0 0 8px 0;">${this.escapeHtml(item.title)}</h3>
                            <p style="font-size: 14px; color: ${s.textColor}; margin: 0;">${this.escapeHtml(item.description)}</p>
                        </div>
                    `).join('');
                    return `<div style="background: ${s.backgroundColor}; padding: 60px 20px;">
                        <h2 style="text-align: center; font-size: 32px; font-weight: 700; color: ${s.headingColor}; margin: 0 0 40px 0;">${this.escapeHtml(el.props.heading)}</h2>
                        <div style="display: grid; grid-template-columns: repeat(${el.props.columns}, 1fr); gap: 24px; max-width: 1000px; margin: 0 auto;">
                            ${items}
                        </div>
                    </div>`;
                },
                testimonial: (el) => {
                    const s = el.styles;
                    const stars = '★'.repeat(el.props.rating) + '☆'.repeat(5 - el.props.rating);
                    return `<div style="background: ${s.backgroundColor}; padding: 60px 20px; text-align: center;">
                        <div style="max-width: 600px; margin: 0 auto;">
                            <div style="color: ${s.accentColor}; font-size: 24px; margin-bottom: 16px;">${stars}</div>
                            <p style="font-size: 20px; color: ${s.quoteColor}; font-style: italic; margin: 0 0 24px 0;">${this.escapeHtml(el.props.quote)}</p>
                            <p style="font-weight: 600; color: ${s.authorColor}; margin: 0;">${this.escapeHtml(el.props.author)}</p>
                            <p style="font-size: 14px; color: ${s.roleColor}; margin: 4px 0 0 0;">${this.escapeHtml(el.props.role)}</p>
                        </div>
                    </div>`;
                },
                pricing: (el) => {
                    const s = el.styles;
                    const plans = el.props.plans.map(plan => {
                        const bg = plan.featured ? s.featuredBgColor : s.cardBgColor;
                        const text = plan.featured ? '#ffffff' : s.textColor;
                        const price = plan.featured ? '#ffffff' : s.priceColor;
                        const features = plan.features.map(f => `<li style="padding: 8px 0; border-bottom: 1px solid ${plan.featured ? 'rgba(255,255,255,0.2)' : '#e5e7eb'};">${this.escapeHtml(f)}</li>`).join('');
                        return `<div style="background: ${bg}; color: ${text}; border-radius: 12px; padding: 32px; text-align: center; ${plan.featured ? 'transform: scale(1.05);' : ''}">
                            <h3 style="font-size: 24px; font-weight: 600; margin: 0 0 8px 0;">${this.escapeHtml(plan.name)}</h3>
                            <div style="font-size: 48px; font-weight: 700; color: ${price}; margin: 16px 0;">$${plan.price}<span style="font-size: 16px; font-weight: 400;">/${plan.period}</span></div>
                            <ul style="list-style: none; padding: 0; margin: 24px 0; text-align: left;">${features}</ul>
                            <button style="width: 100%; padding: 12px; background: ${plan.featured ? '#ffffff' : s.buttonBgColor}; color: ${plan.featured ? s.featuredBgColor : '#ffffff'}; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">${this.escapeHtml(plan.buttonText)}</button>
                        </div>`;
                    }).join('');
                    return `<div style="background: ${s.backgroundColor}; padding: 60px 20px;">
                        <h2 style="text-align: center; font-size: 32px; font-weight: 700; color: ${s.textColor}; margin: 0 0 40px 0;">${this.escapeHtml(el.props.heading)}</h2>
                        <div style="display: grid; grid-template-columns: repeat(${el.props.plans.length}, 1fr); gap: 24px; max-width: 1000px; margin: 0 auto; align-items: center;">
                            ${plans}
                        </div>
                    </div>`;
                },
                cta: (el) => {
                    const s = el.styles;
                    const secondary = el.props.secondaryButtonText ? `<a href="${el.props.secondaryButtonUrl}" style="display: inline-block; margin-left: 12px; padding: 14px 32px; border: 2px solid ${s.textColor}; color: ${s.textColor}; border-radius: 6px; text-decoration: none; font-weight: 600;">${this.escapeHtml(el.props.secondaryButtonText)}</a>` : '';
                    return `<div style="background: ${s.backgroundColor}; padding: 60px 20px; text-align: center;">
                        <h2 style="font-size: 32px; font-weight: 700; color: ${s.textColor}; margin: 0 0 16px 0;">${this.escapeHtml(el.props.heading)}</h2>
                        <p style="font-size: 18px; color: ${s.textColor}; opacity: 0.9; margin: 0 0 24px 0;">${this.escapeHtml(el.props.description)}</p>
                        <div>
                            <a href="${el.props.buttonUrl}" style="display: inline-block; background: ${s.buttonBgColor}; color: ${s.buttonTextColor}; padding: 14px 32px; border-radius: 6px; text-decoration: none; font-weight: 600;">${this.escapeHtml(el.props.buttonText)}</a>
                            ${secondary}
                        </div>
                    </div>`;
                },
                footer: (el) => {
                    const s = el.styles;
                    const cols = el.props.columns.map(col => `
                        <div>
                            <h4 style="font-size: 14px; font-weight: 600; color: ${s.headingColor}; margin: 0 0 16px 0; text-transform: uppercase;">${this.escapeHtml(col.title)}</h4>
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                ${col.links.map(link => `<li style="margin-bottom: 8px;"><a href="${link.url}" style="color: ${s.linkColor}; text-decoration: none; font-size: 14px;">${this.escapeHtml(link.text)}</a></li>`).join('')}
                            </ul>
                        </div>
                    `).join('');
                    return `<div style="background: ${s.backgroundColor}; padding: 60px 20px 30px;">
                        <div style="max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 2fr repeat(${el.props.columns.length}, 1fr); gap: 40px;">
                            <div>
                                <h3 style="font-size: 20px; font-weight: 700; color: ${s.headingColor}; margin: 0 0 12px 0;">${this.escapeHtml(el.props.companyName)}</h3>
                                <p style="color: ${s.textColor}; font-size: 14px; margin: 0;">${this.escapeHtml(el.props.description)}</p>
                            </div>
                            ${cols}
                        </div>
                        <div style="max-width: 1000px; margin: 40px auto 0; padding-top: 20px; border-top: 1px solid #374151; text-align: center;">
                            <p style="color: ${s.textColor}; font-size: 14px; margin: 0;">${this.escapeHtml(el.props.copyright)}</p>
                        </div>
                    </div>`;
                },
                newsletter: (el) => {
                    const s = el.styles;
                    return `<div style="background: ${s.backgroundColor}; padding: 60px 20px; text-align: center;">
                        <h2 style="font-size: 28px; font-weight: 700; color: ${s.textColor}; margin: 0 0 12px 0;">${this.escapeHtml(el.props.heading)}</h2>
                        <p style="color: ${s.textColor}; opacity: 0.8; margin: 0 0 24px 0;">${this.escapeHtml(el.props.description)}</p>
                        <div style="max-width: 400px; margin: 0 auto; display: flex; gap: 8px;">
                            <input type="email" placeholder="${el.props.placeholder}" style="flex: 1; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 6px; background: ${s.inputBgColor};">
                            <button style="padding: 12px 24px; background: ${s.buttonBgColor}; color: ${s.buttonTextColor}; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">${this.escapeHtml(el.props.buttonText)}</button>
                        </div>
                    </div>`;
                },
                form: (el) => {
                    const s = el.styles;
                    const fields = el.props.fields.map(field => {
                        const input = field.type === 'textarea'
                            ? `<textarea placeholder="${field.placeholder}" style="width: 100%; padding: 10px 12px; border: 1px solid ${s.inputBorderColor}; border-radius: 6px; background: ${s.inputBgColor}; color: ${s.inputTextColor}; min-height: 100px;"></textarea>`
                            : `<input type="${field.type}" placeholder="${field.placeholder}" style="width: 100%; padding: 10px 12px; border: 1px solid ${s.inputBorderColor}; border-radius: 6px; background: ${s.inputBgColor}; color: ${s.inputTextColor};">`;
                        return `<div style="margin-bottom: ${s.spacing}px; width: ${field.width}%;">
                            <label style="display: block; margin-bottom: 6px; font-size: 14px; font-weight: 500; color: ${s.labelColor};">${this.escapeHtml(field.label)}${field.required ? ' *' : ''}</label>
                            ${input}
                        </div>`;
                    }).join('');
                    return `<div style="padding: 20px;">
                        <form onsubmit="return false;">
                            ${fields}
                            <button type="submit" style="padding: 12px 24px; background: ${s.buttonBgColor}; color: ${s.buttonTextColor}; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">${this.escapeHtml(el.props.submitText)}</button>
                        </form>
                    </div>`;
                },
                html: (el) => {
                    return `<div>${el.props.content}</div>`;
                }
            };

            return renderers[element.type] ? renderers[element.type](element) : '<div style="padding: 20px; text-align: center; color: #9ca3af;">Unknown element type</div>';
        },

        escapeHtml(text) {
            if (!text) return '';
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

                if (isCtrlOrCmd && e.key === 'z' && !e.shiftKey) {
                    e.preventDefault();
                    this.undo();
                }

                if ((isCtrlOrCmd && e.shiftKey && e.key === 'z') || (isCtrlOrCmd && e.key === 'y')) {
                    e.preventDefault();
                    this.redo();
                }

                if (isCtrlOrCmd && e.key === 'c' && this.selectedElement) {
                    e.preventDefault();
                    this.copy();
                }

                if (isCtrlOrCmd && e.key === 'v' && this.clipboard) {
                    e.preventDefault();
                    this.paste();
                }

                if (isCtrlOrCmd && e.key === 'x' && this.selectedElement) {
                    e.preventDefault();
                    this.cut();
                }

                if ((e.key === 'Delete' || e.key === 'Backspace') && this.selectedElement && !['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
                    e.preventDefault();
                    this.deleteElement(this.selectedElement);
                }

                if (isCtrlOrCmd && e.key === 's') {
                    e.preventDefault();
                    this.save();
                }

                if (e.key === 'Escape') {
                    this.deselectElement();
                }
            });
        },

        // Auto-save
        initAutoSave() {
            setInterval(() => {
                if (this.isDirty && !this.isSaving) {
                    this.save();
                }
            }, 30000);

            this.$watch('elements', () => {
                this.markDirty();
                this.debouncedSave();
            }, { deep: true });
        },

        debouncedSave() {
            if (this.saveTimeout) {
                clearTimeout(this.saveTimeout);
            }
            this.saveTimeout = setTimeout(() => {
                if (this.isDirty && !this.isSaving) {
                    this.save();
                }
            }, 3000);
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
                    this.lastSaved = new Date().toLocaleTimeString();
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
                    this.showNotification('Page published successfully!', 'success');
                }
            } catch (error) {
                console.error('Publish error:', error);
                this.showNotification('Failed to publish page', 'error');
            }
        },

        // Delete page
        async deletePage() {
            try {
                const response = await fetch(`/api/pages/${this.pageId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success || response.ok) {
                    window.location.href = '/dashboard/pages';
                } else {
                    this.showNotification('Failed to delete page', 'error');
                }
            } catch (error) {
                console.error('Delete error:', error);
                this.showNotification('Failed to delete page', 'error');
            }
            this.showDeleteConfirm = false;
        },

        // Show notification
        showNotification(message, type = 'success') {
            // Simple notification - can be enhanced
            alert(message);
        },

        // Preview
        preview() {
            window.open(`/builder/${this.pageId}/preview`, '_blank');
        }
    };
}
