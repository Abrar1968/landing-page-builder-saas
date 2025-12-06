<template>
  <div :id="settings.anchor_id ?? 'anchor'" class="menu-anchor-widget h-0"></div>
</template>

<script setup>
defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});
</script>
