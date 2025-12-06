<template>
  <div class="icon-list-widget">
    <div
      v-for="(item, index) in items"
      :key="index"
      :style="itemStyles"
    >
      <component
        :is="item.link ? 'a' : 'div'"
        :href="item.link"
        class="flex items-center gap-3 no-underline"
        :style="{ color: 'inherit' }"
      >
        <span :style="iconStyles">{{ item.icon }}</span>
        <span :style="textStyles">{{ item.text }}</span>
      </component>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const items = computed(() => [
  { icon: props.settings.item1_icon ?? '✓', text: props.settings.item1_text ?? 'List Item 1', link: props.settings.item1_link },
  { icon: props.settings.item2_icon ?? '✓', text: props.settings.item2_text ?? 'List Item 2', link: props.settings.item2_link },
  { icon: props.settings.item3_icon ?? '✓', text: props.settings.item3_text ?? 'List Item 3', link: props.settings.item3_link }
].filter((_, i) => i === 0 || props.settings[`item${i + 1}_text`]));

const itemStyles = computed(() => ({
  marginBottom: (props.settings.spacing ?? 12) + 'px'
}));

const iconStyles = computed(() => ({
  fontSize: (props.settings.icon_size ?? 20) + 'px',
  color: props.settings.icon_color ?? '#4f46e5'
}));

const textStyles = computed(() => ({
  color: props.settings.text_color ?? '#1f2937',
  fontSize: (props.settings.text_size ?? 16) + 'px'
}));
</script>
