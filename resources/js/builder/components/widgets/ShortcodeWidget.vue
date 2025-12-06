<template>
  <div class="shortcode-widget border-2 border-dashed border-gray-300 rounded p-4 text-center text-gray-500">
    <span class="text-xl">[ ]</span>
    <p class="mt-2 font-mono text-sm">{{ settings.shortcode ?? '[shortcode]' }}</p>
  </div>
</template>

<script setup>
defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});
</script>
