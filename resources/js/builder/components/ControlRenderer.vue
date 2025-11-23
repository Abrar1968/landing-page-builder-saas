<template>
  <div class="control-item mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">
      {{ control.label }}
    </label>

    <!-- Text Input -->
    <input
      v-if="control.type === 'text'"
      type="text"
      :value="modelValue"
      :placeholder="control.placeholder"
      @input="$emit('update:modelValue', $event.target.value)"
      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
    />

    <!-- Textarea -->
    <textarea
      v-else-if="control.type === 'textarea'"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      rows="3"
      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
    ></textarea>

    <!-- Number Input -->
    <input
      v-else-if="control.type === 'number'"
      type="number"
      :value="modelValue"
      :min="control.min"
      :max="control.max"
      @input="$emit('update:modelValue', Number($event.target.value))"
      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
    />

    <!-- Select -->
    <select
      v-else-if="control.type === 'select'"
      :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value)"
      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
    >
      <option v-for="(label, value) in control.options" :key="value" :value="value">
        {{ label }}
      </option>
    </select>

    <!-- Choose (Button Group) -->
    <div v-else-if="control.type === 'choose'" class="flex border border-gray-300 rounded-md overflow-hidden">
      <button
        v-for="(option, value) in control.options"
        :key="value"
        @click="$emit('update:modelValue', value)"
        :class="[
          'flex-1 px-3 py-2 text-sm font-medium transition-colors',
          modelValue === value
            ? 'bg-indigo-600 text-white'
            : 'bg-white text-gray-700 hover:bg-gray-50'
        ]"
        type="button"
      >
        {{ option.icon || option.title }}
      </button>
    </div>

    <!-- Color Picker -->
    <div v-else-if="control.type === 'color'" class="flex items-center gap-2">
      <input
        type="color"
        :value="modelValue || control.default || '#000000'"
        @input="$emit('update:modelValue', $event.target.value)"
        class="w-10 h-10 rounded cursor-pointer"
      />
      <input
        type="text"
        :value="modelValue || control.default"
        @input="$emit('update:modelValue', $event.target.value)"
        class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
      />
    </div>

    <!-- Slider -->
    <div v-else-if="control.type === 'slider'">
      <div class="flex items-center gap-3">
        <input
          type="range"
          :value="modelValue ?? control.default ?? control.min"
          :min="control.min"
          :max="control.max"
          @input="$emit('update:modelValue', Number($event.target.value))"
          class="flex-1"
        />
        <span class="text-sm text-gray-600 w-16 text-right">
          {{ modelValue ?? control.default ?? control.min }}{{ control.unit || '' }}
        </span>
      </div>
    </div>

    <!-- Switcher (Toggle) -->
    <div v-else-if="control.type === 'switcher'" class="flex items-center">
      <button
        type="button"
        @click="$emit('update:modelValue', !modelValue)"
        :class="[
          'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
          modelValue ? 'bg-indigo-600' : 'bg-gray-200'
        ]"
      >
        <span
          :class="[
            'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
            modelValue ? 'translate-x-5' : 'translate-x-0'
          ]"
        ></span>
      </button>
    </div>

    <!-- Media Picker -->
    <div v-else-if="control.type === 'media'">
      <div
        v-if="modelValue"
        class="relative mb-2 rounded-lg overflow-hidden bg-gray-100"
      >
        <img :src="modelValue" class="w-full h-32 object-cover" />
        <button
          type="button"
          @click="$emit('update:modelValue', '')"
          class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600"
        >
          ✕
        </button>
      </div>
      <button
        type="button"
        @click="$emit('openMedia')"
        class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        {{ modelValue ? 'Change Image' : 'Select Image' }}
      </button>
    </div>

    <!-- WYSIWYG Editor -->
    <div v-else-if="control.type === 'wysiwyg'">
      <div class="border border-gray-300 rounded-md overflow-hidden">
        <div class="bg-gray-50 border-b border-gray-300 p-2 flex gap-1">
          <button
            type="button"
            @click="execCommand('bold')"
            class="px-2 py-1 text-sm font-bold hover:bg-gray-200 rounded"
          >B</button>
          <button
            type="button"
            @click="execCommand('italic')"
            class="px-2 py-1 text-sm italic hover:bg-gray-200 rounded"
          >I</button>
          <button
            type="button"
            @click="execCommand('underline')"
            class="px-2 py-1 text-sm underline hover:bg-gray-200 rounded"
          >U</button>
          <span class="border-l border-gray-300 mx-1"></span>
          <button
            type="button"
            @click="execCommand('insertUnorderedList')"
            class="px-2 py-1 text-sm hover:bg-gray-200 rounded"
          >•</button>
          <button
            type="button"
            @click="execCommand('insertOrderedList')"
            class="px-2 py-1 text-sm hover:bg-gray-200 rounded"
          >1.</button>
        </div>
        <div
          ref="wysiwygEditor"
          contenteditable="true"
          @input="onWysiwygInput"
          class="p-3 min-h-[100px] focus:outline-none"
          v-html="modelValue"
        ></div>
      </div>
    </div>

    <!-- Dimensions (Margin/Padding) -->
    <div v-else-if="control.type === 'dimensions'" class="grid grid-cols-4 gap-2">
      <div>
        <label class="text-xs text-gray-500">Top</label>
        <input
          type="number"
          :value="(modelValue?.top ?? 0)"
          @input="updateDimension('top', $event.target.value)"
          class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
        />
      </div>
      <div>
        <label class="text-xs text-gray-500">Right</label>
        <input
          type="number"
          :value="(modelValue?.right ?? 0)"
          @input="updateDimension('right', $event.target.value)"
          class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
        />
      </div>
      <div>
        <label class="text-xs text-gray-500">Bottom</label>
        <input
          type="number"
          :value="(modelValue?.bottom ?? 0)"
          @input="updateDimension('bottom', $event.target.value)"
          class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
        />
      </div>
      <div>
        <label class="text-xs text-gray-500">Left</label>
        <input
          type="number"
          :value="(modelValue?.left ?? 0)"
          @input="updateDimension('left', $event.target.value)"
          class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
        />
      </div>
    </div>

    <!-- Fallback for unknown types -->
    <input
      v-else
      type="text"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      class="w-full px-3 py-2 border border-gray-300 rounded-md"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  control: {
    type: Object,
    required: true
  },
  modelValue: {
    type: [String, Number, Boolean, Object],
    default: null
  }
});

const emit = defineEmits(['update:modelValue', 'openMedia']);

const wysiwygEditor = ref(null);

function execCommand(command) {
  document.execCommand(command, false, null);
  if (wysiwygEditor.value) {
    emit('update:modelValue', wysiwygEditor.value.innerHTML);
  }
}

function onWysiwygInput(event) {
  emit('update:modelValue', event.target.innerHTML);
}

function updateDimension(key, value) {
  const current = props.modelValue || { top: 0, right: 0, bottom: 0, left: 0, unit: 'px' };
  emit('update:modelValue', {
    ...current,
    [key]: Number(value)
  });
}
</script>
