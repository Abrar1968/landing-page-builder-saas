<template>
  <div :style="containerStyles">
    <a v-for="social in visibleSocials" :key="social.name" :href="social.url" :style="iconStyles" target="_blank" rel="noopener noreferrer">
      {{ social.icon }}
    </a>
    <span v-if="visibleSocials.length === 0" class="text-gray-400">Add social links</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const socials = [
  { name: 'facebook', icon: 'f', key: 'facebook' },
  { name: 'twitter', icon: '𝕏', key: 'twitter' },
  { name: 'instagram', icon: '📷', key: 'instagram' },
  { name: 'linkedin', icon: 'in', key: 'linkedin' },
  { name: 'youtube', icon: '▶', key: 'youtube' },
  { name: 'tiktok', icon: '♪', key: 'tiktok' }
];

const visibleSocials = computed(() =>
  socials.filter(s => props.settings[s.key]).map(s => ({ ...s, url: props.settings[s.key] }))
);

const containerStyles = computed(() => ({
  display: 'flex',
  justifyContent: props.settings.alignment ?? 'center',
  gap: (props.settings.spacing ?? 10) + 'px',
  flexWrap: 'wrap'
}));

const iconStyles = computed(() => ({
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  width: (props.settings.icon_size ?? 40) + 'px',
  height: (props.settings.icon_size ?? 40) + 'px',
  fontSize: (props.settings.icon_size ?? 40) * 0.5 + 'px',
  color: props.settings.icon_color ?? '#ffffff',
  backgroundColor: props.settings.background_color ?? '#4f46e5',
  borderRadius: props.settings.shape === 'circle' ? '50%' : (props.settings.border_radius ?? 4) + 'px',
  textDecoration: 'none',
  transition: 'all 0.3s ease'
}));
</script>
