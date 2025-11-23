/**
 * Elementor-Pro-Style Page Builder
 * Architecture: Section > Column > Widget
 * Features: Content/Style/Advanced tabs, Controls Registry, History, Templates
 */

// Controls Registry - All available control types
const ControlsRegistry = {
    types: {
        text: {
            render: (control, value, onChange) => `
                <div class="control-group">
                    <label class="control-label">${control.label}</label>
                    <input type="text"
                           value="${value || control.default || ''}"
                           placeholder="${control.placeholder || ''}"
                           @input="updateSetting('${control.name}', $event.target.value)"
                           class="control-input">
                    ${control.description ? `<p class="control-desc">${control.description}</p>` : ''}
                </div>
            `
        },
        textarea: {
            render: (control, value, onChange) => `
                <div class="control-group">
                    <label class="control-label">${control.label}</label>
                    <textarea rows="${control.rows || 4}"
                              placeholder="${control.placeholder || ''}"
                              @input="updateSetting('${control.name}', $event.target.value)"
                              class="control-textarea">${value || control.default || ''}</textarea>
                </div>
            `
        },
        wysiwyg: {
            render: (control, value, onChange) => `
                <div class="control-group">
                    <label class="control-label">${control.label}</label>
                    <div class="wysiwyg-toolbar">
                        <button type="button" @click="execCommand('bold')" class="wysiwyg-btn" title="Bold">B</button>
                        <button type="button" @click="execCommand('italic')" class="wysiwyg-btn" title="Italic">I</button>
                        <button type="button" @click="execCommand('underline')" class="wysiwyg-btn" title="Underline">U</button>
                    </div>
                    <div contenteditable="true"
                         @input="updateSetting('${control.name}', $event.target.innerHTML)"
                         class="wysiwyg-editor">${value || control.default || ''}</div>
                </div>
            `
        },
        number: {
            render: (control, value, onChange) => `
                <div class="control-group">
                    <label class="control-label">${control.label}</label>
                    <div class="number-control">
                        <input type="number"
                               value="${value || control.default || 0}"
                               min="${control.min ?? ''}"
                               max="${control.max ?? ''}"
                               step="${control.step || 1}"
                               @input="updateSetting('${control.name}', parseFloat($event.target.value))"
                               class="control-input">
                        ${control.unit ? `<span class="control-unit">${control.unit}</span>` : ''}
                    </div>
                </div>
            `
        },
        slider: {
            render: (control, value, onChange) => `
                <div class="control-group">
                    <label class="control-label">${control.label}</label>
                    <div class="slider-control">
                        <input type="range"
                               value="${value || control.default || 0}"
                               min="${control.min || 0}"
                               max="${control.max || 100}"
                               step="${control.step || 1}"
                               @input="updateSetting('${control.name}', parseFloat($event.target.value))"
                               class="control-slider">
                        <span class="slider-value">${value || control.default || 0}${control.unit || ''}</span>
                    </div>
                </div>
            `
        },
        select: {
            render: (control, value, onChange) => `
                <div class="control-group">
                    <label class="control-label">${control.label}</label>
                    <select @change="updateSetting('${control.name}', $event.target.value)" class="control-select">
                        ${Object.entries(control.options).map(([k, v]) =>
                            `<option value="${k}" ${(value || control.default) === k ? 'selected' : ''}>${v}</option>`
                        ).join('')}
                    </select>
                </div>
            `
        },
        switcher: {
            render: (control, value, onChange) => `
                <div class="control-group switcher-group">
                    <label class="control-label">${control.label}</label>
                    <button type="button"
                            @click="updateSetting('${control.name}', !getSetting('${control.name}'))"
                            :class="getSetting('${control.name}') ? 'switcher-on' : 'switcher-off'"
                            class="switcher-control">
                        <span class="switcher-toggle"></span>
                    </button>
                </div>
            `
        },
        choose: {
            render: (control, value, onChange) => `
                <div class="control-group">
                    <label class="control-label">${control.label}</label>
                    <div class="choose-control">
                        ${Object.entries(control.options).map(([k, opt]) => `
                            <button type="button"
                                    @click="updateSetting('${control.name}', '${k}')"
                                    :class="getSetting('${control.name}') === '${k}' ? 'choose-active' : ''"
                                    class="choose-btn"
                                    title="${opt.title}">
                                ${opt.icon}
                            </button>
                        `).join('')}
                    </div>
                </div>
            `
        },
        color: {
            render: (control, value, onChange) => `
                <div class="control-group">
                    <label class="control-label">${control.label}</label>
                    <div class="color-control">
                        <input type="color"
                               value="${value || control.default || '#000000'}"
                               @input="updateSetting('${control.name}', $event.target.value)"
                               class="color-picker">
                        <input type="text"
                               value="${value || control.default || '#000000'}"
                               @input="updateSetting('${control.name}', $event.target.value)"
                               class="color-input">
                    </div>
                </div>
            `
        },
        dimensions: {
            render: (control, value, onChange) => `
                <div class="control-group">
                    <label class="control-label">${control.label}</label>
                    <div class="dimensions-control">
                        <div class="dimension-inputs">
                            <div class="dimension-item">
                                <input type="number" placeholder="T"
                                       :value="getDimension('${control.name}', 'top')"
                                       @input="updateDimension('${control.name}', 'top', $event.target.value)">
                            </div>
                            <div class="dimension-item">
                                <input type="number" placeholder="R"
                                       :value="getDimension('${control.name}', 'right')"
                                       @input="updateDimension('${control.name}', 'right', $event.target.value)">
                            </div>
                            <div class="dimension-item">
                                <input type="number" placeholder="B"
                                       :value="getDimension('${control.name}', 'bottom')"
                                       @input="updateDimension('${control.name}', 'bottom', $event.target.value)">
                            </div>
                            <div class="dimension-item">
                                <input type="number" placeholder="L"
                                       :value="getDimension('${control.name}', 'left')"
                                       @input="updateDimension('${control.name}', 'left', $event.target.value)">
                            </div>
                        </div>
                        <button type="button"
                                @click="toggleLinked('${control.name}')"
                                :class="isLinked('${control.name}') ? 'linked' : ''"
                                class="link-btn">
                            <svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg>
                        </button>
                    </div>
                </div>
            `
        },
        media: {
            render: (control, value, onChange) => `
                <div class="control-group">
                    <label class="control-label">${control.label}</label>
                    <div class="media-control">
                        <div class="media-preview" x-show="getSetting('${control.name}')">
                            <img :src="getSetting('${control.name}')" alt="">
                            <button type="button" @click="updateSetting('${control.name}', '')" class="media-remove">×</button>
                        </div>
                        <button type="button" @click="openMediaLibrary('${control.name}')" class="media-select-btn">Select from Media</button>
                        <input type="url"
                               :value="getSetting('${control.name}')"
                               @input="updateSetting('${control.name}', $event.target.value)"
                               placeholder="Or enter URL"
                               class="control-input">
                    </div>
                </div>
            `
        },
        typography: {
            render: (control, value, onChange) => `
                <div class="control-group typography-control">
                    <label class="control-label">${control.label}</label>
                    <div class="typography-fields">
                        <select @change="updateTypography('${control.name}', 'family', $event.target.value)" class="typo-select">
                            <option value="">Default</option>
                            <option value="Inter">Inter</option>
                            <option value="Roboto">Roboto</option>
                            <option value="Open Sans">Open Sans</option>
                            <option value="Montserrat">Montserrat</option>
                            <option value="Poppins">Poppins</option>
                        </select>
                        <div class="typo-row">
                            <input type="number" placeholder="Size"
                                   :value="getTypography('${control.name}', 'size')"
                                   @input="updateTypography('${control.name}', 'size', $event.target.value)"
                                   class="typo-size">
                            <select @change="updateTypography('${control.name}', 'weight', $event.target.value)" class="typo-weight">
                                <option value="300">Light</option>
                                <option value="400">Normal</option>
                                <option value="500">Medium</option>
                                <option value="600">Semibold</option>
                                <option value="700">Bold</option>
                            </select>
                        </div>
                    </div>
                </div>
            `
        },
        background: {
            render: (control, value, onChange) => `
                <div class="control-group">
                    <label class="control-label">${control.label}</label>
                    <div class="background-control">
                        <div class="bg-type-tabs">
                            <button type="button" @click="setBgType('${control.name}', 'classic')"
                                    :class="getBgType('${control.name}') === 'classic' ? 'active' : ''">Classic</button>
                            <button type="button" @click="setBgType('${control.name}', 'gradient')"
                                    :class="getBgType('${control.name}') === 'gradient' ? 'active' : ''">Gradient</button>
                        </div>
                        <div x-show="getBgType('${control.name}') === 'classic'" class="bg-classic">
                            <div class="color-control">
                                <input type="color"
                                       :value="getBgColor('${control.name}')"
                                       @input="setBgColor('${control.name}', $event.target.value)">
                                <input type="text"
                                       :value="getBgColor('${control.name}')"
                                       @input="setBgColor('${control.name}', $event.target.value)"
                                       class="color-input">
                            </div>
                            <input type="url" placeholder="Image URL"
                                   :value="getBgImage('${control.name}')"
                                   @input="setBgImage('${control.name}', $event.target.value)"
                                   class="control-input mt-2">
                        </div>
                    </div>
                </div>
            `
        },
        border: {
            render: (control, value, onChange) => `
                <div class="control-group">
                    <label class="control-label">${control.label}</label>
                    <div class="border-control">
                        <select @change="updateBorder('${control.name}', 'style', $event.target.value)" class="border-style">
                            <option value="">None</option>
                            <option value="solid">Solid</option>
                            <option value="dashed">Dashed</option>
                            <option value="dotted">Dotted</option>
                        </select>
                        <input type="number" placeholder="Width"
                               :value="getBorderWidth('${control.name}')"
                               @input="updateBorder('${control.name}', 'width', $event.target.value)"
                               class="border-width">
                        <input type="color"
                               :value="getBorderColor('${control.name}')"
                               @input="updateBorder('${control.name}', 'color', $event.target.value)"
                               class="border-color">
                    </div>
                </div>
            `
        },
        boxShadow: {
            render: (control, value, onChange) => `
                <div class="control-group">
                    <label class="control-label">${control.label}</label>
                    <div class="shadow-control">
                        <div class="shadow-inputs">
                            <input type="number" placeholder="X" @input="updateShadow('${control.name}', 'x', $event.target.value)">
                            <input type="number" placeholder="Y" @input="updateShadow('${control.name}', 'y', $event.target.value)">
                            <input type="number" placeholder="Blur" @input="updateShadow('${control.name}', 'blur', $event.target.value)">
                            <input type="number" placeholder="Spread" @input="updateShadow('${control.name}', 'spread', $event.target.value)">
                        </div>
                        <input type="color" @input="updateShadow('${control.name}', 'color', $event.target.value)" class="shadow-color">
                    </div>
                </div>
            `
        },
        repeater: {
            render: (control, value, onChange) => `
                <div class="control-group">
                    <label class="control-label">${control.label}</label>
                    <div class="repeater-control">
                        <template x-for="(item, index) in getRepeaterItems('${control.name}')" :key="index">
                            <div class="repeater-item">
                                <div class="repeater-header" @click="toggleRepeaterItem(index)">
                                    <span x-text="item.${control.title_field || 'title'} || 'Item ' + (index + 1)"></span>
                                    <button type="button" @click.stop="removeRepeaterItem('${control.name}', index)">×</button>
                                </div>
                            </div>
                        </template>
                        <button type="button" @click="addRepeaterItem('${control.name}')" class="repeater-add">+ Add Item</button>
                    </div>
                </div>
            `
        }
    }
};

// Widget Registry - All available widgets
const WidgetRegistry = {
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
WidgetRegistry.register('heading', {
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
            { name: 'typography', type: 'typography', label: 'Typography' },
            { name: 'text_color', type: 'color', label: 'Text Color', default: '#1f2937' },
            { name: 'text_shadow', type: 'boxShadow', label: 'Text Shadow' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' },
            { name: 'css_classes', type: 'text', label: 'CSS Classes' },
            { name: 'css_id', type: 'text', label: 'CSS ID' }
        ]
    },
    render: (settings) => {
        const tag = settings.size || 'h2';
        return `<${tag} style="text-align: ${settings.alignment || 'left'}; color: ${settings.text_color || '#1f2937'};">${settings.title || 'Heading'}</${tag}>`;
    }
});

WidgetRegistry.register('text-editor', {
    title: 'Text Editor',
    icon: '¶',
    category: 'basic',
    controls: {
        content: [
            { name: 'editor', type: 'wysiwyg', label: 'Text Editor', default: '<p>Lorem ipsum dolor sit amet</p>' }
        ],
        style: [
            { name: 'typography', type: 'typography', label: 'Typography' },
            { name: 'text_color', type: 'color', label: 'Text Color', default: '#4b5563' },
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'left', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' },
                justify: { title: 'Justify', icon: '≡' }
            }}
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    },
    render: (settings) => {
        return `<div style="text-align: ${settings.alignment || 'left'}; color: ${settings.text_color || '#4b5563'};">${settings.editor || '<p>Text here</p>'}</div>`;
    }
});

WidgetRegistry.register('image', {
    title: 'Image',
    icon: '🖼',
    category: 'basic',
    controls: {
        content: [
            { name: 'image_url', type: 'media', label: 'Image' },
            { name: 'alt_text', type: 'text', label: 'Alt Text' },
            { name: 'caption', type: 'text', label: 'Caption' },
            { name: 'link_to', type: 'select', label: 'Link To', default: 'none', options: { none: 'None', custom: 'Custom URL', media: 'Media File' } },
            { name: 'custom_link', type: 'text', label: 'Custom URL', condition: { link_to: 'custom' } }
        ],
        style: [
            { name: 'width', type: 'slider', label: 'Width', min: 0, max: 100, default: 100, unit: '%' },
            { name: 'max_width', type: 'slider', label: 'Max Width', min: 0, max: 1200, unit: 'px' },
            { name: 'alignment', type: 'choose', label: 'Alignment', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }},
            { name: 'border_radius', type: 'dimensions', label: 'Border Radius' },
            { name: 'opacity', type: 'slider', label: 'Opacity', min: 0, max: 100, default: 100, unit: '%' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    },
    render: (settings) => {
        if (!settings.image_url) {
            return `<div class="image-placeholder">Click to add image</div>`;
        }
        return `<img src="${settings.image_url}" alt="${settings.alt_text || ''}" style="width: ${settings.width || 100}%; opacity: ${(settings.opacity || 100) / 100};">`;
    }
});

WidgetRegistry.register('button', {
    title: 'Button',
    icon: '▢',
    category: 'basic',
    controls: {
        content: [
            { name: 'text', type: 'text', label: 'Text', default: 'Click Me' },
            { name: 'link', type: 'text', label: 'Link', placeholder: 'https://' },
            { name: 'target', type: 'switcher', label: 'Open in new window' },
            { name: 'icon', type: 'text', label: 'Icon' },
            { name: 'icon_position', type: 'select', label: 'Icon Position', options: { left: 'Before', right: 'After' } }
        ],
        style: [
            { name: 'typography', type: 'typography', label: 'Typography' },
            { name: 'alignment', type: 'choose', label: 'Alignment', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' },
                justify: { title: 'Full', icon: '≡' }
            }},
            { name: 'background_color', type: 'color', label: 'Background', default: '#4f46e5' },
            { name: 'text_color', type: 'color', label: 'Text Color', default: '#ffffff' },
            { name: 'border', type: 'border', label: 'Border' },
            { name: 'border_radius', type: 'slider', label: 'Border Radius', min: 0, max: 50, default: 6, unit: 'px' },
            { name: 'padding', type: 'dimensions', label: 'Padding' },
            { name: 'hover_background', type: 'color', label: 'Hover Background', state: 'hover' },
            { name: 'hover_color', type: 'color', label: 'Hover Color', state: 'hover' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'width', type: 'select', label: 'Width', options: { auto: 'Auto', full: 'Full Width' } }
        ]
    },
    render: (settings) => {
        return `<div style="text-align: ${settings.alignment || 'left'};">
            <a href="${settings.link || '#'}" target="${settings.target ? '_blank' : '_self'}"
               style="display: inline-block; background: ${settings.background_color || '#4f46e5'}; color: ${settings.text_color || '#fff'}; padding: 12px 24px; border-radius: ${settings.border_radius || 6}px; text-decoration: none;">
                ${settings.text || 'Click Me'}
            </a>
        </div>`;
    }
});

WidgetRegistry.register('video', {
    title: 'Video',
    icon: '▶',
    category: 'basic',
    controls: {
        content: [
            { name: 'video_type', type: 'select', label: 'Source', default: 'youtube', options: { youtube: 'YouTube', vimeo: 'Vimeo', hosted: 'Self Hosted' } },
            { name: 'youtube_url', type: 'text', label: 'YouTube URL', condition: { video_type: 'youtube' } },
            { name: 'vimeo_url', type: 'text', label: 'Vimeo URL', condition: { video_type: 'vimeo' } },
            { name: 'autoplay', type: 'switcher', label: 'Autoplay' },
            { name: 'mute', type: 'switcher', label: 'Mute' },
            { name: 'loop', type: 'switcher', label: 'Loop' }
        ],
        style: [
            { name: 'aspect_ratio', type: 'select', label: 'Aspect Ratio', default: '16:9', options: { '16:9': '16:9', '4:3': '4:3', '21:9': '21:9' } },
            { name: 'width', type: 'slider', label: 'Width', min: 0, max: 100, default: 100, unit: '%' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    },
    render: (settings) => {
        if (!settings.youtube_url && !settings.vimeo_url) {
            return `<div class="video-placeholder">Add video URL</div>`;
        }
        let embedUrl = '';
        if (settings.video_type === 'youtube' && settings.youtube_url) {
            const match = settings.youtube_url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/);
            embedUrl = match ? `https://www.youtube.com/embed/${match[1]}` : '';
        }
        return `<div style="aspect-ratio: ${settings.aspect_ratio || '16/9'}; width: ${settings.width || 100}%;">
            <iframe src="${embedUrl}" style="width: 100%; height: 100%; border: none;" allowfullscreen></iframe>
        </div>`;
    }
});

WidgetRegistry.register('divider', {
    title: 'Divider',
    icon: '—',
    category: 'basic',
    controls: {
        content: [
            { name: 'style', type: 'select', label: 'Style', default: 'solid', options: { solid: 'Solid', dashed: 'Dashed', dotted: 'Dotted' } },
            { name: 'weight', type: 'select', label: 'Weight', default: 'thin', options: { thin: 'Thin', thick: 'Thick' } }
        ],
        style: [
            { name: 'color', type: 'color', label: 'Color', default: '#e5e7eb' },
            { name: 'width', type: 'slider', label: 'Width', min: 0, max: 100, default: 100, unit: '%' },
            { name: 'alignment', type: 'choose', label: 'Alignment', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }},
            { name: 'gap', type: 'slider', label: 'Gap', min: 0, max: 100, default: 20, unit: 'px' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' }
        ]
    },
    render: (settings) => {
        const thickness = settings.weight === 'thick' ? '3px' : '1px';
        return `<div style="padding: ${settings.gap || 20}px 0; text-align: ${settings.alignment || 'center'};">
            <hr style="border: none; border-top: ${thickness} ${settings.style || 'solid'} ${settings.color || '#e5e7eb'}; width: ${settings.width || 100}%; margin: 0 ${settings.alignment === 'center' ? 'auto' : '0'};">
        </div>`;
    }
});

WidgetRegistry.register('spacer', {
    title: 'Spacer',
    icon: '↕',
    category: 'basic',
    controls: {
        content: [
            { name: 'space', type: 'slider', label: 'Space', min: 0, max: 500, default: 50, unit: 'px' }
        ],
        style: [],
        advanced: []
    },
    render: (settings) => {
        return `<div style="height: ${settings.space || 50}px;"></div>`;
    }
});

// Icon widget
WidgetRegistry.register('icon', {
    title: 'Icon',
    icon: '★',
    category: 'basic',
    controls: {
        content: [
            { name: 'icon', type: 'text', label: 'Icon (emoji or text)', default: '★' },
            { name: 'link', type: 'text', label: 'Link' },
            { name: 'view', type: 'select', label: 'View', default: 'default', options: { default: 'Default', stacked: 'Stacked', framed: 'Framed' } }
        ],
        style: [
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'center', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }},
            { name: 'primary_color', type: 'color', label: 'Primary Color', default: '#4f46e5' },
            { name: 'size', type: 'slider', label: 'Size', min: 10, max: 200, default: 50, unit: 'px' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    },
    render: (settings) => {
        return `<div style="text-align: ${settings.alignment || 'center'}; font-size: ${settings.size || 50}px; color: ${settings.primary_color || '#4f46e5'};">${settings.icon || '★'}</div>`;
    }
});

// Icon Box widget
WidgetRegistry.register('icon-box', {
    title: 'Icon Box',
    icon: '◈',
    category: 'basic',
    controls: {
        content: [
            { name: 'icon', type: 'text', label: 'Icon', default: '⚡' },
            { name: 'title', type: 'text', label: 'Title', default: 'Icon Box' },
            { name: 'description', type: 'textarea', label: 'Description', default: 'Click here to add your own text and edit me.' },
            { name: 'link', type: 'text', label: 'Link' },
            { name: 'position', type: 'select', label: 'Icon Position', default: 'top', options: { left: 'Left', top: 'Top', right: 'Right' } }
        ],
        style: [
            { name: 'icon_color', type: 'color', label: 'Icon Color', default: '#4f46e5' },
            { name: 'icon_size', type: 'slider', label: 'Icon Size', min: 20, max: 100, default: 50, unit: 'px' },
            { name: 'title_color', type: 'color', label: 'Title Color', default: '#1f2937' },
            { name: 'desc_color', type: 'color', label: 'Description Color', default: '#6b7280' },
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
    },
    render: (settings) => {
        return `<div style="text-align: ${settings.alignment || 'center'}; padding: 20px;">
            <div style="font-size: ${settings.icon_size || 50}px; color: ${settings.icon_color || '#4f46e5'}; margin-bottom: 15px;">${settings.icon || '⚡'}</div>
            <h3 style="color: ${settings.title_color || '#1f2937'}; margin-bottom: 10px; font-size: 1.25rem; font-weight: 600;">${settings.title || 'Icon Box'}</h3>
            <p style="color: ${settings.desc_color || '#6b7280'}; font-size: 0.875rem;">${settings.description || ''}</p>
        </div>`;
    }
});

// Image Box widget
WidgetRegistry.register('image-box', {
    title: 'Image Box',
    icon: '🖼️',
    category: 'basic',
    controls: {
        content: [
            { name: 'image_url', type: 'media', label: 'Image' },
            { name: 'title', type: 'text', label: 'Title', default: 'Image Box' },
            { name: 'description', type: 'textarea', label: 'Description', default: 'Write a short description.' },
            { name: 'link', type: 'text', label: 'Link' }
        ],
        style: [
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'center', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }},
            { name: 'title_color', type: 'color', label: 'Title Color', default: '#1f2937' },
            { name: 'desc_color', type: 'color', label: 'Description Color', default: '#6b7280' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    },
    render: (settings) => {
        return `<div style="text-align: ${settings.alignment || 'center'};">
            ${settings.image_url ? `<img src="${settings.image_url}" style="max-width: 100%; height: auto; margin-bottom: 15px; border-radius: 8px;">` : '<div style="height: 150px; background: #f3f4f6; margin-bottom: 15px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #9ca3af;">Add Image</div>'}
            <h3 style="color: ${settings.title_color || '#1f2937'}; margin-bottom: 8px; font-size: 1.125rem; font-weight: 600;">${settings.title || 'Image Box'}</h3>
            <p style="color: ${settings.desc_color || '#6b7280'}; font-size: 0.875rem;">${settings.description || ''}</p>
        </div>`;
    }
});

// Counter widget
WidgetRegistry.register('counter', {
    title: 'Counter',
    icon: '123',
    category: 'basic',
    controls: {
        content: [
            { name: 'starting_number', type: 'number', label: 'Starting Number', default: 0 },
            { name: 'ending_number', type: 'number', label: 'Ending Number', default: 100 },
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
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    },
    render: (settings) => {
        return `<div style="text-align: ${settings.alignment || 'center'}; padding: 20px;">
            <div style="font-size: ${settings.number_size || 48}px; font-weight: 700; color: ${settings.number_color || '#4f46e5'};">
                ${settings.prefix || ''}${settings.ending_number || 100}${settings.suffix || ''}
            </div>
            <div style="color: ${settings.title_color || '#6b7280'}; margin-top: 8px;">${settings.title || ''}</div>
        </div>`;
    }
});

// Progress Bar widget
WidgetRegistry.register('progress-bar', {
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
            { name: 'bg_color', type: 'color', label: 'Background Color', default: '#e5e7eb' },
            { name: 'title_color', type: 'color', label: 'Title Color', default: '#1f2937' },
            { name: 'height', type: 'slider', label: 'Height', min: 4, max: 50, default: 12, unit: 'px' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    },
    render: (settings) => {
        return `<div style="padding: 10px 0;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span style="color: ${settings.title_color || '#1f2937'}; font-weight: 500;">${settings.title || 'Progress'}</span>
                ${settings.display_percent !== false ? `<span style="color: ${settings.title_color || '#1f2937'};">${settings.percent || 75}%</span>` : ''}
            </div>
            <div style="width: 100%; background: ${settings.bg_color || '#e5e7eb'}; border-radius: 999px; height: ${settings.height || 12}px; overflow: hidden;">
                <div style="width: ${settings.percent || 75}%; height: 100%; background: ${settings.bar_color || '#4f46e5'}; border-radius: 999px;"></div>
            </div>
        </div>`;
    }
});

// Testimonial widget
WidgetRegistry.register('testimonial', {
    title: 'Testimonial',
    icon: '💬',
    category: 'basic',
    controls: {
        content: [
            { name: 'content', type: 'textarea', label: 'Content', default: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis.' },
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
            { name: 'name_color', type: 'color', label: 'Name Color', default: '#1f2937' },
            { name: 'title_color', type: 'color', label: 'Title Color', default: '#6b7280' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    },
    render: (settings) => {
        return `<div style="text-align: ${settings.alignment || 'center'}; padding: 20px;">
            <div style="color: ${settings.content_color || '#4b5563'}; font-style: italic; margin-bottom: 20px; line-height: 1.6;">"${settings.content || ''}"</div>
            <div style="display: flex; align-items: center; justify-content: ${settings.alignment || 'center'}; gap: 12px;">
                ${settings.image_url ? `<img src="${settings.image_url}" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">` : ''}
                <div>
                    <div style="font-weight: 600; color: ${settings.name_color || '#1f2937'};">${settings.name || 'John Doe'}</div>
                    <div style="font-size: 0.875rem; color: ${settings.title_color || '#6b7280'};">${settings.title || 'Designer'}</div>
                </div>
            </div>
        </div>`;
    }
});

// Social Icons widget
WidgetRegistry.register('social-icons', {
    title: 'Social Icons',
    icon: '📱',
    category: 'basic',
    controls: {
        content: [
            { name: 'facebook', type: 'text', label: 'Facebook URL' },
            { name: 'twitter', type: 'text', label: 'Twitter URL' },
            { name: 'instagram', type: 'text', label: 'Instagram URL' },
            { name: 'linkedin', type: 'text', label: 'LinkedIn URL' },
            { name: 'youtube', type: 'text', label: 'YouTube URL' }
        ],
        style: [
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'center', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }},
            { name: 'icon_color', type: 'color', label: 'Icon Color', default: '#4b5563' },
            { name: 'icon_size', type: 'slider', label: 'Size', min: 16, max: 50, default: 24, unit: 'px' },
            { name: 'spacing', type: 'slider', label: 'Spacing', min: 0, max: 50, default: 10, unit: 'px' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    },
    render: (settings) => {
        const icons = [];
        if (settings.facebook) icons.push(`<a href="${settings.facebook}" target="_blank" style="color: ${settings.icon_color || '#4b5563'};">f</a>`);
        if (settings.twitter) icons.push(`<a href="${settings.twitter}" target="_blank" style="color: ${settings.icon_color || '#4b5563'};">𝕏</a>`);
        if (settings.instagram) icons.push(`<a href="${settings.instagram}" target="_blank" style="color: ${settings.icon_color || '#4b5563'};">📷</a>`);
        if (settings.linkedin) icons.push(`<a href="${settings.linkedin}" target="_blank" style="color: ${settings.icon_color || '#4b5563'};">in</a>`);
        if (settings.youtube) icons.push(`<a href="${settings.youtube}" target="_blank" style="color: ${settings.icon_color || '#4b5563'};">▶</a>`);
        return `<div style="text-align: ${settings.alignment || 'center'}; font-size: ${settings.icon_size || 24}px; display: flex; justify-content: ${settings.alignment || 'center'}; gap: ${settings.spacing || 10}px;">
            ${icons.length ? icons.join('') : '<span style="color: #9ca3af;">Add social links</span>'}
        </div>`;
    }
});

// Alert widget
WidgetRegistry.register('alert', {
    title: 'Alert',
    icon: '⚠',
    category: 'basic',
    controls: {
        content: [
            { name: 'title', type: 'text', label: 'Title', default: 'This is an Alert' },
            { name: 'content', type: 'textarea', label: 'Content', default: 'I am a description. Click the edit button to change this text.' },
            { name: 'alert_type', type: 'select', label: 'Type', default: 'info', options: { info: 'Info', success: 'Success', warning: 'Warning', danger: 'Danger' } },
            { name: 'show_icon', type: 'switcher', label: 'Show Icon', default: true },
            { name: 'dismissible', type: 'switcher', label: 'Dismissible' }
        ],
        style: [
            { name: 'typography', type: 'typography', label: 'Typography' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    },
    render: (settings) => {
        const colors = {
            info: { bg: '#eff6ff', border: '#3b82f6', text: '#1e40af', icon: 'ℹ' },
            success: { bg: '#f0fdf4', border: '#22c55e', text: '#166534', icon: '✓' },
            warning: { bg: '#fffbeb', border: '#f59e0b', text: '#92400e', icon: '⚠' },
            danger: { bg: '#fef2f2', border: '#ef4444', text: '#991b1b', icon: '✕' }
        };
        const c = colors[settings.alert_type] || colors.info;
        return `<div style="padding: 16px; background: ${c.bg}; border-left: 4px solid ${c.border}; border-radius: 4px;">
            <div style="display: flex; align-items: start; gap: 12px;">
                ${settings.show_icon !== false ? `<span style="color: ${c.border}; font-size: 1.25rem;">${c.icon}</span>` : ''}
                <div>
                    <div style="font-weight: 600; color: ${c.text}; margin-bottom: 4px;">${settings.title || ''}</div>
                    <div style="color: ${c.text}; opacity: 0.9;">${settings.content || ''}</div>
                </div>
            </div>
        </div>`;
    }
});

// HTML widget
WidgetRegistry.register('html', {
    title: 'HTML',
    icon: '</>',
    category: 'basic',
    controls: {
        content: [
            { name: 'html', type: 'textarea', label: 'HTML Code', rows: 10, default: '<p>Your custom HTML here</p>' }
        ],
        style: [],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    },
    render: (settings) => {
        return settings.html || '<p>Add your HTML</p>';
    }
});

// Google Maps widget
WidgetRegistry.register('google-maps', {
    title: 'Google Maps',
    icon: '📍',
    category: 'basic',
    controls: {
        content: [
            { name: 'address', type: 'text', label: 'Address', default: 'London Eye, London' },
            { name: 'zoom', type: 'slider', label: 'Zoom', min: 1, max: 20, default: 10 }
        ],
        style: [
            { name: 'height', type: 'slider', label: 'Height', min: 100, max: 800, default: 300, unit: 'px' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    },
    render: (settings) => {
        const address = encodeURIComponent(settings.address || 'London');
        return `<div style="height: ${settings.height || 300}px; background: #f3f4f6; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6b7280;">
            <div style="text-align: center;">
                <div style="font-size: 2rem; margin-bottom: 8px;">📍</div>
                <div>${settings.address || 'London'}</div>
                <div style="font-size: 0.75rem; margin-top: 4px;">Map placeholder</div>
            </div>
        </div>`;
    }
});

// Star Rating widget
WidgetRegistry.register('star-rating', {
    title: 'Star Rating',
    icon: '⭐',
    category: 'basic',
    controls: {
        content: [
            { name: 'rating', type: 'slider', label: 'Rating', min: 0, max: 5, step: 0.5, default: 4 },
            { name: 'scale', type: 'number', label: 'Scale', min: 1, max: 10, default: 5 }
        ],
        style: [
            { name: 'star_color', type: 'color', label: 'Star Color', default: '#fbbf24' },
            { name: 'empty_color', type: 'color', label: 'Empty Color', default: '#e5e7eb' },
            { name: 'size', type: 'slider', label: 'Size', min: 10, max: 60, default: 24, unit: 'px' },
            { name: 'alignment', type: 'choose', label: 'Alignment', default: 'center', options: {
                left: { title: 'Left', icon: '⬅' },
                center: { title: 'Center', icon: '⬌' },
                right: { title: 'Right', icon: '➡' }
            }}
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' }
        ]
    },
    render: (settings) => {
        const rating = settings.rating || 4;
        const scale = settings.scale || 5;
        let stars = '';
        for (let i = 1; i <= scale; i++) {
            stars += `<span style="color: ${i <= rating ? settings.star_color || '#fbbf24' : settings.empty_color || '#e5e7eb'};">★</span>`;
        }
        return `<div style="text-align: ${settings.alignment || 'center'}; font-size: ${settings.size || 24}px;">${stars}</div>`;
    }
});

// Accordion widget
WidgetRegistry.register('accordion', {
    title: 'Accordion',
    icon: '☰',
    category: 'basic',
    controls: {
        content: [
            { name: 'item_1_title', type: 'text', label: 'Item 1 Title', default: 'Accordion #1' },
            { name: 'item_1_content', type: 'textarea', label: 'Item 1 Content', default: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' },
            { name: 'item_2_title', type: 'text', label: 'Item 2 Title', default: 'Accordion #2' },
            { name: 'item_2_content', type: 'textarea', label: 'Item 2 Content', default: 'Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.' },
            { name: 'item_3_title', type: 'text', label: 'Item 3 Title', default: 'Accordion #3' },
            { name: 'item_3_content', type: 'textarea', label: 'Item 3 Content', default: 'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.' }
        ],
        style: [
            { name: 'border_color', type: 'color', label: 'Border Color', default: '#e5e7eb' },
            { name: 'title_color', type: 'color', label: 'Title Color', default: '#1f2937' },
            { name: 'content_color', type: 'color', label: 'Content Color', default: '#4b5563' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    },
    render: (settings) => {
        let items = '';
        for (let i = 1; i <= 3; i++) {
            const title = settings[`item_${i}_title`];
            const content = settings[`item_${i}_content`];
            if (title) {
                items += `<div style="border: 1px solid ${settings.border_color || '#e5e7eb'}; margin-bottom: -1px;">
                    <div style="padding: 15px; font-weight: 600; color: ${settings.title_color || '#1f2937'}; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                        <span>${title}</span>
                        <span>+</span>
                    </div>
                    <div style="padding: 15px; border-top: 1px solid ${settings.border_color || '#e5e7eb'}; color: ${settings.content_color || '#4b5563'}; display: ${i === 1 ? 'block' : 'none'};">${content || ''}</div>
                </div>`;
            }
        }
        return `<div>${items}</div>`;
    }
});

// Blockquote widget
WidgetRegistry.register('blockquote', {
    title: 'Blockquote',
    icon: '"',
    category: 'basic',
    controls: {
        content: [
            { name: 'content', type: 'textarea', label: 'Content', default: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.' },
            { name: 'author', type: 'text', label: 'Author', default: 'John Doe' }
        ],
        style: [
            { name: 'border_color', type: 'color', label: 'Border Color', default: '#4f46e5' },
            { name: 'content_color', type: 'color', label: 'Content Color', default: '#4b5563' },
            { name: 'author_color', type: 'color', label: 'Author Color', default: '#6b7280' },
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
    },
    render: (settings) => {
        return `<blockquote style="border-left: 4px solid ${settings.border_color || '#4f46e5'}; padding-left: 20px; margin: 20px 0; text-align: ${settings.alignment || 'left'};">
            <p style="color: ${settings.content_color || '#4b5563'}; font-style: italic; margin-bottom: 10px; font-size: 1.125rem; line-height: 1.75;">${settings.content || ''}</p>
            ${settings.author ? `<cite style="color: ${settings.author_color || '#6b7280'}; font-size: 0.875rem;">— ${settings.author}</cite>` : ''}
        </blockquote>`;
    }
});

// Icon List widget
WidgetRegistry.register('icon-list', {
    title: 'Icon List',
    icon: '•',
    category: 'basic',
    controls: {
        content: [
            { name: 'item_1', type: 'text', label: 'Item 1', default: 'List Item #1' },
            { name: 'item_2', type: 'text', label: 'Item 2', default: 'List Item #2' },
            { name: 'item_3', type: 'text', label: 'Item 3', default: 'List Item #3' },
            { name: 'item_4', type: 'text', label: 'Item 4' },
            { name: 'item_5', type: 'text', label: 'Item 5' },
            { name: 'icon', type: 'text', label: 'Icon', default: '✓' }
        ],
        style: [
            { name: 'icon_color', type: 'color', label: 'Icon Color', default: '#22c55e' },
            { name: 'text_color', type: 'color', label: 'Text Color', default: '#4b5563' },
            { name: 'spacing', type: 'slider', label: 'Spacing', min: 0, max: 30, default: 10, unit: 'px' }
        ],
        advanced: [
            { name: 'margin', type: 'dimensions', label: 'Margin' },
            { name: 'padding', type: 'dimensions', label: 'Padding' }
        ]
    },
    render: (settings) => {
        let items = '';
        for (let i = 1; i <= 5; i++) {
            const item = settings[`item_${i}`];
            if (item) {
                items += `<li style="display: flex; align-items: center; gap: 10px; margin-bottom: ${settings.spacing || 10}px;">
                    <span style="color: ${settings.icon_color || '#22c55e'};">${settings.icon || '✓'}</span>
                    <span style="color: ${settings.text_color || '#4b5563'};">${item}</span>
                </li>`;
            }
        }
        return `<ul style="list-style: none; padding: 0; margin: 0;">${items}</ul>`;
    }
});

// Main Builder Application
function builderApp() {
    return {
        // Document data
        documentId: null,
        documentTitle: '',
        documentSettings: {},
        content: [], // Array of sections

        // UI State
        selectedElement: null,
        selectedType: null, // 'section', 'column', 'widget'
        activeTab: 'content',
        leftPanel: 'widgets', // 'widgets', 'navigator'

        // History
        history: [],
        historyIndex: -1,

        // Clipboard
        clipboard: null,

        // Modals
        showSettings: false,
        showDeleteConfirm: false,
        showTemplates: false,
        showRevisions: false,

        // State
        isDirty: false,
        isSaving: false,
        lastSaved: null,
        previewMode: 'desktop',

        // Context menu
        contextMenu: { show: false, x: 0, y: 0, element: null },

        // Search
        widgetSearch: '',

        // Media Library
        showMediaLibrary: false,
        mediaItems: [],
        mediaLoading: false,
        mediaControlName: null,

        // Sortable instances for cleanup
        sortableInstances: [],

        // Debounce timer
        saveDebounce: null,

        // Widgets list
        get filteredWidgets() {
            const widgets = WidgetRegistry.getAll();
            if (!this.widgetSearch) return widgets;
            const search = this.widgetSearch.toLowerCase();
            return Object.fromEntries(
                Object.entries(widgets).filter(([key, w]) =>
                    w.title.toLowerCase().includes(search) || key.includes(search)
                )
            );
        },

        // Initialize
        init() {
            const pageData = JSON.parse(document.getElementById('page-data').textContent);
            this.documentId = pageData.id;
            this.documentTitle = pageData.title || '';
            this.documentSettings = pageData.settings || {};

            // Convert old flat structure to sections if needed
            if (Array.isArray(pageData.elements) && pageData.elements.length > 0) {
                // If already has sections
                if (pageData.elements[0]?.elType === 'section') {
                    this.content = pageData.elements;
                } else {
                    // Convert old widgets to section>column>widget structure
                    this.content = [{
                        id: this.generateId(),
                        elType: 'section',
                        settings: {},
                        elements: [{
                            id: this.generateId(),
                            elType: 'column',
                            settings: { _column_size: 100 },
                            elements: pageData.elements.map(el => ({
                                id: el.id || this.generateId(),
                                elType: 'widget',
                                widgetType: el.type,
                                settings: { ...el.props, ...el.styles }
                            }))
                        }]
                    }];
                }
            } else {
                this.content = pageData.elements || [];
            }

            this.initSortable();
            this.initKeyboardShortcuts();
            this.initContextMenu();
            this.initAutosave();
            this.addToHistory();
        },

        // Generate unique ID
        generateId() {
            return Math.random().toString(36).substr(2, 9);
        },

        // Add section
        addSection(layout = '100') {
            const columns = layout.split('-').map(size => ({
                id: this.generateId(),
                elType: 'column',
                settings: { _column_size: parseInt(size) },
                elements: []
            }));

            const section = {
                id: this.generateId(),
                elType: 'section',
                settings: {
                    structure: layout,
                    content_width: 'boxed'
                },
                elements: columns
            };

            this.addToHistory();
            this.content.push(section);
            this.selectElement(section.id, 'section');
            this.isDirty = true;
            this.$nextTick(() => this.initSortable());
        },

        // Add widget to column
        addWidget(widgetType, columnId) {
            const widget = {
                id: this.generateId(),
                elType: 'widget',
                widgetType: widgetType,
                settings: this.getWidgetDefaults(widgetType)
            };

            const column = this.findElement(columnId);
            if (column) {
                this.addToHistory();
                column.elements.push(widget);
                this.selectElement(widget.id, 'widget');
                this.isDirty = true;
                this.$nextTick(() => this.initSortable());
            }
        },

        // Get widget default settings
        getWidgetDefaults(widgetType) {
            const widget = WidgetRegistry.get(widgetType);
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
        },

        // Find element by ID recursively
        findElement(id, elements = this.content) {
            for (const el of elements) {
                if (el.id === id) return el;
                if (el.elements) {
                    const found = this.findElement(id, el.elements);
                    if (found) return found;
                }
            }
            return null;
        },

        // Find parent of element
        findParent(id, elements = this.content, parent = null) {
            for (const el of elements) {
                if (el.id === id) return parent;
                if (el.elements) {
                    const found = this.findParent(id, el.elements, el);
                    if (found !== undefined) return found;
                }
            }
            return undefined;
        },

        // Select element
        selectElement(id, type) {
            this.selectedElement = id;
            this.selectedType = type;
            this.activeTab = 'content';
        },

        // Get selected element
        getSelectedElement() {
            if (!this.selectedElement) return null;
            return this.findElement(this.selectedElement);
        },

        // Get current setting value
        getSetting(name) {
            const el = this.getSelectedElement();
            return el?.settings?.[name];
        },

        // Update setting with reactivity trigger
        updateSetting(name, value) {
            const el = this.getSelectedElement();
            if (el) {
                if (!el.settings) el.settings = {};
                el.settings[name] = value;
                this.isDirty = true;
                // Force Alpine reactivity by triggering content update
                this.content = [...this.content];
            }
        },

        // Get controls for selected element
        getControls() {
            const el = this.getSelectedElement();
            if (!el) return [];

            if (el.elType === 'widget') {
                const widget = WidgetRegistry.get(el.widgetType);
                return widget?.controls?.[this.activeTab] || [];
            }

            // Section/Column controls
            if (el.elType === 'section') {
                return this.getSectionControls()[this.activeTab] || [];
            }

            if (el.elType === 'column') {
                return this.getColumnControls()[this.activeTab] || [];
            }

            return [];
        },

        // Section controls
        getSectionControls() {
            return {
                content: [
                    { name: 'structure', type: 'select', label: 'Structure', options: {
                        '100': '1 Column',
                        '50-50': '2 Columns (50/50)',
                        '33-33-33': '3 Columns',
                        '25-50-25': '3 Columns (25/50/25)',
                        '25-25-25-25': '4 Columns'
                    }},
                    { name: 'content_width', type: 'select', label: 'Content Width', options: { boxed: 'Boxed', full: 'Full Width' } },
                    { name: 'height', type: 'select', label: 'Height', options: { default: 'Default', min: 'Min Height', vh: 'Fit to Screen' } },
                    { name: 'min_height', type: 'slider', label: 'Min Height', min: 0, max: 1000, unit: 'px' }
                ],
                style: [
                    { name: 'background', type: 'background', label: 'Background' },
                    { name: 'border', type: 'border', label: 'Border' },
                    { name: 'border_radius', type: 'dimensions', label: 'Border Radius' },
                    { name: 'box_shadow', type: 'boxShadow', label: 'Box Shadow' }
                ],
                advanced: [
                    { name: 'margin', type: 'dimensions', label: 'Margin' },
                    { name: 'padding', type: 'dimensions', label: 'Padding' },
                    { name: 'z_index', type: 'number', label: 'Z-Index' },
                    { name: 'css_classes', type: 'text', label: 'CSS Classes' },
                    { name: 'css_id', type: 'text', label: 'CSS ID' },
                    { name: 'hide_desktop', type: 'switcher', label: 'Hide on Desktop' },
                    { name: 'hide_tablet', type: 'switcher', label: 'Hide on Tablet' },
                    { name: 'hide_mobile', type: 'switcher', label: 'Hide on Mobile' }
                ]
            };
        },

        // Column controls
        getColumnControls() {
            return {
                content: [
                    { name: '_column_size', type: 'slider', label: 'Column Width', min: 0, max: 100, unit: '%' },
                    { name: 'vertical_align', type: 'select', label: 'Vertical Align', options: { top: 'Top', middle: 'Middle', bottom: 'Bottom' } }
                ],
                style: [
                    { name: 'background', type: 'background', label: 'Background' },
                    { name: 'border', type: 'border', label: 'Border' },
                    { name: 'border_radius', type: 'dimensions', label: 'Border Radius' }
                ],
                advanced: [
                    { name: 'margin', type: 'dimensions', label: 'Margin' },
                    { name: 'padding', type: 'dimensions', label: 'Padding' },
                    { name: 'css_classes', type: 'text', label: 'CSS Classes' }
                ]
            };
        },

        // Delete element
        deleteElement(id) {
            this.addToHistory();
            this.deleteRecursive(id, this.content);
            if (this.selectedElement === id) {
                this.selectedElement = null;
                this.selectedType = null;
            }
            this.isDirty = true;
            this.$nextTick(() => this.initSortable());
        },

        deleteRecursive(id, elements) {
            const index = elements.findIndex(el => el.id === id);
            if (index !== -1) {
                elements.splice(index, 1);
                return true;
            }
            for (const el of elements) {
                if (el.elements && this.deleteRecursive(id, el.elements)) {
                    return true;
                }
            }
            return false;
        },

        // Duplicate element
        duplicateElement(id) {
            const el = this.findElement(id);
            const parent = this.findParent(id);
            if (!el || !parent?.elements) return;

            this.addToHistory();
            const duplicate = JSON.parse(JSON.stringify(el));
            this.regenerateIds(duplicate);

            const index = parent.elements.findIndex(e => e.id === id);
            parent.elements.splice(index + 1, 0, duplicate);
            this.selectElement(duplicate.id, duplicate.elType);
            this.isDirty = true;
            this.$nextTick(() => this.initSortable());
        },

        regenerateIds(el) {
            el.id = this.generateId();
            if (el.elements) {
                el.elements.forEach(child => this.regenerateIds(child));
            }
        },

        // Render element preview
        renderElement(element) {
            if (element.elType === 'widget') {
                const widget = WidgetRegistry.get(element.widgetType);
                if (widget?.render) {
                    return widget.render(element.settings || {});
                }
                return `<div class="unknown-widget">Unknown widget: ${element.widgetType}</div>`;
            }
            return '';
        },

        // History management
        addToHistory() {
            const state = JSON.stringify({ content: this.content, settings: this.documentSettings });
            this.history = this.history.slice(0, this.historyIndex + 1);
            this.history.push(state);
            if (this.history.length > 50) this.history.shift();
            this.historyIndex = this.history.length - 1;
        },

        undo() {
            if (this.historyIndex <= 0) return;
            this.historyIndex--;
            const state = JSON.parse(this.history[this.historyIndex]);
            this.content = state.content;
            this.documentSettings = state.settings;
            this.isDirty = true;
            this.$nextTick(() => this.initSortable());
        },

        redo() {
            if (this.historyIndex >= this.history.length - 1) return;
            this.historyIndex++;
            const state = JSON.parse(this.history[this.historyIndex]);
            this.content = state.content;
            this.documentSettings = state.settings;
            this.isDirty = true;
            this.$nextTick(() => this.initSortable());
        },

        // Clipboard
        copy() {
            const el = this.getSelectedElement();
            if (el) {
                this.clipboard = JSON.parse(JSON.stringify(el));
            }
        },

        paste() {
            if (!this.clipboard) return;
            const pasted = JSON.parse(JSON.stringify(this.clipboard));
            this.regenerateIds(pasted);

            // Paste based on element type
            if (pasted.elType === 'section') {
                this.addToHistory();
                this.content.push(pasted);
            } else if (pasted.elType === 'widget') {
                // Find column to paste into
                const selected = this.getSelectedElement();
                let column = null;
                if (selected?.elType === 'column') {
                    column = selected;
                } else if (selected?.elType === 'widget') {
                    column = this.findParent(selected.id);
                }
                if (column?.elements) {
                    this.addToHistory();
                    column.elements.push(pasted);
                }
            }

            this.selectElement(pasted.id, pasted.elType);
            this.isDirty = true;
            this.$nextTick(() => this.initSortable());
        },

        cut() {
            this.copy();
            if (this.selectedElement) {
                this.deleteElement(this.selectedElement);
            }
        },

        // Initialize SortableJS
        initSortable() {
            // Destroy existing instances first
            this.sortableInstances.forEach(instance => {
                if (instance && instance.destroy) {
                    instance.destroy();
                }
            });
            this.sortableInstances = [];

            // Sections sortable
            const sectionsContainer = document.querySelector('[data-sections]');
            if (sectionsContainer) {
                const sectionsSortable = new Sortable(sectionsContainer, {
                    group: 'sections',
                    animation: 200,
                    handle: '.section-handle',
                    ghostClass: 'sortable-ghost',
                    onEnd: (evt) => {
                        this.addToHistory();
                        const [moved] = this.content.splice(evt.oldIndex, 1);
                        this.content.splice(evt.newIndex, 0, moved);
                        this.isDirty = true;
                    }
                });
                this.sortableInstances.push(sectionsSortable);
            }

            // Columns in each section
            document.querySelectorAll('[data-columns]').forEach(container => {
                const columnsSortable = new Sortable(container, {
                    group: 'columns',
                    animation: 200,
                    handle: '.column-handle',
                    ghostClass: 'sortable-ghost'
                });
                this.sortableInstances.push(columnsSortable);
            });

            // Widgets in each column
            document.querySelectorAll('[data-widgets]').forEach(container => {
                const widgetsSortable = new Sortable(container, {
                    group: 'widgets',
                    animation: 200,
                    handle: '.widget-handle',
                    ghostClass: 'sortable-ghost',
                    onAdd: (evt) => {
                        const widgetType = evt.item.dataset.widgetType;
                        const columnId = evt.to.dataset.columnId;
                        if (widgetType && columnId) {
                            evt.item.remove();
                            this.addWidget(widgetType, columnId);
                        }
                    },
                    onSort: (evt) => {
                        // Reorder widgets within the column
                        const columnId = evt.to.dataset.columnId;
                        const column = this.findElement(columnId);
                        if (column && column.elements && evt.oldIndex !== evt.newIndex) {
                            this.addToHistory();
                            const [moved] = column.elements.splice(evt.oldIndex, 1);
                            column.elements.splice(evt.newIndex, 0, moved);
                            this.isDirty = true;
                        }
                    }
                });
                this.sortableInstances.push(widgetsSortable);
            });

            // Widgets panel (drag source)
            const widgetsPanel = document.querySelector('[data-widget-palette]');
            if (widgetsPanel) {
                const paletteSortable = new Sortable(widgetsPanel, {
                    group: { name: 'widgets', pull: 'clone', put: false },
                    sort: false,
                    animation: 200,
                    ghostClass: 'sortable-ghost'
                });
                this.sortableInstances.push(paletteSortable);
            }
        },

        // Keyboard shortcuts
        initKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                const isCmd = e.ctrlKey || e.metaKey;

                if (isCmd && e.key === 's') {
                    e.preventDefault();
                    this.save();
                }
                if (isCmd && e.key === 'z' && !e.shiftKey) {
                    e.preventDefault();
                    this.undo();
                }
                if (isCmd && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) {
                    e.preventDefault();
                    this.redo();
                }
                if (isCmd && e.key === 'c' && this.selectedElement) {
                    e.preventDefault();
                    this.copy();
                }
                if (isCmd && e.key === 'v') {
                    e.preventDefault();
                    this.paste();
                }
                if (isCmd && e.key === 'x' && this.selectedElement) {
                    e.preventDefault();
                    this.cut();
                }
                if (isCmd && e.key === 'd' && this.selectedElement) {
                    e.preventDefault();
                    this.duplicateElement(this.selectedElement);
                }
                if ((e.key === 'Delete' || e.key === 'Backspace') && this.selectedElement && !['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) && !e.target.isContentEditable) {
                    e.preventDefault();
                    this.deleteElement(this.selectedElement);
                }
                if (e.key === 'Escape') {
                    this.selectedElement = null;
                    this.contextMenu.show = false;
                }
            });
        },

        // Context menu
        initContextMenu() {
            document.addEventListener('contextmenu', (e) => {
                const element = e.target.closest('[data-element-id]');
                if (element) {
                    e.preventDefault();
                    this.contextMenu = {
                        show: true,
                        x: e.clientX,
                        y: e.clientY,
                        element: element.dataset.elementId
                    };
                    this.selectElement(element.dataset.elementId, element.dataset.elementType);
                }
            });

            document.addEventListener('click', () => {
                this.contextMenu.show = false;
            });
        },

        // Autosave
        initAutosave() {
            setInterval(() => {
                if (this.isDirty && !this.isSaving) {
                    this.autosave();
                }
            }, 30000);
        },

        // Save
        async save() {
            if (this.isSaving) return;
            this.isSaving = true;

            try {
                const response = await fetch(`/builder/${this.documentId}/save`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        title: this.documentTitle,
                        content: this.content,
                        settings: this.documentSettings
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

        // Autosave
        async autosave() {
            try {
                await fetch(`/builder/${this.documentId}/autosave`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        content: this.content,
                        settings: this.documentSettings
                    })
                });
            } catch (error) {
                console.error('Autosave error:', error);
            }
        },

        // Publish
        async publish() {
            await this.save();

            try {
                const response = await fetch(`/builder/${this.documentId}/publish`, {
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

        // Delete page
        async deletePage() {
            try {
                const response = await fetch(`/api/pages/${this.documentId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    window.location.href = '/pages';
                }
            } catch (error) {
                console.error('Delete error:', error);
            }
            this.showDeleteConfirm = false;
        },

        // Preview
        preview() {
            window.open(`/builder/${this.documentId}/preview`, '_blank');
        },

        // Export
        exportJSON() {
            const data = {
                version: '1.0',
                title: this.documentTitle,
                settings: this.documentSettings,
                content: this.content
            };
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${this.documentTitle || 'page'}.json`;
            a.click();
            URL.revokeObjectURL(url);
        },

        // Import
        importJSON(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const data = JSON.parse(e.target.result);
                    this.addToHistory();
                    this.documentTitle = data.title || this.documentTitle;
                    this.documentSettings = data.settings || {};
                    this.content = data.content || [];
                    this.isDirty = true;
                    this.$nextTick(() => this.initSortable());
                } catch (error) {
                    alert('Invalid JSON file');
                }
            };
            reader.readAsText(file);
        },

        // Helper methods for controls
        getDimension(name, side) {
            const el = this.getSelectedElement();
            return el?.settings?.[name]?.[side] || 0;
        },

        updateDimension(name, side, value) {
            const el = this.getSelectedElement();
            if (!el) return;
            if (!el.settings) el.settings = {};
            if (!el.settings[name]) el.settings[name] = { top: 0, right: 0, bottom: 0, left: 0, isLinked: false };

            if (el.settings[name].isLinked) {
                el.settings[name].top = value;
                el.settings[name].right = value;
                el.settings[name].bottom = value;
                el.settings[name].left = value;
            } else {
                el.settings[name][side] = value;
            }
            this.isDirty = true;
            this.content = [...this.content];
        },

        isLinked(name) {
            const el = this.getSelectedElement();
            return el?.settings?.[name]?.isLinked || false;
        },

        toggleLinked(name) {
            const el = this.getSelectedElement();
            if (!el?.settings?.[name]) return;
            el.settings[name].isLinked = !el.settings[name].isLinked;
            this.isDirty = true;
            this.content = [...this.content];
        },

        getTypography(name, field) {
            const el = this.getSelectedElement();
            return el?.settings?.[name]?.[field] || '';
        },

        updateTypography(name, field, value) {
            const el = this.getSelectedElement();
            if (!el) return;
            if (!el.settings) el.settings = {};
            if (!el.settings[name]) el.settings[name] = {};
            el.settings[name][field] = value;
            this.isDirty = true;
            this.content = [...this.content];
        },

        getBgType(name) {
            const el = this.getSelectedElement();
            return el?.settings?.[name]?.type || 'classic';
        },

        setBgType(name, type) {
            const el = this.getSelectedElement();
            if (!el) return;
            if (!el.settings) el.settings = {};
            if (!el.settings[name]) el.settings[name] = {};
            el.settings[name].type = type;
            this.isDirty = true;
            this.content = [...this.content];
        },

        getBgColor(name) {
            const el = this.getSelectedElement();
            return el?.settings?.[name]?.color || '#ffffff';
        },

        setBgColor(name, color) {
            const el = this.getSelectedElement();
            if (!el) return;
            if (!el.settings) el.settings = {};
            if (!el.settings[name]) el.settings[name] = {};
            el.settings[name].color = color;
            this.isDirty = true;
            this.content = [...this.content];
        },

        getBgImage(name) {
            const el = this.getSelectedElement();
            return el?.settings?.[name]?.image || '';
        },

        setBgImage(name, url) {
            const el = this.getSelectedElement();
            if (!el) return;
            if (!el.settings) el.settings = {};
            if (!el.settings[name]) el.settings[name] = {};
            el.settings[name].image = url;
            this.isDirty = true;
            this.content = [...this.content];
        },

        updateBorder(name, field, value) {
            const el = this.getSelectedElement();
            if (!el) return;
            if (!el.settings) el.settings = {};
            if (!el.settings[name]) el.settings[name] = {};
            el.settings[name][field] = value;
            this.isDirty = true;
            this.content = [...this.content];
        },

        getBorderWidth(name) {
            return this.getSelectedElement()?.settings?.[name]?.width || 0;
        },

        getBorderColor(name) {
            return this.getSelectedElement()?.settings?.[name]?.color || '#000000';
        },

        updateShadow(name, field, value) {
            const el = this.getSelectedElement();
            if (!el) return;
            if (!el.settings) el.settings = {};
            if (!el.settings[name]) el.settings[name] = {};
            el.settings[name][field] = value;
            this.isDirty = true;
            this.content = [...this.content];
        },

        getRepeaterItems(name) {
            return this.getSelectedElement()?.settings?.[name] || [];
        },

        addRepeaterItem(name) {
            const el = this.getSelectedElement();
            if (!el) return;
            if (!el.settings) el.settings = {};
            if (!el.settings[name]) el.settings[name] = [];
            el.settings[name].push({ title: '' });
            this.isDirty = true;
            this.content = [...this.content];
        },

        removeRepeaterItem(name, index) {
            const el = this.getSelectedElement();
            if (!el?.settings?.[name]) return;
            el.settings[name].splice(index, 1);
            this.isDirty = true;
            this.content = [...this.content];
        },

        // Media Library methods
        async openMediaLibrary(controlName) {
            this.mediaControlName = controlName;
            this.showMediaLibrary = true;
            this.mediaLoading = true;

            try {
                const response = await fetch('/api/media', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                this.mediaItems = data.data || data || [];
            } catch (error) {
                console.error('Failed to load media:', error);
                this.mediaItems = [];
            } finally {
                this.mediaLoading = false;
            }
        },

        selectMediaItem(url) {
            if (this.mediaControlName) {
                const el = this.getSelectedElement();
                if (el) {
                    if (!el.settings) el.settings = {};
                    el.settings[this.mediaControlName] = url;
                    this.isDirty = true;
                    this.content = [...this.content];
                }
            }
            this.showMediaLibrary = false;
            this.mediaControlName = null;
        },

        async uploadMedia(event) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);

            try {
                const response = await fetch('/api/media', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                const data = await response.json();
                if (data.url || data.path) {
                    this.mediaItems.unshift(data);
                    this.selectMediaItem(data.url || `/storage/${data.path}`);
                }
            } catch (error) {
                console.error('Upload failed:', error);
                alert('Upload failed. Please try again.');
            }
        },

        // Helper to get target column for widget
        getTargetColumn() {
            // If a column is selected, use it
            if (this.selectedType === 'column') {
                return this.findElement(this.selectedElement);
            }
            // If a widget is selected, get its parent column
            if (this.selectedType === 'widget') {
                return this.findParent(this.selectedElement);
            }
            // Otherwise, get first column of first section
            if (this.content.length > 0 && this.content[0].elements?.length > 0) {
                return this.content[0].elements[0];
            }
            return null;
        },

        // Click to add widget (improved)
        clickAddWidget(widgetType) {
            let column = this.getTargetColumn();

            // If no column available, create a section first
            if (!column) {
                this.addSection('100');
                // Get the newly created column
                if (this.content.length > 0) {
                    column = this.content[this.content.length - 1].elements[0];
                }
            }

            if (column) {
                this.addWidget(widgetType, column.id);
            }
        },

        // Toast notifications
        showToast(message, type = 'info') {
            // Simple toast implementation
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 px-4 py-2 rounded-md shadow-lg text-white z-50 ${
                type === 'error' ? 'bg-red-500' : type === 'success' ? 'bg-green-500' : 'bg-blue-500'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
    };
}
