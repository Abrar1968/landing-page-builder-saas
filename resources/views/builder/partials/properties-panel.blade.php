{{-- Properties Panel Header --}}
<div class="p-4 border-b border-gray-200 bg-gray-50">
    <div class="flex items-center justify-between">
        <h3 class="font-semibold text-gray-900" x-text="getSelectedElement()?.label || 'Properties'"></h3>
        <button @click="deselectElement()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>

{{-- Tabs --}}
<div class="flex border-b border-gray-200 bg-white sticky top-0 z-10">
    <button @click="activeTab = 'content'"
            :class="activeTab === 'content' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="flex-1 py-3 px-4 text-sm font-medium border-b-2 transition-colors">
        Content
    </button>
    <button @click="activeTab = 'style'"
            :class="activeTab === 'style' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="flex-1 py-3 px-4 text-sm font-medium border-b-2 transition-colors">
        Style
    </button>
    <button @click="activeTab = 'advanced'"
            :class="activeTab === 'advanced' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="flex-1 py-3 px-4 text-sm font-medium border-b-2 transition-colors">
        Advanced
    </button>
</div>

<div class="p-4 space-y-4 overflow-y-auto" style="max-height: calc(100vh - 180px);">
    <template x-if="getSelectedElement()">
        <div>
            {{-- CONTENT TAB --}}
            <div x-show="activeTab === 'content'" class="space-y-4">
                {{-- Heading Content --}}
                <template x-if="getSelectedElement().type === 'heading'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Title</label>
                            <input type="text" x-model="getSelectedElement().props.text" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">HTML Tag</label>
                            <select x-model="getSelectedElement().props.level" @change="markDirty()"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="h1">H1</option>
                                <option value="h2">H2</option>
                                <option value="h3">H3</option>
                                <option value="h4">H4</option>
                                <option value="h5">H5</option>
                                <option value="h6">H6</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Alignment</label>
                            <div class="flex gap-1">
                                <button @click="getSelectedElement().props.alignment = 'left'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'left' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                                        class="flex-1 py-2 rounded text-sm">Left</button>
                                <button @click="getSelectedElement().props.alignment = 'center'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'center' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                                        class="flex-1 py-2 rounded text-sm">Center</button>
                                <button @click="getSelectedElement().props.alignment = 'right'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'right' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                                        class="flex-1 py-2 rounded text-sm">Right</button>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Paragraph Content --}}
                <template x-if="getSelectedElement().type === 'paragraph'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Text</label>
                            <textarea x-model="getSelectedElement().props.text" @input="markDirty()" rows="6"
                                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Alignment</label>
                            <div class="flex gap-1">
                                <button @click="getSelectedElement().props.alignment = 'left'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'left' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                                        class="flex-1 py-2 rounded text-sm">Left</button>
                                <button @click="getSelectedElement().props.alignment = 'center'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'center' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                                        class="flex-1 py-2 rounded text-sm">Center</button>
                                <button @click="getSelectedElement().props.alignment = 'right'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'right' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                                        class="flex-1 py-2 rounded text-sm">Right</button>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Button Content --}}
                <template x-if="getSelectedElement().type === 'button'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Text</label>
                            <input type="text" x-model="getSelectedElement().props.text" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Link URL</label>
                            <input type="url" x-model="getSelectedElement().props.url" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="https://">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Link Target</label>
                            <select x-model="getSelectedElement().props.target" @change="markDirty()"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="_self">Same Window</option>
                                <option value="_blank">New Window</option>
                            </select>
                        </div>
                    </div>
                </template>

                {{-- Image Content --}}
                <template x-if="getSelectedElement().type === 'image'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Image URL</label>
                            <input type="url" x-model="getSelectedElement().props.src" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="https://example.com/image.jpg">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Alt Text</label>
                            <input type="text" x-model="getSelectedElement().props.alt" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Caption</label>
                            <input type="text" x-model="getSelectedElement().props.caption" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Link</label>
                            <input type="url" x-model="getSelectedElement().props.link" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Optional link URL">
                        </div>
                    </div>
                </template>

                {{-- Video Content --}}
                <template x-if="getSelectedElement().type === 'video'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Video URL</label>
                            <input type="url" x-model="getSelectedElement().props.src" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="YouTube or Vimeo URL">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Provider</label>
                            <select x-model="getSelectedElement().props.provider" @change="markDirty()"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="youtube">YouTube</option>
                                <option value="vimeo">Vimeo</option>
                            </select>
                        </div>
                    </div>
                </template>

                {{-- Spacer Content --}}
                <template x-if="getSelectedElement().type === 'spacer'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Height (px)</label>
                            <input type="number" x-model="getSelectedElement().styles.height" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   min="0" max="500">
                        </div>
                    </div>
                </template>

                {{-- Divider Content --}}
                <template x-if="getSelectedElement().type === 'divider'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Style</label>
                            <select x-model="getSelectedElement().props.style" @change="markDirty()"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="solid">Solid</option>
                                <option value="dashed">Dashed</option>
                                <option value="dotted">Dotted</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Alignment</label>
                            <div class="flex gap-1">
                                <button @click="getSelectedElement().props.alignment = 'left'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'left' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                                        class="flex-1 py-2 rounded text-sm">Left</button>
                                <button @click="getSelectedElement().props.alignment = 'center'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'center' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                                        class="flex-1 py-2 rounded text-sm">Center</button>
                                <button @click="getSelectedElement().props.alignment = 'right'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'right' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                                        class="flex-1 py-2 rounded text-sm">Right</button>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Hero Content --}}
                <template x-if="getSelectedElement().type === 'hero'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Heading</label>
                            <input type="text" x-model="getSelectedElement().props.heading" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Subheading</label>
                            <textarea x-model="getSelectedElement().props.subheading" @input="markDirty()" rows="3"
                                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" x-model="getSelectedElement().props.showButton" @change="markDirty()"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label class="text-xs font-medium text-gray-600">Show Button</label>
                        </div>
                        <template x-if="getSelectedElement().props.showButton">
                            <div class="space-y-4 pl-4 border-l-2 border-indigo-100">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Button Text</label>
                                    <input type="text" x-model="getSelectedElement().props.buttonText" @input="markDirty()"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Button URL</label>
                                    <input type="url" x-model="getSelectedElement().props.buttonUrl" @input="markDirty()"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                        </template>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Alignment</label>
                            <div class="flex gap-1">
                                <button @click="getSelectedElement().props.alignment = 'left'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'left' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                                        class="flex-1 py-2 rounded text-sm">Left</button>
                                <button @click="getSelectedElement().props.alignment = 'center'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'center' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                                        class="flex-1 py-2 rounded text-sm">Center</button>
                                <button @click="getSelectedElement().props.alignment = 'right'; markDirty()"
                                        :class="getSelectedElement().props.alignment === 'right' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                                        class="flex-1 py-2 rounded text-sm">Right</button>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- CTA Content --}}
                <template x-if="getSelectedElement().type === 'cta'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Heading</label>
                            <input type="text" x-model="getSelectedElement().props.heading" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Description</label>
                            <textarea x-model="getSelectedElement().props.description" @input="markDirty()" rows="3"
                                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Primary Button Text</label>
                            <input type="text" x-model="getSelectedElement().props.buttonText" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Primary Button URL</label>
                            <input type="url" x-model="getSelectedElement().props.buttonUrl" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Secondary Button Text (Optional)</label>
                            <input type="text" x-model="getSelectedElement().props.secondaryButtonText" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </template>

                {{-- Testimonial Content --}}
                <template x-if="getSelectedElement().type === 'testimonial'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Quote</label>
                            <textarea x-model="getSelectedElement().props.quote" @input="markDirty()" rows="4"
                                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Author Name</label>
                            <input type="text" x-model="getSelectedElement().props.author" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Role/Company</label>
                            <input type="text" x-model="getSelectedElement().props.role" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Rating</label>
                            <select x-model="getSelectedElement().props.rating" @change="markDirty()"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>
                    </div>
                </template>

                {{-- Newsletter Content --}}
                <template x-if="getSelectedElement().type === 'newsletter'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Heading</label>
                            <input type="text" x-model="getSelectedElement().props.heading" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Description</label>
                            <textarea x-model="getSelectedElement().props.description" @input="markDirty()" rows="2"
                                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Placeholder Text</label>
                            <input type="text" x-model="getSelectedElement().props.placeholder" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Button Text</label>
                            <input type="text" x-model="getSelectedElement().props.buttonText" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </template>

                {{-- HTML Content --}}
                <template x-if="getSelectedElement().type === 'html'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">HTML Code</label>
                            <textarea x-model="getSelectedElement().props.content" @input="markDirty()" rows="10"
                                      class="w-full px-3 py-2 text-sm font-mono border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                    </div>
                </template>
            </div>

            {{-- STYLE TAB --}}
            <div x-show="activeTab === 'style'" class="space-y-4">
                {{-- Typography Styles (for text elements) --}}
                <template x-if="['heading', 'paragraph'].includes(getSelectedElement().type)">
                    <div class="space-y-4">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Typography</div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Font Family</label>
                            <select x-model="getSelectedElement().styles.fontFamily" @change="markDirty()"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <template x-for="font in fonts" :key="font">
                                    <option :value="font" x-text="font"></option>
                                </template>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Size</label>
                                <div class="flex">
                                    <input type="number" x-model="getSelectedElement().styles.fontSize" @input="markDirty()"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-md focus:ring-indigo-500 focus:border-indigo-500">
                                    <span class="px-3 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-md text-sm text-gray-600">px</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Weight</label>
                                <select x-model="getSelectedElement().styles.fontWeight" @change="markDirty()"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="300">Light</option>
                                    <option value="400">Normal</option>
                                    <option value="500">Medium</option>
                                    <option value="600">Semibold</option>
                                    <option value="700">Bold</option>
                                    <option value="800">Extra Bold</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Line Height</label>
                                <input type="text" x-model="getSelectedElement().styles.lineHeight" @input="markDirty()"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="1.5">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Letter Spacing</label>
                                <input type="number" x-model="getSelectedElement().styles.letterSpacing" @input="markDirty()"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                       step="0.1">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Text Transform</label>
                            <select x-model="getSelectedElement().styles.textTransform" @change="markDirty()"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="none">None</option>
                                <option value="uppercase">UPPERCASE</option>
                                <option value="lowercase">lowercase</option>
                                <option value="capitalize">Capitalize</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Text Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="getSelectedElement().styles.color" @input="markDirty()"
                                       class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                                <input type="text" x-model="getSelectedElement().styles.color" @input="markDirty()"
                                       class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Button Styles --}}
                <template x-if="getSelectedElement().type === 'button'">
                    <div class="space-y-4">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Button Style</div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Background Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="getSelectedElement().styles.backgroundColor" @input="markDirty()"
                                       class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                                <input type="text" x-model="getSelectedElement().styles.backgroundColor" @input="markDirty()"
                                       class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Text Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="getSelectedElement().styles.textColor" @input="markDirty()"
                                       class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                                <input type="text" x-model="getSelectedElement().styles.textColor" @input="markDirty()"
                                       class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Font Size</label>
                                <input type="number" x-model="getSelectedElement().styles.fontSize" @input="markDirty()"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Font Weight</label>
                                <select x-model="getSelectedElement().styles.fontWeight" @change="markDirty()"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="400">Normal</option>
                                    <option value="500">Medium</option>
                                    <option value="600">Semibold</option>
                                    <option value="700">Bold</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Padding X</label>
                                <input type="number" x-model="getSelectedElement().styles.paddingX" @input="markDirty()"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Padding Y</label>
                                <input type="number" x-model="getSelectedElement().styles.paddingY" @input="markDirty()"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Border Radius</label>
                            <input type="number" x-model="getSelectedElement().styles.borderRadius" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Alignment</label>
                            <div class="flex gap-1">
                                <button @click="getSelectedElement().styles.alignment = 'left'; markDirty()"
                                        :class="getSelectedElement().styles.alignment === 'left' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                                        class="flex-1 py-2 rounded text-sm">Left</button>
                                <button @click="getSelectedElement().styles.alignment = 'center'; markDirty()"
                                        :class="getSelectedElement().styles.alignment === 'center' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                                        class="flex-1 py-2 rounded text-sm">Center</button>
                                <button @click="getSelectedElement().styles.alignment = 'right'; markDirty()"
                                        :class="getSelectedElement().styles.alignment === 'right' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                                        class="flex-1 py-2 rounded text-sm">Right</button>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Image Styles --}}
                <template x-if="getSelectedElement().type === 'image'">
                    <div class="space-y-4">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Image Style</div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Width</label>
                                <div class="flex">
                                    <input type="number" x-model="getSelectedElement().styles.width" @input="markDirty()"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-md focus:ring-indigo-500 focus:border-indigo-500">
                                    <select x-model="getSelectedElement().styles.widthUnit" @change="markDirty()"
                                            class="px-2 py-2 text-sm border border-l-0 border-gray-300 rounded-r-md focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="%">%</option>
                                        <option value="px">px</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Object Fit</label>
                                <select x-model="getSelectedElement().styles.objectFit" @change="markDirty()"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="cover">Cover</option>
                                    <option value="contain">Contain</option>
                                    <option value="fill">Fill</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Border Radius</label>
                            <input type="number" x-model="getSelectedElement().styles.borderRadius" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Opacity (%)</label>
                            <input type="range" x-model="getSelectedElement().styles.opacity" @input="markDirty()"
                                   min="0" max="100" class="w-full">
                            <div class="text-xs text-gray-500 text-center" x-text="getSelectedElement().styles.opacity + '%'"></div>
                        </div>
                    </div>
                </template>

                {{-- Divider Styles --}}
                <template x-if="getSelectedElement().type === 'divider'">
                    <div class="space-y-4">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Divider Style</div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="getSelectedElement().styles.color" @input="markDirty()"
                                       class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                                <input type="text" x-model="getSelectedElement().styles.color" @input="markDirty()"
                                       class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Thickness</label>
                                <input type="number" x-model="getSelectedElement().styles.thickness" @input="markDirty()"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                       min="1" max="20">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Width (%)</label>
                                <input type="number" x-model="getSelectedElement().styles.width" @input="markDirty()"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                       min="1" max="100">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Gap (px)</label>
                            <input type="number" x-model="getSelectedElement().styles.gap" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </template>

                {{-- Hero Styles --}}
                <template x-if="getSelectedElement().type === 'hero'">
                    <div class="space-y-4">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Hero Style</div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Background Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="getSelectedElement().styles.backgroundColor" @input="markDirty()"
                                       class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                                <input type="text" x-model="getSelectedElement().styles.backgroundColor" @input="markDirty()"
                                       class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Background Image URL</label>
                            <input type="url" x-model="getSelectedElement().styles.backgroundImage" @input="markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="https://...">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Text Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="getSelectedElement().styles.textColor" @input="markDirty()"
                                       class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                                <input type="text" x-model="getSelectedElement().styles.textColor" @input="markDirty()"
                                       class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Heading Size</label>
                                <input type="number" x-model="getSelectedElement().styles.headingSize" @input="markDirty()"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Min Height</label>
                                <input type="number" x-model="getSelectedElement().styles.minHeight" @input="markDirty()"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Button Background</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="getSelectedElement().styles.buttonBgColor" @input="markDirty()"
                                       class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                                <input type="text" x-model="getSelectedElement().styles.buttonBgColor" @input="markDirty()"
                                       class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Section-based styles (CTA, Newsletter, Testimonial) --}}
                <template x-if="['cta', 'newsletter', 'testimonial'].includes(getSelectedElement().type)">
                    <div class="space-y-4">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Section Style</div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Background Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="getSelectedElement().styles.backgroundColor" @input="markDirty()"
                                       class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                                <input type="text" x-model="getSelectedElement().styles.backgroundColor" @input="markDirty()"
                                       class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Text Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="getSelectedElement().styles.textColor" @input="markDirty()"
                                       class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                                <input type="text" x-model="getSelectedElement().styles.textColor" @input="markDirty()"
                                       class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Button Background</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="getSelectedElement().styles.buttonBgColor" @input="markDirty()"
                                       class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                                <input type="text" x-model="getSelectedElement().styles.buttonBgColor" @input="markDirty()"
                                       class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- ADVANCED TAB --}}
            <div x-show="activeTab === 'advanced'" class="space-y-4">
                {{-- Margin --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Margin</span>
                        <button @click="toggleLinkedSpacing('margin')"
                                :class="getSelectedElement().advanced?.margin?.linked ? 'text-indigo-600' : 'text-gray-400'"
                                class="p-1 hover:bg-gray-100 rounded">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-4 gap-2">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 text-center">Top</label>
                            <input type="number"
                                   :value="getSelectedElement().advanced?.margin?.top || 0"
                                   @input="updateLinkedSpacing('margin', 'top', $event.target.value)"
                                   class="w-full px-2 py-1.5 text-sm text-center border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 text-center">Right</label>
                            <input type="number"
                                   :value="getSelectedElement().advanced?.margin?.right || 0"
                                   @input="updateLinkedSpacing('margin', 'right', $event.target.value)"
                                   class="w-full px-2 py-1.5 text-sm text-center border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 text-center">Bottom</label>
                            <input type="number"
                                   :value="getSelectedElement().advanced?.margin?.bottom || 0"
                                   @input="updateLinkedSpacing('margin', 'bottom', $event.target.value)"
                                   class="w-full px-2 py-1.5 text-sm text-center border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 text-center">Left</label>
                            <input type="number"
                                   :value="getSelectedElement().advanced?.margin?.left || 0"
                                   @input="updateLinkedSpacing('margin', 'left', $event.target.value)"
                                   class="w-full px-2 py-1.5 text-sm text-center border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                {{-- Padding --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Padding</span>
                        <button @click="toggleLinkedSpacing('padding')"
                                :class="getSelectedElement().advanced?.padding?.linked ? 'text-indigo-600' : 'text-gray-400'"
                                class="p-1 hover:bg-gray-100 rounded">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-4 gap-2">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 text-center">Top</label>
                            <input type="number"
                                   :value="getSelectedElement().advanced?.padding?.top || 0"
                                   @input="updateLinkedSpacing('padding', 'top', $event.target.value)"
                                   class="w-full px-2 py-1.5 text-sm text-center border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 text-center">Right</label>
                            <input type="number"
                                   :value="getSelectedElement().advanced?.padding?.right || 0"
                                   @input="updateLinkedSpacing('padding', 'right', $event.target.value)"
                                   class="w-full px-2 py-1.5 text-sm text-center border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 text-center">Bottom</label>
                            <input type="number"
                                   :value="getSelectedElement().advanced?.padding?.bottom || 0"
                                   @input="updateLinkedSpacing('padding', 'bottom', $event.target.value)"
                                   class="w-full px-2 py-1.5 text-sm text-center border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 text-center">Left</label>
                            <input type="number"
                                   :value="getSelectedElement().advanced?.padding?.left || 0"
                                   @input="updateLinkedSpacing('padding', 'left', $event.target.value)"
                                   class="w-full px-2 py-1.5 text-sm text-center border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                {{-- Responsive Visibility --}}
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Responsive</span>
                    <div class="mt-2 space-y-2">
                        <label class="flex items-center gap-2">
                            <input type="checkbox"
                                   :checked="getSelectedElement().advanced?.hideDesktop"
                                   @change="getSelectedElement().advanced.hideDesktop = $event.target.checked; markDirty()"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Hide on Desktop</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox"
                                   :checked="getSelectedElement().advanced?.hideTablet"
                                   @change="getSelectedElement().advanced.hideTablet = $event.target.checked; markDirty()"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Hide on Tablet</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox"
                                   :checked="getSelectedElement().advanced?.hideMobile"
                                   @change="getSelectedElement().advanced.hideMobile = $event.target.checked; markDirty()"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Hide on Mobile</span>
                        </label>
                    </div>
                </div>

                {{-- Custom CSS --}}
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">CSS</span>
                    <div class="mt-2 space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">CSS Classes</label>
                            <input type="text"
                                   :value="getSelectedElement().advanced?.cssClass || ''"
                                   @input="getSelectedElement().advanced.cssClass = $event.target.value; markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="my-class another-class">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">CSS ID</label>
                            <input type="text"
                                   :value="getSelectedElement().advanced?.cssId || ''"
                                   @input="getSelectedElement().advanced.cssId = $event.target.value; markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="my-element">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Z-Index</label>
                            <input type="number"
                                   :value="getSelectedElement().advanced?.zIndex || ''"
                                   @input="getSelectedElement().advanced.zIndex = $event.target.value; markDirty()"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
