<template>
  <div :style="containerStyles">
    <a v-if="settings.link" :href="settings.link" :target="settings.link_target ? '_blank' : '_self'">
      <span :style="iconStyles" :class="iconClass">{{ settings.icon ?? '★' }}</span>
    </a>
    <span v-else :style="iconStyles" :class="iconClass">{{ settings.icon ?? '★' }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const containerStyles = computed(() => ({
  textAlign: props.settings.alignment ?? 'center'
}));

const iconStyles = computed(() => {
  const s = props.settings;
  return {
    fontSize: (s.size ?? 50) + 'px',
    color: s.primary_color ?? '#4f46e5',
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    width: s.view === 'framed' || s.view === 'stacked' ? (s.size ?? 50) * 2 + 'px' : 'auto',
    height: s.view === 'framed' || s.view === 'stacked' ? (s.size ?? 50) * 2 + 'px' : 'auto',
    backgroundColor: s.view === 'stacked' ? (s.secondary_color ?? '#e5e7eb') : 'transparent',
    border: s.view === 'framed' ? `2px solid ${s.secondary_color ?? '#4f46e5'}` : 'none',
    borderRadius: s.shape === 'circle' ? '50%' : (s.border_radius ?? 0) + 'px',
    transition: 'all 0.3s ease',
    cursor: s.link ? 'pointer' : 'default',
    transform: `rotate(${s.rotate ?? 0}deg)`
  };
});

const iconClass = computed(() => `icon-widget-${props.widgetId}`);
</script>
