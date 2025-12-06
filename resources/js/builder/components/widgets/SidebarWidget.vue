<template>
  <div class="sidebar-widget border-2 border-dashed border-gray-300 rounded p-4 text-center text-gray-400">
    <span class="text-2xl">▐</span>
    <p class="mt-2">Sidebar: {{ settings.sidebar_id ?? 'primary' }}</p>
  </div>
</template>

<script setup>
defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});
</script>
