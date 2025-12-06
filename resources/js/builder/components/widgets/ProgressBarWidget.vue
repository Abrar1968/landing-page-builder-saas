<template>
  <div>
    <div v-if="settings.title" class="mb-2 flex justify-between">
      <span :style="{ color: settings.title_color ?? '#1f2937' }">{{ settings.title }}</span>
      <span v-if="settings.display_percent !== false" :style="{ color: settings.title_color ?? '#1f2937' }">
        {{ settings.percent ?? 75 }}%
      </span>
    </div>
    <div :style="trackStyles">
      <div :style="barStyles"></div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const trackStyles = computed(() => ({
  backgroundColor: props.settings.bg_color ?? '#e5e7eb',
  height: (props.settings.height ?? 12) + 'px',
  borderRadius: '9999px',
  overflow: 'hidden'
}));

const barStyles = computed(() => ({
  backgroundColor: props.settings.bar_color ?? '#4f46e5',
  width: (props.settings.percent ?? 75) + '%',
  height: '100%',
  borderRadius: '9999px',
  transition: 'width 0.3s ease'
}));
</script>
