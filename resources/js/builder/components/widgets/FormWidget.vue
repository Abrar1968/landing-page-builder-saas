<template>
  <div class="form-widget" :style="containerStyles">
    <h3 v-if="settings.form_name" class="text-xl font-semibold mb-4" :style="titleStyles">
      {{ settings.form_name }}
    </h3>

    <form class="space-y-4" @submit.prevent="handleSubmit">
      <!-- Name Field -->
      <div v-if="settings.name_field ?? true">
        <label v-if="settings.show_labels ?? true" class="block text-sm font-medium mb-1" :style="labelStyles">
          {{ settings.name_label ?? 'Name' }}
        </label>
        <input
          type="text"
          :placeholder="settings.name_placeholder ?? 'Your Name'"
          class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2"
          :style="fieldStyles"
        />
      </div>

      <!-- Email Field -->
      <div v-if="settings.email_field ?? true">
        <label v-if="settings.show_labels ?? true" class="block text-sm font-medium mb-1" :style="labelStyles">
          {{ settings.email_label ?? 'Email' }}
        </label>
        <input
          type="email"
          :placeholder="settings.email_placeholder ?? 'your@email.com'"
          class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2"
          :style="fieldStyles"
        />
      </div>

      <!-- Phone Field -->
      <div v-if="settings.phone_field">
        <label v-if="settings.show_labels ?? true" class="block text-sm font-medium mb-1" :style="labelStyles">
          {{ settings.phone_label ?? 'Phone' }}
        </label>
        <input
          type="tel"
          :placeholder="settings.phone_placeholder ?? 'Your Phone'"
          class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2"
          :style="fieldStyles"
        />
      </div>

      <!-- Message Field -->
      <div v-if="settings.message_field ?? true">
        <label v-if="settings.show_labels ?? true" class="block text-sm font-medium mb-1" :style="labelStyles">
          {{ settings.message_label ?? 'Message' }}
        </label>
        <textarea
          :placeholder="settings.message_placeholder ?? 'Your Message'"
          :rows="settings.message_rows ?? 4"
          class="w-full px-4 py-2 rounded border resize-none focus:outline-none focus:ring-2"
          :style="fieldStyles"
        ></textarea>
      </div>

      <!-- Submit Button -->
      <button
        type="submit"
        class="px-6 py-3 rounded font-medium transition-colors"
        :style="buttonStyles"
      >
        {{ settings.button_text ?? 'Send Message' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: { type: Object, required: true },
  widgetId: { type: String, required: true }
});

const handleSubmit = () => {
  // Form submission handled by parent/page
  console.log('Form submitted');
};

const containerStyles = computed(() => ({
  padding: (props.settings.padding ?? 0) + 'px',
  backgroundColor: props.settings.background_color ?? 'transparent',
  borderRadius: (props.settings.border_radius ?? 0) + 'px'
}));

const titleStyles = computed(() => ({
  color: props.settings.title_color ?? '#1f2937'
}));

const labelStyles = computed(() => ({
  color: props.settings.label_color ?? '#374151'
}));

const fieldStyles = computed(() => ({
  backgroundColor: props.settings.field_background ?? '#ffffff',
  borderColor: props.settings.field_border ?? '#d1d5db',
  color: props.settings.field_text ?? '#1f2937',
  marginBottom: (props.settings.spacing ?? 16) + 'px'
}));

const buttonStyles = computed(() => ({
  backgroundColor: props.settings.button_background ?? '#4f46e5',
  color: props.settings.button_text_color ?? '#ffffff',
  width: props.settings.button_full_width ? '100%' : 'auto'
}));
</script>
