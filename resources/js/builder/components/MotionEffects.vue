<template>
  <div class="motion-effects space-y-3">
    <div class="border-b pb-2 mb-3">
      <h4 class="text-sm font-medium text-gray-900">Motion Effects</h4>
      <p class="text-xs text-gray-500 mt-1">Add entrance animations and scroll effects</p>
    </div>

    <!-- Entrance Animation -->
    <div>
      <label class="block text-xs font-medium text-gray-700 mb-1">
        Entrance Animation
      </label>
      <select
        :value="modelValue.entrance_animation || 'none'"
        @change="updateEffect('entrance_animation', $event.target.value)"
        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      >
        <option value="none">None</option>
        <optgroup label="Fading">
          <option value="fadeIn">Fade In</option>
          <option value="fadeInDown">Fade In Down</option>
          <option value="fadeInUp">Fade In Up</option>
          <option value="fadeInLeft">Fade In Left</option>
          <option value="fadeInRight">Fade In Right</option>
        </optgroup>
        <optgroup label="Zooming">
          <option value="zoomIn">Zoom In</option>
          <option value="zoomOut">Zoom Out</option>
        </optgroup>
        <optgroup label="Bouncing">
          <option value="bounceIn">Bounce In</option>
          <option value="bounceInDown">Bounce In Down</option>
          <option value="bounceInUp">Bounce In Up</option>
        </optgroup>
        <optgroup label="Sliding">
          <option value="slideInDown">Slide In Down</option>
          <option value="slideInUp">Slide In Up</option>
          <option value="slideInLeft">Slide In Left</option>
          <option value="slideInRight">Slide In Right</option>
        </optgroup>
      </select>
    </div>

    <!-- Animation Duration -->
    <div v-if="modelValue.entrance_animation && modelValue.entrance_animation !== 'none'">
      <label class="flex items-center justify-between text-xs font-medium text-gray-700 mb-1">
        <span>Animation Duration</span>
        <span class="text-gray-500">{{ modelValue.animation_duration || 1000 }}ms</span>
      </label>
      <input
        type="range"
        :value="modelValue.animation_duration || 1000"
        @input="updateEffect('animation_duration', parseInt($event.target.value))"
        min="200"
        max="3000"
        step="100"
        class="w-full"
      />
    </div>

    <!-- Animation Delay -->
    <div v-if="modelValue.entrance_animation && modelValue.entrance_animation !== 'none'">
      <label class="flex items-center justify-between text-xs font-medium text-gray-700 mb-1">
        <span>Animation Delay</span>
        <span class="text-gray-500">{{ modelValue.animation_delay || 0 }}ms</span>
      </label>
      <input
        type="range"
        :value="modelValue.animation_delay || 0"
        @input="updateEffect('animation_delay', parseInt($event.target.value))"
        min="0"
        max="3000"
        step="100"
        class="w-full"
      />
    </div>

    <!-- Sticky Position -->
    <div class="pt-3 border-t">
      <label class="flex items-center gap-2 cursor-pointer">
        <input
          type="checkbox"
          :checked="modelValue.sticky === true"
          @change="updateEffect('sticky', $event.target.checked)"
          class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
        />
        <span class="text-xs font-medium text-gray-700">Sticky (Fixed on Scroll)</span>
      </label>
    </div>

    <!-- Sticky Options -->
    <div v-if="modelValue.sticky" class="pl-6 space-y-2">
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Sticky Position</label>
        <select
          :value="modelValue.sticky_position || 'top'"
          @change="updateEffect('sticky_position', $event.target.value)"
          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500"
        >
          <option value="top">Top</option>
          <option value="bottom">Bottom</option>
        </select>
      </div>
      <div>
        <label class="flex items-center justify-between text-xs font-medium text-gray-700 mb-1">
          <span>Offset</span>
          <span class="text-gray-500">{{ modelValue.sticky_offset || 0 }}px</span>
        </label>
        <input
          type="range"
          :value="modelValue.sticky_offset || 0"
          @input="updateEffect('sticky_offset', parseInt($event.target.value))"
          min="0"
          max="200"
          step="5"
          class="w-full"
        />
      </div>
    </div>

    <!-- Parallax Effect -->
    <div class="pt-3 border-t">
      <label class="flex items-center gap-2 cursor-pointer">
        <input
          type="checkbox"
          :checked="modelValue.parallax === true"
          @change="updateEffect('parallax', $event.target.checked)"
          class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
        />
        <span class="text-xs font-medium text-gray-700">Parallax Scrolling</span>
      </label>
    </div>

    <!-- Parallax Options -->
    <div v-if="modelValue.parallax" class="pl-6 space-y-2">
      <div>
        <label class="flex items-center justify-between text-xs font-medium text-gray-700 mb-1">
          <span>Parallax Speed</span>
          <span class="text-gray-500">{{ modelValue.parallax_speed || 0.5 }}</span>
        </label>
        <input
          type="range"
          :value="modelValue.parallax_speed || 0.5"
          @input="updateEffect('parallax_speed', parseFloat($event.target.value))"
          min="0.1"
          max="2"
          step="0.1"
          class="w-full"
        />
        <p class="text-xs text-gray-500 mt-1">Lower = slower movement</p>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:modelValue']);

function updateEffect(key, value) {
  emit('update:modelValue', {
    ...props.modelValue,
    [key]: value
  });
}
</script>

<style scoped>
input[type="range"] {
  accent-color: #4f46e5;
}

input[type="range"]::-webkit-slider-thumb {
  cursor: pointer;
}

input[type="range"]::-moz-range-thumb {
  cursor: pointer;
}
</style>
