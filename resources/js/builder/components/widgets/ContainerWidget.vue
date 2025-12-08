<template>
  <component :is="settings.html_tag ?? 'div'" :style="containerStyles" class="container-widget">
    <div :class="contentWidthClass">
      <slot>
        <div class="text-center text-gray-400 py-8 border-2 border-dashed border-gray-300 rounded">
          <span class="text-2xl">▭</span>
          <p class="mt-2">Drop widgets here</p>
        </div>
      </slot>
    </div>
  </component>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const contentWidthClass = computed(() =>
  props.settings.content_width === 'boxed' ? 'max-w-7xl mx-auto px-4' : 'w-full'
);

const containerStyles = computed(() => {
  const s = props.settings;
  const styles = {};

  // Layout
  if (s.container_layout === 'flex' || s.flex_direction) {
    styles.display = 'flex';
  }
  if (s.flex_direction) styles.flexDirection = s.flex_direction;
  if (s.justify_content) styles.justifyContent = s.justify_content;
  if (s.align_items) styles.alignItems = s.align_items;
  if (s.flex_wrap) styles.flexWrap = s.flex_wrap;
  if (s.gaps) {
    const gap = s.gaps;
    styles.gap = `${gap.row ?? 20}px ${gap.column ?? 20}px`;
  }

  // Size
  if (s.width) styles.width = s.width + (s.width_unit ?? 'px');
  if (s.min_height) styles.minHeight = s.min_height + 'px';
  if (s.min_width) styles.minWidth = s.min_width + 'px';
  if (s.max_width) styles.maxWidth = s.max_width + 'px';

  // Self alignment (for nested containers)
  if (s.align_self) styles.alignSelf = s.align_self;
  if (s.order) styles.order = s.order;
  if (s.size) styles.flex = s.size === 'grow' ? '1 1 auto' : s.size === 'shrink' ? '0 1 auto' : 'none';

  // Position
  if (s.position && s.position !== 'default') {
    styles.position = s.position;
  }

  // Background (for real-time preview)
  if (s.background_color) styles.backgroundColor = s.background_color;
  if (s.background_image) {
    styles.backgroundImage = `url(${s.background_image})`;
    styles.backgroundSize = s.background_size ?? 'cover';
    styles.backgroundPosition = s.background_position ?? 'center';
  }

  return styles;
});

const formatDimensions = (dims) => {
  if (!dims) return '0';
  const unit = dims.unit ?? 'px';
  return `${dims.top ?? 0}${unit} ${dims.right ?? 0}${unit} ${dims.bottom ?? 0}${unit} ${dims.left ?? 0}${unit}`;
};
</script>
