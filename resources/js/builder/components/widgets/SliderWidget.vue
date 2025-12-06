<template>
  <div class="slider-widget relative overflow-hidden rounded-lg" :style="containerStyles">
    <!-- Current Slide -->
    <div class="slide relative w-full h-full flex items-center justify-center text-center text-white">
      <div
        v-if="currentSlide.image"
        class="absolute inset-0 bg-cover bg-center"
        :style="{ backgroundImage: `url(${currentSlide.image})` }"
      ></div>
      <div class="absolute inset-0" :style="overlayStyles"></div>

      <div class="relative z-10 px-8 max-w-3xl">
        <h2 class="text-4xl font-bold mb-4" :style="titleStyles">
          {{ currentSlide.title }}
        </h2>
        <p class="text-lg mb-6" :style="descriptionStyles">
          {{ currentSlide.description }}
        </p>
        <a
          v-if="currentSlide.button"
          :href="currentSlide.link || '#'"
          class="inline-block px-6 py-3 rounded font-medium transition-colors"
          :style="buttonStyles"
        >
          {{ currentSlide.button }}
        </a>
      </div>
    </div>

    <!-- Navigation Arrows -->
    <div v-if="settings.show_arrows ?? true" class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex justify-between px-4 pointer-events-none">
      <button
        class="w-10 h-10 rounded-full flex items-center justify-center pointer-events-auto hover:bg-black/50 transition-colors"
        :style="arrowStyles"
        @click="prevSlide"
      >‹</button>
      <button
        class="w-10 h-10 rounded-full flex items-center justify-center pointer-events-auto hover:bg-black/50 transition-colors"
        :style="arrowStyles"
        @click="nextSlide"
      >›</button>
    </div>

    <!-- Navigation Dots -->
    <div v-if="settings.show_dots ?? true" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
      <button
        v-for="(slide, index) in slides"
        :key="index"
        class="w-2 h-2 rounded-full transition-opacity"
        :style="{ backgroundColor: settings.dots_color ?? '#ffffff', opacity: index === currentIndex ? 1 : 0.5 }"
        @click="currentIndex = index"
      ></button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const currentIndex = ref(0);
let autoplayTimer = null;

const slides = computed(() => [
  {
    image: props.settings.slide1_image,
    title: props.settings.slide1_title ?? 'First Slide',
    description: props.settings.slide1_description ?? 'This is the first slide content.',
    button: props.settings.slide1_button,
    link: props.settings.slide1_link
  },
  {
    image: props.settings.slide2_image,
    title: props.settings.slide2_title ?? 'Second Slide',
    description: props.settings.slide2_description ?? 'This is the second slide content.',
    button: props.settings.slide2_button,
    link: props.settings.slide2_link
  },
  {
    image: props.settings.slide3_image,
    title: props.settings.slide3_title ?? 'Third Slide',
    description: props.settings.slide3_description ?? 'This is the third slide content.',
    button: props.settings.slide3_button,
    link: props.settings.slide3_link
  }
].filter(s => s.image || s.title !== 'First Slide'));

const currentSlide = computed(() => slides.value[currentIndex.value] || slides.value[0]);

const nextSlide = () => {
  currentIndex.value = (currentIndex.value + 1) % slides.value.length;
};

const prevSlide = () => {
  currentIndex.value = (currentIndex.value - 1 + slides.value.length) % slides.value.length;
};

onMounted(() => {
  if (props.settings.autoplay) {
    const interval = (props.settings.autoplay_speed ?? 5) * 1000;
    autoplayTimer = setInterval(nextSlide, interval);
  }
});

onUnmounted(() => {
  if (autoplayTimer) clearInterval(autoplayTimer);
});

const containerStyles = computed(() => ({
  height: (props.settings.height ?? 500) + 'px',
  backgroundColor: '#1f2937'
}));

const overlayStyles = computed(() => ({
  backgroundColor: props.settings.overlay_color ?? 'rgba(0,0,0,0.3)'
}));

const titleStyles = computed(() => ({
  color: props.settings.title_color ?? '#ffffff'
}));

const descriptionStyles = computed(() => ({
  color: props.settings.description_color ?? '#f3f4f6'
}));

const buttonStyles = computed(() => ({
  backgroundColor: props.settings.button_background ?? '#4f46e5',
  color: props.settings.button_color ?? '#ffffff'
}));

const arrowStyles = computed(() => ({
  backgroundColor: 'rgba(0,0,0,0.3)',
  color: props.settings.arrows_color ?? '#ffffff'
}));
</script>
