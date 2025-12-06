<template>
  <div class="relative rounded-lg overflow-hidden border" :style="containerStyles">
    <!-- Featured Ribbon -->
    <div
      v-if="settings.featured && settings.ribbon_text"
      class="absolute top-4 right-0 px-3 py-1 text-white text-xs font-medium transform translate-x-2"
      :style="ribbonStyles"
    >
      {{ settings.ribbon_text }}
    </div>

    <!-- Header -->
    <div class="p-6 text-center" :style="headerStyles">
      <h3 class="text-xl font-bold">{{ settings.title ?? 'Pro' }}</h3>
      <p v-if="settings.subtitle" class="mt-1 text-sm opacity-80">{{ settings.subtitle }}</p>
    </div>

    <!-- Price -->
    <div class="p-6 text-center" :style="priceContainerStyles">
      <div class="text-4xl font-bold" :style="priceStyles">
        {{ settings.currency ?? '$' }}{{ settings.price ?? '49' }}
        <span class="text-lg font-normal">{{ settings.period ?? '/month' }}</span>
      </div>

      <!-- Features -->
      <ul class="mt-6 space-y-3 text-left" :style="featuresStyles">
        <li
          v-for="(feature, idx) in features"
          :key="idx"
          class="flex items-center gap-2"
        >
          <span :style="{ color: settings.check_color ?? '#10b981' }">✓</span>
          {{ feature }}
        </li>
      </ul>

      <!-- Button -->
      <a
        :href="settings.button_link ?? '#'"
        :style="buttonStyles"
        class="mt-6 w-full inline-block py-3 rounded font-medium text-center transition-colors"
      >
        {{ settings.button_text ?? 'Get Started' }}
      </a>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const features = computed(() => {
  const featuresText = props.settings.features ?? '10 Projects\n50GB Storage\nPriority Support\nCustom Domain';
  return featuresText.split('\n').filter(f => f.trim());
});

const containerStyles = computed(() => ({
  borderColor: props.settings.border_color ?? '#e5e7eb',
  boxShadow: props.settings.featured ? '0 4px 6px -1px rgba(0, 0, 0, 0.1)' : 'none'
}));

const ribbonStyles = computed(() => ({
  backgroundColor: props.settings.ribbon_background ?? '#4f46e5'
}));

const headerStyles = computed(() => ({
  backgroundColor: props.settings.header_background ?? '#4f46e5',
  color: props.settings.header_color ?? '#ffffff'
}));

const priceContainerStyles = computed(() => ({
  backgroundColor: props.settings.price_background ?? '#ffffff'
}));

const priceStyles = computed(() => ({
  color: props.settings.price_color ?? '#1f2937'
}));

const featuresStyles = computed(() => ({
  color: props.settings.features_color ?? '#4b5563'
}));

const buttonStyles = computed(() => ({
  backgroundColor: props.settings.button_background ?? '#4f46e5',
  color: props.settings.button_color ?? '#ffffff'
}));
</script>
