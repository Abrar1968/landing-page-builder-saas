<template>
  <div class="widget-content">
    <!-- Heading Widget -->
    <component
      :is="getTagName()"
      v-if="widget.type === 'heading'"
      :style="getHeadingStyles()"
    >
      {{ settings.title ?? 'Heading' }}
    </component>

    <!-- Text Editor Widget -->
    <div
      v-else-if="widget.type === 'text-editor'"
      :style="getTextEditorStyles()"
      v-html="settings.editor ?? '<p>Lorem ipsum dolor sit amet</p>'"
    ></div>

    <!-- Image Widget -->
    <div v-else-if="widget.type === 'image'" :style="getImageStyles()">
      <img
        v-if="settings.image_url"
        :src="settings.image_url"
        :alt="settings.alt_text ?? ''"
        :style="{ width: (settings.width ?? 100) + '%' }"
      />
      <div v-else class="image-placeholder">
        <span>🖼</span>
        <span>Click to add image</span>
      </div>
      <p v-if="settings.caption" class="caption">{{ settings.caption }}</p>
    </div>

    <!-- Button Widget -->
    <div v-else-if="widget.type === 'button'" :style="getButtonContainerStyles()">
      <a
        :href="settings.link || '#'"
        :target="settings.target ? '_blank' : '_self'"
        :style="getButtonStyles()"
        class="inline-block px-6 py-3 font-medium"
      >
        {{ settings.text ?? 'Click Me' }}
      </a>
    </div>

    <!-- Video Widget -->
    <div v-else-if="widget.type === 'video'" class="video-wrapper" :style="getVideoStyles()">
      <iframe
        v-if="getVideoEmbedUrl()"
        :src="getVideoEmbedUrl()"
        frameborder="0"
        allowfullscreen
        class="w-full h-full"
      ></iframe>
      <div v-else class="video-placeholder">
        <span>▶</span>
        <span>Add video URL</span>
      </div>
    </div>

    <!-- Divider Widget -->
    <div v-else-if="widget.type === 'divider'" :style="getDividerContainerStyles()">
      <hr :style="getDividerStyles()" />
    </div>

    <!-- Spacer Widget -->
    <div
      v-else-if="widget.type === 'spacer'"
      :style="{ height: (settings.space ?? 50) + 'px' }"
    ></div>

    <!-- Icon Widget -->
    <div v-else-if="widget.type === 'icon'" :style="getIconContainerStyles()">
      <a v-if="settings.link" :href="settings.link">
        <span :style="getIconStyles()">{{ settings.icon ?? '★' }}</span>
      </a>
      <span v-else :style="getIconStyles()">{{ settings.icon ?? '★' }}</span>
    </div>

    <!-- Icon Box Widget -->
    <div v-else-if="widget.type === 'icon-box'" :style="getIconBoxStyles()">
      <div :style="{ fontSize: (settings.icon_size ?? 50) + 'px', color: settings.icon_color ?? '#4f46e5' }">
        {{ settings.icon ?? '⚡' }}
      </div>
      <h4 :style="{ color: settings.title_color ?? '#1f2937' }" class="font-semibold mt-3">
        {{ settings.title ?? 'Icon Box' }}
      </h4>
      <p class="text-gray-600 mt-2">{{ settings.description ?? 'Click here to add your own text.' }}</p>
    </div>

    <!-- Counter Widget -->
    <div v-else-if="widget.type === 'counter'" :style="getCounterStyles()">
      <div :style="{ fontSize: (settings.number_size ?? 48) + 'px', color: settings.number_color ?? '#4f46e5' }" class="font-bold">
        {{ settings.prefix ?? '' }}{{ settings.ending_number ?? 100 }}{{ settings.suffix ?? '' }}
      </div>
      <div :style="{ color: settings.title_color ?? '#6b7280' }">{{ settings.title ?? 'Cool Number' }}</div>
    </div>

    <!-- Progress Bar Widget -->
    <div v-else-if="widget.type === 'progress-bar'">
      <div v-if="settings.title" class="mb-2 flex justify-between">
        <span>{{ settings.title }}</span>
        <span v-if="settings.display_percent !== false">{{ settings.percent ?? 75 }}%</span>
      </div>
      <div :style="{ backgroundColor: settings.bg_color ?? '#e5e7eb', height: (settings.height ?? 12) + 'px', borderRadius: '9999px' }">
        <div :style="{
          backgroundColor: settings.bar_color ?? '#4f46e5',
          width: (settings.percent ?? 75) + '%',
          height: '100%',
          borderRadius: '9999px',
          transition: 'width 0.3s'
        }"></div>
      </div>
    </div>

    <!-- Testimonial Widget -->
    <div v-else-if="widget.type === 'testimonial'" :style="getTestimonialStyles()">
      <p :style="{ color: settings.content_color ?? '#4b5563' }" class="italic mb-4">
        "{{ settings.content ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' }}"
      </p>
      <div class="flex items-center justify-center gap-3">
        <img
          v-if="settings.image_url"
          :src="settings.image_url"
          class="w-12 h-12 rounded-full object-cover"
        />
        <div>
          <div :style="{ color: settings.name_color ?? '#1f2937' }" class="font-semibold">
            {{ settings.name ?? 'John Doe' }}
          </div>
          <div class="text-sm text-gray-500">{{ settings.title ?? 'Designer' }}</div>
        </div>
      </div>
    </div>

    <!-- Social Icons Widget -->
    <div v-else-if="widget.type === 'social-icons'" :style="getSocialIconsStyles()">
      <a v-if="settings.facebook" :href="settings.facebook" class="mx-2" :style="getSocialIconStyle()">f</a>
      <a v-if="settings.twitter" :href="settings.twitter" class="mx-2" :style="getSocialIconStyle()">𝕏</a>
      <a v-if="settings.instagram" :href="settings.instagram" class="mx-2" :style="getSocialIconStyle()">📷</a>
      <a v-if="settings.linkedin" :href="settings.linkedin" class="mx-2" :style="getSocialIconStyle()">in</a>
      <span v-if="!settings.facebook && !settings.twitter && !settings.instagram && !settings.linkedin" class="text-gray-400">
        Add social links
      </span>
    </div>

    <!-- Alert Widget -->
    <div v-else-if="widget.type === 'alert'" :class="getAlertClasses()" class="p-4 rounded-lg">
      <div class="flex items-start gap-3">
        <span v-if="settings.show_icon !== false" class="text-xl">{{ getAlertIcon() }}</span>
        <div>
          <div class="font-semibold">{{ settings.title ?? 'This is an Alert' }}</div>
          <div class="mt-1">{{ settings.content ?? 'Click to edit this text.' }}</div>
        </div>
      </div>
    </div>

    <!-- Default/Unknown Widget -->
    <div v-else class="p-4 bg-gray-100 rounded text-center text-gray-500">
      Unknown widget: {{ widget.type }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  widget: {
    type: Object,
    required: true
  }
});

const settings = computed(() => props.widget.settings || {});

// Helper functions for styles
function getTagName() {
  return settings.value.size ?? 'h2';
}

function getHeadingStyles() {
  return {
    color: settings.value.text_color ?? '#1f2937',
    textAlign: settings.value.alignment ?? 'left',
    margin: formatDimensions(settings.value.margin),
    padding: formatDimensions(settings.value.padding)
  };
}

function getTextEditorStyles() {
  return {
    color: settings.value.text_color ?? '#4b5563',
    textAlign: settings.value.alignment ?? 'left',
    margin: formatDimensions(settings.value.margin),
    padding: formatDimensions(settings.value.padding)
  };
}

function getImageStyles() {
  const alignment = settings.value.alignment ?? 'left';
  return {
    textAlign: alignment,
    margin: formatDimensions(settings.value.margin),
    padding: formatDimensions(settings.value.padding)
  };
}

function getButtonContainerStyles() {
  return {
    textAlign: settings.value.alignment ?? 'left',
    margin: formatDimensions(settings.value.margin)
  };
}

function getButtonStyles() {
  return {
    backgroundColor: settings.value.background_color ?? '#4f46e5',
    color: settings.value.text_color ?? '#ffffff',
    borderRadius: (settings.value.border_radius ?? 6) + 'px'
  };
}

function getVideoStyles() {
  const ratio = settings.value.aspect_ratio ?? '16:9';
  const [w, h] = ratio.split(':').map(Number);
  return {
    width: (settings.value.width ?? 100) + '%',
    paddingBottom: ((h / w) * 100) + '%',
    position: 'relative',
    margin: formatDimensions(settings.value.margin)
  };
}

function getVideoEmbedUrl() {
  const url = settings.value.youtube_url;
  if (!url) return null;

  // Extract YouTube video ID
  const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([^&?]+)/);
  if (match) {
    return `https://www.youtube.com/embed/${match[1]}`;
  }

  // Extract Vimeo video ID
  const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
  if (vimeoMatch) {
    return `https://player.vimeo.com/video/${vimeoMatch[1]}`;
  }

  return null;
}

function getDividerContainerStyles() {
  return {
    textAlign: settings.value.alignment ?? 'center',
    margin: formatDimensions(settings.value.margin)
  };
}

function getDividerStyles() {
  return {
    borderStyle: settings.value.style ?? 'solid',
    borderColor: settings.value.color ?? '#e5e7eb',
    borderWidth: (settings.value.weight ?? 1) + 'px',
    width: (settings.value.width ?? 100) + '%',
    display: 'inline-block'
  };
}

function getIconContainerStyles() {
  return {
    textAlign: settings.value.alignment ?? 'center',
    margin: formatDimensions(settings.value.margin)
  };
}

function getIconStyles() {
  return {
    fontSize: (settings.value.size ?? 50) + 'px',
    color: settings.value.primary_color ?? '#4f46e5'
  };
}

function getIconBoxStyles() {
  return {
    textAlign: settings.value.alignment ?? 'center',
    margin: formatDimensions(settings.value.margin),
    padding: formatDimensions(settings.value.padding)
  };
}

function getCounterStyles() {
  return {
    textAlign: settings.value.alignment ?? 'center',
    margin: formatDimensions(settings.value.margin)
  };
}

function getTestimonialStyles() {
  return {
    textAlign: settings.value.alignment ?? 'center',
    margin: formatDimensions(settings.value.margin),
    padding: formatDimensions(settings.value.padding)
  };
}

function getSocialIconsStyles() {
  return {
    textAlign: settings.value.alignment ?? 'center',
    margin: formatDimensions(settings.value.margin)
  };
}

function getSocialIconStyle() {
  return {
    color: settings.value.icon_color ?? '#4b5563',
    fontSize: (settings.value.icon_size ?? 24) + 'px'
  };
}

function getAlertClasses() {
  const type = settings.value.alert_type ?? 'info';
  const classes = {
    info: 'bg-blue-50 text-blue-800 border border-blue-200',
    success: 'bg-green-50 text-green-800 border border-green-200',
    warning: 'bg-yellow-50 text-yellow-800 border border-yellow-200',
    danger: 'bg-red-50 text-red-800 border border-red-200'
  };
  return classes[type] || classes.info;
}

function getAlertIcon() {
  const type = settings.value.alert_type ?? 'info';
  const icons = {
    info: 'ℹ️',
    success: '✅',
    warning: '⚠️',
    danger: '❌'
  };
  return icons[type] || icons.info;
}

function formatDimensions(dim) {
  if (!dim) return undefined;
  const top = dim.top ?? 0;
  const right = dim.right ?? 0;
  const bottom = dim.bottom ?? 0;
  const left = dim.left ?? 0;
  const unit = dim.unit ?? 'px';
  return `${top}${unit} ${right}${unit} ${bottom}${unit} ${left}${unit}`;
}
</script>

<style scoped>
.image-placeholder,
.video-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: #f3f4f6;
  border: 2px dashed #d1d5db;
  border-radius: 0.5rem;
  color: #9ca3af;
}

.image-placeholder span:first-child,
.video-placeholder span:first-child {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.caption {
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: #6b7280;
  text-align: center;
}

.video-wrapper {
  position: relative;
}

.video-wrapper iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
</style>
