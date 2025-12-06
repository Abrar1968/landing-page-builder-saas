<template>
  <div :class="alertClasses" :style="containerStyles" class="p-4 rounded-lg">
    <div class="flex items-start gap-3">
      <span v-if="settings.show_icon !== false" class="text-xl flex-shrink-0">{{ alertIcon }}</span>
      <div class="flex-1">
        <div v-if="settings.title" class="font-semibold" :style="titleStyles">{{ settings.title }}</div>
        <div :style="contentStyles" :class="{ 'mt-1': settings.title }">{{ settings.content ?? 'Click to edit this text.' }}</div>
      </div>
      <button v-if="settings.show_dismiss" class="text-current opacity-70 hover:opacity-100" @click="$emit('dismiss')">×</button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

defineEmits(['dismiss']);

const alertType = computed(() => props.settings.alert_type ?? 'info');

const alertClasses = computed(() => {
  const type = alertType.value;
  const baseClasses = {
    info: 'bg-blue-50 text-blue-800 border border-blue-200',
    success: 'bg-green-50 text-green-800 border border-green-200',
    warning: 'bg-yellow-50 text-yellow-800 border border-yellow-200',
    danger: 'bg-red-50 text-red-800 border border-red-200'
  };
  return baseClasses[type] || baseClasses.info;
});

const alertIcon = computed(() => {
  const icons = {
    info: 'ℹ️',
    success: '✅',
    warning: '⚠️',
    danger: '❌'
  };
  return props.settings.icon || icons[alertType.value] || icons.info;
});

const containerStyles = computed(() => ({
  backgroundColor: props.settings.background_color,
  borderColor: props.settings.border_color
}));

const titleStyles = computed(() => ({
  color: props.settings.title_color
}));

const contentStyles = computed(() => ({
  color: props.settings.content_color
}));
</script>
