<template>
  <Teleport to="body">
    <div
      v-if="visible"
      ref="menuRef"
      :style="{ top: position.y + 'px', left: position.x + 'px' }"
      class="fixed z-[9999] min-w-[200px] bg-white rounded-lg shadow-2xl border border-gray-200 py-1 text-sm"
      @click.stop
    >
      <button
        v-for="item in filteredItems"
        :key="item.action"
        @click="handleAction(item.action)"
        :disabled="item.disabled"
        :class="[
          'w-full px-4 py-2 text-left flex items-center gap-3 transition-colors',
          item.disabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 cursor-pointer',
          item.separator ? 'border-t border-gray-200 mt-1 pt-3' : ''
        ]"
      >
        <span class="text-base">{{ item.icon }}</span>
        <span class="flex-1">{{ item.label }}</span>
        <span v-if="item.shortcut" class="text-xs text-gray-400">{{ item.shortcut }}</span>
      </button>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  position: {
    type: Object,
    default: () => ({ x: 0, y: 0 })
  },
  elementType: {
    type: String, // 'widget', 'section', 'column', 'canvas'
    default: null
  },
  elementData: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'action']);

const menuRef = ref(null);

const menuItems = computed(() => {
  const items = [];

  if (props.elementType === 'widget') {
    items.push(
      { action: 'edit', label: 'Edit Widget', icon: '✏️', shortcut: '' },
      { action: 'duplicate', label: 'Duplicate', icon: '📋', shortcut: 'Ctrl+D' },
      { action: 'copy', label: 'Copy', icon: '📄', shortcut: 'Ctrl+C' },
      { action: 'paste_style', label: 'Paste Style', icon: '🎨', shortcut: '', disabled: true },
      { action: 'delete', label: 'Delete', icon: '🗑️', shortcut: 'Del', separator: true },
      { action: 'save_global', label: 'Save as Global', icon: '🌐', shortcut: '' },
      { action: 'reset_style', label: 'Reset Style', icon: '↺', shortcut: '' },
      { action: 'navigator', label: 'Show in Navigator', icon: '🗺️', shortcut: '', separator: true }
    );
  } else if (props.elementType === 'section') {
    items.push(
      { action: 'edit', label: 'Edit Section', icon: '✏️', shortcut: '' },
      { action: 'duplicate', label: 'Duplicate', icon: '📋', shortcut: 'Ctrl+D' },
      { action: 'copy', label: 'Copy', icon: '📄', shortcut: 'Ctrl+C' },
      { action: 'delete', label: 'Delete', icon: '🗑️', shortcut: 'Del', separator: true },
      { action: 'add_section', label: 'Add New Section', icon: '➕', shortcut: '' },
      { action: 'save_template', label: 'Save as Template', icon: '💾', shortcut: '' },
      { action: 'navigator', label: 'Show in Navigator', icon: '🗺️', shortcut: '', separator: true }
    );
  } else if (props.elementType === 'column') {
    items.push(
      { action: 'edit', label: 'Edit Column', icon: '✏️', shortcut: '' },
      { action: 'duplicate', label: 'Duplicate', icon: '📋', shortcut: 'Ctrl+D' },
      { action: 'copy', label: 'Copy', icon: '📄', shortcut: 'Ctrl+C' },
      { action: 'delete', label: 'Delete', icon: '🗑️', shortcut: 'Del', separator: true },
      { action: 'add_column', label: 'Add New Column', icon: '➕', shortcut: '' },
      { action: 'navigator', label: 'Show in Navigator', icon: '🗺️', shortcut: '', separator: true }
    );
  } else if (props.elementType === 'canvas') {
    items.push(
      { action: 'add_section', label: 'Add Section', icon: '➕', shortcut: '' },
      { action: 'paste', label: 'Paste', icon: '📋', shortcut: 'Ctrl+V', disabled: true },
      { action: 'templates', label: 'Browse Templates', icon: '📚', shortcut: '', separator: true },
      { action: 'page_settings', label: 'Page Settings', icon: '⚙️', shortcut: '' }
    );
  }

  return items;
});

const filteredItems = computed(() => {
  return menuItems.value.filter(item => !item.hidden);
});

function handleAction(action) {
  emit('action', { action, elementData: props.elementData });
  emit('close');
}

function handleClickOutside(event) {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    emit('close');
  }
}

function adjustPosition() {
  if (!menuRef.value) return;

  nextTick(() => {
    const menu = menuRef.value;
    const rect = menu.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;

    // Adjust horizontal position if menu goes off screen
    if (rect.right > viewportWidth) {
      menu.style.left = (props.position.x - rect.width) + 'px';
    }

    // Adjust vertical position if menu goes off screen
    if (rect.bottom > viewportHeight) {
      menu.style.top = (props.position.y - rect.height) + 'px';
    }
  });
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  document.addEventListener('contextmenu', handleClickOutside);
  adjustPosition();
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  document.removeEventListener('contextmenu', handleClickOutside);
});
</script>
