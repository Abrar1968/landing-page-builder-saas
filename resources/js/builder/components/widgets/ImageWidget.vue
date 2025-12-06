<template>
  <div :style="containerStyles" :class="alignmentClass">
    <component
      :is="settings.link ? 'a' : 'div'"
      :href="settings.link || undefined"
      :target="settings.link && settings.link_target ? '_blank' : undefined"
      :rel="settings.link && settings.link_target ? 'noopener noreferrer' : undefined"
      class="inline-block image-link-wrapper"
    >
      <img
        v-if="settings.image_url"
        :src="settings.image_url"
        :alt="settings.alt_text ?? ''"
        :style="imageStyles"
        :class="['transition-all duration-300', imageWidgetClass, hoverClass]"
      />
      <div v-else class="image-placeholder">
        <span>🖼</span>
        <span>Click to add image</span>
      </div>
    </component>
    <p v-if="settings.caption" :style="captionStyles" class="caption">{{ settings.caption }}</p>
    <!-- Inject scoped hover styles -->
    <component :is="'style'" v-if="hasHoverAnimation">
      .{{ imageWidgetClass }}.hover-zoom:hover { transform: scale(1.1) !important; }
      .{{ imageWidgetClass }}.hover-zoom_out:hover { transform: scale(0.9) !important; }
      .{{ imageWidgetClass }}.hover-grayscale:hover { filter: grayscale(100%) !important; }
      .{{ imageWidgetClass }}.hover-blur:hover { filter: blur(3px) !important; }
      .{{ imageWidgetClass }}.hover-brightness:hover { filter: brightness(1.2) !important; }
      .{{ imageWidgetClass }}.hover-sepia:hover { filter: sepia(100%) !important; }
      .{{ imageWidgetClass }}.hover-rotate:hover { transform: rotate(5deg) !important; }
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
  },
  hoverSettings: {
    type: Object,
    default: () => ({})
  }
});

// Unique class for this widget instance
const imageWidgetClass = computed(() => `image-widget-${props.widgetId}`);

// Check if hover animation is enabled
const hasHoverAnimation = computed(() => {
  const animation = props.settings.hover_animation;
  return animation && animation !== 'none';
});

// Alignment class for flex container
const alignmentClass = computed(() => {
  const alignment = props.settings.alignment;
  if (!alignment || alignment === 'left') return 'text-left';
  if (alignment === 'center') return 'text-center';
  if (alignment === 'right') return 'text-right';
  return '';
});

// Container styles (minimal - WidgetRenderer handles most)
const containerStyles = computed(() => {
  return {};
});

// Caption styles
const captionStyles = computed(() => {
  return {
    marginTop: '0.5rem',
    fontSize: '0.875rem',
    color: props.settings.caption_color ?? '#6b7280',
    textAlign: props.settings.alignment ?? 'center'
  };
});

// Image element styles
const imageStyles = computed(() => {
  const styles = {
    maxWidth: '100%',
    height: 'auto',
    display: 'block'
  };

  // Width percentage
  const width = props.settings.width;
  if (width !== undefined && width !== null) {
    styles.width = width + '%';
  } else {
    styles.width = '100%';
  }

  // Max width in pixels
  if (props.settings.max_width && props.settings.max_width > 0) {
    styles.maxWidth = props.settings.max_width + 'px';
  }

  // Opacity
  const opacity = props.settings.opacity;
  if (opacity !== undefined && opacity !== null && opacity !== 1) {
    styles.opacity = opacity;
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

  // Border radius for image itself
  if (props.settings.image_border_radius) {
    styles.borderRadius = props.settings.image_border_radius + 'px';
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
}

.image-link-wrapper {
  display: inline-block;
}

.text-left {
  text-align: left;
}

.text-center {
  text-align: center;
}

.text-right {
  text-align: right;
}
</style>
