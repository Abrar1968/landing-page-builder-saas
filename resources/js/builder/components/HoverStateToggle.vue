<template>
  <div class="hover-state-toggle mb-4">
    <div class="flex items-center gap-2 p-1 bg-gray-100 rounded-lg">
      <button
        @click="$emit('update:state', 'normal')"
        :class="[
          'flex-1 px-3 py-1.5 text-sm font-medium rounded-md transition-all',
          state === 'normal'
            ? 'bg-white text-gray-900 shadow-sm'
            : 'text-gray-600 hover:text-gray-900'
        ]"
      >
        Normal
      </button>
      <button
        @click="$emit('update:state', 'hover')"
        :class="[
          'flex-1 px-3 py-1.5 text-sm font-medium rounded-md transition-all',
          state === 'hover'
            ? 'bg-white text-gray-900 shadow-sm'
            : 'text-gray-600 hover:text-gray-900'
        ]"
      >
        Hover
      </button>
    </div>
    <p v-if="state === 'hover'" class="text-xs text-gray-500 mt-2 flex items-center gap-1">
      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      Hover settings apply when mouse is over the element
    </p>
  </div>
</template>

<script setup>
defineProps({
  state: {
    type: String,
    default: 'normal',
    validator: (value) => ['normal', 'hover'].includes(value)
  }
});

defineEmits(['update:state']);
</script>
