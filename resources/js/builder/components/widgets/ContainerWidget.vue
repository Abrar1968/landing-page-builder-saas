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

const containerStyles = computed(() => ({
  minHeight: (props.settings.min_height ?? 100) + 'px',
  backgroundColor: props.settings.background_color ?? 'transparent',
  backgroundImage: props.settings.background_image ? `url(${props.settings.background_image})` : 'none',
  backgroundSize: props.settings.background_size ?? 'cover',
  backgroundPosition: props.settings.background_position ?? 'center',
  padding: formatDimensions(props.settings.padding),
  margin: formatDimensions(props.settings.margin)
}));

const formatDimensions = (dims) => {
  if (!dims) return '0';
  const unit = dims.unit ?? 'px';
  return `${dims.top ?? 0}${unit} ${dims.right ?? 0}${unit} ${dims.bottom ?? 0}${unit} ${dims.left ?? 0}${unit}`;
};
</script>
