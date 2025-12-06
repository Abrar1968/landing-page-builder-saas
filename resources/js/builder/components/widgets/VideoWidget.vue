<template>
  <div class="video-wrapper" :style="wrapperStyles">
    <iframe
      v-if="embedUrl"
      :src="embedUrl"
      frameborder="0"
      allowfullscreen
      class="w-full h-full"
    ></iframe>
    <div v-else class="video-placeholder">
      <span>▶</span>
      <span>Add video URL</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const wrapperStyles = computed(() => ({
  width: (props.settings.width ?? 100) + '%',
  aspectRatio: props.settings.aspect_ratio ?? '16/9',
  backgroundColor: '#000'
}));

const embedUrl = computed(() => {
  const url = props.settings.youtube_url ?? '';
  const videoType = props.settings.video_type ?? 'youtube';

  if (!url) return '';

  const autoplay = props.settings.autoplay ? '1' : '0';
  const mute = props.settings.mute ? '1' : '0';
  const loop = props.settings.loop ? '1' : '0';
  const controls = (props.settings.controls ?? true) ? '1' : '0';
  const startTime = props.settings.start_time ?? 0;

  if (videoType === 'vimeo') {
    const match = url.match(/(?:vimeo\.com\/|player\.vimeo\.com\/video\/)(\d+)/);
    const videoId = match?.[1];
    if (!videoId) return '';

    let params = [];
    if (autoplay === '1') params.push('autoplay=1');
    if (mute === '1') params.push('muted=1');
    if (loop === '1') params.push('loop=1');
    if (controls === '0') params.push('controls=0');

    return `https://player.vimeo.com/video/${videoId}${params.length ? '?' + params.join('&') : ''}`;
  }

  // YouTube
  const patterns = [
    /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\s?]+)/
  ];

  let videoId = '';
  for (const pattern of patterns) {
    const match = url.match(pattern);
    if (match) {
      videoId = match[1];
      break;
    }
  }

  if (!videoId) return '';

  let params = [
    `autoplay=${autoplay}`,
    `mute=${mute}`,
    `loop=${loop}`,
    `controls=${controls}`,
    `modestbranding=${props.settings.modest_branding ? '1' : '0'}`
  ];

  if (startTime > 0) params.push(`start=${startTime}`);
  if (loop === '1') params.push(`playlist=${videoId}`);

  return `https://www.youtube.com/embed/${videoId}?${params.join('&')}`;
});
</script>

<style scoped>
.video-placeholder {
  width: 100%;
  height: 100%;
  min-height: 200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #1f2937;
  color: #9ca3af;
  gap: 8px;
}
.video-placeholder span:first-child {
  font-size: 48px;
}
</style>
