<template>
  <div class="toggle-widget border rounded-lg overflow-hidden" :style="containerStyles">
    <div v-for="(item, index) in items" :key="index" class="border-b last:border-b-0">
      <div
        :style="titleContainerStyles"
        class="px-4 py-3 font-medium flex justify-between items-center cursor-pointer"
        @click="toggleItem(index)"
      >
        <span>{{ item.title }}</span>
        <span>{{ openItems.includes(index) ? '−' : '+' }}</span>
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

const openItems = ref([]);

const items = computed(() => [
  { title: props.settings.item1_title ?? 'Toggle Item 1', content: props.settings.item1_content ?? '<p>Content for toggle item 1.</p>' },
  { title: props.settings.item2_title ?? 'Toggle Item 2', content: props.settings.item2_content ?? '<p>Content for toggle item 2.</p>' },
  { title: props.settings.item3_title ?? 'Toggle Item 3', content: props.settings.item3_content ?? '<p>Content for toggle item 3.</p>' }
]);

const toggleItem = (index) => {
  const idx = openItems.value.indexOf(index);
  if (idx > -1) openItems.value.splice(idx, 1);
  else openItems.value.push(index);
};

const containerStyles = computed(() => ({
  borderColor: props.settings.border_color ?? '#e5e7eb'
}));

const titleContainerStyles = computed(() => ({
  backgroundColor: props.settings.title_background ?? '#f3f4f6',
  color: props.settings.title_color ?? '#1f2937'
}));

const contentStyles = computed(() => ({
  color: props.settings.content_color ?? '#4b5563',
  backgroundColor: props.settings.content_background ?? '#ffffff'
}));
</script>
