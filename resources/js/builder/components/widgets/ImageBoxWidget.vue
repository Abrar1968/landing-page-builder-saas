<template>
  <component
    :is="linkWrapper"
    :href="settings.link?.url"
    :target="settings.link?.is_external ? '_blank' : undefined"
    :style="containerStyles"
    :class="['image-box-wrapper block no-underline', layoutClass, `image-box-${widgetId}`]"
  >
    <div class="image-box-image" :style="imageContainerStyles">
      <img
        v-if="settings.image_url"
        :src="settings.image_url"
        class="object-cover rounded-lg transition-all duration-300"
        :class="hoverClass"
        :style="imageStyles"
      />
      <div v-else class="bg-gray-200 rounded-lg flex items-center justify-center text-gray-400" :style="imageStyles">🖼️</div>
    </div>
    <div class="image-box-content" :style="contentStyles">
      <component :is="settings.title_tag ?? 'h4'" class="image-box-title font-semibold text-lg" :style="titleStyles">
        {{ settings.title ?? 'Image Box' }}
      </component>
      <p class="image-box-description mt-2" :style="descriptionStyles">
        {{ settings.description ?? 'Click here to add your own text.' }}
      </p>
    </div>
  </component>

  <!-- Hover styles -->
  <component :is="'style'" v-if="hasHoverStyles">
    .image-box-{{ widgetId }}:hover .image-box-title { color: {{ settings.hover_title_color ?? settings.title_color ?? '#1f2937' }} !important; }
    .image-box-{{ widgetId }} .hover-zoom:hover { transform: scale(1.1); }
    .image-box-{{ widgetId }} .hover-zoom_out:hover { transform: scale(0.9); }
    .image-box-{{ widgetId }} .hover-grayscale:hover { filter: grayscale(100%); }
    .image-box-{{ widgetId }} .hover-blur:hover { filter: blur(3px); }
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
  const position = props.settings.image_position ?? 'top';
  return {
    top: 'flex flex-col',
    left: 'flex flex-row items-start',
    right: 'flex flex-row-reverse items-start'
  }[position] || 'flex flex-col';
});

const hoverClass = computed(() => {
  const effect = props.settings.hover_animation;
  if (!effect || effect === 'none') return '';
  return `hover-${effect}`;
});

const containerStyles = computed(() => ({
  padding: (props.settings.padding ?? 0) + 'px',
  backgroundColor: props.settings.background_color ?? 'transparent',
  borderRadius: (props.settings.border_radius ?? 0) + 'px',
  gap: (props.settings.spacing ?? 15) + 'px'
}));

const imageContainerStyles = computed(() => ({
  width: props.settings.image_position === 'top' ? '100%' : (props.settings.image_width ?? 50) + '%'
}));

const imageStyles = computed(() => ({
  width: '100%',
  height: (props.settings.image_height ?? 200) + 'px'
}));

const contentStyles = computed(() => ({
  flex: 1,
  textAlign: props.settings.content_alignment ?? 'left'
}));

const titleStyles = computed(() => ({
  color: props.settings.title_color ?? '#1f2937',
  transition: 'color 0.3s ease'
}));

const descriptionStyles = computed(() => ({
  color: props.settings.description_color ?? '#6b7280'
}));

const hasHoverStyles = computed(() =>
  props.settings.hover_title_color || props.settings.hover_animation
);
</script>
