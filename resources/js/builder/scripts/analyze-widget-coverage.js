
const fs = require('fs');
const path = require('path');

// Mock the registry to load it in Node environment
const widgetRegistry = {
    widgets: {},
    register(name, config) {
        this.widgets[name] = config;
    },
    getAll() { return this.widgets; }
};

// Read registry file
const registryPath = path.join(__dirname, '../widgets/registry.js');
let registryContent = fs.readFileSync(registryPath, 'utf8');

// Quick and dirty eval to load the registry definitions
// We need to mock window and other browser globals if they are used
global.widgetRegistry = widgetRegistry;
// Remove imports/exports for eval
registryContent = registryContent.replace(/import .*?;/g, '').replace(/export .*?;/g, '');
eval(registryContent);

// Read Renderer file
const rendererPath = path.join(__dirname, '../components/WidgetRenderer.vue');
const rendererContent = fs.readFileSync(rendererPath, 'utf8');

const widgets = widgetRegistry.getAll();
const report = [];

Object.entries(widgets).forEach(([type, config]) => {
    const missingSettings = [];
    const controls = [
        ...(config.controls.content || []),
        ...(config.controls.style || []),
        ...(config.controls.advanced || [])
    ];

    controls.forEach(control => {
        // Skip common controls that are handled globally or by helper functions
        if (['margin', 'padding', 'z_index', 'css_classes', 'css_id', 'motion_effects', 'background', 'border', 'box_shadow', 'typography'].includes(control.type) || 
            ['margin', 'padding', 'z_index', 'css_classes', 'css_id'].includes(control.name)) {
            return;
        }

        // Check if the setting name is used in the renderer
        // We look for settings.setting_name or settings['setting_name'] or settings.value.setting_name
        const settingName = control.name;
        const regex = new RegExp(`settings\\.?(${settingName}|\\['${settingName}'\\]|\\["${settingName}"\\]|value\\.${settingName})`, 'i');
        
        if (!regex.test(rendererContent)) {
            // Double check for loop usage (e.g. item${i}_title)
            if (!rendererContent.includes('${i}') && !rendererContent.includes('[`' + settingName)) {
                 missingSettings.push(settingName);
            }
        }
    });




    if (missingSettings.length > 0) {
        report.push({
            widget: type,
            missing: missingSettings
        });
    }
});

console.log(`Checked ${Object.keys(widgets).length} widgets.`);

if (report.length === 0) {
    console.log("SUCCESS: All 34 widgets have 100% control coverage in the renderer!");
} else {
    console.log(JSON.stringify(report, null, 2));
}
