<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50" @click="$emit('close')"></div>

    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
      <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[80vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b">
          <h3 class="text-lg font-semibold text-gray-900">Media Library</h3>
          <button
            type="button"
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-500"
          >
            <span class="text-2xl">&times;</span>
          </button>
        </div>

        <!-- Tabs -->
        <div class="border-b">
          <div class="flex">
            <button
              type="button"
              @click="activeTab = 'library'"
              :class="[
                'px-4 py-3 text-sm font-medium border-b-2 -mb-px',
                activeTab === 'library'
                  ? 'text-indigo-600 border-indigo-600'
                  : 'text-gray-500 border-transparent hover:text-gray-700'
              ]"
            >
              Media Library
            </button>
            <button
              type="button"
              @click="activeTab = 'upload'"
              :class="[
                'px-4 py-3 text-sm font-medium border-b-2 -mb-px',
                activeTab === 'upload'
                  ? 'text-indigo-600 border-indigo-600'
                  : 'text-gray-500 border-transparent hover:text-gray-700'
              ]"
            >
              Upload New
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-4">
          <!-- Library Tab -->
          <div v-if="activeTab === 'library'">
            <!-- Search -->
            <div class="mb-4">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search media..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-8">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-indigo-600 border-t-transparent"></div>
              <p class="mt-2 text-gray-600">Loading media...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="filteredMedia.length === 0" class="text-center py-8">
              <span class="text-4xl">🖼️</span>
              <p class="mt-2 text-gray-600">No media files found</p>
              <button
                type="button"
                @click="activeTab = 'upload'"
                class="mt-3 text-indigo-600 hover:text-indigo-500"
              >
                Upload your first file
              </button>
            </div>

            <!-- Media Grid -->
            <div v-else class="grid grid-cols-4 gap-4">
              <div
                v-for="item in filteredMedia"
                :key="item.id"
                @click="selectItem(item)"
                :class="[
                  'relative aspect-square rounded-lg overflow-hidden cursor-pointer border-2 transition-all',
                  selectedItem?.id === item.id
                    ? 'border-indigo-600 ring-2 ring-indigo-600'
                    : 'border-transparent hover:border-gray-300'
                ]"
              >
                <img
                  :src="item.thumbnail_url || item.url"
                  :alt="item.filename"
                  class="w-full h-full object-cover"
                />
                <div
                  v-if="selectedItem?.id === item.id"
                  class="absolute top-2 right-2 w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center"
                >
                  <span class="text-white text-sm">✓</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Upload Tab -->
          <div v-else-if="activeTab === 'upload'">
            <div
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="handleDrop"
              :class="[
                'border-2 border-dashed rounded-lg p-8 text-center transition-colors',
                isDragging
                  ? 'border-indigo-600 bg-indigo-50'
                  : 'border-gray-300 hover:border-gray-400'
              ]"
            >
              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                multiple
                @change="handleFileSelect"
                class="hidden"
              />
              <span class="text-4xl">📁</span>
              <p class="mt-4 text-gray-600">
                Drag and drop files here, or
                <button
                  type="button"
                  @click="$refs.fileInput.click()"
                  class="text-indigo-600 hover:text-indigo-500 font-medium"
                >
                  browse
                </button>
              </p>
              <p class="mt-2 text-sm text-gray-500">
                PNG, JPG, GIF up to 10MB
              </p>
            </div>

            <!-- Upload Progress -->
            <div v-if="uploading" class="mt-4">
              <div class="flex items-center gap-3">
                <div class="flex-1 bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-indigo-600 h-2 rounded-full transition-all"
                    :style="{ width: uploadProgress + '%' }"
                  ></div>
                </div>
                <span class="text-sm text-gray-600">{{ uploadProgress }}%</span>
              </div>
            </div>

            <!-- Recently Uploaded -->
            <div v-if="recentlyUploaded.length > 0" class="mt-6">
              <h4 class="text-sm font-medium text-gray-700 mb-3">Recently Uploaded</h4>
              <div class="grid grid-cols-4 gap-4">
                <div
                  v-for="item in recentlyUploaded"
                  :key="item.id"
                  @click="selectItem(item)"
                  :class="[
                    'relative aspect-square rounded-lg overflow-hidden cursor-pointer border-2 transition-all',
                    selectedItem?.id === item.id
                      ? 'border-indigo-600'
                      : 'border-transparent hover:border-gray-300'
                  ]"
                >
                  <img
                    :src="item.thumbnail_url || item.url"
                    :alt="item.filename"
                    class="w-full h-full object-cover"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between p-4 border-t bg-gray-50">
          <div v-if="selectedItem" class="text-sm text-gray-600">
            Selected: {{ selectedItem.filename }}
          </div>
          <div v-else class="text-sm text-gray-500">
            No file selected
          </div>
          <div class="flex gap-3">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-800"
            >
              Cancel
            </button>
            <button
              type="button"
              @click="insertSelected"
              :disabled="!selectedItem"
              :class="[
                'px-4 py-2 text-sm font-medium rounded-md',
                selectedItem
                  ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                  : 'bg-gray-300 text-gray-500 cursor-not-allowed'
              ]"
            >
              Insert
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'select']);

const activeTab = ref('library');
const searchQuery = ref('');
const loading = ref(false);
const uploading = ref(false);
const uploadProgress = ref(0);
const isDragging = ref(false);
const mediaItems = ref([]);
const recentlyUploaded = ref([]);
const selectedItem = ref(null);

const filteredMedia = computed(() => {
  if (!searchQuery.value) return mediaItems.value;
  const query = searchQuery.value.toLowerCase();
  return mediaItems.value.filter(item =>
    item.filename.toLowerCase().includes(query)
  );
});

// Load media when modal opens
watch(() => props.show, (newVal) => {
  if (newVal) {
    loadMedia();
    selectedItem.value = null;
  }
});

async function loadMedia() {
  loading.value = true;
  try {
    const response = await fetch('/api/media');
    if (response.ok) {
      const data = await response.json();
      mediaItems.value = data.data || data;
    }
  } catch (error) {
    console.error('Failed to load media:', error);
  } finally {
    loading.value = false;
  }
}

function selectItem(item) {
  selectedItem.value = item;
}

function insertSelected() {
  if (selectedItem.value) {
    emit('select', selectedItem.value.url);
    emit('close');
  }
}

function handleFileSelect(event) {
  const files = event.target.files;
  if (files.length > 0) {
    uploadFiles(files);
  }
}

function handleDrop(event) {
  isDragging.value = false;
  const files = event.dataTransfer.files;
  if (files.length > 0) {
    uploadFiles(files);
  }
}

async function uploadFiles(files) {
  uploading.value = true;
  uploadProgress.value = 0;

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    const formData = new FormData();
    formData.append('file', file);

    try {
      const response = await fetch('/api/media/upload', {
        method: 'POST',
        body: formData,
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
      });

      if (response.ok) {
        const data = await response.json();
        const newItem = data.data || data;
        recentlyUploaded.value.unshift(newItem);
        mediaItems.value.unshift(newItem);
        selectedItem.value = newItem;
      } else {
        const error = await response.json();
        console.error('Upload failed:', error);
        alert('Upload failed: ' + (error.message || 'Unknown error'));
      }
    } catch (error) {
      console.error('Upload failed:', error);
      alert('Upload failed. Please try again.');
    }

    uploadProgress.value = Math.round(((i + 1) / files.length) * 100);
  }

  uploading.value = false;
  activeTab.value = 'library';
}
</script>
