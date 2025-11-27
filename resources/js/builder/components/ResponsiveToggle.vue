<template>
  <div class="responsive-toggle flex items-center gap-1 p-1 bg-gray-100 rounded-lg">
    <button
      @click="$emit('update:device', 'desktop')"
      :class="[
        'px-3 py-1.5 rounded text-sm font-medium transition-colors',
        device === 'desktop' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'
      ]"
      title="Desktop (> 1024px)"
    >
      <span class="text-base">🖥️</span>
      <span class="ml-1">Desktop</span>
    </button>
    <button
      @click="$emit('update:device', 'tablet')"
      :class="[
        'px-3 py-1.5 rounded text-sm font-medium transition-colors',
        device === 'tablet' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'
      ]"
      title="Tablet (768px - 1024px)"
    >
      <span class="text-base">📱</span>
      <span class="ml-1">Tablet</span>
    </button>
    <button
      @click="$emit('update:device', 'mobile')"
      :class="[
        'px-3 py-1.5 rounded text-sm font-medium transition-colors',
        device === 'mobile' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'
      ]"
      title="Mobile (< 768px)"
    >
      <span class="text-base">📱</span>
      <span class="ml-1">Mobile</span>
    </button>
  </div>
</template>

<script setup>
defineProps({
  device: {
    type: String,
    default: 'desktop',
    validator: (value) => ['desktop', 'tablet', 'mobile'].includes(value)
  }
});

defineEmits(['update:device']);
</script>

<style scoped>
.responsive-toggle button {
  white-space: nowrap;
}
</style>
