<template>
  <div
    class="widget-content"
    :style="wrapperStyles"
    :class="[settings.css_classes, responsiveClasses, animationClasses]"
    :id="settings.css_id"
    ref="widgetRef"
  >
    <component
      v-if="widgetComponent"
      :is="widgetComponent"
      :settings="settings"
      :hover-settings="hoverSettings"
      :widget-id="widget.id"
    />
    <div v-else class="p-4 bg-yellow-50 border border-yellow-200 rounded text-center text-yellow-700">
      <span class="font-medium">Unknown widget:</span> {{ widget.widgetType }}
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
import { widgetComponentMap } from './widgets/index.js';

const props = defineProps({
  widget: {
    type: Object,
    required: true
  }
});

const settings = computed(() => props.widget.settings || {});
const hoverSettings = computed(() => props.widget.hover_settings || {});
const widgetRef = ref(null);
const animationTriggered = ref(false);

// DEBUG: Watch for widget.settings changes at WidgetRenderer level
watch(() => props.widget.settings, (newVal) => {
  console.log('[WidgetRenderer] widget.settings changed for', props.widget.widgetType, props.widget.id);
  console.log('[WidgetRenderer] New settings:', JSON.stringify(newVal));
}, { deep: true });

// Get the widget component from the map
const widgetComponent = computed(() => {
  return widgetComponentMap[props.widget.widgetType] || null;
});

// Format dimensions helper (for margin/padding objects)
const formatDimensions = (dims) => {
  if (!dims) return '';
  if (typeof dims === 'string') return dims;
  if (dims.linked) {
    const val = dims.top ?? 0;
    return `${val}px`;
  }
  return `${dims.top ?? 0}px ${dims.right ?? 0}px ${dims.bottom ?? 0}px ${dims.left ?? 0}px`;
};

// Format border width (can be number or object with sides)
const formatBorderWidth = (width) => {
  if (!width) return '1px';
  if (typeof width === 'number' || typeof width === 'string') return width + 'px';
  if (typeof width === 'object') {
    return `${width.top ?? 1}px ${width.right ?? 1}px ${width.bottom ?? 1}px ${width.left ?? 1}px`;
  }
  return '1px';
};

// Format border radius (can be number or object with corners)
const formatBorderRadius = (radius) => {
  if (!radius) return '';
  if (typeof radius === 'number' || typeof radius === 'string') return radius + 'px';
  if (typeof radius === 'object') {
    return `${radius.topLeft ?? 0}px ${radius.topRight ?? 0}px ${radius.bottomRight ?? 0}px ${radius.bottomLeft ?? 0}px`;
  }
  return '';
};

// Wrapper styles (common to all widgets)
const wrapperStyles = computed(() => {
  const s = settings.value;
  const styles = {};

  // Background - handle nested object structure
  const bg = s.background;
  if (bg) {
    if (bg.type === 'gradient' && bg.color1 && bg.color2) {
      const angle = bg.angle ?? 180;
      styles.backgroundImage = `linear-gradient(${angle}deg, ${bg.color1}, ${bg.color2})`;
    } else if (bg.color) {
      styles.backgroundColor = bg.color;
    }
    if (bg.image) {
      styles.backgroundImage = `url(${bg.image})`;
      styles.backgroundSize = bg.size ?? 'cover';
      styles.backgroundPosition = bg.position ?? 'center center';
      styles.backgroundRepeat = bg.repeat ?? 'no-repeat';
    }
  }
  // Also support flat properties for backwards compatibility
  if (s.background_color) styles.backgroundColor = s.background_color;
  if (s.background_image) {
    styles.backgroundImage = `url(${s.background_image})`;
    styles.backgroundSize = s.background_size ?? 'cover';
    styles.backgroundPosition = s.background_position ?? 'center';
    styles.backgroundRepeat = s.background_repeat ?? 'no-repeat';
  }

  // Spacing - margin and padding are stored as objects {top, right, bottom, left}
  if (s.margin) styles.margin = formatDimensions(s.margin);
  if (s.padding) styles.padding = formatDimensions(s.padding);

  // Border - handle nested object structure {type, width, color, radius}
  const border = s.border;
  if (border && border.type && border.type !== 'none') {
    styles.borderStyle = border.type;
    styles.borderWidth = formatBorderWidth(border.width);
    styles.borderColor = border.color ?? '#e5e7eb';
  }
  if (border?.radius) {
    styles.borderRadius = formatBorderRadius(border.radius);
  }
  // Also support flat properties for backwards compatibility
  if (s.border_type && s.border_type !== 'none') {
    styles.borderStyle = s.border_type;
    styles.borderWidth = (s.border_width ?? 1) + 'px';
    styles.borderColor = s.border_color ?? '#e5e7eb';
  }
  if (s.border_radius && !border?.radius) {
    styles.borderRadius = typeof s.border_radius === 'object'
      ? formatDimensions(s.border_radius)
      : s.border_radius + 'px';
  }

  // Box Shadow - stored as object {horizontal, vertical, blur, spread, color}
  if (s.box_shadow) {
    const sh = s.box_shadow;
    const h = sh.horizontal ?? 0;
    const v = sh.vertical ?? 4;
    const b = sh.blur ?? 10;
    const sp = sh.spread ?? 0;
    const c = sh.color ?? 'rgba(0,0,0,0.1)';
    // Only apply if there's actual shadow values
    if (h !== 0 || v !== 0 || b !== 0 || sp !== 0) {
      styles.boxShadow = h + 'px ' + v + 'px ' + b + 'px ' + sp + 'px ' + c;
    }
  }

  // Animation delay from motion effects
  const motion = s.motion_effects;
  if (motion?.animation_delay) {
    styles.animationDelay = `${motion.animation_delay}ms`;
  }

  // Sticky positioning with offset
  if (motion?.sticky) {
    styles.position = 'sticky';
    const offset = motion.sticky_offset ?? 0;
    if (motion.sticky_position === 'bottom') {
      styles.bottom = offset + 'px';
    } else {
      styles.top = offset + 'px';
    }
    styles.zIndex = styles.zIndex ?? 100;
  }

  // Z-Index
  if (s.z_index) styles.zIndex = s.z_index;

  return styles;
});

// Responsive visibility classes
const responsiveClasses = computed(() => {
  const v = settings.value.responsive_visibility;
  if (!v) return '';
  const classes = [];
  if (v.hide_desktop) classes.push('hidden-desktop');
  if (v.hide_tablet) classes.push('hidden-tablet');
  if (v.hide_mobile) classes.push('hidden-mobile');
  return classes.join(' ');
});

// Animation classes
const animationClasses = computed(() => {
  const motion = settings.value.motion_effects;
  if (!motion) return '';

  const classes = [];

  if (motion.entrance_animation && motion.entrance_animation !== 'none') {
    if (!animationTriggered.value) {
      classes.push('animation-hidden');
    } else {
      classes.push(`animate-${motion.entrance_animation}`);
      if (motion.animation_duration) {
        classes.push(`animation-duration-${motion.animation_duration}`);
      }
    }
  }

  if (motion.sticky) {
    classes.push(`sticky-${motion.sticky_position || 'top'}`);
  }

  if (motion.parallax) {
    classes.push('parallax-scroll');
  }

  return classes.join(' ');
});

// Intersection Observer for entrance animations
let animationObserver = null;

const setupAnimationObserver = () => {
  const motion = settings.value.motion_effects;
  if (!motion?.entrance_animation || motion.entrance_animation === 'none') {
    animationTriggered.value = true;
    return;
  }

  animationObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting && !animationTriggered.value) {
        animationTriggered.value = true;
        animationObserver?.disconnect();
      }
    });
  }, { threshold: 0.1 });

  if (widgetRef.value) {
    animationObserver.observe(widgetRef.value);
  }
};

onMounted(() => {
  nextTick(() => {
    setupAnimationObserver();
  });
});

onBeforeUnmount(() => {
  animationObserver?.disconnect();
});
</script>

<style scoped>
/* Responsive visibility */
@media (min-width: 1025px) {
  .hidden-desktop {
    display: none !important;
  }
}

@media (min-width: 768px) and (max-width: 1024px) {
  .hidden-tablet {
    display: none !important;
  }
}

@media (max-width: 767px) {
  .hidden-mobile {
    display: none !important;
  }
}

/* Animation Base */
.animation-hidden {
  opacity: 0;
}

/* Duration Classes */
.animation-duration-slow { animation-duration: 2s; }
.animation-duration-normal { animation-duration: 1s; }
.animation-duration-fast { animation-duration: 0.5s; }

/* Entrance Animations */
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeInLeft { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
@keyframes fadeInRight { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
@keyframes zoomIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
@keyframes zoomOut { from { opacity: 0; transform: scale(1.1); } to { opacity: 1; transform: scale(1); } }
@keyframes bounceIn { 0% { opacity: 0; transform: scale(0.3); } 50% { opacity: 1; transform: scale(1.05); } 70% { transform: scale(0.9); } 100% { transform: scale(1); } }
@keyframes slideInDown { from { opacity: 0; transform: translateY(-100%); } to { opacity: 1; transform: translateY(0); } }
@keyframes slideInUp { from { opacity: 0; transform: translateY(100%); } to { opacity: 1; transform: translateY(0); } }
@keyframes slideInLeft { from { opacity: 0; transform: translateX(-100%); } to { opacity: 1; transform: translateX(0); } }
@keyframes slideInRight { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }
@keyframes rotateIn { from { opacity: 0; transform: rotate(-200deg); } to { opacity: 1; transform: rotate(0); } }
@keyframes flipInX { from { opacity: 0; transform: perspective(400px) rotateX(90deg); } to { opacity: 1; transform: perspective(400px) rotateX(0); } }
@keyframes flipInY { from { opacity: 0; transform: perspective(400px) rotateY(90deg); } to { opacity: 1; transform: perspective(400px) rotateY(0); } }

/* Animation Classes */
.animate-fadeIn { animation-name: fadeIn; animation-fill-mode: forwards; }
.animate-fadeInDown { animation-name: fadeInDown; animation-fill-mode: forwards; }
.animate-fadeInUp { animation-name: fadeInUp; animation-fill-mode: forwards; }
.animate-fadeInLeft { animation-name: fadeInLeft; animation-fill-mode: forwards; }
.animate-fadeInRight { animation-name: fadeInRight; animation-fill-mode: forwards; }
.animate-zoomIn { animation-name: zoomIn; animation-fill-mode: forwards; }
.animate-zoomOut { animation-name: zoomOut; animation-fill-mode: forwards; }
.animate-bounceIn { animation-name: bounceIn; animation-fill-mode: forwards; }
.animate-slideInDown { animation-name: slideInDown; animation-fill-mode: forwards; }
.animate-slideInUp { animation-name: slideInUp; animation-fill-mode: forwards; }
.animate-slideInLeft { animation-name: slideInLeft; animation-fill-mode: forwards; }
.animate-slideInRight { animation-name: slideInRight; animation-fill-mode: forwards; }
.animate-rotateIn { animation-name: rotateIn; animation-fill-mode: forwards; }
.animate-flipInX { animation-name: flipInX; animation-fill-mode: forwards; }
.animate-flipInY { animation-name: flipInY; animation-fill-mode: forwards; }

/* Sticky positioning */
.sticky-top { position: sticky; top: 0; z-index: 100; }
.sticky-bottom { position: sticky; bottom: 0; z-index: 100; }
</style>
