<template>
  <div :style="containerStyles">
    <div :style="starContainerStyles">
      <span
        v-for="i in 5"
        :key="i"
        :style="getStarStyle(i)"
      >★</span>
    </div>
    <div v-if="settings.title" :style="titleStyles" class="mt-1">{{ settings.title }}</div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const containerStyles = computed(() => ({
  textAlign: props.settings.alignment ?? 'left'
}));

const starContainerStyles = computed(() => ({
  fontSize: (props.settings.size ?? 24) + 'px',
  display: 'inline-flex',
  gap: (props.settings.gap ?? 2) + 'px'
}));

const getStarStyle = (index) => ({
  color: index <= (props.settings.rating ?? 4)
    ? (props.settings.color ?? '#fbbf24')
    : (props.settings.unmarked_color ?? '#d1d5db')
});

const titleStyles = computed(() => ({
  color: props.settings.title_color ?? '#6b7280',
  fontSize: '14px'
}));
</script>
