<template>
  <div class="html-widget" v-html="sanitizedHtml"></div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const sanitizedHtml = computed(() =>
  props.settings.html_code ?? '<div class="custom-html"><p>Add your custom HTML here</p></div>'
);
</script>
