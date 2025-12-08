<template>
  <component
    :is="linkWrapper"
    :href="settings.link?.url"
    :target="settings.link?.is_external ? '_blank' : undefined"
    :style="containerStyles"
    :class="['icon-box-wrapper block no-underline', layoutClass, `icon-box-${widgetId}`, hoverAnimationClass]"
  >
    <div class="icon-box-icon" :style="iconStyles">{{ settings.icon ?? '⚡' }}</div>
    <div class="icon-box-content" :style="contentStyles">
      <component :is="settings.title_tag ?? 'h4'" class="icon-box-title font-semibold" :style="titleStyles">
        {{ settings.title ?? 'Icon Box' }}
      </component>
      <p class="icon-box-description mt-2" :style="descriptionStyles">
        {{ settings.description ?? 'Click here to add your own text.' }}
      </p>
    </div>
  </component>

  <!-- Hover styles -->
  <component :is="'style'" v-if="hasHoverStyles">
    .icon-box-{{ widgetId }}:hover .icon-box-icon { color: {{ settings.hover_icon_color ?? settings.icon_color ?? '#4f46e5' }} !important; }
    .icon-box-{{ widgetId }}:hover .icon-box-title { color: {{ settings.hover_title_color ?? settings.title_color ?? '#1f2937' }} !important; }
  </component>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const linkWrapper = computed(() => props.settings.link?.url ? 'a' : 'div');

const layoutClass = computed(() => {
  const position = props.settings.icon_position ?? 'top';
  return {
    top: 'flex flex-col items-center text-center',
    left: 'flex flex-row items-start',
    right: 'flex flex-row-reverse items-start'
  }[position] || 'flex flex-col items-center text-center';
});

const containerStyles = computed(() => ({
  padding: (props.settings.padding ?? 20) + 'px',
  backgroundColor: props.settings.background_color ?? 'transparent',
  borderRadius: (props.settings.border_radius ?? 0) + 'px',
  gap: (props.settings.spacing ?? 15) + 'px'
}));

const iconStyles = computed(() => ({
  fontSize: (props.settings.icon_size ?? 50) + 'px',
  color: props.settings.icon_color ?? '#4f46e5',
  backgroundColor: props.settings.icon_background ?? 'transparent',
  padding: props.settings.icon_padding ? props.settings.icon_padding + 'px' : '0',
  borderRadius: props.settings.icon_border_radius ? props.settings.icon_border_radius + 'px' : '0',
  transition: 'color 0.3s ease'
}));

const contentStyles = computed(() => ({
  flex: 1
}));

const titleStyles = computed(() => ({
  color: props.settings.title_color ?? '#1f2937',
  fontSize: (props.settings.title_size ?? 18) + 'px',
  transition: 'color 0.3s ease'
}));

const descriptionStyles = computed(() => ({
  color: props.settings.description_color ?? '#6b7280',
  fontSize: (props.settings.description_size ?? 14) + 'px'
}));

const hasHoverStyles = computed(() =>
  props.settings.hover_icon_color || props.settings.hover_title_color || props.settings.hover_animation
);

// Hover animation class
const hoverAnimationClass = computed(() => {
  const animation = props.settings.hover_animation;
  if (!animation || animation === 'none') return '';
  return `icon-box-hover-${animation}`;
});
</script>
