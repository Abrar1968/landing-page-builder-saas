<template>
  <div :style="containerStyles">
    <!-- Simple divider -->
    <hr v-if="!hasElement" :style="dividerStyles" />
    <!-- Divider with element -->
    <div v-else class="flex items-center" :style="wrapperStyles">
      <hr :style="lineStyles" class="flex-1" />
      <span :style="elementStyles">{{ elementContent }}</span>
      <hr :style="lineStyles" class="flex-1" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const hasElement = computed(() =>
  props.settings.divider_element && props.settings.divider_element !== 'none'
);

const elementContent = computed(() =>
  props.settings.divider_element === 'text'
    ? (props.settings.element_text || 'OR')
    : (props.settings.element_icon || '★')
);

const containerStyles = computed(() => ({
  textAlign: props.settings.alignment ?? 'center',
  padding: `${props.settings.gap ?? 15}px 0`
}));

const dividerStyles = computed(() => ({
  width: (props.settings.width ?? 100) + '%',
  margin: '0 auto',
  border: 'none',
  borderTop: `${props.settings.weight ?? 1}px ${props.settings.style ?? 'solid'} ${props.settings.color ?? '#d1d5db'}`
}));

const wrapperStyles = computed(() => ({
  width: (props.settings.width ?? 100) + '%',
  margin: '0 auto',
  gap: (props.settings.element_spacing ?? 15) + 'px'
}));

const lineStyles = computed(() => ({
  border: 'none',
  borderTop: `${props.settings.weight ?? 1}px ${props.settings.style ?? 'solid'} ${props.settings.color ?? '#d1d5db'}`
}));

const elementStyles = computed(() => ({
  color: props.settings.element_color ?? '#6b7280',
  fontSize: (props.settings.element_size ?? 14) + 'px',
  fontWeight: props.settings.element_weight ?? 'normal',
  whiteSpace: 'nowrap'
}));
</script>
