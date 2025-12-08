<template>
  <div :style="containerStyles">
    <p :style="contentStyles" class="italic mb-4">
      "{{ settings.content ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' }}"
    </p>
    <div class="flex items-center justify-center gap-3">
      <img
        v-if="settings.image_url"
        :src="settings.image_url"
        class="w-12 h-12 rounded-full object-cover"
      />
      <div>
        <div :style="nameStyles" class="font-semibold">{{ settings.name ?? 'John Doe' }}</div>
        <div :style="titleTextStyles" class="text-sm">{{ settings.title ?? 'Designer' }}</div>
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
  textAlign: props.settings.alignment ?? 'center',
  padding: '20px'
}));

const contentStyles = computed(() => {
  const typo = props.settings.content_typography || {};
  return {
    color: props.settings.content_color ?? '#4b5563',
    fontSize: (typo.size ?? props.settings.content_size ?? 16) + 'px',
    fontFamily: typo.family && typo.family !== 'inherit' ? typo.family : 'inherit',
    fontWeight: typo.weight ?? '400',
    lineHeight: typo.lineHeight ?? '1.5'
  };
});

const nameStyles = computed(() => {
  const typo = props.settings.name_typography || {};
  return {
    color: props.settings.name_color ?? '#1f2937',
    fontSize: (typo.size ?? 18) + 'px',
    fontFamily: typo.family && typo.family !== 'inherit' ? typo.family : 'inherit',
    fontWeight: typo.weight ?? '600',
    lineHeight: typo.lineHeight ?? '1.2'
  };
});

const titleTextStyles = computed(() => {
  const typo = props.settings.title_typography || {};
  return {
    color: props.settings.title_color ?? '#6b7280',
    fontSize: (typo.size ?? 14) + 'px',
    fontFamily: typo.family && typo.family !== 'inherit' ? typo.family : 'inherit',
    fontWeight: typo.weight ?? '400',
    lineHeight: typo.lineHeight ?? '1.2'
  };
});
</script>
