<template>
  <div :style="styles"></div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const styles = computed(() => ({
  height: (props.settings.space ?? 50) + (props.settings.space_unit ?? 'px')
}));
</script>
