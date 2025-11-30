<template>
  <div class="widget-content">
    <!-- Heading Widget -->
    <component
      :is="getTagName()"
      v-if="widget.widgetType === 'heading'"
      :style="getHeadingStyles()"
    >
      {{ settings.title ?? 'Heading' }}
    </component>

    <!-- Text Editor Widget -->
    <div
      v-else-if="widget.widgetType === 'text-editor'"
      :style="getTextEditorStyles()"
      v-html="settings.editor ?? '<p>Lorem ipsum dolor sit amet</p>'"
    </div>

    <!-- Image Widget -->
    <div v-else-if="widget.widgetType === 'image'" :style="getImageStyles()">
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
    <div v-else-if="widget.widgetType === 'button'" :style="getButtonContainerStyles()">
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
      <hr :style="getDividerStyles()" />
    </div>

    <!-- Spacer Widget -->
    <div
      v-else-if="widget.widgetType === 'spacer'"
      :style="{ height: (settings.space ?? 50) + 'px' }"
    ></div>

    <!-- Icon Widget -->
    <div v-else-if="widget.widgetType === 'icon'" :style="getIconContainerStyles()">
      <a v-if="settings.link" :href="settings.link">
        <span :style="getIconStyles()">{{ settings.icon ?? '★' }}</span>
      </a>
      <span v-else :style="getIconStyles()">{{ settings.icon ?? '★' }}</span>
    </div>

    <!-- Icon Box Widget -->
    <div v-else-if="widget.widgetType === 'icon-box'" :style="getIconBoxStyles()">
      <div :style="{ fontSize: (settings.icon_size ?? 50) + 'px', color: settings.icon_color ?? '#4f46e5' }">
        {{ settings.icon ?? '⚡' }}
      </div>
      <h4 :style="{ color: settings.title_color ?? '#1f2937' }" class="font-semibold mt-3">
        {{ settings.title ?? 'Icon Box' }}
      </h4>
      <p class="text-gray-600 mt-2">{{ settings.description ?? 'Click here to add your own text.' }}</p>
    </div>

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
    <div v-else-if="widget.widgetType === 'image-box'" :style="getImageBoxStyles()" class="text-center">
      <img v-if="settings.image_url" :src="settings.image_url" class="w-full h-40 object-cover rounded-lg mb-4" />
      <div v-else class="w-full h-40 bg-gray-200 rounded-lg mb-4 flex items-center justify-center text-gray-400">🖼️</div>
      <h4 :style="{ color: settings.title_color ?? '#1f2937' }" class="font-semibold text-lg">{{ settings.title ?? 'Image Box' }}</h4>
      <p :style="{ color: settings.description_color ?? '#6b7280' }" class="mt-2">{{ settings.description ?? 'Click here to add your own text.' }}</p>
    </div>

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

function getImageBoxStyles() {
  const bg = settings.value.background;
  const border = settings.value.border;
  const shadow = settings.value.box_shadow;

  const styles = {
    textAlign: settings.value.alignment ?? 'center',
    margin: formatDimensions(settings.value.margin),
    padding: formatDimensions(settings.value.padding)
  };

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

function getContainerStyles() {
  const bg = settings.value.background;
  const border = settings.value.border;
  const shadow = settings.value.box_shadow;

  const styles = {
    minHeight: (settings.value.min_height ?? 0) + 'px',
    margin: formatDimensions(settings.value.margin),
    padding: formatDimensions(settings.value.padding)
  };

  if (settings.value.z_index) {
    styles.zIndex = settings.value.z_index;
  }

  if (bg?.type === 'gradient') {
    styles.background = `linear-gradient(${bg.gradientAngle ?? 180}deg, ${bg.gradientColor1 ?? '#6366f1'}, ${bg.gradientColor2 ?? '#8b5cf6'})`;
  } else if (bg?.color) {
    styles.backgroundColor = bg.color;
  }

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

function getInnerSectionStyles() {
  const bg = settings.value.background;
  const border = settings.value.border;

  const styles = {
    margin: formatDimensions(settings.value.margin),
    padding: formatDimensions(settings.value.padding)
  };

  if (bg?.type === 'gradient') {
    styles.background = `linear-gradient(${bg.gradientAngle ?? 180}deg, ${bg.gradientColor1 ?? '#6366f1'}, ${bg.gradientColor2 ?? '#8b5cf6'})`;
  } else if (bg?.color) {
    styles.backgroundColor = bg.color;
  }

  if (border?.style && border.style !== 'none') {
    styles.borderStyle = border.style;
    styles.borderColor = border.color ?? '#e5e7eb';
    styles.borderWidth = '1px';
  }
  if (border?.radius) {
    styles.borderRadius = `${border.radius.topLeft ?? 0}px ${border.radius.topRight ?? 0}px ${border.radius.bottomRight ?? 0}px ${border.radius.bottomLeft ?? 0}px`;
  }

  return styles;
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
</style>
