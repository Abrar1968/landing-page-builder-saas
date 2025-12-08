<template>
  <div class="accordion-widget border rounded-lg overflow-hidden" :style="containerStyles">
    <div v-for="(item, index) in items" :key="index" class="border-b last:border-b-0">
      <div
        :style="openItems.includes(index) ? activeTitleContainerStyles : titleContainerStyles"
        class="px-4 py-3 font-medium flex justify-between items-center cursor-pointer"
        @click="toggleItem(index)"
      >
        <span>{{ item.title }}</span>
        <span class="transition-transform" :class="{ 'rotate-180': openItems.includes(index) }">
          {{ openItems.includes(index) ? (settings.active_icon ?? settings.icon ?? '▼') : (settings.icon ?? '▼') }}
        </span>
      </div>
      <div
        v-show="openItems.includes(index)"
        :style="contentStyles"
        class="px-4 py-3"
        v-html="item.content"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const openItems = ref(props.settings.first_open !== false ? [0] : []);

const items = computed(() => [
  { title: props.settings.item1_title ?? 'Accordion Item 1', content: props.settings.item1_content ?? '<p>Content for accordion item 1.</p>' },
  { title: props.settings.item2_title ?? 'Accordion Item 2', content: props.settings.item2_content ?? '<p>Content for accordion item 2.</p>' },
  { title: props.settings.item3_title ?? 'Accordion Item 3', content: props.settings.item3_content ?? '<p>Content for accordion item 3.</p>' }
]);

const toggleItem = (index) => {
  if (props.settings.allow_multiple) {
    const idx = openItems.value.indexOf(index);
    if (idx > -1) openItems.value.splice(idx, 1);
    else openItems.value.push(index);
  } else {
    openItems.value = openItems.value.includes(index) ? [] : [index];
  }
};

const containerStyles = computed(() => ({
  borderColor: props.settings.border_color ?? '#e5e7eb'
}));

const titleContainerStyles = computed(() => ({
  backgroundColor: props.settings.title_background ?? '#f3f4f6',
  color: props.settings.title_color ?? '#1f2937'
}));

const activeTitleContainerStyles = computed(() => ({
  backgroundColor: props.settings.active_title_background ?? props.settings.title_background ?? '#e0e7ff',
  color: props.settings.active_title_color ?? props.settings.title_color ?? '#4f46e5'
}));

const contentStyles = computed(() => ({
  color: props.settings.content_color ?? '#4b5563',
  backgroundColor: props.settings.content_background ?? '#ffffff'
}));
</script>
