
const fs = require('fs');
const path = require('path');

// Mock registry
const widgetRegistry = {
    widgets: {},
    register(name, config) { this.widgets[name] = config; },
    getAll() { return this.widgets; },
    get(name) { return this.widgets[name]; }
};

const registryPath = path.join(__dirname, '../widgets/registry.js');
let registryContent = fs.readFileSync(registryPath, 'utf8');
global.widgetRegistry = widgetRegistry;
registryContent = registryContent.replace(/import .*?;/g, '').replace(/export .*?;/g, '');
eval(registryContent);

// Generate content
const widgets = widgetRegistry.getAll();
const content = [];
let section = {
    id: 'section_1',
    elType: 'section',
    settings: { structure: '100', padding: { top: 50, bottom: 50 } },
    elements: []
};

let column = {
    id: 'column_1',
    elType: 'column',
    settings: { _column_size: 100 },
    elements: []
};

section.elements.push(column);
content.push(section);

Object.keys(widgets).forEach((type, index) => {
    // Create a new section every 5 widgets for better readability
    if (index > 0 && index % 5 === 0) {
        section = {
            id: `section_${Math.floor(index/5) + 1}`,
            elType: 'section',
            settings: { structure: '100', padding: { top: 50, bottom: 50 } },
            elements: []
        };
        column = {
            id: `column_${Math.floor(index/5) + 1}`,
            elType: 'column',
            settings: { _column_size: 100 },
            elements: []
        };
        section.elements.push(column);
        content.push(section);
    }

    const widgetConfig = widgets[type];
    const settings = {};
    
    // Populate settings with defaults or sample values
    ['content', 'style', 'advanced'].forEach(tab => {
        (widgetConfig.controls[tab] || []).forEach(control => {
            if (control.default !== undefined) {
                settings[control.name] = control.default;
            }
            // Add specific test values for common types
            if (control.type === 'text' && !settings[control.name]) {
                settings[control.name] = `Test ${control.label}`;
            }
        });
    });

    column.elements.push({
        id: `widget_${type}_${index}`,
        elType: 'widget',
        widgetType: type,
        settings: settings
    });
});

console.log(JSON.stringify(content));
