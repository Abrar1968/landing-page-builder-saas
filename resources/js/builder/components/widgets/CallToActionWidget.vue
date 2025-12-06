<template>
  <div class="relative p-8 rounded-lg" :style="containerStyles">
    <div
      v-if="settings.ribbon_text"
      class="absolute top-0 right-0 px-3 py-1 text-white text-sm font-medium"
      :style="ribbonStyles"
    >
      {{ settings.ribbon_text }}
    </div>

    <component :is="settings.title_tag ?? 'h3'" :style="titleStyles" class="text-2xl font-bold">
      {{ settings.title ?? 'This is the heading' }}
    </component>

    <p :style="descriptionStyles" class="mt-2">
      {{ settings.description ?? 'Click here to add your own text and edit me.' }}
    </p>

    <a
      :href="settings.button_link ?? '#'"
      :target="settings.button_target ? '_blank' : '_self'"
      :style="buttonStyles"
      class="mt-4 inline-block px-6 py-2 rounded font-medium transition-colors"
    >
      {{ settings.button_text ?? 'Click Here' }}
    </a>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const containerStyles = computed(() => ({
  backgroundColor: props.settings.background_color ?? '#f3f4f6',
  backgroundImage: props.settings.background_image ? `url(${props.settings.background_image})` : 'none',
  backgroundSize: 'cover',
  backgroundPosition: 'center',
  textAlign: props.settings.alignment ?? 'left'
}));

const ribbonStyles = computed(() => ({
  backgroundColor: props.settings.ribbon_color ?? '#ef4444'
}));

const titleStyles = computed(() => ({
  color: props.settings.title_color ?? '#1f2937'
}));

const descriptionStyles = computed(() => ({
  color: props.settings.description_color ?? '#4b5563'
}));

const buttonStyles = computed(() => ({
  backgroundColor: props.settings.button_background ?? '#4f46e5',
  color: props.settings.button_color ?? '#ffffff'
}));
</script>
