<template>
  <component
    :is="headingTag"
    :style="headingStyles"
  >
    <a
      v-if="settings.link?.url"
      :href="settings.link.url"
      :target="settings.link.is_external ? '_blank' : '_self'"
      :rel="settings.link.nofollow ? 'nofollow' : ''"
      style="color: inherit; text-decoration: inherit;"
    >
      {{ settings.title ?? 'Heading' }}
    </a>
    <template v-else>
      {{ settings.title ?? 'Heading' }}
    </template>
  </component>
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

// Computed property for heading tag (H1-H6)
const headingTag = computed(() => {
  return props.settings.size || props.settings.tag || 'h2';
});

// Computed property for heading styles
const headingStyles = computed(() => {
  const typography = props.settings.typography || {};
  const styles = {
    color: props.settings.text_color ?? '#1f2937',
    textAlign: props.settings.alignment ?? 'left',
    margin: formatDimensions(props.settings.margin),
    padding: formatDimensions(props.settings.padding)
  };

  // Font Family
  if (typography.family && typography.family !== 'Default' && typography.family !== '') {
    const fontFamily = typography.family.includes(' ')
      ? `'${typography.family}', sans-serif`
      : `${typography.family}, sans-serif`;
    styles.fontFamily = fontFamily;
  }

  // Font Size
  if (typography.size) {
    const sizeValue = typeof typography.size === 'string' ? parseFloat(typography.size) : typography.size;
    const unit = typography.sizeUnit || 'px';
    if (!isNaN(sizeValue) && sizeValue > 0) {
      styles.fontSize = sizeValue + unit;
    }
  } else {
    // Default font sizes based on heading tag
    const defaultSizes = {
      h1: '2.5rem',
      h2: '2rem',
      h3: '1.75rem',
      h4: '1.5rem',
      h5: '1.25rem',
      h6: '1rem'
    };
    const tag = headingTag.value;
    if (defaultSizes[tag]) {
      styles.fontSize = defaultSizes[tag];
    }
  }

  // Font Weight
  if (typography.weight) {
    const weight = typeof typography.weight === 'string' ? typography.weight : String(typography.weight);
    if (weight && weight !== '400' && weight !== 'Normal') {
      styles.fontWeight = weight;
    }
  }

  // Line Height
  if (typography.lineHeight) {
    styles.lineHeight = typography.lineHeight;
  }

  // Letter Spacing
  if (typography.letterSpacing) {
    styles.letterSpacing = typography.letterSpacing + 'px';
  }

  // Text Transform
  if (typography.transform && typography.transform !== 'none') {
    styles.textTransform = typography.transform;
  }

  // Text Style (Italic)
  if (typography.style && typography.style !== 'normal') {
    styles.fontStyle = typography.style;
  }

  // Text Decoration
  if (typography.decoration && typography.decoration !== 'none') {
    styles.textDecoration = typography.decoration;
  }

  // Text Shadow
  if (props.settings.text_shadow) {
    const shadow = props.settings.text_shadow;
    if (shadow.color || shadow.horizontal || shadow.vertical || shadow.blur) {
      styles.textShadow = `${shadow.horizontal ?? 0}px ${shadow.vertical ?? 0}px ${shadow.blur ?? 0}px ${shadow.color ?? 'rgba(0,0,0,0.3)'}`;
    }
  }

  return styles;
});
</script>
