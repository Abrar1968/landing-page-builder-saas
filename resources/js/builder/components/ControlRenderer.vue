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

    <!-- URL Control with options -->
    <div v-else-if="control.type === 'url'" class="space-y-2">
      <input
        type="url"
        :value="modelValue?.url || modelValue || ''"
        @input="updateUrl('url', $event.target.value)"
        placeholder="https://"
        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
      />
      <div class="flex items-center gap-4 text-sm">
        <label class="flex items-center gap-2 cursor-pointer">
          <input
            type="checkbox"
            :checked="modelValue?.is_external"
            @change="updateUrl('is_external', $event.target.checked)"
            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
          />
          <span class="text-gray-600">Open in new tab</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
          <input
            type="checkbox"
            :checked="modelValue?.nofollow"
            @change="updateUrl('nofollow', $event.target.checked)"
            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
          />
          <span class="text-gray-600">Nofollow</span>
        </label>
      </div>
    </div>

    <!-- Typography Group Control -->
    <div v-else-if="control.type === 'typography'" class="space-y-3 p-3 bg-gray-50 rounded-md">
      <div>
        <label class="block text-xs text-gray-500 mb-1">Font Family</label>
        <select
          :value="modelValue?.family || ''"
          @change="updateTypography('family', $event.target.value)"
          class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
        >
          <option value="">Default</option>
          <option value="Inter">Inter</option>
          <option value="Roboto">Roboto</option>
          <option value="Open Sans">Open Sans</option>
          <option value="Montserrat">Montserrat</option>
          <option value="Poppins">Poppins</option>
          <option value="Playfair Display">Playfair Display</option>
          <option value="Lato">Lato</option>
        </select>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-xs text-gray-500 mb-1">Size</label>
          <div class="flex">
            <input
              type="number"
              :value="modelValue?.size || 16"
              @input="updateTypography('size', Number($event.target.value))"
              class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-l"
            />
            <select
              :value="modelValue?.sizeUnit || 'px'"
              @change="updateTypography('sizeUnit', $event.target.value)"
              class="px-1 py-1.5 text-sm border border-l-0 border-gray-300 rounded-r bg-gray-50"
            >
              <option value="px">px</option>
              <option value="em">em</option>
              <option value="rem">rem</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Weight</label>
          <select
            :value="modelValue?.weight || '400'"
            @change="updateTypography('weight', $event.target.value)"
            class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
          >
            <option value="300">Light</option>
            <option value="400">Normal</option>
            <option value="500">Medium</option>
            <option value="600">Semi Bold</option>
            <option value="700">Bold</option>
            <option value="800">Extra Bold</option>
          </select>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-xs text-gray-500 mb-1">Transform</label>
          <select
            :value="modelValue?.transform || 'none'"
            @change="updateTypography('transform', $event.target.value)"
            class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
          >
            <option value="none">None</option>
            <option value="uppercase">UPPERCASE</option>
            <option value="lowercase">lowercase</option>
            <option value="capitalize">Capitalize</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Style</label>
          <select
            :value="modelValue?.style || 'normal'"
            @change="updateTypography('style', $event.target.value)"
            class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
          >
            <option value="normal">Normal</option>
            <option value="italic">Italic</option>
          </select>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-xs text-gray-500 mb-1">Line Height</label>
          <input
            type="number"
            step="0.1"
            :value="modelValue?.lineHeight || 1.5"
            @input="updateTypography('lineHeight', Number($event.target.value))"
            class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
          />
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Letter Spacing</label>
          <input
            type="number"
            step="0.1"
            :value="modelValue?.letterSpacing || 0"
            @input="updateTypography('letterSpacing', Number($event.target.value))"
            class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
            placeholder="px"
          />
        </div>
      </div>
    </div>

    <!-- Background Group Control -->
    <div v-else-if="control.type === 'background'" class="space-y-3 p-3 bg-gray-50 rounded-md">
      <div class="flex gap-1">
        <button
          type="button"
          @click="updateBackground('type', 'classic')"
          :class="[
            'flex-1 py-1.5 text-xs rounded',
            (modelValue?.type || 'classic') === 'classic' ? 'bg-indigo-100 text-indigo-700' : 'bg-white text-gray-600'
          ]"
        >Classic</button>
        <button
          type="button"
          @click="updateBackground('type', 'gradient')"
          :class="[
            'flex-1 py-1.5 text-xs rounded',
            modelValue?.type === 'gradient' ? 'bg-indigo-100 text-indigo-700' : 'bg-white text-gray-600'
          ]"
        >Gradient</button>
      </div>

      <div v-if="(modelValue?.type || 'classic') === 'classic'">
        <label class="block text-xs text-gray-500 mb-1">Color</label>
        <div class="flex items-center gap-2">
          <input
            type="color"
            :value="modelValue?.color || '#ffffff'"
            @input="updateBackground('color', $event.target.value)"
            class="w-8 h-8 rounded border cursor-pointer"
          />
          <input
            type="text"
            :value="modelValue?.color || ''"
            @input="updateBackground('color', $event.target.value)"
            class="flex-1 px-2 py-1.5 text-sm border border-gray-300 rounded"
            placeholder="#ffffff"
          />
        </div>
        <div class="mt-2">
          <label class="block text-xs text-gray-500 mb-1">Image URL</label>
          <input
            type="url"
            :value="modelValue?.image || ''"
            @input="updateBackground('image', $event.target.value)"
            class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
            placeholder="https://..."
          />
        </div>
        <div v-if="modelValue?.image" class="mt-2 grid grid-cols-2 gap-2">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Position</label>
            <select
              :value="modelValue?.position || 'center center'"
              @change="updateBackground('position', $event.target.value)"
              class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
            >
              <option value="center center">Center</option>
              <option value="top left">Top Left</option>
              <option value="top center">Top Center</option>
              <option value="top right">Top Right</option>
              <option value="bottom left">Bottom Left</option>
              <option value="bottom center">Bottom Center</option>
              <option value="bottom right">Bottom Right</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Size</label>
            <select
              :value="modelValue?.size || 'cover'"
              @change="updateBackground('size', $event.target.value)"
              class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
            >
              <option value="auto">Auto</option>
              <option value="cover">Cover</option>
              <option value="contain">Contain</option>
            </select>
          </div>
        </div>
      </div>

      <div v-else>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Color 1</label>
            <input
              type="color"
              :value="modelValue?.gradientColor1 || '#6366f1'"
              @input="updateBackground('gradientColor1', $event.target.value)"
              class="w-full h-8 rounded border cursor-pointer"
            />
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Color 2</label>
            <input
              type="color"
              :value="modelValue?.gradientColor2 || '#8b5cf6'"
              @input="updateBackground('gradientColor2', $event.target.value)"
              class="w-full h-8 rounded border cursor-pointer"
            />
          </div>
        </div>
        <div class="mt-2">
          <label class="block text-xs text-gray-500 mb-1">Angle</label>
          <input
            type="range"
            :value="modelValue?.gradientAngle || 180"
            @input="updateBackground('gradientAngle', Number($event.target.value))"
            min="0"
            max="360"
            class="w-full"
          />
          <span class="text-xs text-gray-500">{{ modelValue?.gradientAngle || 180 }}°</span>
        </div>
      </div>
    </div>

    <!-- Border Group Control -->
    <div v-else-if="control.type === 'border'" class="space-y-3 p-3 bg-gray-50 rounded-md">
      <div>
        <label class="block text-xs text-gray-500 mb-1">Border Type</label>
        <select
          :value="modelValue?.style || 'none'"
          @change="updateBorder('style', $event.target.value)"
          class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
        >
          <option value="none">None</option>
          <option value="solid">Solid</option>
          <option value="dashed">Dashed</option>
          <option value="dotted">Dotted</option>
          <option value="double">Double</option>
        </select>
      </div>
      <div v-if="modelValue?.style && modelValue?.style !== 'none'">
        <label class="block text-xs text-gray-500 mb-1">Width</label>
        <div class="grid grid-cols-4 gap-1">
          <input type="number" :value="modelValue?.width?.top || 1" @input="updateBorderWidth('top', $event.target.value)" placeholder="T" class="px-2 py-1.5 text-sm text-center border border-gray-300 rounded" />
          <input type="number" :value="modelValue?.width?.right || 1" @input="updateBorderWidth('right', $event.target.value)" placeholder="R" class="px-2 py-1.5 text-sm text-center border border-gray-300 rounded" />
          <input type="number" :value="modelValue?.width?.bottom || 1" @input="updateBorderWidth('bottom', $event.target.value)" placeholder="B" class="px-2 py-1.5 text-sm text-center border border-gray-300 rounded" />
          <input type="number" :value="modelValue?.width?.left || 1" @input="updateBorderWidth('left', $event.target.value)" placeholder="L" class="px-2 py-1.5 text-sm text-center border border-gray-300 rounded" />
        </div>
        <div class="mt-2">
          <label class="block text-xs text-gray-500 mb-1">Color</label>
          <div class="flex items-center gap-2">
            <input type="color" :value="modelValue?.color || '#000000'" @input="updateBorder('color', $event.target.value)" class="w-8 h-8 rounded border cursor-pointer" />
            <input type="text" :value="modelValue?.color || ''" @input="updateBorder('color', $event.target.value)" class="flex-1 px-2 py-1.5 text-sm border border-gray-300 rounded" />
          </div>
        </div>
      </div>
      <div>
        <label class="block text-xs text-gray-500 mb-1">Border Radius</label>
        <div class="grid grid-cols-4 gap-1">
          <input type="number" :value="modelValue?.radius?.topLeft || 0" @input="updateBorderRadius('topLeft', $event.target.value)" placeholder="TL" class="px-2 py-1.5 text-sm text-center border border-gray-300 rounded" />
          <input type="number" :value="modelValue?.radius?.topRight || 0" @input="updateBorderRadius('topRight', $event.target.value)" placeholder="TR" class="px-2 py-1.5 text-sm text-center border border-gray-300 rounded" />
          <input type="number" :value="modelValue?.radius?.bottomRight || 0" @input="updateBorderRadius('bottomRight', $event.target.value)" placeholder="BR" class="px-2 py-1.5 text-sm text-center border border-gray-300 rounded" />
          <input type="number" :value="modelValue?.radius?.bottomLeft || 0" @input="updateBorderRadius('bottomLeft', $event.target.value)" placeholder="BL" class="px-2 py-1.5 text-sm text-center border border-gray-300 rounded" />
        </div>
      </div>
    </div>

    <!-- Box Shadow Group Control -->
    <div v-else-if="control.type === 'box_shadow'" class="space-y-3 p-3 bg-gray-50 rounded-md">
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-xs text-gray-500 mb-1">Horizontal</label>
          <input type="number" :value="modelValue?.horizontal || 0" @input="updateBoxShadow('horizontal', Number($event.target.value))" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded" />
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Vertical</label>
          <input type="number" :value="modelValue?.vertical || 0" @input="updateBoxShadow('vertical', Number($event.target.value))" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded" />
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-xs text-gray-500 mb-1">Blur</label>
          <input type="number" :value="modelValue?.blur || 0" @input="updateBoxShadow('blur', Number($event.target.value))" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded" />
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Spread</label>
          <input type="number" :value="modelValue?.spread || 0" @input="updateBoxShadow('spread', Number($event.target.value))" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded" />
        </div>
      </div>
      <div>
        <label class="block text-xs text-gray-500 mb-1">Color</label>
        <div class="flex items-center gap-2">
          <input type="color" :value="modelValue?.color || '#000000'" @input="updateBoxShadow('color', $event.target.value)" class="w-8 h-8 rounded border cursor-pointer" />
          <input type="text" :value="modelValue?.color || 'rgba(0,0,0,0.1)'" @input="updateBoxShadow('color', $event.target.value)" class="flex-1 px-2 py-1.5 text-sm border border-gray-300 rounded" />
        </div>
      </div>
      <div>
        <label class="block text-xs text-gray-500 mb-1">Position</label>
        <select :value="modelValue?.position || 'outline'" @change="updateBoxShadow('position', $event.target.value)" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded">
          <option value="outline">Outline</option>
          <option value="inset">Inset</option>
        </select>
      </div>
    </div>

    <!-- Code/Custom CSS Control -->
    <div v-else-if="control.type === 'code'">
      <textarea
        :value="modelValue || ''"
        @input="$emit('update:modelValue', $event.target.value)"
        rows="6"
        class="w-full px-3 py-2 font-mono text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 bg-gray-900 text-green-400"
        placeholder="selector { /* your CSS */ }"
      ></textarea>
      <p class="text-xs text-gray-500 mt-1">Use "selector" to target this element</p>
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

// URL control helper
function updateUrl(key, value) {
  const current = typeof props.modelValue === 'object' ? props.modelValue : { url: props.modelValue || '' };
  emit('update:modelValue', {
    ...current,
    [key]: value
  });
}

// Typography group control helper
function updateTypography(key, value) {
  const current = props.modelValue || {};
  emit('update:modelValue', {
    ...current,
    [key]: value
  });
}

// Background group control helper
function updateBackground(key, value) {
  const current = props.modelValue || {};
  emit('update:modelValue', {
    ...current,
    [key]: value
  });
}

// Border group control helpers
function updateBorder(key, value) {
  const current = props.modelValue || {};
  emit('update:modelValue', {
    ...current,
    [key]: value
  });
}

function updateBorderWidth(side, value) {
  const current = props.modelValue || {};
  const width = current.width || { top: 1, right: 1, bottom: 1, left: 1 };
  emit('update:modelValue', {
    ...current,
    width: {
      ...width,
      [side]: Number(value)
    }
  });
}

function updateBorderRadius(corner, value) {
  const current = props.modelValue || {};
  const radius = current.radius || { topLeft: 0, topRight: 0, bottomRight: 0, bottomLeft: 0 };
  emit('update:modelValue', {
    ...current,
    radius: {
      ...radius,
      [corner]: Number(value)
    }
  });
}

// Box shadow group control helper
function updateBoxShadow(key, value) {
  const current = props.modelValue || {};
  emit('update:modelValue', {
    ...current,
    [key]: value
  });
}
</script>
