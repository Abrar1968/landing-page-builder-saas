<template>
  <div :style="containerStyles" class="inner-section-widget">
    <div class="grid" :style="gridStyles">
      <div v-for="i in columns" :key="i" class="border-2 border-dashed border-gray-300 rounded p-4 text-center text-gray-400">
        <slot :name="`column-${i}`">
          <p>Column {{ i }}</p>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const columns = computed(() => parseInt(props.settings.columns ?? 2));

const containerStyles = computed(() => ({
  padding: formatDimensions(props.settings.padding),
  margin: formatDimensions(props.settings.margin),
  backgroundColor: props.settings.background_color ?? 'transparent'
}));

const gridStyles = computed(() => ({
  gridTemplateColumns: `repeat(${columns.value}, 1fr)`,
  gap: (props.settings.column_gap ?? 20) + 'px'
}));

const formatDimensions = (dims) => {
  if (!dims) return '0';
  const unit = dims.unit ?? 'px';
  return `${dims.top ?? 0}${unit} ${dims.right ?? 0}${unit} ${dims.bottom ?? 0}${unit} ${dims.left ?? 0}${unit}`;
};
</script>
