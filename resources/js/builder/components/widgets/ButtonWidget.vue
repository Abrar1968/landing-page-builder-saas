<template>
  <div :style="containerStyles" :class="alignmentClass">
    <a
      :href="settings.link || '#'"
      :target="settings.target ? '_blank' : '_self'"
      :rel="settings.target ? 'noopener noreferrer' : undefined"
      :style="buttonStyles"
      :class="['inline-flex items-center font-medium transition-all duration-200', buttonWidgetClass]"
    >
      <span v-if="settings.icon && settings.icon_position !== 'right'" :style="iconLeftStyles">
        {{ settings.icon }}
      </span>
      {{ settings.text ?? 'Click Me' }}
      <span v-if="settings.icon && settings.icon_position === 'right'" :style="iconRightStyles">
        {{ settings.icon }}
      </span>
    </a>
    <!-- Inject scoped hover styles -->
    <component :is="'style'" v-if="hasHoverStyles">
      .{{ buttonWidgetClass }}:hover {
        background-color: {{ hoverBgColor }} !important;
        color: {{ hoverTextColor }} !important;
        border-color: {{ hoverBorderColor }} !important;
        {{ hoverTransform }}
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
  },
  hoverSettings: {
    type: Object,
    default: () => ({})
  }
});

// Unique class for this widget instance
const buttonWidgetClass = computed(() => `button-widget-${props.widgetId}`);

// Check if button has hover styles
const hasHoverStyles = computed(() => {
  return props.settings.hover_background_color ||
         props.settings.hover_text_color ||
         props.settings.hover_border_color ||
         props.hoverSettings.background_color ||
         props.hoverSettings.text_color;
});

// Hover colors (from settings or hoverSettings)
const hoverBgColor = computed(() =>
  props.settings.hover_background_color ??
  props.hoverSettings.background_color ??
  props.settings.background_color ?? '#4338ca'
);
const hoverTextColor = computed(() =>
  props.settings.hover_text_color ??
  props.hoverSettings.text_color ??
  props.settings.text_color ?? '#ffffff'
);
const hoverBorderColor = computed(() =>
  props.settings.hover_border_color ??
  props.hoverSettings.border_color ??
  props.settings.border_color ?? '#4338ca'
);
const hoverTransform = computed(() => {
  const s = props.settings;
  if (s.hover_animation === 'grow') return 'transform: scale(1.05);';
  if (s.hover_animation === 'shrink') return 'transform: scale(0.95);';
  if (s.hover_animation === 'pulse') return 'animation: pulse 1s infinite;';
  return '';
});

// Alignment class
const alignmentClass = computed(() => {
  const alignment = props.settings.alignment;
  if (!alignment || alignment === 'left') return 'text-left';
  if (alignment === 'center') return 'text-center';
  if (alignment === 'right') return 'text-right';
  if (alignment === 'justify') return 'text-justify';
  return '';
});

// Container styles (minimal - WidgetRenderer handles most)
const containerStyles = computed(() => {
  return {};
});

// Icon styles
const iconLeftStyles = computed(() => ({
  marginRight: (props.settings.icon_spacing ?? 8) + 'px'
}));
const iconRightStyles = computed(() => ({
  marginLeft: (props.settings.icon_spacing ?? 8) + 'px'
}));

// Button element styles
const buttonStyles = computed(() => {
  const s = props.settings;
  const typography = s.typography || {};

  const styles = {
    backgroundColor: s.background_color ?? '#4f46e5',
    color: s.text_color ?? '#ffffff',
    textDecoration: 'none',
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    cursor: 'pointer'
  };

  // Padding - support both naming conventions
  const paddingH = s.padding_horizontal ?? s.padding_x ?? 24;
  const paddingV = s.padding_vertical ?? s.padding_y ?? 12;
  styles.padding = `${paddingV}px ${paddingH}px`;

  // Border
  const borderWidth = s.border_width ?? 0;
  const borderRadius = s.border_radius ?? 6;
  styles.borderWidth = borderWidth + 'px';
  styles.borderStyle = 'solid';
  styles.borderColor = s.border_color ?? 'transparent';
  styles.borderRadius = borderRadius + 'px';

  // Typography
  if (typography.font_family) {
    styles.fontFamily = typography.font_family;
  }
  if (typography.size) {
    styles.fontSize = typography.size + 'px';
  }
  if (typography.weight) {
    styles.fontWeight = typography.weight;
  }
  if (typography.line_height) {
    styles.lineHeight = typography.line_height;
  }
  if (typography.letter_spacing) {
    styles.letterSpacing = typography.letter_spacing + 'px';
  }
  if (typography.text_transform) {
    styles.textTransform = typography.text_transform;
  }

  // Box shadow
  if (s.button_shadow) {
    const sh = s.button_shadow;
    styles.boxShadow = `${sh.horizontal ?? 0}px ${sh.vertical ?? 2}px ${sh.blur ?? 4}px ${sh.spread ?? 0}px ${sh.color ?? 'rgba(0,0,0,0.1)'}`;
  }

  return styles;
});
</script>

<style scoped>
.text-left { text-align: left; }
.text-center { text-align: center; }
.text-right { text-align: right; }
.text-justify { text-align: justify; }

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}
</style>
