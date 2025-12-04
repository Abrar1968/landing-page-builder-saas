<template>
  <div class="navigator-item">
    <div
      @click="$emit('select', item.id)"
      :class="[
        'flex items-center gap-1 px-2 py-1.5 rounded cursor-pointer group hover:bg-gray-100 transition-colors',
        selectedId === item.id ? 'bg-blue-50 text-blue-600' : 'text-gray-700'
      ]"
      :style="{ paddingLeft: (level * 16 + 8) + 'px' }"
    >
      <!-- Expand/Collapse Icon -->
      <button
        v-if="hasChildren"
        @click.stop="toggleExpand"
        class="w-4 h-4 flex items-center justify-center hover:bg-gray-200 rounded"
      >
        <span class="text-xs">{{ isExpanded ? '▼' : '▶' }}</span>
      </button>
      <span v-else class="w-4"></span>

      <!-- Element Icon -->
      <span class="text-sm">{{ getIcon() }}</span>

      <!-- Element Label -->
      <span class="flex-1 truncate text-sm">{{ getLabel() }}</span>

      <!-- Actions (show on hover) -->
      <div class="hidden group-hover:flex items-center gap-1">
        <button
          @click.stop="$emit('duplicate', item.id)"
          class="w-6 h-6 flex items-center justify-center hover:bg-gray-200 rounded"
          title="Duplicate"
        >
          <span class="text-xs">📋</span>
        </button>
        <button
          @click.stop="$emit('delete', item.id)"
          class="w-6 h-6 flex items-center justify-center hover:bg-red-100 hover:text-red-600 rounded"
          title="Delete"
        >
          <span class="text-xs">🗑</span>
        </button>
      </div>
    </div>

    <!-- Children -->
    <div v-if="hasChildren && isExpanded">
      <NavigatorItem
        v-for="child in item.elements"
        :key="child.id"
        :item="child"
        :level="level + 1"
        :selectedId="selectedId"
        :expandAll="expandAll"
        @select="$emit('select', $event)"
        @delete="$emit('delete', $event)"
        @duplicate="$emit('duplicate', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { widgetRegistry } from '../widgets/registry';

const props = defineProps({
  item: {
    type: Object,
    required: true
  },
  level: {
    type: Number,
    default: 0
  },
  selectedId: {
    type: String,
    default: null
  },
  expandAll: {
    type: Boolean,
    default: false
  }
});

defineEmits(['select', 'delete', 'duplicate']);

const isExpanded = ref(props.level < 2); // Auto-expand first 2 levels

const hasChildren = computed(() => {
  return props.item.elements && props.item.elements.length > 0;
});

function toggleExpand() {
  isExpanded.value = !isExpanded.value;
}

function getIcon() {
  if (props.item.elType === 'section') return '📦';
  if (props.item.elType === 'container') return '🟪';
  if (props.item.elType === 'column') return '📊';
  if (props.item.elType === 'widget') {
    const widget = widgetRegistry.get(props.item.widgetType);
    return widget?.icon || '🔲';
  }
  return '❓';
}

function getLabel() {
  if (props.item.elType === 'section') {
    return `Section (${props.item.elements?.length || 0} containers)`;
  }
  if (props.item.elType === 'container') {
    const width = props.item.settings?.content_width || 'boxed';
    return `Container (${width})`;
  }
  if (props.item.elType === 'column') {
    const width = props.item.settings?._column_size || 100;
    return `Column (${width}%)`;
  }
  if (props.item.elType === 'widget') {
    const widget = widgetRegistry.get(props.item.widgetType);
    return widget?.title || 'Widget';
  }
  return 'Unknown';
}

// Watch expandAll prop
watch(() => props.expandAll, (newVal) => {
  isExpanded.value = newVal;
});
</script>

<style scoped>
.navigator-item {
  user-select: none;
}
</style>
