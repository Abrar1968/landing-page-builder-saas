<template>
  <div
    class="flip-box relative"
    :style="containerStyles"
    @mouseenter="flipped = true"
    @mouseleave="flipped = false"
  >
    <div class="flip-box-inner" :class="{ flipped }">
      <!-- Front -->
      <div class="flip-box-front" :style="frontStyles">
        <div class="text-4xl mb-4">{{ settings.front_icon ?? '⚡' }}</div>
        <h4 class="font-semibold text-lg">{{ settings.front_title ?? 'Front Title' }}</h4>
        <p class="mt-2 text-sm">{{ settings.front_description ?? 'This is the front content.' }}</p>
      </div>
      <!-- Back -->
      <div class="flip-box-back" :style="backStyles">
        <div class="text-4xl mb-4">{{ settings.back_icon ?? '🎯' }}</div>
        <h4 class="font-semibold text-lg">{{ settings.back_title ?? 'Back Title' }}</h4>
        <p class="mt-2 text-sm">{{ settings.back_description ?? 'This is the back content.' }}</p>
        <a
          v-if="settings.button_text"
          :href="settings.button_link ?? '#'"
          class="mt-4 inline-block px-4 py-2 rounded text-sm font-medium"
          :style="buttonStyles"
        >
          {{ settings.button_text }}
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const flipped = ref(false);

const containerStyles = computed(() => ({
  height: (props.settings.height ?? 300) + 'px',
  perspective: '1000px'
}));

const frontStyles = computed(() => ({
  backgroundColor: props.settings.front_background ?? '#ffffff',
  color: props.settings.front_color ?? '#1f2937'
}));

const backStyles = computed(() => ({
  backgroundColor: props.settings.back_background ?? '#4f46e5',
  color: props.settings.back_color ?? '#ffffff'
}));

const buttonStyles = computed(() => ({
  backgroundColor: props.settings.button_background ?? '#ffffff',
  color: props.settings.button_color ?? '#4f46e5'
}));
</script>

<style scoped>
.flip-box-inner {
  position: relative;
  width: 100%;
  height: 100%;
  transition: transform 0.6s;
  transform-style: preserve-3d;
}
.flip-box-inner.flipped {
  transform: rotateY(180deg);
}
.flip-box-front,
.flip-box-back {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 24px;
}
.flip-box-back {
  transform: rotateY(180deg);
}
</style>
