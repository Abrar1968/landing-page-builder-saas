<template>
  <div :style="styles" v-html="content"></div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const content = computed(() => props.settings.editor ?? '<p>Lorem ipsum dolor sit amet</p>');

const styles = computed(() => ({
  color: props.settings.text_color ?? '#4b5563',
  textAlign: props.settings.alignment ?? 'left'
}));
</script>
