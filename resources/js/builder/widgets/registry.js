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
            { name: 'title', type: 'textarea', label: 'Title', default: 'Add Your Heading Text Here' },
            { name: 'link', type: 'url', label: 'Link' },
            { name: 'size', type: 'select', label: 'HTML Tag', default: 'h2', options: { h1: 'H1', h2: 'H2', h3: 'H3', h4: 'H4', h5: 'H5', h6: 'H6' } },
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'left', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }}
        ],
        style: [
            { name: 'text_color', type: 'color', label: 'Text Color', default: '#1f2937' },
            { name: 'typography', type: 'typography', label: 'Typography' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' },
            { name: 'css_id', type: 'text', label: 'CSS ID' },
            { name: 'custom_css', type: 'code', label: 'Custom CSS' }
        ]
    }
});

widgetRegistry.register('text-editor', {
    title: 'Text Editor',
    icon: '¶',
    category: 'basic',
    controls: {
        content: [
            { name: 'editor', type: 'wysiwyg', label: 'Text Editor', default: '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.</p>' }
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
            { name: 'icon', type: 'icon', label: 'Choose Icon', default: '⭐' },
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
            { name: 'icon', type: 'icon', label: 'Choose Icon', default: '⚡' },
            { name: 'title', type: 'text', label: 'Title', default: 'This is the heading' },
            { name: 'description', type: 'textarea', label: 'Description', default: 'Click here to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.' }
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
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' },
            { name: 'css_id', type: 'text', label: 'CSS ID' }
        ]
    }
});

// Additional Pro-style widgets

widgetRegistry.register('image-box', {
    title: 'Image Box',
    icon: '🖼️',
    category: 'general',
    controls: {
        content: [
            { name: 'image_url', type: 'media', label: 'Image' },
            { name: 'title', type: 'text', label: 'Title', default: 'Image Box' },
            { name: 'description', type: 'textarea', label: 'Description', default: 'Click here to add your own text.' },
            { name: 'link', type: 'url', label: 'Link' }
        ],
        style: [
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'center', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }},
            { name: 'title_color', type: 'color', label: 'Title Color', default: '#1f2937' },
            { name: 'description_color', type: 'color', label: 'Description Color', default: '#6b7280' },
            { name: 'background', type: 'background', label: 'Background' },
            { name: 'border', type: 'border', label: 'Border' },
            { name: 'box_shadow', type: 'box_shadow', label: 'Box Shadow' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' },
            { name: 'css_id', type: 'text', label: 'CSS ID' }
        ]
    }
});

widgetRegistry.register('star-rating', {
    title: 'Star Rating',
    icon: '⭐',
    category: 'general',
    controls: {
        content: [
            { name: 'rating', type: 'slider', label: 'Rating', min: 0, max: 5, default: 4, unit: '' },
            { name: 'scale', type: 'select', label: 'Scale', default: '5', options: { '5': '1-5', '10': '1-10' } },
            { name: 'title', type: 'text', label: 'Title' }
        ],
        style: [
            { name: 'size', type: 'slider', label: 'Size', min: 10, max: 100, default: 24, unit: 'px' },
            { name: 'color', type: 'color', label: 'Color', default: '#fbbf24' },
            { name: 'unmarked_color', type: 'color', label: 'Unmarked Color', default: '#d1d5db' },
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'left', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }}
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' }
        ]
    }
});

widgetRegistry.register('tabs', {
    title: 'Tabs',
    icon: '📑',
    category: 'general',
    controls: {
        content: [
            { name: 'tab1_title', type: 'text', label: 'Tab 1 Title', default: 'Tab 1' },
            { name: 'tab1_content', type: 'wysiwyg', label: 'Tab 1 Content', default: '<p>Tab 1 content goes here.</p>' },
            { name: 'tab2_title', type: 'text', label: 'Tab 2 Title', default: 'Tab 2' },
            { name: 'tab2_content', type: 'wysiwyg', label: 'Tab 2 Content', default: '<p>Tab 2 content goes here.</p>' },
            { name: 'tab3_title', type: 'text', label: 'Tab 3 Title', default: 'Tab 3' },
            { name: 'tab3_content', type: 'wysiwyg', label: 'Tab 3 Content', default: '<p>Tab 3 content goes here.</p>' }
        ],
        style: [
            { name: 'tab_color', type: 'color', label: 'Tab Color', default: '#4f46e5' },
            { name: 'content_color', type: 'color', label: 'Content Color', default: '#1f2937' },
            { name: 'border', type: 'border', label: 'Border' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' }
        ]
    }
});

widgetRegistry.register('accordion', {
    title: 'Accordion',
    icon: '📋',
    category: 'general',
    controls: {
        content: [
            { name: 'item1_title', type: 'text', label: 'Item 1 Title', default: 'Accordion Item 1' },
            { name: 'item1_content', type: 'wysiwyg', label: 'Item 1 Content', default: '<p>Content for accordion item 1.</p>' },
            { name: 'item2_title', type: 'text', label: 'Item 2 Title', default: 'Accordion Item 2' },
            { name: 'item2_content', type: 'wysiwyg', label: 'Item 2 Content', default: '<p>Content for accordion item 2.</p>' },
            { name: 'item3_title', type: 'text', label: 'Item 3 Title', default: 'Accordion Item 3' },
            { name: 'item3_content', type: 'wysiwyg', label: 'Item 3 Content', default: '<p>Content for accordion item 3.</p>' },
            { name: 'first_open', type: 'switcher', label: 'First Item Open', default: true }
        ],
        style: [
            { name: 'title_color', type: 'color', label: 'Title Color', default: '#1f2937' },
            { name: 'title_background', type: 'color', label: 'Title Background', default: '#f3f4f6' },
            { name: 'content_color', type: 'color', label: 'Content Color', default: '#4b5563' },
            { name: 'border', type: 'border', label: 'Border' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' }
        ]
    }
});

widgetRegistry.register('countdown', {
    title: 'Countdown',
    icon: '⏱️',
    category: 'general',
    controls: {
        content: [
            { name: 'due_date', type: 'text', label: 'Due Date', default: '2025-12-31', placeholder: 'YYYY-MM-DD' },
            { name: 'due_time', type: 'text', label: 'Due Time', default: '23:59', placeholder: 'HH:MM' },
            { name: 'show_days', type: 'switcher', label: 'Show Days', default: true },
            { name: 'show_hours', type: 'switcher', label: 'Show Hours', default: true },
            { name: 'show_minutes', type: 'switcher', label: 'Show Minutes', default: true },
            { name: 'show_seconds', type: 'switcher', label: 'Show Seconds', default: true },
            { name: 'show_labels', type: 'switcher', label: 'Show Labels', default: true }
        ],
        style: [
            { name: 'number_color', type: 'color', label: 'Number Color', default: '#1f2937' },
            { name: 'label_color', type: 'color', label: 'Label Color', default: '#6b7280' },
            { name: 'number_size', type: 'slider', label: 'Number Size', min: 20, max: 100, default: 48, unit: 'px' },
            { name: 'background', type: 'background', label: 'Box Background' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' }
        ]
    }
});

widgetRegistry.register('google-maps', {
    title: 'Google Maps',
    icon: '🗺️',
    category: 'general',
    controls: {
        content: [
            { name: 'address', type: 'text', label: 'Address', default: 'New York, USA' },
            { name: 'zoom', type: 'slider', label: 'Zoom', min: 1, max: 20, default: 14, unit: '' }
        ],
        style: [
            { name: 'height', type: 'slider', label: 'Height', min: 100, max: 800, default: 400, unit: 'px' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' }
        ]
    }
});

widgetRegistry.register('call-to-action', {
    title: 'Call to Action',
    icon: '📢',
    category: 'marketing',
    controls: {
        content: [
            { name: 'title', type: 'text', label: 'Title', default: 'This is the heading' },
            { name: 'description', type: 'textarea', label: 'Description', default: 'Click here to add your own text and edit me.' },
            { name: 'button_text', type: 'text', label: 'Button Text', default: 'Click Here' },
            { name: 'button_link', type: 'url', label: 'Button Link' },
            { name: 'ribbon_text', type: 'text', label: 'Ribbon Text' }
        ],
        style: [
            { name: 'background', type: 'background', label: 'Background' },
            { name: 'title_color', type: 'color', label: 'Title Color', default: '#1f2937' },
            { name: 'description_color', type: 'color', label: 'Description Color', default: '#4b5563' },
            { name: 'button_background', type: 'color', label: 'Button Background', default: '#4f46e5' },
            { name: 'button_color', type: 'color', label: 'Button Text Color', default: '#ffffff' },
            { name: 'ribbon_color', type: 'color', label: 'Ribbon Color', default: '#ef4444' },
            { name: 'box_shadow', type: 'box_shadow', label: 'Box Shadow' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' }
        ]
    }
});

widgetRegistry.register('flip-box', {
    title: 'Flip Box',
    icon: '🔄',
    category: 'marketing',
    controls: {
        content: [
            { name: 'front_icon', type: 'text', label: 'Front Icon', default: '⚡' },
            { name: 'front_title', type: 'text', label: 'Front Title', default: 'Front Title' },
            { name: 'front_description', type: 'textarea', label: 'Front Description', default: 'This is the front content.' },
            { name: 'back_icon', type: 'text', label: 'Back Icon', default: '✨' },
            { name: 'back_title', type: 'text', label: 'Back Title', default: 'Back Title' },
            { name: 'back_description', type: 'textarea', label: 'Back Description', default: 'This is the back content.' },
            { name: 'button_text', type: 'text', label: 'Button Text', default: 'Click Here' },
            { name: 'button_link', type: 'url', label: 'Button Link' },
            { name: 'flip_direction', type: 'select', label: 'Flip Direction', default: 'horizontal', options: { horizontal: 'Horizontal', vertical: 'Vertical' } }
        ],
        style: [
            { name: 'front_background', type: 'color', label: 'Front Background', default: '#ffffff' },
            { name: 'front_color', type: 'color', label: 'Front Color', default: '#1f2937' },
            { name: 'back_background', type: 'color', label: 'Back Background', default: '#4f46e5' },
            { name: 'back_color', type: 'color', label: 'Back Color', default: '#ffffff' },
            { name: 'height', type: 'slider', label: 'Height', min: 200, max: 600, default: 300, unit: 'px' },
            { name: 'border', type: 'border', label: 'Border' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' }
        ]
    }
});

widgetRegistry.register('price-table', {
    title: 'Price Table',
    icon: '💰',
    category: 'marketing',
    controls: {
        content: [
            { name: 'title', type: 'text', label: 'Plan Name', default: 'Pro' },
            { name: 'price', type: 'text', label: 'Price', default: '$49' },
            { name: 'period', type: 'text', label: 'Period', default: '/month' },
            { name: 'features', type: 'textarea', label: 'Features (one per line)', default: '10 Projects\n50GB Storage\nPriority Support\nCustom Domain' },
            { name: 'button_text', type: 'text', label: 'Button Text', default: 'Get Started' },
            { name: 'button_link', type: 'url', label: 'Button Link' },
            { name: 'featured', type: 'switcher', label: 'Featured', default: false },
            { name: 'ribbon_text', type: 'text', label: 'Ribbon Text', default: 'Popular' }
        ],
        style: [
            { name: 'header_background', type: 'color', label: 'Header Background', default: '#4f46e5' },
            { name: 'header_color', type: 'color', label: 'Header Color', default: '#ffffff' },
            { name: 'price_color', type: 'color', label: 'Price Color', default: '#1f2937' },
            { name: 'features_color', type: 'color', label: 'Features Color', default: '#4b5563' },
            { name: 'button_background', type: 'color', label: 'Button Background', default: '#4f46e5' },
            { name: 'button_color', type: 'color', label: 'Button Color', default: '#ffffff' },
            { name: 'border', type: 'border', label: 'Border' },
            { name: 'box_shadow', type: 'box_shadow', label: 'Box Shadow' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' }
        ]
    }
});

// Form Builder Widget
widgetRegistry.register('form', {
    title: 'Form',
    icon: '📝',
    category: 'pro',
    controls: {
        content: [
            { name: 'form_name', type: 'text', label: 'Form Name', default: 'Contact Form' },
            { name: 'show_labels', type: 'switcher', label: 'Show Labels', default: true },
            { name: 'name_field', type: 'switcher', label: 'Name Field', default: true },
            { name: 'email_field', type: 'switcher', label: 'Email Field', default: true },
            { name: 'message_field', type: 'switcher', label: 'Message Field', default: true },
            { name: 'button_text', type: 'text', label: 'Button Text', default: 'Send Message' },
            { name: 'success_message', type: 'textarea', label: 'Success Message', default: 'Thank you! Your message has been sent.' }
        ],
        style: [
            { name: 'field_background', type: 'color', label: 'Field Background', default: '#ffffff' },
            { name: 'field_border', type: 'color', label: 'Field Border', default: '#d1d5db' },
            { name: 'field_text', type: 'color', label: 'Field Text', default: '#1f2937' },
            { name: 'button_background', type: 'color', label: 'Button Background', default: '#4f46e5' },
            { name: 'button_text', type: 'color', label: 'Button Text', default: '#ffffff' },
            { name: 'spacing', type: 'slider', label: 'Field Spacing', min: 0, max: 50, default: 16, unit: 'px' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' }
        ]
    }
});

// Slider/Carousel Widget
widgetRegistry.register('slider', {
    title: 'Slider',
    icon: '🎠',
    category: 'pro',
    controls: {
        content: [
            { name: 'slide1_image', type: 'media', label: 'Slide 1 Image' },
            { name: 'slide1_title', type: 'text', label: 'Slide 1 Title', default: 'First Slide' },
            { name: 'slide1_description', type: 'textarea', label: 'Slide 1 Description', default: 'This is the first slide content.' },
            { name: 'slide1_button', type: 'text', label: 'Slide 1 Button Text', default: 'Learn More' },
            { name: 'slide1_link', type: 'url', label: 'Slide 1 Button Link' },
            { name: 'slide2_image', type: 'media', label: 'Slide 2 Image' },
            { name: 'slide2_title', type: 'text', label: 'Slide 2 Title', default: 'Second Slide' },
            { name: 'slide2_description', type: 'textarea', label: 'Slide 2 Description', default: 'This is the second slide content.' },
            { name: 'slide2_button', type: 'text', label: 'Slide 2 Button Text', default: 'Learn More' },
            { name: 'slide2_link', type: 'url', label: 'Slide 2 Button Link' },
            { name: 'slide3_image', type: 'media', label: 'Slide 3 Image' },
            { name: 'slide3_title', type: 'text', label: 'Slide 3 Title', default: 'Third Slide' },
            { name: 'slide3_description', type: 'textarea', label: 'Slide 3 Description', default: 'This is the third slide content.' },
            { name: 'slide3_button', type: 'text', label: 'Slide 3 Button Text', default: 'Learn More' },
            { name: 'slide3_link', type: 'url', label: 'Slide 3 Button Link' },
            { name: 'autoplay', type: 'switcher', label: 'Autoplay', default: true },
            { name: 'autoplay_speed', type: 'slider', label: 'Autoplay Speed (ms)', min: 1000, max: 10000, default: 3000, unit: 'ms' },
            { name: 'show_arrows', type: 'switcher', label: 'Show Arrows', default: true },
            { name: 'show_dots', type: 'switcher', label: 'Show Dots', default: true }
        ],
        style: [
            { name: 'height', type: 'slider', label: 'Height', min: 200, max: 800, default: 500, unit: 'px' },
            { name: 'overlay_color', type: 'color', label: 'Overlay Color', default: 'rgba(0,0,0,0.3)' },
            { name: 'title_color', type: 'color', label: 'Title Color', default: '#ffffff' },
            { name: 'description_color', type: 'color', label: 'Description Color', default: '#f3f4f6' },
            { name: 'button_background', type: 'color', label: 'Button Background', default: '#4f46e5' },
            { name: 'button_color', type: 'color', label: 'Button Color', default: '#ffffff' },
            { name: 'arrows_color', type: 'color', label: 'Arrows Color', default: '#ffffff' },
            { name: 'dots_color', type: 'color', label: 'Dots Color', default: '#ffffff' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' }
        ]
    }
});
