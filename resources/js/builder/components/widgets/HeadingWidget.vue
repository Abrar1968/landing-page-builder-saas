<template>
  <component
    :is="headingTag"
    :style="headingStyles"
    :class="[`heading-widget-${widgetId}`, 'transition-all duration-300']"
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
  <!-- Inject scoped hover styles -->
  <component :is="'style'" v-if="hasHoverStyles">
    .heading-widget-{{ widgetId }}:hover {
      {{ hoverStylesCSS }}
    }
  </component>
</template>

<script setup>
import { computed, watch } from 'vue';

const props = defineProps({
  settings: {
    type: Object,
    required: true
  },
  hoverSettings: {
    type: Object,
    default: () => ({})
  },
  widgetId: {
    type: String,
    required: true
  }
});

// DEBUG: Watch for settings changes
watch(() => props.settings, (newVal, oldVal) => {
  console.log('[HeadingWidget] Settings changed for', props.widgetId);
  console.log('[HeadingWidget] New settings:', JSON.stringify(newVal));
}, { deep: true });

// Computed property for heading tag (H1-H6)
const headingTag = computed(() => {
  return props.settings.size || props.settings.tag || 'h2';
});

// Computed property for heading styles (text-specific only - wrapper handles margin/padding/background/border)
const headingStyles = computed(() => {
  const typography = props.settings.typography || {};
  const styles = {
    color: props.settings.text_color ?? '#1f2937',
    textAlign: props.settings.alignment ?? 'left',
    // Remove default margin on heading elements
    margin: 0,
    padding: 0
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

  // Font Weight - always apply if set
  if (typography.weight) {
    styles.fontWeight = typography.weight;
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

// Check if we have any hover styles
const hasHoverStyles = computed(() => {
  const h = props.hoverSettings;
  return h && (h.text_color || h.typography);
});

// Generate hover CSS string
const hoverStylesCSS = computed(() => {
  const h = props.hoverSettings;
  if (!h) return '';

  const rules = [];

  if (h.text_color) {
    rules.push(`color: ${h.text_color} !important`);
  }

  if (h.typography) {
    const t = h.typography;
    if (t.size) rules.push(`font-size: ${t.size}${t.sizeUnit || 'px'} !important`);
    if (t.weight) rules.push(`font-weight: ${t.weight} !important`);
    if (t.letterSpacing) rules.push(`letter-spacing: ${t.letterSpacing}px !important`);
  }

  if (h.text_shadow) {
    const s = h.text_shadow;
    rules.push(`text-shadow: ${s.horizontal ?? 0}px ${s.vertical ?? 0}px ${s.blur ?? 0}px ${s.color ?? 'rgba(0,0,0,0.3)'} !important`);
  }

  return rules.join('; ');
});
</script>
