<template>
  <div class="navigator-panel h-full flex flex-col bg-white">
    <!-- Header -->
    <div class="p-4 border-b flex items-center justify-between">
      <h3 class="font-medium text-gray-900">Navigator</h3>
      <button
        @click="expandAll = !expandAll"
        class="text-xs text-gray-500 hover:text-gray-700"
      >
        {{ expandAll ? 'Collapse All' : 'Expand All' }}
      </button>
    </div>

    <!-- Tree -->
    <div class="flex-1 overflow-y-auto p-2">
      <div v-if="content.length === 0" class="text-center text-gray-400 text-sm py-8">
        No elements yet. Add a section to start building.
      </div>
      <NavigatorItem
        v-for="item in content"
        :key="item.id"
        :item="item"
        :level="0"
        :selectedId="selectedElement"
        :expandAll="expandAll"
        @select="$emit('select', $event)"
        @delete="$emit('delete', $event)"
        @duplicate="$emit('duplicate', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import NavigatorItem from './NavigatorItem.vue';

defineProps({
  content: {
    type: Array,
    default: () => []
  },
  selectedElement: {
    type: String,
    default: null
  }
});

defineEmits(['select', 'delete', 'duplicate']);

const expandAll = ref(false);
</script>

<style scoped>
.navigator-panel {
  font-size: 13px;
}
</style>
