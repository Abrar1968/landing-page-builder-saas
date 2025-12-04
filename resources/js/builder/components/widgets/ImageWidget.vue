<template>
  <div :style="containerStyles">
    <component
      :is="settings.link ? 'a' : 'div'"
      :href="settings.link || undefined"
      :target="settings.link && settings.link_target ? '_blank' : undefined"
      class="inline-block"
    >
      <img
        v-if="settings.image_url"
        :src="settings.image_url"
        :alt="settings.alt_text ?? ''"
        :style="imageStyles"
        :class="['transition-all duration-300', `image-widget-${widgetId}`, hoverClass]"
      />
      <div v-else class="image-placeholder">
        <span>🖼</span>
        <span>Click to add image</span>
      </div>
    </component>
    <p v-if="settings.caption" class="caption">{{ settings.caption }}</p>
    <!-- Inject scoped hover styles -->
    <component :is="'style'" v-if="settings.hover_animation && settings.hover_animation !== 'none'">
      .image-widget-{{ widgetId }}.hover-zoom:hover { transform: scale(1.1); }
      .image-widget-{{ widgetId }}.hover-zoom_out:hover { transform: scale(0.9); }
      .image-widget-{{ widgetId }}.hover-grayscale:hover { filter: grayscale(100%); }
      .image-widget-{{ widgetId }}.hover-blur:hover { filter: blur(3px); }
      .image-widget-{{ widgetId }}.hover-brightness:hover { filter: brightness(1.2); }
    </component>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: {
    type: Object,
    required: true
  },
  widgetId: {
    type: String,
    required: true
  }
});

// Format dimensions helper
const formatDimensions = (dims) => {
  if (!dims) return '';
  if (dims.linked) {
    const val = dims.top ?? 0;
    return `${val}px`;
  }
  return `${dims.top ?? 0}px ${dims.right ?? 0}px ${dims.bottom ?? 0}px ${dims.left ?? 0}px`;
};

// Container styles
const containerStyles = computed(() => {
  const alignment = props.settings.alignment ?? 'left';
  return {
    textAlign: alignment,
    margin: formatDimensions(props.settings.margin),
    padding: formatDimensions(props.settings.padding)
  };
});

// Image element styles
const imageStyles = computed(() => {
  const styles = {
    width: (props.settings.width ?? 100) + '%',
    opacity: props.settings.opacity ?? 1,
  };

  // Max width
  if (props.settings.max_width && props.settings.max_width > 0) {
    styles.maxWidth = props.settings.max_width + 'px';
  }

  // Build CSS filter string
  const filters = [];
  if (props.settings.filter_blur && props.settings.filter_blur > 0) {
    filters.push(`blur(${props.settings.filter_blur}px)`);
  }
  if (props.settings.filter_brightness && props.settings.filter_brightness !== 100) {
    filters.push(`brightness(${props.settings.filter_brightness}%)`);
  }
  if (props.settings.filter_contrast && props.settings.filter_contrast !== 100) {
    filters.push(`contrast(${props.settings.filter_contrast}%)`);
  }
  if (props.settings.filter_saturation && props.settings.filter_saturation !== 100) {
    filters.push(`saturate(${props.settings.filter_saturation}%)`);
  }
  if (props.settings.filter_hue && props.settings.filter_hue > 0) {
    filters.push(`hue-rotate(${props.settings.filter_hue}deg)`);
  }

  if (filters.length > 0) {
    styles.filter = filters.join(' ');
  }

  return styles;
});

// Hover animation class
const hoverClass = computed(() => {
  const animation = props.settings.hover_animation;
  if (!animation || animation === 'none') return '';
  return `hover-${animation}`;
});
</script>

<style scoped>
.image-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: #f3f4f6;
  border: 2px dashed #d1d5db;
  border-radius: 0.5rem;
  color: #9ca3af;
}

.image-placeholder span:first-child {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.caption {
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: #6b7280;
  text-align: center;
}
</style>
