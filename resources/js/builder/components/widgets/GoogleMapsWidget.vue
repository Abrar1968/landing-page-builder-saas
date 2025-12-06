<template>
  <div :style="containerStyles" class="bg-gray-200 rounded-lg overflow-hidden">
    <iframe
      v-if="mapUrl"
      :src="mapUrl"
      width="100%"
      height="100%"
      style="border:0;"
      allowfullscreen=""
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade"
    ></iframe>
    <div v-else class="w-full h-full flex items-center justify-center">
      <div class="text-center text-gray-500">
        <span class="text-4xl">🗺️</span>
        <p class="mt-2">{{ settings.address ?? 'New York, USA' }}</p>
        <p class="text-xs">Zoom: {{ settings.zoom ?? 14 }}</p>
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

const containerStyles = computed(() => ({
  height: (props.settings.height ?? 400) + 'px',
  width: '100%'
}));

const mapUrl = computed(() => {
  const address = props.settings.address;
  if (!address) return '';

  const zoom = props.settings.zoom ?? 14;
  const encoded = encodeURIComponent(address);

  // Use OpenStreetMap embed (free, no API key needed)
  return `https://www.openstreetmap.org/export/embed.html?bbox=-0.1,51.5,0.1,51.6&layer=mapnik&marker=${encoded}`;
});
</script>
