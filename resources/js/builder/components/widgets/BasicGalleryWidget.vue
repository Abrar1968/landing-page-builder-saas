<template>
  <div class="basic-gallery-widget">
    <div class="grid" :style="gridStyles">
      <div
        v-for="(image, index) in images"
        :key="index"
        class="gallery-item relative overflow-hidden cursor-pointer"
        :class="hoverClass"
        @click="openLightbox(index)"
      >
        <img
          v-if="image"
          :src="image"
          class="w-full h-40 object-cover transition-all duration-300"
          :style="imageStyles"
        />
        <div
          v-else
          class="w-full h-40 bg-gray-200 flex items-center justify-center text-gray-400"
          :style="imageStyles"
        >
          <span class="text-3xl">🖼️</span>
        </div>
      </div>
    </div>

    <!-- Lightbox -->
    <div
      v-if="lightboxOpen"
      class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center"
      @click="lightboxOpen = false"
    >
      <button class="absolute top-4 right-4 text-white text-3xl" @click="lightboxOpen = false">×</button>
      <button class="absolute left-4 text-white text-3xl" @click.stop="prevImage">‹</button>
      <img :src="images[lightboxIndex]" class="max-h-[90vh] max-w-[90vw] object-contain" />
      <button class="absolute right-4 text-white text-3xl" @click.stop="nextImage">›</button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const lightboxOpen = ref(false);
const lightboxIndex = ref(0);

const images = computed(() => [
  props.settings.image1,
  props.settings.image2,
  props.settings.image3,
  props.settings.image4,
  props.settings.image5,
  props.settings.image6
].filter(Boolean));

const openLightbox = (index) => {
  if (props.settings.lightbox !== false) {
    lightboxIndex.value = index;
    lightboxOpen.value = true;
  }
};

const nextImage = () => {
  lightboxIndex.value = (lightboxIndex.value + 1) % images.value.length;
};

const prevImage = () => {
  lightboxIndex.value = (lightboxIndex.value - 1 + images.value.length) % images.value.length;
};

const hoverClass = computed(() => {
  const effect = props.settings.hover_effect;
  if (effect === 'zoom') return 'hover-zoom';
  if (effect === 'grayscale') return 'hover-grayscale';
  return '';
});

const gridStyles = computed(() => ({
  gridTemplateColumns: `repeat(${props.settings.columns ?? 3}, 1fr)`,
  gap: (props.settings.gap ?? 10) + 'px'
}));

const imageStyles = computed(() => ({
  borderRadius: (props.settings.border_radius ?? 8) + 'px'
}));
</script>

<style scoped>
.hover-zoom img:hover {
  transform: scale(1.1);
}
.hover-grayscale img:hover {
  filter: grayscale(100%);
}
</style>
