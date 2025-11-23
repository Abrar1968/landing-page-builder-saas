// Widget Registry - Central store for all widget definitions
export const widgetRegistry = {
    widgets: {},

    register(name, config) {
        this.widgets[name] = config;
    },

    get(name) {
        return this.widgets[name];
    },

    getAll() {
        return this.widgets;
    }
};

// Register default widgets
widgetRegistry.register('heading', {
    title: 'Heading',
    icon: 'H',
    category: 'basic',
    controls: {
        content: [
            { name: 'title', type: 'textarea', label: 'Title', default: 'Heading' },
            { name: 'link', type: 'text', label: 'Link', placeholder: 'https://' },
            { name: 'size', type: 'select', label: 'HTML Tag', default: 'h2', options: { h1: 'H1', h2: 'H2', h3: 'H3', h4: 'H4', h5: 'H5', h6: 'H6' } },
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'left', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }}
        ],
        style: [
            { name: 'text_color', type: 'color', label: 'Text Color', default: '#1f2937' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    }
});

widgetRegistry.register('text-editor', {
    title: 'Text Editor',
    icon: '¶',
    category: 'basic',
    controls: {
        content: [
            { name: 'editor', type: 'wysiwyg', label: 'Text Editor', default: '<p>Lorem ipsum dolor sit amet</p>' }
        ],
        style: [
            { name: 'text_color', type: 'color', label: 'Text Color', default: '#4b5563' },
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'left', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }}
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    }
});

widgetRegistry.register('image', {
    title: 'Image',
    icon: '🖼',
    category: 'basic',
    controls: {
        content: [
            { name: 'image_url', type: 'media', label: 'Image' },
            { name: 'alt_text', type: 'text', label: 'Alt Text' },
            { name: 'caption', type: 'text', label: 'Caption' }
        ],
        style: [
            { name: 'width', type: 'slider', label: 'Width', min: 0, max: 100, default: 100, unit: '%' },
            { name: 'alignment', type: 'choose', label: 'Alignment', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }}
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    }
});

widgetRegistry.register('button', {
    title: 'Button',
    icon: '▢',
    category: 'basic',
    controls: {
        content: [
            { name: 'text', type: 'text', label: 'Text', default: 'Click Me' },
            { name: 'link', type: 'text', label: 'Link', placeholder: 'https://' },
            { name: 'target', type: 'switcher', label: 'Open in new window' }
        ],
        style: [
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'left', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }},
            { name: 'background_color', type: 'color', label: 'Background', default: '#4f46e5' },
            { name: 'text_color', type: 'color', label: 'Text Color', default: '#ffffff' },
            { name: 'border_radius', type: 'slider', label: 'Border Radius', min: 0, max: 50, default: 6, unit: 'px' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' }
        ]
    }
});

widgetRegistry.register('video', {
    title: 'Video',
    icon: '▶',
    category: 'basic',
    controls: {
        content: [
            { name: 'video_type', type: 'select', label: 'Source', default: 'youtube', options: { youtube: 'YouTube', vimeo: 'Vimeo' } },
            { name: 'youtube_url', type: 'text', label: 'YouTube URL' }
        ],
        style: [
            { name: 'aspect_ratio', type: 'select', label: 'Aspect Ratio', default: '16:9', options: { '16:9': '16:9', '4:3': '4:3' } },
            { name: 'width', type: 'slider', label: 'Width', min: 0, max: 100, default: 100, unit: '%' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' }
        ]
    }
});

widgetRegistry.register('divider', {
    title: 'Divider',
    icon: '—',
    category: 'basic',
    controls: {
        content: [
            { name: 'style', type: 'select', label: 'Style', default: 'solid', options: { solid: 'Solid', dashed: 'Dashed', dotted: 'Dotted' } }
        ],
        style: [
            { name: 'color', type: 'color', label: 'Color', default: '#e5e7eb' },
            { name: 'weight', type: 'slider', label: 'Weight', min: 1, max: 10, default: 1, unit: 'px' },
            { name: 'width', type: 'slider', label: 'Width', min: 0, max: 100, default: 100, unit: '%' },
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'center', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }}
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' }
        ]
    }
});

widgetRegistry.register('spacer', {
    title: 'Spacer',
    icon: '↕',
    category: 'basic',
    controls: {
        content: [
            { name: 'space', type: 'slider', label: 'Space', min: 0, max: 500, default: 50, unit: 'px' }
        ],
        style: [],
        advanced: []
    }
});

widgetRegistry.register('icon', {
    title: 'Icon',
    icon: '★',
    category: 'basic',
    controls: {
        content: [
            { name: 'icon', type: 'text', label: 'Icon (emoji)', default: '★' },
            { name: 'link', type: 'text', label: 'Link' }
        ],
        style: [
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'center', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }},
            { name: 'primary_color', type: 'color', label: 'Color', default: '#4f46e5' },
            { name: 'size', type: 'slider', label: 'Size', min: 10, max: 200, default: 50, unit: 'px' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' }
        ]
    }
});

widgetRegistry.register('icon-box', {
    title: 'Icon Box',
    icon: '◈',
    category: 'basic',
    controls: {
        content: [
            { name: 'icon', type: 'text', label: 'Icon', default: '⚡' },
            { name: 'title', type: 'text', label: 'Title', default: 'Icon Box' },
            { name: 'description', type: 'textarea', label: 'Description', default: 'Click here to add your own text.' }
        ],
        style: [
            { name: 'icon_color', type: 'color', label: 'Icon Color', default: '#4f46e5' },
            { name: 'icon_size', type: 'slider', label: 'Icon Size', min: 20, max: 100, default: 50, unit: 'px' },
            { name: 'title_color', type: 'color', label: 'Title Color', default: '#1f2937' },
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'center', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }}
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    }
});

widgetRegistry.register('counter', {
    title: 'Counter',
    icon: '123',
    category: 'basic',
    controls: {
        content: [
            { name: 'ending_number', type: 'number', label: 'Number', default: 100 },
            { name: 'prefix', type: 'text', label: 'Prefix' },
            { name: 'suffix', type: 'text', label: 'Suffix' },
            { name: 'title', type: 'text', label: 'Title', default: 'Cool Number' }
        ],
        style: [
            { name: 'number_color', type: 'color', label: 'Number Color', default: '#4f46e5' },
            { name: 'title_color', type: 'color', label: 'Title Color', default: '#6b7280' },
            { name: 'number_size', type: 'slider', label: 'Number Size', min: 20, max: 100, default: 48, unit: 'px' },
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'center', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }}
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' }
        ]
    }
});

widgetRegistry.register('progress-bar', {
    title: 'Progress Bar',
    icon: '█▒',
    category: 'basic',
    controls: {
        content: [
            { name: 'title', type: 'text', label: 'Title', default: 'Progress' },
            { name: 'percent', type: 'slider', label: 'Percentage', min: 0, max: 100, default: 75, unit: '%' },
            { name: 'display_percent', type: 'switcher', label: 'Display Percentage', default: true }
        ],
        style: [
            { name: 'bar_color', type: 'color', label: 'Bar Color', default: '#4f46e5' },
            { name: 'bg_color', type: 'color', label: 'Background', default: '#e5e7eb' },
            { name: 'height', type: 'slider', label: 'Height', min: 4, max: 50, default: 12, unit: 'px' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' }
        ]
    }
});

widgetRegistry.register('testimonial', {
    title: 'Testimonial',
    icon: '💬',
    category: 'basic',
    controls: {
        content: [
            { name: 'content', type: 'textarea', label: 'Content', default: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' },
            { name: 'image_url', type: 'media', label: 'Image' },
            { name: 'name', type: 'text', label: 'Name', default: 'John Doe' },
            { name: 'title', type: 'text', label: 'Title', default: 'Designer' }
        ],
        style: [
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'center', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }},
            { name: 'content_color', type: 'color', label: 'Content Color', default: '#4b5563' },
            { name: 'name_color', type: 'color', label: 'Name Color', default: '#1f2937' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    }
});

widgetRegistry.register('social-icons', {
    title: 'Social Icons',
    icon: '📱',
    category: 'basic',
    controls: {
        content: [
            { name: 'facebook', type: 'text', label: 'Facebook URL' },
            { name: 'twitter', type: 'text', label: 'Twitter URL' },
            { name: 'instagram', type: 'text', label: 'Instagram URL' },
            { name: 'linkedin', type: 'text', label: 'LinkedIn URL' }
        ],
        style: [
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'center', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }},
            { name: 'icon_color', type: 'color', label: 'Icon Color', default: '#4b5563' },
            { name: 'icon_size', type: 'slider', label: 'Size', min: 16, max: 50, default: 24, unit: 'px' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' }
        ]
    }
});

widgetRegistry.register('alert', {
    title: 'Alert',
    icon: '⚠',
    category: 'basic',
    controls: {
        content: [
            { name: 'title', type: 'text', label: 'Title', default: 'This is an Alert' },
            { name: 'content', type: 'textarea', label: 'Content', default: 'Click to edit this text.' },
            { name: 'alert_type', type: 'select', label: 'Type', default: 'info', options: { info: 'Info', success: 'Success', warning: 'Warning', danger: 'Danger' } },
            { name: 'show_icon', type: 'switcher', label: 'Show Icon', default: true }
        ],
        style: [],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' }
        ]
    }
});
