<template>
  <div class="tabs-widget">
    <div class="flex border-b" :style="tabContainerStyles">
      <button
        v-for="(tab, index) in tabs"
        :key="index"
        :style="index === activeTab ? activeTabStyles : inactiveTabStyles"
        @click="activeTab = index"
        class="px-4 py-2 font-medium transition-colors"
      >
        {{ tab.title }}
      </button>
    </div>
    <div class="p-4" :style="contentStyles" v-html="tabs[activeTab]?.content"></div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const activeTab = ref(0);

const tabs = computed(() => [
  { title: props.settings.tab1_title ?? 'Tab 1', content: props.settings.tab1_content ?? '<p>Tab 1 content goes here.</p>' },
  { title: props.settings.tab2_title ?? 'Tab 2', content: props.settings.tab2_content ?? '<p>Tab 2 content goes here.</p>' },
  { title: props.settings.tab3_title ?? 'Tab 3', content: props.settings.tab3_content ?? '<p>Tab 3 content goes here.</p>' }
].filter((_, i) => i === 0 || props.settings[`tab${i + 1}_title`]));

const tabContainerStyles = computed(() => ({
  borderColor: props.settings.border_color ?? '#e5e7eb'
}));

const activeTabStyles = computed(() => ({
  color: props.settings.active_tab_color ?? props.settings.tab_color ?? '#4f46e5',
  backgroundColor: props.settings.active_content_color ?? 'transparent',
  borderBottom: `2px solid ${props.settings.active_tab_color ?? props.settings.tab_color ?? '#4f46e5'}`,
  marginBottom: '-1px'
}));

const inactiveTabStyles = computed(() => ({
  color: props.settings.inactive_color ?? '#6b7280'
}));

const contentStyles = computed(() => ({
  color: props.settings.content_color ?? '#1f2937',
  backgroundColor: props.settings.content_background ?? 'transparent'
}));
</script>
