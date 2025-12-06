<template>
  <div :style="containerStyles">
    <div :style="numberStyles" class="font-bold">
      {{ settings.prefix ?? '' }}{{ settings.ending_number ?? 100 }}{{ settings.suffix ?? '' }}
    </div>
    <div :style="titleStyles">{{ settings.title ?? 'Cool Number' }}</div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const containerStyles = computed(() => ({
  textAlign: props.settings.alignment ?? 'center'
}));

const numberStyles = computed(() => ({
  fontSize: (props.settings.number_size ?? 48) + 'px',
  color: props.settings.number_color ?? '#4f46e5'
}));

const titleStyles = computed(() => ({
  color: props.settings.title_color ?? '#6b7280',
  marginTop: '8px'
}));
</script>
