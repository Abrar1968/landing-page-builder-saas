<template>
  <div class="text-path-widget text-center">
    <svg :viewBox="viewBox" class="w-full" :style="svgStyles">
      <defs>
        <path :id="`textPath-${widgetId}`" :d="pathD" fill="none" />
      </defs>
      <text :style="textStyles">
        <textPath :href="`#textPath-${widgetId}`" startOffset="50%" text-anchor="middle">
          {{ settings.text ?? 'Curved Text' }}
        </textPath>
      </text>
    </svg>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const pathType = computed(() => props.settings.path_type ?? 'arc');

const viewBox = computed(() => {
  if (pathType.value === 'circle') return '0 0 200 200';
  return '0 0 500 150';
});

const pathD = computed(() => {
  switch (pathType.value) {
    case 'wave':
      return 'M 0 75 Q 125 25, 250 75 T 500 75';
    case 'circle':
      return 'M 100 10 a 90 90 0 1 1 -1 0';
    case 'line':
      return 'M 0 75 L 500 75';
    default: // arc
      return 'M 50 120 Q 250 20, 450 120';
  }
});

const svgStyles = computed(() => ({
  maxWidth: (props.settings.width ?? 500) + 'px',
  margin: '0 auto'
}));

const textStyles = computed(() => ({
  fontSize: (props.settings.font_size ?? 24) + 'px',
  fill: props.settings.text_color ?? '#1f2937',
  fontWeight: props.settings.font_weight ?? '400',
  fontFamily: props.settings.font_family ?? 'inherit'
}));
</script>
