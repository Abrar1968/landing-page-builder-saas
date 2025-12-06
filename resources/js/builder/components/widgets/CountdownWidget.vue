<template>
  <div class="flex justify-center" :style="containerStyles">
    <div v-if="settings.show_days ?? true" class="text-center" :style="itemStyles">
      <div :style="numberStyles" class="font-bold">{{ timeLeft.days }}</div>
      <div v-if="settings.show_labels ?? true" :style="labelStyles">{{ settings.days_label ?? 'Days' }}</div>
    </div>
    <div v-if="(settings.show_days ?? true) && (settings.show_hours ?? true)" :style="separatorStyles">:</div>

    <div v-if="settings.show_hours ?? true" class="text-center" :style="itemStyles">
      <div :style="numberStyles" class="font-bold">{{ timeLeft.hours }}</div>
      <div v-if="settings.show_labels ?? true" :style="labelStyles">{{ settings.hours_label ?? 'Hours' }}</div>
    </div>
    <div v-if="(settings.show_hours ?? true) && (settings.show_minutes ?? true)" :style="separatorStyles">:</div>

    <div v-if="settings.show_minutes ?? true" class="text-center" :style="itemStyles">
      <div :style="numberStyles" class="font-bold">{{ timeLeft.minutes }}</div>
      <div v-if="settings.show_labels ?? true" :style="labelStyles">{{ settings.minutes_label ?? 'Minutes' }}</div>
    </div>
    <div v-if="(settings.show_minutes ?? true) && (settings.show_seconds ?? true)" :style="separatorStyles">:</div>

    <div v-if="settings.show_seconds ?? true" class="text-center" :style="itemStyles">
      <div :style="numberStyles" class="font-bold">{{ timeLeft.seconds }}</div>
      <div v-if="settings.show_labels ?? true" :style="labelStyles">{{ settings.seconds_label ?? 'Seconds' }}</div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const timeLeft = ref({ days: '00', hours: '00', minutes: '00', seconds: '00' });
let timer = null;

const calculateTimeLeft = () => {
  const targetDate = props.settings.due_date ? new Date(props.settings.due_date) : new Date(Date.now() + 86400000);
  const now = new Date();
  const diff = targetDate - now;

  if (diff <= 0) {
    timeLeft.value = { days: '00', hours: '00', minutes: '00', seconds: '00' };
    return;
  }

  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((diff % (1000 * 60)) / 1000);

  timeLeft.value = {
    days: String(days).padStart(2, '0'),
    hours: String(hours).padStart(2, '0'),
    minutes: String(minutes).padStart(2, '0'),
    seconds: String(seconds).padStart(2, '0')
  };
};

onMounted(() => {
  calculateTimeLeft();
  timer = setInterval(calculateTimeLeft, 1000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});

const containerStyles = computed(() => ({
  gap: (props.settings.item_spacing ?? 16) + 'px'
}));

const itemStyles = computed(() => ({
  minWidth: (props.settings.item_width ?? 80) + 'px'
}));

const numberStyles = computed(() => ({
  fontSize: (props.settings.number_size ?? 48) + 'px',
  color: props.settings.number_color ?? '#1f2937'
}));

const labelStyles = computed(() => ({
  color: props.settings.label_color ?? '#6b7280',
  fontSize: (props.settings.label_size ?? 14) + 'px'
}));

const separatorStyles = computed(() => ({
  fontSize: (props.settings.number_size ?? 48) + 'px',
  color: props.settings.number_color ?? '#1f2937',
  alignSelf: 'flex-start'
}));
</script>
