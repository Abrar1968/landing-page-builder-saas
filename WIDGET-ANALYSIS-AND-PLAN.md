# Widget System Analysis & Implementation Plan

## 1. Executive Summary

**Current Status**: The codebase contains a robust foundation for a page builder using Vue.js 3 and Pinia.
- **Widgets**: 36 widgets are registered in `registry.js` and implemented in `WidgetRenderer.vue`.
- **Architecture**: Monolithic `WidgetRenderer.vue` handles all widget rendering.
- **Completeness**: The "missing" widgets (Toggle, Icon List, Text Path, Image Carousel) are actually **present** in the code.
- **Gap**: While widgets exist, they lack depth compared to Elementor. Most widgets have basic "Content" controls but miss extensive "Style" and "Advanced" options.

**Key Findings**:
1.  **Monolithic Renderer**: `WidgetRenderer.vue` is becoming too large (1000+ lines). It needs refactoring into sub-components.
2.  **Missing Global Features**: Motion Effects, Responsive Visibility, and Custom Positioning are missing from the "Advanced" tab of all widgets.
3.  **Style Limitations**: Most widgets lack granular control over states (Hover/Active), typography (for non-text widgets), and box shadows (for internal elements).

---

## 2. Detailed Widget Comparison (vs. Elementor)

| Widget | Status | Missing Features (vs. Elementor) | Priority |
| :--- | :--- | :--- | :--- |
| **Heading** | ✅ Working | Text Shadow, Blend Mode, Stroke effect. | Low |
| **Image** | ✅ **Enhanced** | ~~Opacity~~, ~~CSS Filters~~, ~~Hover Animation~~, Lightbox, Custom Link attributes. | Medium |
| **Text Editor** | ✅ Working | Drop Cap, Columns, Text Shadow. | Low |
| **Video** | ✅ **Enhanced** | ~~Autoplay~~, ~~Mute~~, ~~Loop~~, ~~Controls~~, Image Overlay, Lightbox. | High |
| **Button** | ✅ **Enhanced** | ~~Hover Effects (Bg/Text color change)~~, ~~Icon support~~, ~~Typography settings~~, Text Shadow, Padding (Style tab). | **Critical** |
| **Divider** | ✅ **Enhanced** | ~~Gap control~~, ~~Icon/Text in middle of divider~~. | Medium |
| **Spacer** | ✅ Working | **Viewport units (VH)** for responsive spacing. | Medium |
| **Google Maps** | ✅ Working | Prevent Scroll option. | Low |
| **Icon** | ✅ Working | **View modes** (Stacked/Framed), Rotate, Hover Animation, Secondary Color. | Medium |
| **Image Box** | ✅ Working | **Image Position** (Left/Right/Top), Hover effects, Image spacing, Content vertical alignment. | Medium |
| **Icon Box** | ✅ Working | **View modes** (Stacked/Framed), **Icon Position** (Left/Right/Top), Hover effects. | Medium |
| **Star Rating** | ✅ Working | Icon style (Solid/Outline), Gap. | Low |
| **Image Carousel** | ✅ Working | **Slides to Scroll**, **Infinite Loop**, **Pause on Hover**, Lightbox. | Medium |
| **Basic Gallery** | ✅ Working | Lightbox, Masonry Layout. | Low |
| **Icon List** | ✅ Working | **Layout** (Inline/List), Hover effects, Divider between items. | Medium |
| **Counter** | ✅ Working | Starting Number, Animation Duration, Thousand Separator. | Low |
| **Progress Bar** | ✅ Working | Inner Text, Striped effect. | Low |
| **Testimonial** | ✅ Working | **Image Position** (Top/Side), Link. | Medium |
| **Tabs** | ✅ Working | Vertical Layout, Icon support in tabs. | Low |
| **Accordion** | ✅ Working | Icon customization (Open/Close icons). | Low |
| **Toggle** | ✅ Working | Icon customization. | Low |
| **Social Icons** | ✅ Working | **Custom Items** (Repeater), Shape (Circle/Square), Hover Animation, Official Colors. | Medium |
| **Alert** | ✅ Working | Dismiss Button. | Low |
| **SoundCloud** | ✅ Working | User Name display, Play Counts display. | Low |
| **Shortcode** | ✅ Working | N/A (Basic implementation is sufficient). | - |
| **HTML** | ✅ Working | N/A. | - |
| **Menu Anchor** | ✅ Working | N/A. | - |
| **Sidebar** | ✅ Working | N/A. | - |
| **Text Path** | ✅ Working | Show Path (for editing), Word Spacing. | Low |

---

## 3. Global Feature Gaps

These features are standard in Elementor for *every* widget but are missing here:

### A. Advanced Tab
1.  **Motion Effects**: Entrance animations (Fade In, Zoom In, Slide Up, etc.).
2.  **Responsive**: "Hide on Desktop", "Hide on Tablet", "Hide on Mobile".
3.  **Positioning**:
    *   Width: Default, Full Width, Inline (Auto), Custom.
    *   Position: Default, Absolute, Fixed.
4.  **Attributes**: Custom HTML attributes (e.g., `data-category="marketing"`).

### B. Style Tab
1.  **Hover States**: Most widgets only style the "Normal" state. Elementor allows separate styling for "Hover" (e.g., Button background color on hover).
2.  **Typography**: The current typography control is good but needs to be applied more consistently across all text elements inside complex widgets (e.g., Button text, Tab titles).

---

## 4. Implementation Plan

### Phase 1: Critical Widget Enhancements ✅ **COMPLETED**
*Goal: Fix the most visible functional gaps in commonly used widgets.*

1.  **Button Widget** ✅:
    *   ✅ Added `icon` control (emoji/text-based).
    *   ✅ Added `icon_position` (Before/After) and `icon_spacing`.
    *   ✅ Added `hover_tabs` control in Style tab.
    *   ✅ Added hover colors: `hover_background_color`, `hover_text_color`, `hover_border_color`.
    *   ✅ Added `border_width`, `border_color` controls.
    *   ✅ Added `padding_horizontal`, `padding_vertical` controls.
    *   ✅ Added `typography` control for button text.
    *   ✅ Updated `WidgetRenderer.vue` with icon rendering and scoped hover styles.

2.  **Video Widget** ✅:
    *   ✅ Added toggles: `autoplay`, `mute`, `loop`, `controls`, `modest_branding`.
    *   ✅ Added `start_time` and `end_time` controls.
    *   ✅ Added "21:9" aspect ratio option.
    *   ✅ Updated `getVideoEmbedUrl()` to append YouTube/Vimeo parameters.

3.  **Image Widget** ✅:
    *   ✅ Added `opacity` slider (0-1).
    *   ✅ Added CSS filters: `filter_blur`, `filter_brightness`, `filter_contrast`, `filter_saturation`, `filter_hue`.
    *   ✅ Added `hover_animation` select (Zoom In, Zoom Out, Grayscale, Blur, Brighten).
    *   ✅ Added `link` and `link_target` controls.
    *   ✅ Added `max_width` control.
    *   ✅ Updated `WidgetRenderer.vue` with new `getImageContainerStyles()`, `getImageElementStyles()`, and `getImageHoverClass()` functions.

4.  **Divider Widget** ✅:
    *   ✅ Added `gap` control for vertical spacing.
    *   ✅ Added `divider_element` select (None, Text, Icon).
    *   ✅ Added `element_text` and `element_icon` controls.
    *   ✅ Added `element_color`, `element_size`, `element_spacing` style controls.
    *   ✅ Added "double" border style option.
    *   ✅ Updated `WidgetRenderer.vue` with new divider rendering (with/without element).

### Phase 2: Global Features - Advanced Tab ✅ **COMPLETED**
*Goal: Implement the "Advanced" tab features common to all widgets.*

1.  **Responsive Visibility** ✅:
    *   ✅ Added `responsive_visibility` control type to `ControlRenderer.vue`.
    *   ✅ Added `responsive_visibility` to all widgets via `getAdvancedControls()` helper.
    *   ✅ Updated `WidgetRenderer.vue` to apply `.hidden-desktop`, `.hidden-tablet`, `.hidden-mobile` classes.
    *   ✅ Added responsive media query CSS for hiding elements on different devices.
2.  **Motion Effects** ✅:
    *   ✅ Added `motion_effects` control to all widgets via `getAdvancedControls()` helper.
    *   ✅ Implemented 15 entrance animations: fadeIn, fadeInDown, fadeInUp, fadeInLeft, fadeInRight, zoomIn, zoomOut, bounceIn, slideInDown, slideInUp, slideInLeft, slideInRight, rotateIn, flipInX, flipInY.
    *   ✅ Added animation duration classes (slow, normal, fast).
    *   ✅ Implemented IntersectionObserver to trigger animations on viewport entry.
    *   ✅ Added all CSS keyframes and animation classes to `WidgetRenderer.vue`.

### Phase 3: Architecture Refactor ✅ **COMPLETED**
*Goal: Prepare the architecture for future scalability while maintaining current functionality.*

1.  **Widget Folder Structure** ✅: Created `resources/js/builder/components/widgets/` folder.
2.  **Composables** ✅: Created `resources/js/builder/composables/useWidgetStyles.js` with shared utilities:
    *   `useWidgetStyles()` - Common styling functions (formatDimensions, getCommonWrapperStyles, responsiveVisibilityClasses)
    *   `useWidgetAnimation()` - Animation handling (animationClasses, setupAnimationObserver)
3.  **Example Widget Components** ✅: Extracted 3 widgets as examples for future extraction:
    *   `HeadingWidget.vue` - Text/heading widget with typography
    *   `ButtonWidget.vue` - Button with hover effects and icon support
    *   `ImageWidget.vue` - Image with filters and hover animations
4.  **Widget Index** ✅: Created `widgets/index.js` with dynamic import mapping and documentation for future full extraction.
5.  **Registry Helper** ✅: Created `getAdvancedControls()` helper function in `registry.js` to standardize advanced controls across all 36 widgets.

### Phase 4: Layout & Style Depth ✅ **COMPLETED**
*Goal: Reach parity with Elementor Pro features.*

1.  **Hover States System** ✅:
    *   ✅ Added hover color controls to Icon Box (hover_icon_color, hover_title_color).
    *   ✅ Added hover color controls to Image Box (hover_title_color).
    *   ✅ Added hover animation support to Image Box (zoom, zoom_out, grayscale, blur).
    *   ✅ Implemented scoped hover styles using dynamic `<style>` tags.
2.  **Image/Icon Box Layouts** ✅:
    *   ✅ Added `icon_position` control (Top/Left/Right) to Icon Box widget.
    *   ✅ Added `image_position` control (Top/Left/Right) to Image Box widget.
    *   ✅ Added `icon_spacing` and `image_spacing` controls for element spacing.
    *   ✅ Added `content_vertical_alignment` control (Top/Center/Bottom) to both widgets.
    *   ✅ Implemented flex layout for left/right positions in renderer.
    *   ✅ Added link support to Icon Box widget.
3.  **Spacer Viewport Units** ✅:
    *   ✅ Added `space_unit` select control (px/vh) to Spacer widget.
    *   ✅ Updated renderer to use dynamic unit based on settings.

---

## 5. Immediate Action Items

1.  ✅ ~~**Add Button Hover Effects**: This is the most requested missing feature for users.~~
2.  ✅ ~~**Add Video Playback Controls**: Essential for marketing pages.~~
3.  ✅ ~~**Add Image Filters & Opacity**: Common styling requirement.~~
4.  ✅ ~~**Add Divider Gap Control**: Basic spacing improvement.~~
5.  ✅ ~~**Implement Responsive Visibility**: Essential for mobile-first design.~~
6.  ✅ ~~**Refactor `WidgetRenderer.vue`**: Architecture prepared for future extraction.~~

---

## 6. Phase 1 Implementation Notes

### Files Modified:
- `resources/js/builder/widgets/registry.js` - Updated button, video, image, divider widget definitions
- `resources/js/builder/components/WidgetRenderer.vue` - Added new rendering logic and helper functions

### New Controls Added:
- **Button**: icon, icon_position, icon_spacing, hover_background_color, hover_text_color, hover_border_color, border_width, border_color, padding_horizontal, padding_vertical, typography
- **Video**: autoplay, mute, loop, controls, modest_branding, start_time, end_time
- **Image**: link, link_target, max_width, opacity, filter_blur, filter_brightness, filter_contrast, filter_saturation, filter_hue, hover_animation
- **Divider**: gap, divider_element, element_text, element_icon, element_color, element_size, element_spacing

### New Functions in WidgetRenderer.vue:
- `getImageContainerStyles()` - Container alignment and spacing
- `getImageElementStyles()` - Image opacity and CSS filters
- `getImageHoverClass()` - Returns hover animation class
- `getDividerWrapperStyles()` - Wrapper for element-containing dividers
- `getDividerLineStyles()` - Individual line styles
- `getDividerElementStyles()` - Text/icon element styling

### CSS Hover System:
Button and Image widgets now use dynamically generated `<style>` tags with scoped selectors (e.g., `.button-widget-{id}:hover`) to apply hover effects without affecting other instances.

---

## 7. Phase 2 Implementation Notes

### Files Modified:
- `resources/js/builder/widgets/registry.js` - Added `getAdvancedControls()` helper and updated all 36 widgets
- `resources/js/builder/components/WidgetRenderer.vue` - Added responsive visibility and motion effects
- `resources/js/builder/components/controls/ControlRenderer.vue` - Added `responsive_visibility` control type

### New Helper Function (registry.js):
```javascript
const commonAdvancedControls = [
    { name: "margin", type: "dimensions", label: "Margin" },
    { name: "padding", type: "dimensions", label: "Padding" },
    { name: "z_index", type: "number", label: "Z-Index", default: 0 },
    { name: "css_classes", type: "text", label: "CSS Classes" },
    { name: "css_id", type: "text", label: "CSS ID" },
    { name: "background", type: "background", label: "Background" },
    { name: "border", type: "border", label: "Border" },
    { name: "box_shadow", type: "box_shadow", label: "Box Shadow" },
    { name: "responsive_visibility", type: "responsive_visibility", label: "Responsive Visibility" },
    { name: "motion_effects", type: "motion_effects", label: "Motion Effects" },
];

function getAdvancedControls(extras = []) {
    return [...extras, ...commonAdvancedControls];
}
```

### Responsive Visibility Control (ControlRenderer.vue):
- New control type with 3 checkboxes: Hide on Desktop, Hide on Tablet, Hide on Mobile
- Stores as `{ hide_desktop: bool, hide_tablet: bool, hide_mobile: bool }`

### Animation System (WidgetRenderer.vue):
- IntersectionObserver triggers animation when widget enters viewport
- 15 entrance animations with CSS keyframes
- Duration classes: slow (2s), normal (1s), fast (0.5s)
- Animations only trigger once (one-time entrance effect)

### CSS Media Queries:
```css
@media (min-width: 1025px) { .hidden-desktop { display: none !important; } }
@media (min-width: 768px) and (max-width: 1024px) { .hidden-tablet { display: none !important; } }
@media (max-width: 767px) { .hidden-mobile { display: none !important; } }
```

---

## 8. Phase 3 Implementation Notes

### New Files Created:
- `resources/js/builder/composables/useWidgetStyles.js` - Shared widget utilities
- `resources/js/builder/components/widgets/HeadingWidget.vue` - Extracted heading widget
- `resources/js/builder/components/widgets/ButtonWidget.vue` - Extracted button widget
- `resources/js/builder/components/widgets/ImageWidget.vue` - Extracted image widget
- `resources/js/builder/components/widgets/index.js` - Widget index and dynamic import map

### Architecture Decision:
The current implementation keeps `WidgetRenderer.vue` as the main renderer for backward compatibility.
Example widget components have been extracted to demonstrate the pattern for future full extraction.
The `widgetComponentMap` in `widgets/index.js` provides the foundation for dynamic component loading.

### Future Extraction Pattern:
```vue
<script setup>
import { defineAsyncComponent, computed } from 'vue';
import { widgetComponentMap, hasExtractedComponent } from './widgets';

const WidgetComponent = computed(() => {
  if (!hasExtractedComponent(props.widget.widgetType)) return null;
  const loader = widgetComponentMap[props.widget.widgetType];
  return defineAsyncComponent(loader);
});
</script>

<template>
  <component 
    v-if="WidgetComponent" 
    :is="WidgetComponent" 
    :settings="widget.settings" 
    :widget-id="widget.id"
  />
  <!-- Fallback to inline rendering for non-extracted widgets -->
  <div v-else>...</div>
</template>
```

### Composable Usage:
```javascript
import { useWidgetStyles, useWidgetAnimation } from '@/builder/composables/useWidgetStyles';

// In widget component setup()
const { formatDimensions, getCommonWrapperStyles, responsiveVisibilityClasses } = useWidgetStyles(settings);
const { animationClasses, setupAnimationObserver, cleanupAnimationObserver } = useWidgetAnimation(settings, widgetRef);
```

---

## 9. Summary of All Changes

### Phase 1 (Completed):
- ✅ Button: Icon support, hover effects, typography
- ✅ Video: Autoplay, mute, loop, controls, time controls
- ✅ Image: Filters, opacity, hover animations, link
- ✅ Divider: Gap, text/icon elements

### Phase 2 (Completed):
- ✅ Responsive Visibility for all 36 widgets
- ✅ Motion Effects (15 animations) for all 36 widgets
- ✅ `getAdvancedControls()` helper in registry.js
- ✅ `responsive_visibility` control type

### Phase 3 (Completed):
- ✅ Widget folder structure created
- ✅ Composables for shared utilities
- ✅ Example widget components (Heading, Button, Image)
- ✅ Widget index with dynamic import map
- ✅ Documentation for future full extraction

### Phase 4 (Completed):
- ✅ Spacer: Viewport units (vh) support
- ✅ Icon Box: Position control (Top/Left/Right), hover states, link support
- ✅ Image Box: Position control (Top/Left/Right), hover states, image controls

### Total Widgets Enhanced: 36 widgets now have:
- Responsive visibility (hide on desktop/tablet/mobile)
- Motion effects (entrance animations)
- Consistent advanced controls via helper function

---

## 10. Phase 4 Implementation Notes

### Files Modified:
- `resources/js/builder/widgets/registry.js` - Updated Spacer, Icon Box, Image Box definitions
- `resources/js/builder/components/WidgetRenderer.vue` - Added layout and hover support

### Spacer Widget Updates:
```javascript
// New control added
{
    name: "space_unit",
    type: "select",
    label: "Unit",
    default: "px",
    options: {
        px: "Pixels (px)",
        vh: "Viewport Height (vh)",
    },
}
```

### Icon Box Widget Updates:
- `icon_position` (Top/Left/Right) - Changes layout direction
- `icon_spacing` - Space between icon and content
- `hover_icon_color` - Icon color on hover
- `hover_title_color` - Title color on hover
- `content_vertical_alignment` - Vertical alignment for left/right layouts
- `link` - Optional URL support

### Image Box Widget Updates:
- `image_position` (Top/Left/Right) - Changes layout direction
- `image_spacing` - Space between image and content
- `image_width` / `image_height` - Image dimensions
- `hover_animation` - Image hover effect (zoom, grayscale, blur)
- `hover_title_color` - Title color on hover
- `content_vertical_alignment` - Vertical alignment for left/right layouts

### New Helper Functions in WidgetRenderer.vue:
```javascript
// Icon Box helpers
getIconBoxLayoutClass()     // Returns position class
getIconBoxIconStyles()      // Icon styles with spacing
getIconBoxContentStyles()   // Content flex styles

// Image Box helpers  
getImageBoxLayoutClass()           // Returns position class
getImageBoxImageContainerStyles()  // Image container with spacing
getImageBoxImageStyles()           // Image dimensions
getImageBoxImageHoverClass()       // Hover animation class
getImageBoxContentStyles()         // Content flex styles
```

### CSS Additions:
```css
/* Icon Box and Image Box transitions */
.icon-box-wrapper { transition: all 0.3s ease; }
.icon-box-wrapper .icon-box-title { transition: color 0.3s ease; }
.image-box-wrapper { transition: all 0.3s ease; }
.image-box-wrapper .image-box-title { transition: color 0.3s ease; }

/* Image hover effects */
.image-box-wrapper .hover-zoom { transition: transform 0.3s ease; }
.image-box-wrapper .hover-zoom_out { transition: transform 0.3s ease; }
.image-box-wrapper .hover-grayscale { transition: filter 0.3s ease; }
.image-box-wrapper .hover-blur { transition: filter 0.3s ease; }
```
