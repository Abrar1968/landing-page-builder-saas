<template>
  <div class="image-carousel-widget relative">
    <div class="flex overflow-hidden" :style="trackStyles" ref="track">
      <div
        v-for="(image, index) in images"
        :key="index"
        class="flex-shrink-0 transition-transform duration-300"
        :style="slideStyles"
      >
        <img
          v-if="image"
          :src="image"
          class="w-full h-48 object-cover"
          :style="imageStyles"
        />
        <div
          v-else
          class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-400"
          :style="imageStyles"
        >
          <span class="text-4xl">🖼️</span>
        </div>
      </div>
    </div>

    <!-- Arrows -->
    <div v-if="settings.show_arrows ?? true" class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex justify-between px-2 pointer-events-none">
      <button
        class="w-8 h-8 rounded-full flex items-center justify-center pointer-events-auto"
        :style="arrowStyles"
        @click="prev"
      >‹</button>
      <button
        class="w-8 h-8 rounded-full flex items-center justify-center pointer-events-auto"
        :style="arrowStyles"
        @click="next"
      >›</button>
    </div>

    <!-- Dots -->
    <div v-if="settings.show_dots ?? true" class="flex justify-center gap-2 mt-4">
      <button
        v-for="(_, index) in Math.ceil(images.length / slidesToShow)"
        :key="index"
        class="w-2 h-2 rounded-full transition-colors"
        :style="{ backgroundColor: index === currentPage ? (settings.dot_color ?? '#4f46e5') : '#d1d5db' }"
        @click="currentPage = index"
      ></button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const currentPage = ref(0);

const slidesToShow = computed(() => props.settings.slides_to_show ?? 3);

const images = computed(() => [
  props.settings.image1,
  props.settings.image2,
  props.settings.image3,
  props.settings.image4,
  props.settings.image5,
  props.settings.image6
].filter(Boolean));

const next = () => {
  const maxPage = Math.ceil(images.value.length / slidesToShow.value) - 1;
  currentPage.value = Math.min(currentPage.value + 1, maxPage);
};

const prev = () => {
  currentPage.value = Math.max(currentPage.value - 1, 0);
};

const trackStyles = computed(() => ({
  gap: (props.settings.image_spacing ?? 10) + 'px',
  transform: `translateX(-${currentPage.value * 100}%)`
}));

const slideStyles = computed(() => ({
  width: `calc(${100 / slidesToShow.value}% - ${(props.settings.image_spacing ?? 10) * (slidesToShow.value - 1) / slidesToShow.value}px)`
}));

const imageStyles = computed(() => ({
  borderRadius: (props.settings.border_radius ?? 8) + 'px'
}));

const arrowStyles = computed(() => ({
  backgroundColor: 'rgba(0,0,0,0.5)',
  color: props.settings.arrow_color ?? '#ffffff'
}));
</script>
