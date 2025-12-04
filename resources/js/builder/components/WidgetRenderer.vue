<template>
  <div
    class="widget-content"
    :style="getCommonWrapperStyles()"
    :class="[settings.css_classes, responsiveVisibilityClasses, animationClasses]"
    :id="settings.css_id"
    ref="widgetRef"
  >
    <!-- Heading Widget -->
    <h1
      v-if="widget.widgetType === 'heading' && headingTag === 'h1'"
      :style="headingStyles"
    >
      <a
        v-if="settings.link?.url"
        :href="settings.link.url"
        :target="settings.link.is_external ? '_blank' : '_self'"
        :rel="settings.link.nofollow ? 'nofollow' : ''"
        style="color: inherit; text-decoration: inherit;"
      >
        {{ settings.title ?? 'Heading' }}
      </a>
      <template v-else>
        {{ settings.title ?? 'Heading' }}
      </template>
    </h1>
    <h2
      v-else-if="widget.widgetType === 'heading' && headingTag === 'h2'"
      :style="headingStyles"
    >
      <a
        v-if="settings.link?.url"
        :href="settings.link.url"
        :target="settings.link.is_external ? '_blank' : '_self'"
        :rel="settings.link.nofollow ? 'nofollow' : ''"
        style="color: inherit; text-decoration: inherit;"
      >
        {{ settings.title ?? 'Heading' }}
      </a>
      <template v-else>
        {{ settings.title ?? 'Heading' }}
      </template>
    </h2>
    <h3
      v-else-if="widget.widgetType === 'heading' && headingTag === 'h3'"
      :style="headingStyles"
    >
      <a
        v-if="settings.link?.url"
        :href="settings.link.url"
        :target="settings.link.is_external ? '_blank' : '_self'"
        :rel="settings.link.nofollow ? 'nofollow' : ''"
        style="color: inherit; text-decoration: inherit;"
      >
        {{ settings.title ?? 'Heading' }}
      </a>
      <template v-else>
        {{ settings.title ?? 'Heading' }}
      </template>
    </h3>
    <h4
      v-else-if="widget.widgetType === 'heading' && headingTag === 'h4'"
      :style="headingStyles"
    >
      <a
        v-if="settings.link?.url"
        :href="settings.link.url"
        :target="settings.link.is_external ? '_blank' : '_self'"
        :rel="settings.link.nofollow ? 'nofollow' : ''"
        style="color: inherit; text-decoration: inherit;"
      >
        {{ settings.title ?? 'Heading' }}
      </a>
      <template v-else>
        {{ settings.title ?? 'Heading' }}
      </template>
    </h4>
    <h5
      v-else-if="widget.widgetType === 'heading' && headingTag === 'h5'"
      :style="headingStyles"
    >
      <a
        v-if="settings.link?.url"
        :href="settings.link.url"
        :target="settings.link.is_external ? '_blank' : '_self'"
        :rel="settings.link.nofollow ? 'nofollow' : ''"
        style="color: inherit; text-decoration: inherit;"
      >
        {{ settings.title ?? 'Heading' }}
      </a>
      <template v-else>
        {{ settings.title ?? 'Heading' }}
      </template>
    </h5>
    <h6
      v-else-if="widget.widgetType === 'heading' && headingTag === 'h6'"
      :style="headingStyles"
    >
      <a
        v-if="settings.link?.url"
        :href="settings.link.url"
        :target="settings.link.is_external ? '_blank' : '_self'"
        :rel="settings.link.nofollow ? 'nofollow' : ''"
        style="color: inherit; text-decoration: inherit;"
      >
        {{ settings.title ?? 'Heading' }}
      </a>
      <template v-else>
        {{ settings.title ?? 'Heading' }}
      </template>
    </h6>

    <!-- Text Editor Widget -->
    <div
      v-else-if="widget.widgetType === 'text-editor'"
      :style="getTextEditorStyles()"
      v-html="settings.editor ?? '<p>Lorem ipsum dolor sit amet</p>'"
    </div>

    <!-- Image Widget -->
    <div v-else-if="widget.widgetType === 'image'" :style="getImageContainerStyles()">
      <component :is="settings.link ? 'a' : 'div'" :href="settings.link || undefined" :target="settings.link && settings.link_target ? '_blank' : undefined" class="inline-block">
        <img
          v-if="settings.image_url"
          :src="settings.image_url"
          :alt="settings.alt_text ?? ''"
          :style="getImageElementStyles()"
          :class="['transition-all duration-300', `image-widget-${widget.id}`, getImageHoverClass()]"
        />
        <div v-else class="image-placeholder">
          <span>🖼</span>
          <span>Click to add image</span>
        </div>
      </component>
      <p v-if="settings.caption" class="caption">{{ settings.caption }}</p>
      <!-- Inject scoped hover styles -->
      <component :is="'style'" v-if="settings.hover_animation && settings.hover_animation !== 'none'">
        .image-widget-{{ widget.id }}.hover-zoom:hover { transform: scale(1.1); }
        .image-widget-{{ widget.id }}.hover-zoom_out:hover { transform: scale(0.9); }
        .image-widget-{{ widget.id }}.hover-grayscale:hover { filter: grayscale(100%); }
        .image-widget-{{ widget.id }}.hover-blur:hover { filter: blur(3px); }
        .image-widget-{{ widget.id }}.hover-brightness:hover { filter: brightness(1.2); }
      </component>
    </div>

    <!-- Button Widget -->
    <div v-else-if="widget.widgetType === 'button'" :style="getButtonContainerStyles()">
      <a
        :href="settings.link || '#'"
        :target="settings.target ? '_blank' : '_self'"
        :style="getButtonStyles()"
        :class="['inline-flex items-center font-medium transition-colors duration-200', `button-widget-${widget.id}`]"
        @mouseenter="buttonHover = true"
        @mouseleave="buttonHover = false"
      >
        <span v-if="settings.icon && settings.icon_position !== 'right'" :style="{ marginRight: (settings.icon_spacing ?? 8) + 'px' }">{{ settings.icon }}</span>
        {{ settings.text ?? 'Click Me' }}
        <span v-if="settings.icon && settings.icon_position === 'right'" :style="{ marginLeft: (settings.icon_spacing ?? 8) + 'px' }">{{ settings.icon }}</span>
      </a>
      <!-- Inject scoped hover styles -->
      <component :is="'style'" v-if="hasButtonHoverStyles">
        .button-widget-{{ widget.id }}:hover {
          background-color: {{ settings.hover_background_color ?? settings.background_color ?? '#4338ca' }} !important;
          color: {{ settings.hover_text_color ?? settings.text_color ?? '#ffffff' }} !important;
          border-color: {{ settings.hover_border_color ?? settings.border_color ?? '#4338ca' }} !important;
        }
      </component>
    </div>

    <!-- Video Widget -->
    <div v-else-if="widget.widgetType === 'video'" class="video-wrapper" :style="getVideoStyles()">
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
    <div v-else-if="widget.widgetType === 'divider'" :style="getDividerContainerStyles()">
      <!-- Simple divider (no element) -->
      <hr v-if="!settings.divider_element || settings.divider_element === 'none'" :style="getDividerStyles()" />
      <!-- Divider with element (text or icon) -->
      <div v-else class="flex items-center" :style="getDividerWrapperStyles()">
        <hr :style="getDividerLineStyles()" class="flex-1" />
        <span :style="getDividerElementStyles()">
          {{ settings.divider_element === 'text' ? (settings.element_text || 'OR') : (settings.element_icon || '★') }}
        </span>
        <hr :style="getDividerLineStyles()" class="flex-1" />
      </div>
    </div>

    <!-- Spacer Widget -->
    <div
      v-else-if="widget.widgetType === 'spacer'"
      :style="{ height: (settings.space ?? 50) + (settings.space_unit ?? 'px') }"
    ></div>

    <!-- Icon Widget -->
    <div v-else-if="widget.widgetType === 'icon'" :style="getIconContainerStyles()">
      <a v-if="settings.link" :href="settings.link">
        <span :style="getIconStyles()">{{ settings.icon ?? '★' }}</span>
      </a>
      <span v-else :style="getIconStyles()">{{ settings.icon ?? '★' }}</span>
    </div>

    <!-- Icon Box Widget -->
    <component
      :is="settings.link?.url ? 'a' : 'div'"
      v-else-if="widget.widgetType === 'icon-box'"
      :href="settings.link?.url"
      :target="settings.link?.is_external ? '_blank' : undefined"
      :style="getIconBoxStyles()"
      :class="[`icon-box-widget-${widget.id}`, getIconBoxLayoutClass()]"
      class="icon-box-wrapper block no-underline"
    >
      <div class="icon-box-icon" :style="getIconBoxIconStyles()">
        {{ settings.icon ?? '⚡' }}
      </div>
      <div class="icon-box-content" :style="getIconBoxContentStyles()">
        <h4 class="icon-box-title font-semibold" :style="{ color: settings.title_color ?? '#1f2937' }">
          {{ settings.title ?? 'Icon Box' }}
        </h4>
        <p class="icon-box-description mt-2" :style="{ color: settings.description_color ?? '#6b7280' }">
          {{ settings.description ?? 'Click here to add your own text.' }}
        </p>
      </div>
    </component>
    <!-- Icon Box hover styles -->
    <component :is="'style'" v-if="widget.widgetType === 'icon-box' && (settings.hover_icon_color || settings.hover_title_color)">
      .icon-box-widget-{{ widget.id }}:hover .icon-box-icon { color: {{ settings.hover_icon_color ?? settings.icon_color ?? '#4f46e5' }} !important; }
      .icon-box-widget-{{ widget.id }}:hover .icon-box-title { color: {{ settings.hover_title_color ?? settings.title_color ?? '#1f2937' }} !important; }
    </component>

    <!-- Counter Widget -->
    <div v-else-if="widget.widgetType === 'counter'" :style="getCounterStyles()">
      <div :style="{ fontSize: (settings.number_size ?? 48) + 'px', color: settings.number_color ?? '#4f46e5' }" class="font-bold">
        {{ settings.prefix ?? '' }}{{ settings.ending_number ?? 100 }}{{ settings.suffix ?? '' }}
      </div>
      <div :style="{ color: settings.title_color ?? '#6b7280' }">{{ settings.title ?? 'Cool Number' }}</div>
    </div>

    <!-- Progress Bar Widget -->
    <div v-else-if="widget.widgetType === 'progress-bar'">
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
    <div v-else-if="widget.widgetType === 'testimonial'" :style="getTestimonialStyles()">
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
    <div v-else-if="widget.widgetType === 'social-icons'" :style="getSocialIconsStyles()">
      <a v-if="settings.facebook" :href="settings.facebook" class="mx-2" :style="getSocialIconStyle()">f</a>
      <a v-if="settings.twitter" :href="settings.twitter" class="mx-2" :style="getSocialIconStyle()">𝕏</a>
      <a v-if="settings.instagram" :href="settings.instagram" class="mx-2" :style="getSocialIconStyle()">📷</a>
      <a v-if="settings.linkedin" :href="settings.linkedin" class="mx-2" :style="getSocialIconStyle()">in</a>
      <span v-if="!settings.facebook && !settings.twitter && !settings.instagram && !settings.linkedin" class="text-gray-400">
        Add social links
      </span>
    </div>

    <!-- Alert Widget -->
    <div v-else-if="widget.widgetType === 'alert'" :class="getAlertClasses()" class="p-4 rounded-lg">
      <div class="flex items-start gap-3">
        <span v-if="settings.show_icon !== false" class="text-xl">{{ getAlertIcon() }}</span>
        <div>
          <div class="font-semibold">{{ settings.title ?? 'This is an Alert' }}</div>
          <div class="mt-1">{{ settings.content ?? 'Click to edit this text.' }}</div>
        </div>
      </div>
    </div>

    <!-- Image Box Widget -->
    <component
      :is="settings.link?.url ? 'a' : 'div'"
      v-else-if="widget.widgetType === 'image-box'"
      :href="settings.link?.url"
      :target="settings.link?.is_external ? '_blank' : undefined"
      :style="getImageBoxStyles()"
      :class="[`image-box-widget-${widget.id}`, getImageBoxLayoutClass()]"
      class="image-box-wrapper block no-underline"
    >
      <div class="image-box-image" :style="getImageBoxImageContainerStyles()">
        <img
          v-if="settings.image_url"
          :src="settings.image_url"
          class="object-cover rounded-lg transition-all duration-300"
          :class="getImageBoxImageHoverClass()"
          :style="getImageBoxImageStyles()"
        />
        <div v-else class="bg-gray-200 rounded-lg flex items-center justify-center text-gray-400" :style="getImageBoxImageStyles()">🖼️</div>
      </div>
      <div class="image-box-content" :style="getImageBoxContentStyles()">
        <h4 class="image-box-title font-semibold text-lg" :style="{ color: settings.title_color ?? '#1f2937' }">
          {{ settings.title ?? 'Image Box' }}
        </h4>
        <p class="image-box-description mt-2" :style="{ color: settings.description_color ?? '#6b7280' }">
          {{ settings.description ?? 'Click here to add your own text.' }}
        </p>
      </div>
    </component>
    <!-- Image Box hover styles -->
    <component :is="'style'" v-if="widget.widgetType === 'image-box' && (settings.hover_title_color || settings.hover_animation)">
      .image-box-widget-{{ widget.id }}:hover .image-box-title { color: {{ settings.hover_title_color ?? settings.title_color ?? '#1f2937' }} !important; }
      .image-box-widget-{{ widget.id }} .hover-zoom:hover { transform: scale(1.1); }
      .image-box-widget-{{ widget.id }} .hover-zoom_out:hover { transform: scale(0.9); }
      .image-box-widget-{{ widget.id }} .hover-grayscale:hover { filter: grayscale(100%); }
      .image-box-widget-{{ widget.id }} .hover-blur:hover { filter: blur(3px); }
    </component>

    <!-- Star Rating Widget -->
    <div v-else-if="widget.widgetType === 'star-rating'" :style="{ textAlign: settings.alignment ?? 'left' }">
      <div :style="{ fontSize: (settings.size ?? 24) + 'px' }">
        <span v-for="i in 5" :key="i" :style="{ color: i <= (settings.rating ?? 4) ? (settings.color ?? '#fbbf24') : (settings.unmarked_color ?? '#d1d5db') }">★</span>
      </div>
      <div v-if="settings.title" class="text-sm text-gray-600 mt-1">{{ settings.title }}</div>
    </div>

    <!-- Tabs Widget -->
    <div v-else-if="widget.widgetType === 'tabs'" class="tabs-widget">
      <div class="flex border-b">
        <button class="px-4 py-2 font-medium" :style="{ color: settings.tab_color ?? '#4f46e5', borderBottom: '2px solid ' + (settings.tab_color ?? '#4f46e5') }">{{ settings.tab1_title ?? 'Tab 1' }}</button>
        <button class="px-4 py-2 text-gray-500">{{ settings.tab2_title ?? 'Tab 2' }}</button>
        <button class="px-4 py-2 text-gray-500">{{ settings.tab3_title ?? 'Tab 3' }}</button>
      </div>
      <div class="p-4" :style="{ color: settings.content_color ?? '#1f2937' }" v-html="settings.tab1_content ?? '<p>Tab 1 content goes here.</p>'"></div>
    </div>

    <!-- Accordion Widget -->
    <div v-else-if="widget.widgetType === 'accordion'" class="accordion-widget border rounded-lg overflow-hidden">
      <div v-for="i in 3" :key="i" class="border-b last:border-b-0">
        <div :style="{ backgroundColor: settings.title_background ?? '#f3f4f6', color: settings.title_color ?? '#1f2937' }" class="px-4 py-3 font-medium flex justify-between items-center cursor-pointer">
          <span>{{ settings[`item${i}_title`] ?? `Accordion Item ${i}` }}</span>
          <span>{{ i === 1 && (settings.first_open ?? true) ? '−' : '+' }}</span>
        </div>
        <div v-if="i === 1 && (settings.first_open ?? true)" :style="{ color: settings.content_color ?? '#4b5563' }" class="px-4 py-3" v-html="settings.item1_content ?? '<p>Content for accordion item 1.</p>'"></div>
      </div>
    </div>

    <!-- Countdown Widget -->
    <div v-else-if="widget.widgetType === 'countdown'" class="flex justify-center gap-4">
      <div v-if="settings.show_days ?? true" class="text-center">
        <div :style="{ fontSize: (settings.number_size ?? 48) + 'px', color: settings.number_color ?? '#1f2937' }" class="font-bold">00</div>
        <div v-if="settings.show_labels ?? true" :style="{ color: settings.label_color ?? '#6b7280' }" class="text-sm">Days</div>
      </div>
      <div v-if="settings.show_hours ?? true" class="text-center">
        <div :style="{ fontSize: (settings.number_size ?? 48) + 'px', color: settings.number_color ?? '#1f2937' }" class="font-bold">00</div>
        <div v-if="settings.show_labels ?? true" :style="{ color: settings.label_color ?? '#6b7280' }" class="text-sm">Hours</div>
      </div>
      <div v-if="settings.show_minutes ?? true" class="text-center">
        <div :style="{ fontSize: (settings.number_size ?? 48) + 'px', color: settings.number_color ?? '#1f2937' }" class="font-bold">00</div>
        <div v-if="settings.show_labels ?? true" :style="{ color: settings.label_color ?? '#6b7280' }" class="text-sm">Minutes</div>
      </div>
      <div v-if="settings.show_seconds ?? true" class="text-center">
        <div :style="{ fontSize: (settings.number_size ?? 48) + 'px', color: settings.number_color ?? '#1f2937' }" class="font-bold">00</div>
        <div v-if="settings.show_labels ?? true" :style="{ color: settings.label_color ?? '#6b7280' }" class="text-sm">Seconds</div>
      </div>
    </div>

    <!-- Google Maps Widget -->
    <div v-else-if="widget.widgetType === 'google-maps'" :style="{ height: (settings.height ?? 400) + 'px' }" class="bg-gray-200 rounded-lg flex items-center justify-center">
      <div class="text-center text-gray-500">
        <span class="text-4xl">🗺️</span>
        <p class="mt-2">{{ settings.address ?? 'New York, USA' }}</p>
        <p class="text-xs">Zoom: {{ settings.zoom ?? 14 }}</p>
      </div>
    </div>

    <!-- Call to Action Widget -->
    <div v-else-if="widget.widgetType === 'call-to-action'" class="relative p-8 rounded-lg" :style="getCtaStyles()">
      <div v-if="settings.ribbon_text" class="absolute top-0 right-0 px-3 py-1 text-white text-sm font-medium" :style="{ backgroundColor: settings.ribbon_color ?? '#ef4444' }">{{ settings.ribbon_text }}</div>
      <h3 :style="{ color: settings.title_color ?? '#1f2937' }" class="text-2xl font-bold">{{ settings.title ?? 'This is the heading' }}</h3>
      <p :style="{ color: settings.description_color ?? '#4b5563' }" class="mt-2">{{ settings.description ?? 'Click here to add your own text and edit me.' }}</p>
      <button :style="{ backgroundColor: settings.button_background ?? '#4f46e5', color: settings.button_color ?? '#ffffff' }" class="mt-4 px-6 py-2 rounded font-medium">{{ settings.button_text ?? 'Click Here' }}</button>
    </div>

    <!-- Flip Box Widget -->
    <div v-else-if="widget.widgetType === 'flip-box'" class="relative" :style="{ height: (settings.height ?? 300) + 'px', perspective: '1000px' }">
      <div class="w-full h-full rounded-lg p-6 flex flex-col items-center justify-center text-center" :style="{ backgroundColor: settings.front_background ?? '#ffffff', color: settings.front_color ?? '#1f2937' }">
        <div class="text-4xl mb-4">{{ settings.front_icon ?? '⚡' }}</div>
        <h4 class="font-semibold text-lg">{{ settings.front_title ?? 'Front Title' }}</h4>
        <p class="mt-2 text-sm">{{ settings.front_description ?? 'This is the front content.' }}</p>
      </div>
    </div>

    <!-- Price Table Widget -->
    <div v-else-if="widget.widgetType === 'price-table'" class="relative rounded-lg overflow-hidden border" :style="getPriceTableStyles()">
      <div v-if="settings.featured && settings.ribbon_text" class="absolute top-4 right-0 px-3 py-1 text-white text-xs font-medium bg-indigo-600 transform translate-x-2">{{ settings.ribbon_text }}</div>
      <div class="p-6 text-center" :style="{ backgroundColor: settings.header_background ?? '#4f46e5', color: settings.header_color ?? '#ffffff' }">
        <h3 class="text-xl font-bold">{{ settings.title ?? 'Pro' }}</h3>
      </div>
      <div class="p-6 text-center">
        <div class="text-4xl font-bold" :style="{ color: settings.price_color ?? '#1f2937' }">{{ settings.price ?? '$49' }}<span class="text-lg font-normal">{{ settings.period ?? '/month' }}</span></div>
        <ul class="mt-6 space-y-3 text-left" :style="{ color: settings.features_color ?? '#4b5563' }">
          <li v-for="(feature, idx) in (settings.features ?? '10 Projects\n50GB Storage\nPriority Support\nCustom Domain').split('\n')" :key="idx" class="flex items-center gap-2">
            <span class="text-green-500">✓</span> {{ feature }}
          </li>
        </ul>
        <button :style="{ backgroundColor: settings.button_background ?? '#4f46e5', color: settings.button_color ?? '#ffffff' }" class="mt-6 w-full py-3 rounded font-medium">{{ settings.button_text ?? 'Get Started' }}</button>
      </div>
    </div>

    <!-- Form Widget -->
    <div v-else-if="widget.widgetType === 'form'" class="form-widget" :style="getFormStyles()">
      <h3 v-if="settings.form_name" class="text-xl font-semibold mb-4">{{ settings.form_name }}</h3>

      <form class="space-y-4" :style="{ '--field-spacing': (settings.spacing ?? 16) + 'px' }">
        <!-- Name Field -->
        <div v-if="settings.name_field ?? true">
          <label v-if="settings.show_labels ?? true" class="block text-sm font-medium mb-1">Name</label>
          <input
            type="text"
            placeholder="Your Name"
            class="w-full px-4 py-2 rounded border"
            :style="{
              backgroundColor: settings.field_background ?? '#ffffff',
              borderColor: settings.field_border ?? '#d1d5db',
              color: settings.field_text ?? '#1f2937',
              marginBottom: settings.spacing + 'px'
            }"
          />
        </div>

        <!-- Email Field -->
        <div v-if="settings.email_field ?? true">
          <label v-if="settings.show_labels ?? true" class="block text-sm font-medium mb-1">Email</label>
          <input
            type="email"
            placeholder="your@email.com"
            class="w-full px-4 py-2 rounded border"
            :style="{
              backgroundColor: settings.field_background ?? '#ffffff',
              borderColor: settings.field_border ?? '#d1d5db',
              color: settings.field_text ?? '#1f2937',
              marginBottom: settings.spacing + 'px'
            }"
          />
        </div>

        <!-- Message Field -->
        <div v-if="settings.message_field ?? true">
          <label v-if="settings.show_labels ?? true" class="block text-sm font-medium mb-1">Message</label>
          <textarea
            placeholder="Your Message"
            rows="4"
            class="w-full px-4 py-2 rounded border resize-none"
            :style="{
              backgroundColor: settings.field_background ?? '#ffffff',
              borderColor: settings.field_border ?? '#d1d5db',
              color: settings.field_text ?? '#1f2937'
            }"
          ></textarea>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          class="px-6 py-3 rounded font-medium"
          :style="{
            backgroundColor: settings.button_background ?? '#4f46e5',
            color: settings.button_text ?? '#ffffff'
          }"
        >
          {{ settings.button_text ?? 'Send Message' }}
        </button>
      </form>
    </div>

    <!-- Slider Widget -->
    <div v-else-if="widget.widgetType === 'slider'" class="slider-widget relative overflow-hidden rounded-lg" :style="getSliderStyles()">
      <!-- Slide 1 -->
      <div class="slide relative w-full h-full flex items-center justify-center text-center text-white">
        <div
          v-if="settings.slide1_image"
          class="absolute inset-0 bg-cover bg-center"
          :style="{ backgroundImage: `url(${settings.slide1_image})` }"
        ></div>
        <div class="absolute inset-0" :style="{ backgroundColor: settings.overlay_color ?? 'rgba(0,0,0,0.3)' }"></div>

        <div class="relative z-10 px-8 max-w-3xl">
          <h2 class="text-4xl font-bold mb-4" :style="{ color: settings.title_color ?? '#ffffff' }">
            {{ settings.slide1_title ?? 'First Slide' }}
          </h2>
          <p class="text-lg mb-6" :style="{ color: settings.description_color ?? '#f3f4f6' }">
            {{ settings.slide1_description ?? 'This is the first slide content.' }}
          </p>
          <a
            v-if="settings.slide1_button"
            :href="settings.slide1_link || '#'"
            class="inline-block px-6 py-3 rounded font-medium"
            :style="{
              backgroundColor: settings.button_background ?? '#4f46e5',
              color: settings.button_color ?? '#ffffff'
            }"
          >
            {{ settings.slide1_button }}
          </a>
        </div>
      </div>

      <!-- Navigation Arrows -->
      <div v-if="settings.show_arrows ?? true" class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex justify-between px-4 pointer-events-none">
        <button class="w-10 h-10 rounded-full flex items-center justify-center pointer-events-auto" :style="{ backgroundColor: 'rgba(0,0,0,0.3)', color: settings.arrows_color ?? '#ffffff' }">‹</button>
        <button class="w-10 h-10 rounded-full flex items-center justify-center pointer-events-auto" :style="{ backgroundColor: 'rgba(0,0,0,0.3)', color: settings.arrows_color ?? '#ffffff' }">›</button>
      </div>

      <!-- Navigation Dots -->
      <div v-if="settings.show_dots ?? true" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
        <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: settings.dots_color ?? '#ffffff', opacity: 1 }"></span>
        <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: settings.dots_color ?? '#ffffff', opacity: 0.5 }"></span>
        <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: settings.dots_color ?? '#ffffff', opacity: 0.5 }"></span>
      </div>
    </div>

    <!-- Toggle Widget -->
    <div v-else-if="widget.widgetType === 'toggle'" class="toggle-widget border rounded-lg overflow-hidden">
      <div v-for="i in 3" :key="i" class="border-b last:border-b-0">
        <div :style="{ backgroundColor: settings.title_background ?? '#f3f4f6', color: settings.title_color ?? '#1f2937' }" class="px-4 py-3 font-medium flex justify-between items-center cursor-pointer">
          <span>{{ settings[`item${i}_title`] ?? `Toggle Item ${i}` }}</span>
          <span>+</span>
        </div>
      </div>
    </div>

    <!-- Icon List Widget -->
    <div v-else-if="widget.widgetType === 'icon-list'" class="icon-list-widget">
      <div v-for="i in 3" :key="i" class="flex items-center gap-3" :style="{ marginBottom: (settings.spacing ?? 12) + 'px' }">
        <a v-if="settings[`item${i}_link`]" :href="settings[`item${i}_link`]" class="flex items-center gap-3 no-underline">
          <span :style="{ fontSize: (settings.icon_size ?? 20) + 'px', color: settings.icon_color ?? '#4f46e5' }">{{ settings[`item${i}_icon`] ?? '✓' }}</span>
          <span :style="{ color: settings.text_color ?? '#1f2937' }">{{ settings[`item${i}_text`] ?? `List Item ${i}` }}</span>
        </a>
        <div v-else class="flex items-center gap-3">
          <span :style="{ fontSize: (settings.icon_size ?? 20) + 'px', color: settings.icon_color ?? '#4f46e5' }">{{ settings[`item${i}_icon`] ?? '✓' }}</span>
          <span :style="{ color: settings.text_color ?? '#1f2937' }">{{ settings[`item${i}_text`] ?? `List Item ${i}` }}</span>
        </div>
      </div>
    </div>

    <!-- Text Path Widget -->
    <div v-else-if="widget.widgetType === 'text-path'" class="text-path-widget text-center">
      <svg viewBox="0 0 500 100" class="w-full" style="max-width: 500px; margin: 0 auto;">
        <defs>
          <path v-if="settings.path_type === 'wave'" id="textPath" d="M 0 50 Q 125 20, 250 50 T 500 50" />
          <path v-else-if="settings.path_type === 'circle'" id="textPath" d="M 50 50 m -40 0 a 40 40 0 1 1 80 0 a 40 40 0 1 1 -80 0" />
          <path v-else id="textPath" d="M 50 80 Q 250 20, 450 80" />
        </defs>
        <text :style="{ fontSize: (settings.font_size ?? 24) + 'px', fill: settings.text_color ?? '#1f2937', fontWeight: settings.font_weight ?? '400' }">
          <textPath href="#textPath" startOffset="50%" text-anchor="middle">
            {{ settings.text ?? 'Curved Text' }}
          </textPath>
        </text>
      </svg>
    </div>

    <!-- Image Carousel Widget -->
    <div v-else-if="widget.widgetType === 'image-carousel'" class="image-carousel-widget relative">
      <div class="flex gap-2 overflow-hidden" :style="{ gap: (settings.image_spacing ?? 10) + 'px' }">
        <div v-for="i in (settings.slides_to_show ?? 3)" :key="i" class="flex-shrink-0" :style="{ width: `calc(${100 / (settings.slides_to_show ?? 3)}% - ${(settings.image_spacing ?? 10) * ((settings.slides_to_show ?? 3) - 1) / (settings.slides_to_show ?? 3)}px)` }">
          <img v-if="settings[`image${i}`]" :src="settings[`image${i}`]" class="w-full h-48 object-cover" :style="{ borderRadius: (settings.border_radius ?? 8) + 'px' }" />
          <div v-else class="w-full h-48 bg-gray-200 rounded flex items-center justify-center text-gray-400" :style="{ borderRadius: (settings.border_radius ?? 8) + 'px' }">
            <span class="text-4xl">🖼️</span>
          </div>
        </div>
      </div>
      <div v-if="settings.show_arrows ?? true" class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex justify-between px-2 pointer-events-none">
        <button class="w-8 h-8 rounded-full flex items-center justify-center pointer-events-auto" :style="{ backgroundColor: 'rgba(0,0,0,0.5)', color: settings.arrow_color ?? '#ffffff' }">‹</button>
        <button class="w-8 h-8 rounded-full flex items-center justify-center pointer-events-auto" :style="{ backgroundColor: 'rgba(0,0,0,0.5)', color: settings.arrow_color ?? '#ffffff' }">›</button>
      </div>
      <div v-if="settings.show_dots ?? true" class="flex justify-center gap-2 mt-4">
        <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: settings.dot_color ?? '#4f46e5' }"></span>
        <span class="w-2 h-2 rounded-full bg-gray-300"></span>
        <span class="w-2 h-2 rounded-full bg-gray-300"></span>
      </div>
    </div>

    <!-- Basic Gallery Widget -->
    <div v-else-if="widget.widgetType === 'basic-gallery'" class="basic-gallery-widget">
      <div class="grid" :style="{ gridTemplateColumns: `repeat(${settings.columns ?? 3}, 1fr)`, gap: (settings.gap ?? 10) + 'px' }">
        <div v-for="i in 6" :key="i" class="gallery-item relative overflow-hidden cursor-pointer" :class="{ 'hover-zoom': settings.hover_effect === 'zoom', 'hover-grayscale': settings.hover_effect === 'grayscale' }">
          <img v-if="settings[`image${i}`]" :src="settings[`image${i}`]" class="w-full h-40 object-cover transition-transform duration-300" :style="{ borderRadius: (settings.border_radius ?? 8) + 'px' }" />
          <div v-else class="w-full h-40 bg-gray-200 flex items-center justify-center text-gray-400" :style="{ borderRadius: (settings.border_radius ?? 8) + 'px' }">
            <span class="text-3xl">🖼️</span>
          </div>
        </div>
      </div>
    </div>

    <!-- SoundCloud Widget -->
    <div v-else-if="widget.widgetType === 'soundcloud'" class="soundcloud-widget">
      <iframe v-if="settings.url" width="100%" :height="settings.height ?? 166" scrolling="no" frameborder="no" allow="autoplay" :src="getSoundCloudEmbedUrl()"></iframe>
      <div v-else class="bg-gray-200 rounded flex items-center justify-center text-gray-500" :style="{ height: (settings.height ?? 166) + 'px' }">
        <div class="text-center">
          <span class="text-4xl">🎵</span>
          <p class="mt-2 text-sm">Add SoundCloud URL</p>
        </div>
      </div>
    </div>

    <!-- Container Widget -->
    <component v-else-if="widget.widgetType === 'container'" :is="settings.html_tag ?? 'div'" :style="getContainerStyles()" class="container-widget">
      <div :class="settings.content_width === 'boxed' ? 'max-w-7xl mx-auto px-4' : 'w-full'">
        <div class="text-center text-gray-400 py-8 border-2 border-dashed border-gray-300 rounded">
          <span class="text-2xl">▭</span>
          <p class="mt-2">Drop widgets here</p>
        </div>
      </div>
    </component>

    <!-- Inner Section Widget -->
    <div v-else-if="widget.widgetType === 'inner-section'" :style="getInnerSectionStyles()" class="inner-section-widget">
      <div class="grid" :style="{ gridTemplateColumns: `repeat(${settings.columns ?? 2}, 1fr)`, gap: (settings.column_gap ?? 20) + 'px' }">
        <div v-for="i in parseInt(settings.columns ?? 2)" :key="i" class="border-2 border-dashed border-gray-300 rounded p-4 text-center text-gray-400">
          <p>Column {{ i }}</p>
        </div>
      </div>
    </div>

    <!-- Menu Anchor Widget -->
    <div v-else-if="widget.widgetType === 'menu-anchor'" :id="settings.anchor_id ?? 'anchor'" class="menu-anchor-widget h-0"></div>

    <!-- Sidebar Widget -->
    <div v-else-if="widget.widgetType === 'sidebar'" class="sidebar-widget border-2 border-dashed border-gray-300 rounded p-4 text-center text-gray-400">
      <span class="text-2xl">▐</span>
      <p class="mt-2">Sidebar: {{ settings.sidebar_id ?? 'primary' }}</p>
    </div>

    <!-- HTML Widget -->
    <div v-else-if="widget.widgetType === 'html'" class="html-widget" v-html="settings.html_code ?? '<div class=\'custom-html\'><p>Add your custom HTML here</p></div>'"></div>

    <!-- Shortcode Widget -->
    <div v-else-if="widget.widgetType === 'shortcode'" class="shortcode-widget border-2 border-dashed border-gray-300 rounded p-4 text-center text-gray-500">
      <span class="text-xl">[ ]</span>
      <p class="mt-2 font-mono text-sm">{{ settings.shortcode ?? '[shortcode]' }}</p>
    </div>

    <!-- Default/Unknown Widget -->
    <div v-else class="p-4 bg-gray-100 rounded text-center text-gray-500">
      Unknown widget: {{ widget.widgetType }}
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUpdated, onBeforeUnmount, ref, nextTick } from 'vue';

const props = defineProps({
  widget: {
    type: Object,
    required: true
  }
});

const settings = computed(() => props.widget.settings || {});

// Ref for the widget element (for animation observation)
const widgetRef = ref(null);

// Track if entrance animation has been triggered
const animationTriggered = ref(false);

// Button hover state
const buttonHover = ref(false);

// Responsive visibility classes based on settings
const responsiveVisibilityClasses = computed(() => {
  const visibility = settings.value.responsive_visibility;
  if (!visibility) return '';

  const classes = [];
  if (visibility.hide_desktop) classes.push('hidden-desktop');
  if (visibility.hide_tablet) classes.push('hidden-tablet');
  if (visibility.hide_mobile) classes.push('hidden-mobile');

  return classes.join(' ');
});

// Entrance animation classes based on motion effects settings
const animationClasses = computed(() => {
  const motion = settings.value.motion_effects;
  if (!motion || !motion.entrance_animation || motion.entrance_animation === 'none') return '';

  // Only apply animation if triggered
  if (!animationTriggered.value) return 'animation-hidden';

  const classes = [`animate-${motion.entrance_animation}`];

  // Animation duration
  if (motion.animation_duration) {
    classes.push(`animation-duration-${motion.animation_duration}`);
  }

  return classes.join(' ');
});

// Animation delay style (can't be done via class easily)
const animationDelayStyle = computed(() => {
  const motion = settings.value.motion_effects;
  if (!motion || !motion.animation_delay) return {};
  return { animationDelay: `${motion.animation_delay}ms` };
});

// Computed property to check if button has hover styles
const hasButtonHoverStyles = computed(() => {
  return settings.value.hover_background_color || settings.value.hover_text_color || settings.value.hover_border_color;
});

// Computed property for heading tag (H1-H6) - ensures reactivity
const headingTag = computed(() => {
  // Check both 'size' and 'tag' properties for backwards compatibility
  return settings.value.size || settings.value.tag || 'h2';
});

// Computed property for heading styles - ensures reactivity
const headingStyles = computed(() => {
  const typography = settings.value.typography || {};
  const styles = {
    color: settings.value.text_color ?? '#1f2937',
    textAlign: settings.value.alignment ?? 'left',
    margin: formatDimensions(settings.value.margin),
    padding: formatDimensions(settings.value.padding)
  };

  // Apply typography settings with proper fallbacks and validation
  // Font Family
  if (typography.family && typography.family !== 'Default' && typography.family !== '') {
    // Wrap font names with spaces in quotes
    const fontFamily = typography.family.includes(' ')
      ? `'${typography.family}', sans-serif`
      : `${typography.family}, sans-serif`;
    styles.fontFamily = fontFamily;
  }

  // Font Size - with default sizes for heading levels
  if (typography.size) {
    const sizeValue = typeof typography.size === 'string' ? parseFloat(typography.size) : typography.size;
    const unit = typography.sizeUnit || 'px';
    if (!isNaN(sizeValue) && sizeValue > 0) {
      styles.fontSize = sizeValue + unit;
    }
  } else {
    // Apply default font sizes based on heading tag if no custom size is set
    const defaultSizes = {
      h1: '2.5rem',
      h2: '2rem',
      h3: '1.75rem',
      h4: '1.5rem',
      h5: '1.25rem',
      h6: '1rem'
    };
    const tag = headingTag.value;
    if (defaultSizes[tag]) {
      styles.fontSize = defaultSizes[tag];
    }
  }

  // Font Weight
  if (typography.weight) {
    // Handle both string and number weights
    const weight = typeof typography.weight === 'string' ? typography.weight : String(typography.weight);
    if (weight && weight !== '400' && weight !== 'Normal') {
      styles.fontWeight = weight;
    }
  }

  // Text Transform
  if (typography.transform && typography.transform !== 'None' && typography.transform !== 'none') {
    styles.textTransform = typography.transform.toLowerCase();
  }

  // Font Style
  if (typography.style && typography.style !== 'Normal' && typography.style !== 'normal') {
    styles.fontStyle = typography.style.toLowerCase();
  }

  // Line Height
  if (typography.lineHeight !== undefined && typography.lineHeight !== null && typography.lineHeight !== '') {
    const lineHeightValue = typeof typography.lineHeight === 'string' ? parseFloat(typography.lineHeight) : typography.lineHeight;
    if (!isNaN(lineHeightValue) && lineHeightValue > 0) {
      styles.lineHeight = lineHeightValue;
    }
  }

  // Letter Spacing
  if (typography.letterSpacing !== undefined && typography.letterSpacing !== null && typography.letterSpacing !== '') {
    const spacingValue = typeof typography.letterSpacing === 'string' ? parseFloat(typography.letterSpacing) : typography.letterSpacing;
    if (!isNaN(spacingValue)) {
      styles.letterSpacing = spacingValue + 'px';
    }
  }

  return styles;
});

// Inject custom CSS into document head
function injectCustomCSS() {
  // Remove old style tag if exists
  const oldStyle = document.getElementById(`widget-custom-css-${props.widget.id}`);
  if (oldStyle) {
    oldStyle.remove();
  }

  // Add new style tag if custom CSS exists
  if (props.widget.widgetType === 'heading' && settings.value.custom_css) {
    const styleTag = document.createElement('style');
    styleTag.id = `widget-custom-css-${props.widget.id}`;
    styleTag.textContent = settings.value.custom_css;
    document.head.appendChild(styleTag);
  }
}

// Intersection Observer for entrance animations
let animationObserver = null;

function setupAnimationObserver() {
  const motion = settings.value.motion_effects;
  if (!motion || !motion.entrance_animation || motion.entrance_animation === 'none') {
    // No animation configured, trigger immediately
    animationTriggered.value = true;
    return;
  }

  // Create observer to trigger animation when widget enters viewport
  animationObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting && !animationTriggered.value) {
        animationTriggered.value = true;
        // Disconnect after triggering (one-time animation)
        animationObserver?.disconnect();
      }
    });
  }, { threshold: 0.1 });

  if (widgetRef.value) {
    animationObserver.observe(widgetRef.value);
  }
}

// Inject on mount and update
onMounted(() => {
  injectCustomCSS();
  nextTick(() => {
    setupAnimationObserver();
  });
});

onUpdated(() => {
  injectCustomCSS();
});

// Cleanup on unmount
onBeforeUnmount(() => {
  const styleTag = document.getElementById(`widget-custom-css-${props.widget.id}`);
  if (styleTag) {
    styleTag.remove();
  }
  // Cleanup animation observer
  animationObserver?.disconnect();
});

function getTextEditorStyles() {
  return {
    color: settings.value.text_color ?? '#4b5563',
    textAlign: settings.value.alignment ?? 'left',
    margin: formatDimensions(settings.value.margin),
    padding: formatDimensions(settings.value.padding)
  };
}

function getImageContainerStyles() {
  const alignment = settings.value.alignment ?? 'left';
  return {
    textAlign: alignment,
    margin: formatDimensions(settings.value.margin),
    padding: formatDimensions(settings.value.padding)
  };
}

function getImageElementStyles() {
  const styles = {
    width: (settings.value.width ?? 100) + '%',
    opacity: settings.value.opacity ?? 1,
  };

  // Max width
  if (settings.value.max_width && settings.value.max_width > 0) {
    styles.maxWidth = settings.value.max_width + 'px';
  }

  // Build CSS filter string
  const filters = [];
  if (settings.value.filter_blur && settings.value.filter_blur > 0) {
    filters.push(`blur(${settings.value.filter_blur}px)`);
  }
  if (settings.value.filter_brightness && settings.value.filter_brightness !== 100) {
    filters.push(`brightness(${settings.value.filter_brightness}%)`);
  }
  if (settings.value.filter_contrast && settings.value.filter_contrast !== 100) {
    filters.push(`contrast(${settings.value.filter_contrast}%)`);
  }
  if (settings.value.filter_saturation && settings.value.filter_saturation !== 100) {
    filters.push(`saturate(${settings.value.filter_saturation}%)`);
  }
  if (settings.value.filter_hue && settings.value.filter_hue > 0) {
    filters.push(`hue-rotate(${settings.value.filter_hue}deg)`);
  }

  if (filters.length > 0) {
    styles.filter = filters.join(' ');
  }

  return styles;
}

function getImageHoverClass() {
  const animation = settings.value.hover_animation;
  if (!animation || animation === 'none') return '';
  return `hover-${animation}`;
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
  const typography = settings.value.typography || {};
  const styles = {
    backgroundColor: settings.value.background_color ?? '#4f46e5',
    color: settings.value.text_color ?? '#ffffff',
    borderRadius: (settings.value.border_radius ?? 6) + 'px',
    borderWidth: (settings.value.border_width ?? 0) + 'px',
    borderStyle: settings.value.border_width > 0 ? 'solid' : 'none',
    borderColor: settings.value.border_color ?? settings.value.background_color ?? '#4f46e5',
    paddingLeft: (settings.value.padding_horizontal ?? 24) + 'px',
    paddingRight: (settings.value.padding_horizontal ?? 24) + 'px',
    paddingTop: (settings.value.padding_vertical ?? 12) + 'px',
    paddingBottom: (settings.value.padding_vertical ?? 12) + 'px',
  };

  // Apply typography settings
  if (typography.family && typography.family !== 'Default') {
    styles.fontFamily = typography.family.includes(' ')
      ? `'${typography.family}', sans-serif`
      : `${typography.family}, sans-serif`;
  }
  if (typography.size) {
    const sizeValue = typeof typography.size === 'string' ? parseFloat(typography.size) : typography.size;
    const unit = typography.sizeUnit || 'px';
    if (!isNaN(sizeValue) && sizeValue > 0) {
      styles.fontSize = sizeValue + unit;
    }
  }
  if (typography.weight && typography.weight !== '400') {
    styles.fontWeight = typography.weight;
  }
  if (typography.transform && typography.transform !== 'none') {
    styles.textTransform = typography.transform.toLowerCase();
  }
  if (typography.letterSpacing) {
    styles.letterSpacing = typography.letterSpacing + 'px';
  }

  return styles;
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

  // Build query parameters based on settings
  const params = new URLSearchParams();

  // Extract YouTube video ID
  const youtubeMatch = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([^&?]+)/);
  if (youtubeMatch) {
    // YouTube parameters
    if (settings.value.autoplay) params.set('autoplay', '1');
    if (settings.value.mute) params.set('mute', '1');
    if (settings.value.loop) {
      params.set('loop', '1');
      params.set('playlist', youtubeMatch[1]); // Required for loop to work
    }
    if (settings.value.controls === false) params.set('controls', '0');
    if (settings.value.modest_branding) params.set('modestbranding', '1');
    if (settings.value.start_time) params.set('start', settings.value.start_time.toString());
    if (settings.value.end_time) params.set('end', settings.value.end_time.toString());

    const queryString = params.toString();
    return `https://www.youtube.com/embed/${youtubeMatch[1]}${queryString ? '?' + queryString : ''}`;
  }

  // Extract Vimeo video ID
  const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
  if (vimeoMatch) {
    // Vimeo parameters
    if (settings.value.autoplay) params.set('autoplay', '1');
    if (settings.value.mute) params.set('muted', '1');
    if (settings.value.loop) params.set('loop', '1');
    if (settings.value.controls === false) params.set('controls', '0');

    const queryString = params.toString();
    return `https://player.vimeo.com/video/${vimeoMatch[1]}${queryString ? '?' + queryString : ''}`;
  }

  return null;
}

function getDividerContainerStyles() {
  const gap = settings.value.gap ?? 20;
  return {
    textAlign: settings.value.alignment ?? 'center',
    marginTop: gap + 'px',
    marginBottom: gap + 'px',
  };
}

function getDividerWrapperStyles() {
  return {
    width: (settings.value.width ?? 100) + '%',
    margin: settings.value.alignment === 'center' ? '0 auto' : (settings.value.alignment === 'right' ? '0 0 0 auto' : '0'),
  };
}

function getDividerLineStyles() {
  return {
    borderStyle: settings.value.style ?? 'solid',
    borderColor: settings.value.color ?? '#e5e7eb',
    borderWidth: '0',
    borderTopWidth: (settings.value.weight ?? 1) + 'px',
  };
}

function getDividerElementStyles() {
  return {
    color: settings.value.element_color ?? '#6b7280',
    fontSize: (settings.value.element_size ?? 16) + 'px',
    paddingLeft: (settings.value.element_spacing ?? 16) + 'px',
    paddingRight: (settings.value.element_spacing ?? 16) + 'px',
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
    textAlign: settings.value.alignment ?? 'center'
  };
}

function getIconStyles() {
  return {
    fontSize: (settings.value.size ?? 50) + 'px',
    color: settings.value.primary_color ?? '#4f46e5'
  };
}

function getIconBoxStyles() {
  const position = settings.value.icon_position ?? 'top';
  const alignment = settings.value.alignment ?? 'center';
  const verticalAlign = settings.value.content_vertical_alignment ?? 'top';

  const styles = {
    textAlign: alignment,
  };

  // Apply flex layout for left/right positions
  if (position === 'left' || position === 'right') {
    styles.display = 'flex';
    styles.flexDirection = position === 'right' ? 'row-reverse' : 'row';
    styles.textAlign = 'left';

    // Vertical alignment
    if (verticalAlign === 'center') {
      styles.alignItems = 'center';
    } else if (verticalAlign === 'bottom') {
      styles.alignItems = 'flex-end';
    } else {
      styles.alignItems = 'flex-start';
    }
  }

  return styles;
}

function getIconBoxLayoutClass() {
  const position = settings.value.icon_position ?? 'top';
  return `icon-position-${position}`;
}

function getIconBoxIconStyles() {
  const position = settings.value.icon_position ?? 'top';
  const spacing = settings.value.icon_spacing ?? 15;

  const styles = {
    fontSize: (settings.value.icon_size ?? 50) + 'px',
    color: settings.value.icon_color ?? '#4f46e5',
    transition: 'color 0.3s ease',
  };

  // Add spacing based on position
  if (position === 'top') {
    styles.marginBottom = spacing + 'px';
  } else if (position === 'left') {
    styles.marginRight = spacing + 'px';
  } else if (position === 'right') {
    styles.marginLeft = spacing + 'px';
  }

  return styles;
}

function getIconBoxContentStyles() {
  const position = settings.value.icon_position ?? 'top';

  if (position === 'left' || position === 'right') {
    return { flex: '1' };
  }
  return {};
}

function getCounterStyles() {
  return {
    textAlign: settings.value.alignment ?? 'center',
    textAlign: settings.value.alignment ?? 'center'
  };
}

function getTestimonialStyles() {
  return {
    textAlign: settings.value.alignment ?? 'center',
    textAlign: settings.value.alignment ?? 'center'
  };
}

function getSocialIconsStyles() {
  return {
    textAlign: settings.value.alignment ?? 'center',
    textAlign: settings.value.alignment ?? 'center'
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

function getImageBoxStyles() {
  const bg = settings.value.background;
  const border = settings.value.border;
  const shadow = settings.value.box_shadow;
  const position = settings.value.image_position ?? 'top';
  const alignment = settings.value.alignment ?? 'center';
  const verticalAlign = settings.value.content_vertical_alignment ?? 'top';

  const styles = {
    textAlign: alignment,
    margin: formatDimensions(settings.value.margin),
    padding: formatDimensions(settings.value.padding)
  };

  // Apply flex layout for left/right positions
  if (position === 'left' || position === 'right') {
    styles.display = 'flex';
    styles.flexDirection = position === 'right' ? 'row-reverse' : 'row';
    styles.textAlign = 'left';

    // Vertical alignment
    if (verticalAlign === 'center') {
      styles.alignItems = 'center';
    } else if (verticalAlign === 'bottom') {
      styles.alignItems = 'flex-end';
    } else {
      styles.alignItems = 'flex-start';
    }
  }

  if (bg?.color) styles.backgroundColor = bg.color;
  if (border?.style && border.style !== 'none') {
    styles.borderStyle = border.style;
    styles.borderColor = border.color ?? '#e5e7eb';
    styles.borderWidth = '1px';
  }
  if (border?.radius) {
    styles.borderRadius = `${border.radius.topLeft ?? 0}px ${border.radius.topRight ?? 0}px ${border.radius.bottomRight ?? 0}px ${border.radius.bottomLeft ?? 0}px`;
  }
  if (shadow && (shadow.horizontal || shadow.vertical || shadow.blur)) {
    const inset = shadow.position === 'inset' ? 'inset ' : '';
    styles.boxShadow = `${inset}${shadow.horizontal ?? 0}px ${shadow.vertical ?? 0}px ${shadow.blur ?? 0}px ${shadow.spread ?? 0}px ${shadow.color ?? 'rgba(0,0,0,0.1)'}`;
  }

  return styles;
}

function getImageBoxLayoutClass() {
  const position = settings.value.image_position ?? 'top';
  return `image-position-${position}`;
}

function getImageBoxImageContainerStyles() {
  const position = settings.value.image_position ?? 'top';
  const spacing = settings.value.image_spacing ?? 15;

  const styles = {};

  // Add spacing based on position
  if (position === 'top') {
    styles.marginBottom = spacing + 'px';
  } else if (position === 'left') {
    styles.marginRight = spacing + 'px';
    styles.flexShrink = 0;
  } else if (position === 'right') {
    styles.marginLeft = spacing + 'px';
    styles.flexShrink = 0;
  }

  return styles;
}

function getImageBoxImageStyles() {
  const position = settings.value.image_position ?? 'top';

  const styles = {
    height: (settings.value.image_height ?? 160) + 'px',
    transition: 'all 0.3s ease',
  };

  if (position === 'top') {
    styles.width = (settings.value.image_width ?? 100) + '%';
  } else {
    // For left/right positions, use fixed width
    styles.width = Math.min(settings.value.image_width ?? 100, 200) + 'px';
  }

  return styles;
}

function getImageBoxImageHoverClass() {
  const animation = settings.value.hover_animation;
  if (!animation || animation === 'none') return '';
  return `hover-${animation}`;
}

function getImageBoxContentStyles() {
  const position = settings.value.image_position ?? 'top';

  if (position === 'left' || position === 'right') {
    return { flex: '1' };
  }
  return {};
}

function getCtaStyles() {
  const bg = settings.value.background;
  const shadow = settings.value.box_shadow;

  const styles = {
    margin: formatDimensions(settings.value.margin),
    padding: formatDimensions(settings.value.padding)
  };

  if (bg?.type === 'gradient') {
    styles.background = `linear-gradient(${bg.gradientAngle ?? 180}deg, ${bg.gradientColor1 ?? '#6366f1'}, ${bg.gradientColor2 ?? '#8b5cf6'})`;
  } else if (bg?.color) {
    styles.backgroundColor = bg.color;
  }

  if (shadow && (shadow.horizontal || shadow.vertical || shadow.blur)) {
    const inset = shadow.position === 'inset' ? 'inset ' : '';
    styles.boxShadow = `${inset}${shadow.horizontal ?? 0}px ${shadow.vertical ?? 0}px ${shadow.blur ?? 0}px ${shadow.spread ?? 0}px ${shadow.color ?? 'rgba(0,0,0,0.1)'}`;
  }

  return styles;
}

function getPriceTableStyles() {
  const border = settings.value.border;
  const shadow = settings.value.box_shadow;

  const styles = {
    margin: formatDimensions(settings.value.margin),
    padding: formatDimensions(settings.value.padding)
  };

  if (border?.style && border.style !== 'none') {
    styles.borderStyle = border.style;
    styles.borderColor = border.color ?? '#e5e7eb';
    styles.borderWidth = '1px';
  }
  if (border?.radius) {
    styles.borderRadius = `${border.radius.topLeft ?? 0}px ${border.radius.topRight ?? 0}px ${border.radius.bottomRight ?? 0}px ${border.radius.bottomLeft ?? 0}px`;
  }
  if (shadow && (shadow.horizontal || shadow.vertical || shadow.blur)) {
    const inset = shadow.position === 'inset' ? 'inset ' : '';
    styles.boxShadow = `${inset}${shadow.horizontal ?? 0}px ${shadow.vertical ?? 0}px ${shadow.blur ?? 0}px ${shadow.spread ?? 0}px ${shadow.color ?? 'rgba(0,0,0,0.1)'}`;
  }

  return styles;
}

function getFormStyles() {
  return {
    margin: formatDimensions(settings.value.margin),
    padding: formatDimensions(settings.value.padding)
  };
}

function getSliderStyles() {
  return {
    height: (settings.value.height ?? 500) + 'px',
    margin: formatDimensions(settings.value.margin)
  };
}

function getSoundCloudEmbedUrl() {
  const url = settings.value.url;
  if (!url) return null;

  const visual = settings.value.visual ? 'true' : 'false';
  const autoPlay = settings.value.auto_play ? 'true' : 'false';
  const buying = settings.value.buying ? 'true' : 'false';
  const sharing = settings.value.sharing ? 'true' : 'false';
  const download = settings.value.download ? 'true' : 'false';

  return `https://w.soundcloud.com/player/?url=${encodeURIComponent(url)}&visual=${visual}&auto_play=${autoPlay}&buying=${buying}&sharing=${sharing}&download=${download}`;
}

function getCommonWrapperStyles() {
  const bg = settings.value.background;
  const border = settings.value.border;
  const shadow = settings.value.box_shadow;
  const position = settings.value.position;

  const styles = {
    margin: formatDimensions(settings.value.margin),
    padding: formatDimensions(settings.value.padding),
  };

  // Z-Index
  if (settings.value.z_index) {
    styles.zIndex = settings.value.z_index;
    styles.position = 'relative';
  }

  // Position Control
  if (position?.type && position.type !== 'default') {
    styles.position = position.type;
    if (position.top) styles.top = position.top + 'px';
    if (position.right) styles.right = position.right + 'px';
    if (position.bottom) styles.bottom = position.bottom + 'px';
    if (position.left) styles.left = position.left + 'px';
  }

  // Background
  if (bg?.type === 'gradient') {
    styles.background = `linear-gradient(${bg.gradientAngle ?? 180}deg, ${bg.gradientColor1 ?? '#6366f1'}, ${bg.gradientColor2 ?? '#8b5cf6'})`;
  } else if (bg?.color) {
    styles.backgroundColor = bg.color;
  }

  if (bg?.image) {
    styles.backgroundImage = `url(${bg.image})`;
    styles.backgroundPosition = bg.position ?? 'center center';
    styles.backgroundSize = bg.size ?? 'cover';
    styles.backgroundRepeat = bg.repeat ?? 'no-repeat';
  }

  // Border
  if (border?.style && border.style !== 'none') {
    styles.borderStyle = border.style;
    styles.borderColor = border.color ?? '#e5e7eb';
    styles.borderWidth = '1px';
  }
  if (border?.radius) {
    styles.borderRadius = `${border.radius.topLeft ?? 0}px ${border.radius.topRight ?? 0}px ${border.radius.bottomRight ?? 0}px ${border.radius.bottomLeft ?? 0}px`;
  }

  // Box Shadow
  if (shadow && (shadow.horizontal || shadow.vertical || shadow.blur)) {
    const inset = shadow.position === 'inset' ? 'inset ' : '';
    styles.boxShadow = `${inset}${shadow.horizontal ?? 0}px ${shadow.vertical ?? 0}px ${shadow.blur ?? 0}px ${shadow.spread ?? 0}px ${shadow.color ?? 'rgba(0,0,0,0.1)'}`;
  }

  return styles;
}

function getContainerStyles() {
  const size = settings.value.size;

  const styles = {
    minHeight: (settings.value.min_height ?? 0) + 'px',
    // Flexbox layout
    display: 'flex',
    flexDirection: settings.value.flex_direction ?? 'row',
    justifyContent: settings.value.justify_content ?? 'flex-start',
    alignItems: settings.value.align_items ?? 'flex-start',
    flexWrap: settings.value.flex_wrap ?? 'nowrap',
  };

  // Gaps
  if (settings.value.gaps) {
    const gaps = settings.value.gaps;
    styles.gap = `${gaps.row ?? 20}px ${gaps.column ?? 20}px`;
  }

  // Width
  if (settings.value.width) {
    styles.width = settings.value.width + 'px';
  }

  // Size control
  if (size?.type === 'full') {
    styles.width = '100%';
  } else if (size?.type === 'custom') {
    styles.width = (size.width ?? 100) + (size.widthUnit ?? '%');
    if (size.maxWidth) {
      styles.maxWidth = (size.maxWidth ?? 1140) + (size.maxWidthUnit ?? 'px');
    }
  }

  // Align Self
  if (settings.value.align_self && settings.value.align_self !== 'auto') {
    styles.alignSelf = settings.value.align_self;
  }

  // Order
  if (settings.value.order) {
    styles.order = settings.value.order;
  }

  return styles;
}

function getInnerSectionStyles() {
  return {};
}
</script>

<style scoped>
/* Responsive Visibility Classes */
@media (min-width: 1025px) {
  .hidden-desktop {
    display: none !important;
  }
}

@media (min-width: 768px) and (max-width: 1024px) {
  .hidden-tablet {
    display: none !important;
  }
}

@media (max-width: 767px) {
  .hidden-mobile {
    display: none !important;
  }
}

/* Animation Base States */
.animation-hidden {
  opacity: 0;
}

/* Animation Duration Classes */
.animation-duration-slow {
  animation-duration: 2s;
}

.animation-duration-normal {
  animation-duration: 1s;
}

.animation-duration-fast {
  animation-duration: 0.5s;
}

/* Entrance Animations */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes fadeInDown {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInLeft {
  from { opacity: 0; transform: translateX(-20px); }
  to { opacity: 1; transform: translateX(0); }
}

@keyframes fadeInRight {
  from { opacity: 0; transform: translateX(20px); }
  to { opacity: 1; transform: translateX(0); }
}

@keyframes zoomIn {
  from { opacity: 0; transform: scale(0.9); }
  to { opacity: 1; transform: scale(1); }
}

@keyframes zoomOut {
  from { opacity: 0; transform: scale(1.1); }
  to { opacity: 1; transform: scale(1); }
}

@keyframes bounceIn {
  0% { opacity: 0; transform: scale(0.3); }
  50% { opacity: 1; transform: scale(1.05); }
  70% { transform: scale(0.9); }
  100% { transform: scale(1); }
}

@keyframes slideInDown {
  from { opacity: 0; transform: translateY(-100%); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes slideInUp {
  from { opacity: 0; transform: translateY(100%); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes slideInLeft {
  from { opacity: 0; transform: translateX(-100%); }
  to { opacity: 1; transform: translateX(0); }
}

@keyframes slideInRight {
  from { opacity: 0; transform: translateX(100%); }
  to { opacity: 1; transform: translateX(0); }
}

@keyframes rotateIn {
  from { opacity: 0; transform: rotate(-200deg); }
  to { opacity: 1; transform: rotate(0); }
}

@keyframes flipInX {
  from { opacity: 0; transform: perspective(400px) rotateX(90deg); }
  to { opacity: 1; transform: perspective(400px) rotateX(0); }
}

@keyframes flipInY {
  from { opacity: 0; transform: perspective(400px) rotateY(90deg); }
  to { opacity: 1; transform: perspective(400px) rotateY(0); }
}

/* Animation Classes */
.animate-fadeIn { animation-name: fadeIn; animation-fill-mode: forwards; }
.animate-fadeInDown { animation-name: fadeInDown; animation-fill-mode: forwards; }
.animate-fadeInUp { animation-name: fadeInUp; animation-fill-mode: forwards; }
.animate-fadeInLeft { animation-name: fadeInLeft; animation-fill-mode: forwards; }
.animate-fadeInRight { animation-name: fadeInRight; animation-fill-mode: forwards; }
.animate-zoomIn { animation-name: zoomIn; animation-fill-mode: forwards; }
.animate-zoomOut { animation-name: zoomOut; animation-fill-mode: forwards; }
.animate-bounceIn { animation-name: bounceIn; animation-fill-mode: forwards; }
.animate-slideInDown { animation-name: slideInDown; animation-fill-mode: forwards; }
.animate-slideInUp { animation-name: slideInUp; animation-fill-mode: forwards; }
.animate-slideInLeft { animation-name: slideInLeft; animation-fill-mode: forwards; }
.animate-slideInRight { animation-name: slideInRight; animation-fill-mode: forwards; }
.animate-rotateIn { animation-name: rotateIn; animation-fill-mode: forwards; }
.animate-flipInX { animation-name: flipInX; animation-fill-mode: forwards; }
.animate-flipInY { animation-name: flipInY; animation-fill-mode: forwards; }

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

.gallery-item.hover-zoom:hover img {
  transform: scale(1.1);
}

.gallery-item.hover-grayscale img {
  filter: grayscale(0);
  transition: filter 0.3s;
}

.gallery-item.hover-grayscale:hover img {
  filter: grayscale(100%);
}

/* Icon Box Widget Styles */
.icon-box-wrapper {
  transition: all 0.3s ease;
}

.icon-box-wrapper .icon-box-icon {
  line-height: 1;
}

.icon-box-wrapper .icon-box-title {
  transition: color 0.3s ease;
}

/* Image Box Widget Styles */
.image-box-wrapper {
  transition: all 0.3s ease;
}

.image-box-wrapper .image-box-title {
  transition: color 0.3s ease;
}

.image-box-wrapper img {
  overflow: hidden;
}

/* Image Box Hover Effects */
.image-box-wrapper .hover-zoom {
  transition: transform 0.3s ease;
}

.image-box-wrapper .hover-zoom_out {
  transition: transform 0.3s ease;
}

.image-box-wrapper .hover-grayscale {
  transition: filter 0.3s ease;
}

.image-box-wrapper .hover-blur {
  transition: filter 0.3s ease;
}
</style>
