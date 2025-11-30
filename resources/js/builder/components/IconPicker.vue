<template>
  <div class="icon-picker">
    <!-- Trigger Button -->
    <button
      type="button"
      @click="showModal = true"
      class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 flex items-center justify-center gap-2"
    >
      <span v-if="modelValue" class="text-2xl">{{ modelValue }}</span>
      <span>{{ modelValue ? 'Change Icon' : 'Select Icon' }}</span>
    </button>

    <!-- Modal -->
    <Teleport to="body">
      <div
        v-if="showModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50"
        @click.self="showModal = false"
      >
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-4xl max-h-[80vh] flex flex-col">
          <!-- Header -->
          <div class="p-4 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Select Icon</h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Search -->
          <div class="p-4 border-b">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search icons..."
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
          </div>

          <!-- Tabs -->
          <div class="flex border-b">
            <button
              v-for="category in categories"
              :key="category"
              @click="activeCategory = category"
              :class="[
                'px-4 py-3 text-sm font-medium border-b-2 transition-colors',
                activeCategory === category
                  ? 'border-indigo-500 text-indigo-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700'
              ]"
            >
              {{ category }}
            </button>
          </div>

          <!-- Icons Grid -->
          <div class="flex-1 overflow-y-auto p-4">
            <div v-if="filteredIcons.length === 0" class="text-center py-12 text-gray-400">
              No icons found
            </div>
            <div v-else class="grid grid-cols-8 gap-3">
              <button
                v-for="icon in filteredIcons"
                :key="icon.value"
                @click="selectIcon(icon.value)"
                :class="[
                  'aspect-square flex items-center justify-center text-3xl rounded-lg border-2 transition-all hover:border-indigo-300 hover:bg-indigo-50',
                  modelValue === icon.value ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'
                ]"
                :title="icon.name"
              >
                {{ icon.value }}
              </button>
            </div>
          </div>

          <!-- Footer -->
          <div class="p-4 border-t flex justify-between items-center">
            <button
              v-if="modelValue"
              @click="clearIcon"
              class="px-4 py-2 text-sm text-red-600 hover:text-red-700 font-medium"
            >
              Clear Icon
            </button>
            <div class="flex gap-2 ml-auto">
              <button
                @click="showModal = false"
                class="px-4 py-2 text-sm border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                @click="showModal = false"
                class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
              >
                Done
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue']);

const showModal = ref(false);
const searchQuery = ref('');
const activeCategory = ref('Popular');

const categories = ['Popular', 'Arrows', 'Social', 'Business', 'Media', 'UI'];

const iconLibrary = {
  Popular: [
    { name: 'Star', value: '⭐' },
    { name: 'Heart', value: '❤️' },
    { name: 'Fire', value: '🔥' },
    { name: 'Check', value: '✓' },
    { name: 'Bolt', value: '⚡' },
    { name: 'Sparkles', value: '✨' },
    { name: 'Rocket', value: '🚀' },
    { name: 'Trophy', value: '🏆' },
    { name: 'Diamond', value: '💎' },
    { name: 'Crown', value: '👑' },
    { name: 'Medal', value: '🏅' },
    { name: 'Gift', value: '🎁' },
    { name: 'Bell', value: '🔔' },
    { name: 'Light Bulb', value: '💡' },
    { name: 'Target', value: '🎯' },
    { name: 'Shield', value: '🛡️' },
  ],
  Arrows: [
    { name: 'Arrow Right', value: '→' },
    { name: 'Arrow Left', value: '←' },
    { name: 'Arrow Up', value: '↑' },
    { name: 'Arrow Down', value: '↓' },
    { name: 'Arrow Up Right', value: '↗' },
    { name: 'Arrow Down Right', value: '↘' },
    { name: 'Arrow Down Left', value: '↙' },
    { name: 'Arrow Up Left', value: '↖' },
    { name: 'Double Arrow Right', value: '⇒' },
    { name: 'Double Arrow Left', value: '⇐' },
    { name: 'Curved Arrow', value: '↪' },
    { name: 'Refresh', value: '↻' },
    { name: 'Undo', value: '↶' },
    { name: 'Redo', value: '↷' },
  ],
  Social: [
    { name: 'Facebook', value: '📘' },
    { name: 'Twitter', value: '🐦' },
    { name: 'Instagram', value: '📷' },
    { name: 'LinkedIn', value: '💼' },
    { name: 'YouTube', value: '📹' },
    { name: 'TikTok', value: '🎵' },
    { name: 'WhatsApp', value: '💬' },
    { name: 'Telegram', value: '✈️' },
    { name: 'Email', value: '✉️' },
    { name: 'Phone', value: '📞' },
    { name: 'Message', value: '💬' },
    { name: 'Chat', value: '💭' },
  ],
  Business: [
    { name: 'Briefcase', value: '💼' },
    { name: 'Chart Up', value: '📈' },
    { name: 'Chart Down', value: '📉' },
    { name: 'Money Bag', value: '💰' },
    { name: 'Dollar', value: '💵' },
    { name: 'Credit Card', value: '💳' },
    { name: 'Building', value: '🏢' },
    { name: 'Handshake', value: '🤝' },
    { name: 'Calendar', value: '📅' },
    { name: 'Clock', value: '⏰' },
    { name: 'Document', value: '📄' },
    { name: 'Folder', value: '📁' },
    { name: 'Graph', value: '📊' },
    { name: 'Clipboard', value: '📋' },
  ],
  Media: [
    { name: 'Camera', value: '📷' },
    { name: 'Video', value: '🎥' },
    { name: 'Music', value: '🎵' },
    { name: 'Microphone', value: '🎤' },
    { name: 'Headphones', value: '🎧' },
    { name: 'Film', value: '🎬' },
    { name: 'TV', value: '📺' },
    { name: 'Radio', value: '📻' },
    { name: 'Image', value: '🖼️' },
    { name: 'Play', value: '▶️' },
    { name: 'Pause', value: '⏸️' },
    { name: 'Stop', value: '⏹️' },
  ],
  UI: [
    { name: 'Home', value: '🏠' },
    { name: 'Search', value: '🔍' },
    { name: 'Settings', value: '⚙️' },
    { name: 'Menu', value: '☰' },
    { name: 'Close', value: '✕' },
    { name: 'Plus', value: '➕' },
    { name: 'Minus', value: '➖' },
    { name: 'Lock', value: '🔒' },
    { name: 'Unlock', value: '🔓' },
    { name: 'Key', value: '🔑' },
    { name: 'Link', value: '🔗' },
    { name: 'Download', value: '⬇️' },
    { name: 'Upload', value: '⬆️' },
    { name: 'Share', value: '↗️' },
    { name: 'Info', value: 'ℹ️' },
    { name: 'Question', value: '❓' },
    { name: 'Warning', value: '⚠️' },
    { name: 'Error', value: '❌' },
  ]
};

const filteredIcons = computed(() => {
  let icons = iconLibrary[activeCategory.value] || [];

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    const allIcons = Object.values(iconLibrary).flat();
    icons = allIcons.filter(icon =>
      icon.name.toLowerCase().includes(query)
    );
  }

  return icons;
});

function selectIcon(value) {
  emit('update:modelValue', value);
}

function clearIcon() {
  emit('update:modelValue', '');
  showModal.value = false;
}
</script>
