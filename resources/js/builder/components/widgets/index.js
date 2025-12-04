/**
 * Widget Components Index
 *
 * This file exports all individual widget components for use with dynamic imports.
 *
 * NOTE: Currently, WidgetRenderer.vue still uses a monolithic approach for rendering.
 * These extracted components are prepared for a future refactoring phase where
 * each widget will be loaded dynamically.
 *
 * Usage example for future dynamic loading:
 *
 * ```vue
 * <script setup>
 * import { defineAsyncComponent, computed } from 'vue';
 *
 * const widgetComponents = {
 *   'heading': () => import('./HeadingWidget.vue'),
 *   'button': () => import('./ButtonWidget.vue'),
 *   'image': () => import('./ImageWidget.vue'),
 *   // ... more widgets
 * };
 *
 * const props = defineProps(['widget']);
 *
 * const WidgetComponent = computed(() => {
 *   const loader = widgetComponents[props.widget.widgetType];
 *   return loader ? defineAsyncComponent(loader) : null;
 * });
 * </script>
 *
 * <template>
 *   <component
 *     v-if="WidgetComponent"
 *     :is="WidgetComponent"
 *     :settings="widget.settings"
 *     :widget-id="widget.id"
 *   />
 * </template>
 * ```
 */

// Basic Widgets
export { default as HeadingWidget } from './HeadingWidget.vue';
export { default as ButtonWidget } from './ButtonWidget.vue';
export { default as ImageWidget } from './ImageWidget.vue';

// Widget type to component mapping for dynamic loading
export const widgetComponentMap = {
    'heading': () => import('./HeadingWidget.vue'),
    'button': () => import('./ButtonWidget.vue'),
    'image': () => import('./ImageWidget.vue'),
    // Future: Add more widget mappings as they are extracted
    // 'text-editor': () => import('./TextEditorWidget.vue'),
    // 'video': () => import('./VideoWidget.vue'),
    // 'divider': () => import('./DividerWidget.vue'),
    // etc...
};

/**
 * Helper to check if a widget has been extracted to a separate component
 */
export function hasExtractedComponent(widgetType) {
    return widgetType in widgetComponentMap;
}
