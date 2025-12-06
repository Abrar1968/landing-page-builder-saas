<template>
  <div class="soundcloud-widget">
    <iframe
      v-if="embedUrl"
      width="100%"
      :height="settings.height ?? 166"
      scrolling="no"
      frameborder="no"
      allow="autoplay"
      :src="embedUrl"
    ></iframe>
    <div
      v-else
      class="bg-gray-200 rounded flex items-center justify-center text-gray-500"
      :style="{ height: (settings.height ?? 166) + 'px' }"
    >
      <div class="text-center">
        <span class="text-4xl">🎵</span>
        <p class="mt-2 text-sm">Add SoundCloud URL</p>
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

const embedUrl = computed(() => {
  const url = props.settings.url;
  if (!url) return '';

  const autoPlay = props.settings.auto_play ? 'true' : 'false';
  const color = (props.settings.color ?? '#ff5500').replace('#', '');
  const visual = props.settings.visual ? 'true' : 'false';

  return `https://w.soundcloud.com/player/?url=${encodeURIComponent(url)}&auto_play=${autoPlay}&color=${color}&visual=${visual}&hide_related=false&show_comments=true&show_user=true&show_reposts=false`;
});
</script>
