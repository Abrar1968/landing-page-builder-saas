/**
 * Widget Property Coverage Analyzer (CommonJS compatible)
 * Run with: node --no-warnings resources/js/builder/scripts/analyze-widget-coverage.cjs
 */

const fs = require('fs');
const path = require('path');

// Widget component directory
const widgetsDir = path.join(__dirname, '../components/widgets');
const registryPath = path.join(__dirname, '../widgets/registry.js');

// Read registry and extract widget definitions using regex
const registryContent = fs.readFileSync(registryPath, 'utf8');

// Parse widget registrations: widgetRegistry.register("widget-name", { ... })
const widgetRegex = /widgetRegistry\.register\s*\(\s*["']([^"']+)["']\s*,\s*\{/g;
const widgets = [];
let match;
while ((match = widgetRegex.exec(registryContent)) !== null) {
    widgets.push(match[1]);
}

// Map widget types to Vue file names
const widgetFileMap = {
    'heading': 'HeadingWidget.vue',
    'text-editor': 'TextEditorWidget.vue',
    'image': 'ImageWidget.vue',
    'button': 'ButtonWidget.vue',
    'divider': 'DividerWidget.vue',
    'spacer': 'SpacerWidget.vue',
    'icon': 'IconWidget.vue',
    'icon-box': 'IconBoxWidget.vue',
    'counter': 'CounterWidget.vue',
    'progress-bar': 'ProgressBarWidget.vue',
    'testimonial': 'TestimonialWidget.vue',
    'social-icons': 'SocialIconsWidget.vue',
    'alert': 'AlertWidget.vue',
    'image-box': 'ImageBoxWidget.vue',
    'star-rating': 'StarRatingWidget.vue',
    'tabs': 'TabsWidget.vue',
    'accordion': 'AccordionWidget.vue',
    'countdown': 'CountdownWidget.vue',
    'google-maps': 'GoogleMapsWidget.vue',
    'call-to-action': 'CallToActionWidget.vue',
    'flip-box': 'FlipBoxWidget.vue',
    'price-table': 'PriceTableWidget.vue',
    'form': 'FormWidget.vue',
    'slider': 'SliderWidget.vue',
    'toggle': 'ToggleWidget.vue',
    'icon-list': 'IconListWidget.vue',
    'text-path': 'TextPathWidget.vue',
    'image-carousel': 'ImageCarouselWidget.vue',
    'basic-gallery': 'BasicGalleryWidget.vue',
    'soundcloud': 'SoundCloudWidget.vue',
    'container': 'ContainerWidget.vue',
    'inner-section': 'InnerSectionWidget.vue',
    'menu-anchor': 'MenuAnchorWidget.vue',
    'sidebar': 'SidebarWidget.vue',
    'html': 'HtmlWidget.vue',
    'shortcode': 'ShortcodeWidget.vue',
    'video': 'VideoWidget.vue'
};

// Extract controls for a widget from registry content
function extractControls(widgetType) {
    // Find the widget registration block
    const startRegex = new RegExp(`widgetRegistry\\.register\\s*\\(\\s*["']${widgetType}["']\\s*,\\s*\\{`, 'g');
    const startMatch = startRegex.exec(registryContent);
    if (!startMatch) return [];

    // Find matching closing brace
    let depth = 1;
    let pos = startMatch.index + startMatch[0].length;
    while (depth > 0 && pos < registryContent.length) {
        if (registryContent[pos] === '{') depth++;
        else if (registryContent[pos] === '}') depth--;
        pos++;
    }

    const widgetBlock = registryContent.slice(startMatch.index, pos);
    
    // Extract control names using regex
    const controlRegex = /name:\s*["']([^"']+)["']/g;
    const controls = [];
    let controlMatch;
    while ((controlMatch = controlRegex.exec(widgetBlock)) !== null) {
        controls.push(controlMatch[1]);
    }
    
    return controls;
}

// Controls to skip (handled globally by WidgetRenderer wrapper)
const globalControls = [
    'margin', 'padding', 'z_index', 'css_classes', 'css_id', 
    'motion_effects', 'background', 'border', 'box_shadow',
    'responsive_visibility'
];

console.log('='.repeat(60));
console.log('WIDGET PROPERTY COVERAGE ANALYSIS');
console.log('='.repeat(60));
console.log('');

const report = [];
let totalControls = 0;
let coveredControls = 0;

widgets.forEach(type => {
    const fileName = widgetFileMap[type];
    if (!fileName) {
        console.log(`⚠️  ${type}: No Vue component mapping`);
        return;
    }

    const filePath = path.join(widgetsDir, fileName);
    if (!fs.existsSync(filePath)) {
        console.log(`❌ ${type}: File not found - ${fileName}`);
        return;
    }

    const vueContent = fs.readFileSync(filePath, 'utf8');
    const controls = extractControls(type);
    const missingSettings = [];

    controls.forEach(settingName => {
        // Skip global controls
        if (globalControls.includes(settingName)) return;
        
        // Skip _tablet and _mobile responsive variants
        if (settingName.endsWith('_tablet') || settingName.endsWith('_mobile')) return;

        totalControls++;

        // Check for setting usage patterns
        const found = vueContent.includes(`settings.${settingName}`) ||
                      vueContent.includes(`settings['${settingName}']`) ||
                      vueContent.includes(`settings["${settingName}"]`) ||
                      vueContent.includes(`props.settings.${settingName}`);
        
        if (found) {
            coveredControls++;
        } else {
            missingSettings.push(settingName);
        }
    });

    if (missingSettings.length > 0) {
        report.push({ widget: type, file: fileName, missing: missingSettings });
        console.log(`❌ ${type} (${fileName}): ${missingSettings.length} missing`);
        missingSettings.forEach(s => console.log(`   - ${s}`));
    } else {
        console.log(`✅ ${type}: All properties bound`);
    }
});

console.log('');
console.log('='.repeat(60));
console.log('SUMMARY');
console.log('='.repeat(60));
console.log(`Total widgets: ${widgets.length}`);
console.log(`Coverage: ${coveredControls}/${totalControls} (${((coveredControls/totalControls)*100).toFixed(1)}%)`);
console.log(`Widgets with gaps: ${report.length}`);

if (report.length > 0) {
    console.log('');
    console.log('GAPS TO FIX:');
    report.forEach(r => {
        console.log(`  ${r.widget}: ${r.missing.join(', ')}`);
    });
}
