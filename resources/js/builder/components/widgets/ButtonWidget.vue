<template>
  <div :style="containerStyles">
    <a
      :href="settings.link || '#'"
      :target="settings.target ? '_blank' : '_self'"
      :style="buttonStyles"
      :class="['inline-flex items-center font-medium transition-colors duration-200', `button-widget-${widgetId}`]"
    >
      <span v-if="settings.icon && settings.icon_position !== 'right'" :style="{ marginRight: (settings.icon_spacing ?? 8) + 'px' }">
        {{ settings.icon }}
      </span>
      {{ settings.text ?? 'Click Me' }}
      <span v-if="settings.icon && settings.icon_position === 'right'" :style="{ marginLeft: (settings.icon_spacing ?? 8) + 'px' }">
        {{ settings.icon }}
      </span>
    </a>
    <!-- Inject scoped hover styles -->
    <component :is="'style'" v-if="hasHoverStyles">
      .button-widget-{{ widgetId }}:hover {
        background-color: {{ settings.hover_background_color ?? settings.background_color ?? '#4338ca' }} !important;
        color: {{ settings.hover_text_color ?? settings.text_color ?? '#ffffff' }} !important;
        border-color: {{ settings.hover_border_color ?? settings.border_color ?? '#4338ca' }} !important;
      }
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

// Check if button has hover styles
const hasHoverStyles = computed(() => {
  return props.settings.hover_background_color ||
         props.settings.hover_text_color ||
         props.settings.hover_border_color;
});

// Container styles (alignment, margin)
const containerStyles = computed(() => {
  return {
    textAlign: props.settings.alignment ?? 'left',
    margin: formatDimensions(props.settings.margin)
  };
});

// Button element styles
const buttonStyles = computed(() => {
  const typography = props.settings.typography || {};

  const styles = {
    backgroundColor: props.settings.background_color ?? '#4f46e5',
    color: props.settings.text_color ?? '#ffffff',
    padding: `${props.settings.padding_y ?? 12}px ${props.settings.padding_x ?? 24}px`,
    borderRadius: (props.settings.border_radius ?? 4) + 'px',
    borderWidth: (props.settings.border_width ?? 0) + 'px',
    borderStyle: 'solid',
    borderColor: props.settings.border_color ?? 'transparent',
    textDecoration: 'none',
    display: 'inline-flex',
    alignItems: 'center'
  };

  // Typography
  if (typography.size) {
    styles.fontSize = typography.size + 'px';
  }
  if (typography.weight) {
    styles.fontWeight = typography.weight;
  }

  return styles;
});
</script>
