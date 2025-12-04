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
            @input="
                $emit(
                    'update:modelValue',
                    $event.target.value === ''
                        ? ''
                        : Number($event.target.value)
                )
            "
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
        />

        <!-- Select -->
        <select
            v-else-if="control.type === 'select'"
            :value="modelValue"
            @change="$emit('update:modelValue', $event.target.value)"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
        >
            <option
                v-for="(label, value) in control.options"
                :key="value"
                :value="value"
            >
                {{ label }}
            </option>
        </select>

        <!-- Choose (Button Group) -->
        <div
            v-else-if="control.type === 'choose'"
            class="flex border border-gray-300 rounded-md overflow-hidden"
        >
            <button
                v-for="(option, value) in control.options"
                :key="value"
                @click="$emit('update:modelValue', value)"
                :class="[
                    'flex-1 px-3 py-2 text-sm font-medium transition-colors',
                    modelValue === value
                        ? 'bg-indigo-600 text-white'
                        : 'bg-white text-gray-700 hover:bg-gray-50',
                ]"
                type="button"
            >
                {{ option.icon || option.title }}
            </button>
        </div>

        <!-- Color Picker -->
        <div
            v-else-if="control.type === 'color'"
            class="flex items-center gap-2"
        >
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
                    @input="
                        $emit('update:modelValue', Number($event.target.value))
                    "
                    class="flex-1"
                />
                <input
                    type="number"
                    :value="modelValue ?? control.default ?? control.min"
                    :min="control.min"
                    :max="control.max"
                    @input="
                        $emit(
                            'update:modelValue',
                            $event.target.value === ''
                                ? ''
                                : Number($event.target.value)
                        )
                    "
                    class="w-16 px-2 py-1 text-sm border border-gray-300 rounded"
                />
                <span class="text-sm text-gray-600 w-8 text-right">
                    {{ control.unit || "" }}
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
                    modelValue ? 'bg-indigo-600' : 'bg-gray-200',
                ]"
            >
                <span
                    :class="[
                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                        modelValue ? 'translate-x-5' : 'translate-x-0',
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
                {{ modelValue ? "Change Image" : "Select Image" }}
            </button>
        </div>

        <!-- Icon Picker -->
        <div v-else-if="control.type === 'icon'">
            <IconPicker
                :modelValue="modelValue"
                @update:modelValue="$emit('update:modelValue', $event)"
            />
        </div>

        <!-- WYSIWYG Editor -->
        <div v-else-if="control.type === 'wysiwyg'">
            <div class="border border-gray-300 rounded-md overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-300 p-2 flex gap-1">
                    <button
                        type="button"
                        @click="execCommand('bold')"
                        class="px-2 py-1 text-sm font-bold hover:bg-gray-200 rounded"
                    >
                        B
                    </button>
                    <button
                        type="button"
                        @click="execCommand('italic')"
                        class="px-2 py-1 text-sm italic hover:bg-gray-200 rounded"
                    >
                        I
                    </button>
                    <button
                        type="button"
                        @click="execCommand('underline')"
                        class="px-2 py-1 text-sm underline hover:bg-gray-200 rounded"
                    >
                        U
                    </button>
                    <span class="border-l border-gray-300 mx-1"></span>
                    <button
                        type="button"
                        @click="execCommand('insertUnorderedList')"
                        class="px-2 py-1 text-sm hover:bg-gray-200 rounded"
                    >
                        •
                    </button>
                    <button
                        type="button"
                        @click="execCommand('insertOrderedList')"
                        class="px-2 py-1 text-sm hover:bg-gray-200 rounded"
                    >
                        1.
                    </button>
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
        <div
            v-else-if="control.type === 'dimensions'"
            class="grid grid-cols-4 gap-2"
        >
            <div>
                <label class="text-xs text-gray-500">Top</label>
                <input
                    type="number"
                    :value="modelValue?.top"
                    @input="updateDimension('top', $event.target.value)"
                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
                />
            </div>
            <div>
                <label class="text-xs text-gray-500">Right</label>
                <input
                    type="number"
                    :value="modelValue?.right"
                    @input="updateDimension('right', $event.target.value)"
                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
                />
            </div>
            <div>
                <label class="text-xs text-gray-500">Bottom</label>
                <input
                    type="number"
                    :value="modelValue?.bottom"
                    @input="updateDimension('bottom', $event.target.value)"
                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
                />
            </div>
            <div>
                <label class="text-xs text-gray-500">Left</label>
                <input
                    type="number"
                    :value="modelValue?.left"
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
                        @change="
                            updateUrl('is_external', $event.target.checked)
                        "
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
        <div
            v-else-if="control.type === 'typography'"
            class="space-y-3 p-3 bg-gray-50 rounded-md"
        >
            <div>
                <label class="block text-xs text-gray-500 mb-1"
                    >Font Family</label
                >
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
                            @input="
                                updateTypography(
                                    'size',
                                    Number($event.target.value)
                                )
                            "
                            class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-l"
                        />
                        <select
                            :value="modelValue?.sizeUnit || 'px'"
                            @change="
                                updateTypography(
                                    'sizeUnit',
                                    $event.target.value
                                )
                            "
                            class="px-1 py-1.5 text-sm border border-l-0 border-gray-300 rounded-r bg-gray-50"
                        >
                            <option value="px">px</option>
                            <option value="em">em</option>
                            <option value="rem">rem</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1"
                        >Weight</label
                    >
                    <select
                        :value="modelValue?.weight || '400'"
                        @change="
                            updateTypography('weight', $event.target.value)
                        "
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
                    <label class="block text-xs text-gray-500 mb-1"
                        >Transform</label
                    >
                    <select
                        :value="modelValue?.transform || 'none'"
                        @change="
                            updateTypography('transform', $event.target.value)
                        "
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
                    >
                        <option value="none">None</option>
                        <option value="uppercase">UPPERCASE</option>
                        <option value="lowercase">lowercase</option>
                        <option value="capitalize">Capitalize</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1"
                        >Style</label
                    >
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
                    <label class="block text-xs text-gray-500 mb-1"
                        >Line Height</label
                    >
                    <input
                        type="number"
                        step="0.1"
                        :value="modelValue?.lineHeight || 1.5"
                        @input="
                            updateTypography(
                                'lineHeight',
                                Number($event.target.value)
                            )
                        "
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
                    />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1"
                        >Letter Spacing</label
                    >
                    <input
                        type="number"
                        step="0.1"
                        :value="modelValue?.letterSpacing || 0"
                        @input="
                            updateTypography(
                                'letterSpacing',
                                Number($event.target.value)
                            )
                        "
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
                        placeholder="px"
                    />
                </div>
            </div>
        </div>

        <!-- Background Group Control -->
        <div
            v-else-if="control.type === 'background'"
            class="space-y-3 p-3 bg-gray-50 rounded-md"
        >
            <div class="flex gap-1">
                <button
                    type="button"
                    @click="updateBackground('type', 'classic')"
                    :class="[
                        'flex-1 py-1.5 text-xs rounded',
                        (modelValue?.type || 'classic') === 'classic'
                            ? 'bg-indigo-100 text-indigo-700'
                            : 'bg-white text-gray-600',
                    ]"
                >
                    Classic
                </button>
                <button
                    type="button"
                    @click="updateBackground('type', 'gradient')"
                    :class="[
                        'flex-1 py-1.5 text-xs rounded',
                        modelValue?.type === 'gradient'
                            ? 'bg-indigo-100 text-indigo-700'
                            : 'bg-white text-gray-600',
                    ]"
                >
                    Gradient
                </button>
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
                    <label class="block text-xs text-gray-500 mb-1"
                        >Image URL</label
                    >
                    <input
                        type="url"
                        :value="modelValue?.image || ''"
                        @input="updateBackground('image', $event.target.value)"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
                        placeholder="https://..."
                    />
                </div>
                <div
                    v-if="modelValue?.image"
                    class="mt-2 grid grid-cols-2 gap-2"
                >
                    <div>
                        <label class="block text-xs text-gray-500 mb-1"
                            >Position</label
                        >
                        <select
                            :value="modelValue?.position || 'center center'"
                            @change="
                                updateBackground(
                                    'position',
                                    $event.target.value
                                )
                            "
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
                        <label class="block text-xs text-gray-500 mb-1"
                            >Size</label
                        >
                        <select
                            :value="modelValue?.size || 'cover'"
                            @change="
                                updateBackground('size', $event.target.value)
                            "
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
                        <label class="block text-xs text-gray-500 mb-1"
                            >Color 1</label
                        >
                        <input
                            type="color"
                            :value="modelValue?.gradientColor1 || '#6366f1'"
                            @input="
                                updateBackground(
                                    'gradientColor1',
                                    $event.target.value
                                )
                            "
                            class="w-full h-8 rounded border cursor-pointer"
                        />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1"
                            >Color 2</label
                        >
                        <input
                            type="color"
                            :value="modelValue?.gradientColor2 || '#8b5cf6'"
                            @input="
                                updateBackground(
                                    'gradientColor2',
                                    $event.target.value
                                )
                            "
                            class="w-full h-8 rounded border cursor-pointer"
                        />
                    </div>
                </div>
                <div class="mt-2">
                    <label class="block text-xs text-gray-500 mb-1"
                        >Angle</label
                    >
                    <input
                        type="range"
                        :value="modelValue?.gradientAngle || 180"
                        @input="
                            updateBackground(
                                'gradientAngle',
                                Number($event.target.value)
                            )
                        "
                        min="0"
                        max="360"
                        class="w-full"
                    />
                    <span class="text-xs text-gray-500"
                        >{{ modelValue?.gradientAngle || 180 }}°</span
                    >
                </div>
            </div>
        </div>

        <!-- Border Group Control -->
        <div
            v-else-if="control.type === 'border'"
            class="space-y-3 p-3 bg-gray-50 rounded-md"
        >
            <div>
                <label class="block text-xs text-gray-500 mb-1"
                    >Border Type</label
                >
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
                    <input
                        type="number"
                        :value="modelValue?.width?.top || 1"
                        @input="updateBorderWidth('top', $event.target.value)"
                        placeholder="T"
                        class="px-2 py-1.5 text-sm text-center border border-gray-300 rounded"
                    />
                    <input
                        type="number"
                        :value="modelValue?.width?.right || 1"
                        @input="updateBorderWidth('right', $event.target.value)"
                        placeholder="R"
                        class="px-2 py-1.5 text-sm text-center border border-gray-300 rounded"
                    />
                    <input
                        type="number"
                        :value="modelValue?.width?.bottom || 1"
                        @input="
                            updateBorderWidth('bottom', $event.target.value)
                        "
                        placeholder="B"
                        class="px-2 py-1.5 text-sm text-center border border-gray-300 rounded"
                    />
                    <input
                        type="number"
                        :value="modelValue?.width?.left || 1"
                        @input="updateBorderWidth('left', $event.target.value)"
                        placeholder="L"
                        class="px-2 py-1.5 text-sm text-center border border-gray-300 rounded"
                    />
                </div>
                <div class="mt-2">
                    <label class="block text-xs text-gray-500 mb-1"
                        >Color</label
                    >
                    <div class="flex items-center gap-2">
                        <input
                            type="color"
                            :value="modelValue?.color || '#000000'"
                            @input="updateBorder('color', $event.target.value)"
                            class="w-8 h-8 rounded border cursor-pointer"
                        />
                        <input
                            type="text"
                            :value="modelValue?.color || ''"
                            @input="updateBorder('color', $event.target.value)"
                            class="flex-1 px-2 py-1.5 text-sm border border-gray-300 rounded"
                        />
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1"
                    >Border Radius</label
                >
                <div class="grid grid-cols-4 gap-1">
                    <input
                        type="number"
                        :value="modelValue?.radius?.topLeft || 0"
                        @input="
                            updateBorderRadius('topLeft', $event.target.value)
                        "
                        placeholder="TL"
                        class="px-2 py-1.5 text-sm text-center border border-gray-300 rounded"
                    />
                    <input
                        type="number"
                        :value="modelValue?.radius?.topRight || 0"
                        @input="
                            updateBorderRadius('topRight', $event.target.value)
                        "
                        placeholder="TR"
                        class="px-2 py-1.5 text-sm text-center border border-gray-300 rounded"
                    />
                    <input
                        type="number"
                        :value="modelValue?.radius?.bottomRight || 0"
                        @input="
                            updateBorderRadius(
                                'bottomRight',
                                $event.target.value
                            )
                        "
                        placeholder="BR"
                        class="px-2 py-1.5 text-sm text-center border border-gray-300 rounded"
                    />
                    <input
                        type="number"
                        :value="modelValue?.radius?.bottomLeft || 0"
                        @input="
                            updateBorderRadius(
                                'bottomLeft',
                                $event.target.value
                            )
                        "
                        placeholder="BL"
                        class="px-2 py-1.5 text-sm text-center border border-gray-300 rounded"
                    />
                </div>
            </div>
        </div>

        <!-- Box Shadow Group Control -->
        <div
            v-else-if="control.type === 'box_shadow'"
            class="space-y-3 p-3 bg-gray-50 rounded-md"
        >
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs text-gray-500 mb-1"
                        >Horizontal</label
                    >
                    <input
                        type="number"
                        :value="modelValue?.horizontal || 0"
                        @input="
                            updateBoxShadow(
                                'horizontal',
                                Number($event.target.value)
                            )
                        "
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
                    />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1"
                        >Vertical</label
                    >
                    <input
                        type="number"
                        :value="modelValue?.vertical || 0"
                        @input="
                            updateBoxShadow(
                                'vertical',
                                Number($event.target.value)
                            )
                        "
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
                    />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Blur</label>
                    <input
                        type="number"
                        :value="modelValue?.blur || 0"
                        @input="
                            updateBoxShadow('blur', Number($event.target.value))
                        "
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
                    />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1"
                        >Spread</label
                    >
                    <input
                        type="number"
                        :value="modelValue?.spread || 0"
                        @input="
                            updateBoxShadow(
                                'spread',
                                Number($event.target.value)
                            )
                        "
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
                    />
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Color</label>
                <div class="flex items-center gap-2">
                    <input
                        type="color"
                        :value="modelValue?.color || '#000000'"
                        @input="updateBoxShadow('color', $event.target.value)"
                        class="w-8 h-8 rounded border cursor-pointer"
                    />
                    <input
                        type="text"
                        :value="modelValue?.color || 'rgba(0,0,0,0.1)'"
                        @input="updateBoxShadow('color', $event.target.value)"
                        class="flex-1 px-2 py-1.5 text-sm border border-gray-300 rounded"
                    />
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Position</label>
                <select
                    :value="modelValue?.position || 'outline'"
                    @change="updateBoxShadow('position', $event.target.value)"
                    class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
                >
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
            <p class="text-xs text-gray-500 mt-1">
                Use "selector" to target this element
            </p>
        </div>

        <!-- Flexbox Direction Control -->
        <div
            v-else-if="control.type === 'flexbox_direction'"
            class="flex gap-1"
        >
            <button
                v-for="dir in [
                    { value: 'row', icon: '→', title: 'Row' },
                    { value: 'column', icon: '↓', title: 'Column' },
                    { value: 'row-reverse', icon: '←', title: 'Row Reverse' },
                    {
                        value: 'column-reverse',
                        icon: '↑',
                        title: 'Column Reverse',
                    },
                ]"
                :key="dir.value"
                type="button"
                @click="$emit('update:modelValue', dir.value)"
                :class="[
                    'flex-1 px-3 py-2 text-lg border rounded transition-colors',
                    modelValue === dir.value
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                ]"
                :title="dir.title"
            >
                {{ dir.icon }}
            </button>
        </div>

        <!-- Flexbox Justify Content Control -->
        <div
            v-else-if="control.type === 'flexbox_justify'"
            class="grid grid-cols-3 gap-1"
        >
            <button
                v-for="justify in [
                    { value: 'flex-start', icon: '⊣', title: 'Start' },
                    { value: 'center', icon: '⊢⊣', title: 'Center' },
                    { value: 'flex-end', icon: '⊢', title: 'End' },
                    {
                        value: 'space-between',
                        icon: '⊣ ⊢',
                        title: 'Space Between',
                    },
                    {
                        value: 'space-around',
                        icon: '⊣ ⊢',
                        title: 'Space Around',
                    },
                    {
                        value: 'space-evenly',
                        icon: '⊣ ⊢',
                        title: 'Space Evenly',
                    },
                ]"
                :key="justify.value"
                type="button"
                @click="$emit('update:modelValue', justify.value)"
                :class="[
                    'px-3 py-2 text-sm border rounded transition-colors',
                    modelValue === justify.value
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                ]"
                :title="justify.title"
            >
                {{ justify.icon }}
            </button>
        </div>

        <!-- Flexbox Align Items Control -->
        <div
            v-else-if="control.type === 'flexbox_align'"
            class="grid grid-cols-2 gap-1"
        >
            <button
                v-for="align in [
                    { value: 'flex-start', icon: '⊤', title: 'Start' },
                    { value: 'center', icon: '⊥', title: 'Center' },
                    { value: 'flex-end', icon: '⊥', title: 'End' },
                    { value: 'stretch', icon: '↕', title: 'Stretch' },
                ]"
                :key="align.value"
                type="button"
                @click="$emit('update:modelValue', align.value)"
                :class="[
                    'px-3 py-2 text-lg border rounded transition-colors',
                    modelValue === align.value
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                ]"
                :title="align.title"
            >
                {{ align.icon }}
            </button>
        </div>

        <!-- Flexbox Wrap Control -->
        <div v-else-if="control.type === 'flexbox_wrap'" class="flex gap-1">
            <button
                v-for="wrap in [
                    { value: 'nowrap', icon: '→', title: 'No Wrap' },
                    { value: 'wrap', icon: '⤵', title: 'Wrap' },
                    { value: 'wrap-reverse', icon: '⤴', title: 'Wrap Reverse' },
                ]"
                :key="wrap.value"
                type="button"
                @click="$emit('update:modelValue', wrap.value)"
                :class="[
                    'flex-1 px-3 py-2 text-lg border rounded transition-colors',
                    modelValue === wrap.value
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                ]"
                :title="wrap.title"
            >
                {{ wrap.icon }}
            </button>
        </div>

        <!-- Gaps Control (Column/Row with link toggle) -->
        <div v-else-if="control.type === 'gaps'" class="space-y-2">
            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <label class="block text-xs text-gray-500 mb-1"
                        >Column</label
                    >
                    <input
                        type="number"
                        :value="modelValue?.column ?? 20"
                        @input="updateGap('column', $event.target.value)"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
                    />
                </div>
                <div class="flex-1">
                    <label class="block text-xs text-gray-500 mb-1">Row</label>
                    <input
                        type="number"
                        :value="modelValue?.row ?? 20"
                        @input="updateGap('row', $event.target.value)"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
                    />
                </div>
                <button
                    type="button"
                    @click="toggleGapLink"
                    :class="[
                        'mt-5 p-1.5 border rounded transition-colors',
                        modelValue?.linked
                            ? 'bg-indigo-600 text-white border-indigo-600'
                            : 'bg-white text-gray-700 border-gray-300',
                    ]"
                    title="Link values"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                        />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Align Self Control -->
        <div
            v-else-if="control.type === 'align_self'"
            class="grid grid-cols-2 gap-1"
        >
            <button
                v-for="align in [
                    { value: 'auto', icon: 'Auto', title: 'Auto' },
                    { value: 'flex-start', icon: '⊤', title: 'Start' },
                    { value: 'center', icon: '⊥', title: 'Center' },
                    { value: 'flex-end', icon: '⊥', title: 'End' },
                ]"
                :key="align.value"
                type="button"
                @click="$emit('update:modelValue', align.value)"
                :class="[
                    'px-3 py-2 border rounded transition-colors',
                    modelValue === align.value
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                ]"
                :title="align.title"
            >
                {{ align.icon }}
            </button>
        </div>

        <!-- Order Control -->
        <div
            v-else-if="control.type === 'order'"
            class="flex items-center gap-2"
        >
            <button
                type="button"
                @click="$emit('update:modelValue', (modelValue ?? 0) - 1)"
                class="px-2 py-1 border border-gray-300 rounded hover:bg-gray-50"
            >
                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
            </button>
            <input
                type="number"
                :value="modelValue ?? 0"
                @input="$emit('update:modelValue', Number($event.target.value))"
                class="flex-1 px-3 py-2 text-center border border-gray-300 rounded-md"
            />
            <button
                type="button"
                @click="$emit('update:modelValue', (modelValue ?? 0) + 1)"
                class="px-2 py-1 border border-gray-300 rounded hover:bg-gray-50"
            >
                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                    />
                </svg>
            </button>
            <button
                type="button"
                @click="$emit('update:modelValue', 0)"
                class="px-2 py-1 text-xs text-gray-500 hover:text-gray-700"
                title="Reset"
            >
                ↺
            </button>
        </div>

        <!-- Size Control -->
        <div v-else-if="control.type === 'size_control'" class="space-y-2">
            <div class="flex gap-1">
                <button
                    v-for="size in [
                        { value: 'default', label: 'Default' },
                        { value: 'full', label: 'Full Width' },
                        { value: 'custom', label: 'Custom' },
                    ]"
                    :key="size.value"
                    type="button"
                    @click="updateSize('type', size.value)"
                    :class="[
                        'flex-1 px-3 py-1.5 text-xs border rounded transition-colors',
                        (modelValue?.type ?? 'default') === size.value
                            ? 'bg-indigo-600 text-white border-indigo-600'
                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                    ]"
                >
                    {{ size.label }}
                </button>
            </div>
            <div
                v-if="modelValue?.type === 'custom'"
                class="grid grid-cols-2 gap-2"
            >
                <div>
                    <label class="block text-xs text-gray-500 mb-1"
                        >Width</label
                    >
                    <div class="flex">
                        <input
                            type="number"
                            :value="modelValue?.width ?? 100"
                            @input="
                                updateSize('width', Number($event.target.value))
                            "
                            class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-l"
                        />
                        <select
                            :value="modelValue?.widthUnit ?? '%'"
                            @change="
                                updateSize('widthUnit', $event.target.value)
                            "
                            class="px-1 py-1.5 text-sm border border-l-0 border-gray-300 rounded-r bg-gray-50"
                        >
                            <option value="%">%</option>
                            <option value="px">px</option>
                            <option value="vw">vw</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1"
                        >Max Width</label
                    >
                    <div class="flex">
                        <input
                            type="number"
                            :value="modelValue?.maxWidth ?? 1140"
                            @input="
                                updateSize(
                                    'maxWidth',
                                    Number($event.target.value)
                                )
                            "
                            class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-l"
                        />
                        <select
                            :value="modelValue?.maxWidthUnit ?? 'px'"
                            @change="
                                updateSize('maxWidthUnit', $event.target.value)
                            "
                            class="px-1 py-1.5 text-sm border border-l-0 border-gray-300 rounded-r bg-gray-50"
                        >
                            <option value="px">px</option>
                            <option value="%">%</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Position Control -->
        <div v-else-if="control.type === 'position'" class="space-y-2">
            <select
                :value="modelValue?.type ?? 'default'"
                @change="updatePosition('type', $event.target.value)"
                class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
            >
                <option value="default">Default</option>
                <option value="absolute">Absolute</option>
                <option value="fixed">Fixed</option>
                <option value="relative">Relative</option>
                <option value="sticky">Sticky</option>
            </select>
            <div
                v-if="modelValue?.type && modelValue?.type !== 'default'"
                class="grid grid-cols-2 gap-2"
            >
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Top</label>
                    <input
                        type="number"
                        :value="modelValue?.top ?? ''"
                        @input="updatePosition('top', $event.target.value)"
                        placeholder="auto"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
                    />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1"
                        >Right</label
                    >
                    <input
                        type="number"
                        :value="modelValue?.right ?? ''"
                        @input="updatePosition('right', $event.target.value)"
                        placeholder="auto"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
                    />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1"
                        >Bottom</label
                    >
                    <input
                        type="number"
                        :value="modelValue?.bottom ?? ''"
                        @input="updatePosition('bottom', $event.target.value)"
                        placeholder="auto"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
                    />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Left</label>
                    <input
                        type="number"
                        :value="modelValue?.left ?? ''"
                        @input="updatePosition('left', $event.target.value)"
                        placeholder="auto"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
                    />
                </div>
            </div>
        </div>

        <!-- Hover Tabs Control -->
        <div v-else-if="control.type === 'hover_tabs'" class="flex gap-1 mb-3">
            <button
                type="button"
                @click="$emit('update:modelValue', 'normal')"
                :class="[
                    'flex-1 py-2 text-sm font-medium border rounded transition-colors',
                    (modelValue ?? 'normal') === 'normal'
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                ]"
            >
                Normal
            </button>
            <button
                type="button"
                @click="$emit('update:modelValue', 'hover')"
                :class="[
                    'flex-1 py-2 text-sm font-medium border rounded transition-colors',
                    modelValue === 'hover'
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                ]"
            >
                Hover
            </button>
        </div>

        <!-- Shape Divider Control -->
        <div
            v-else-if="control.type === 'shape_divider'"
            class="space-y-3 p-3 bg-gray-50 rounded-md"
        >
            <div>
                <label class="block text-xs text-gray-500 mb-1">Shape</label>
                <select
                    :value="modelValue?.shape ?? 'none'"
                    @change="updateShapeDivider('shape', $event.target.value)"
                    class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"
                >
                    <option value="none">None</option>
                    <option value="triangle">Triangle</option>
                    <option value="curve">Curve</option>
                    <option value="waves">Waves</option>
                    <option value="zigzag">Zigzag</option>
                    <option value="arrow">Arrow</option>
                </select>
            </div>
            <div v-if="modelValue?.shape && modelValue?.shape !== 'none'">
                <div class="mb-2">
                    <label class="block text-xs text-gray-500 mb-1"
                        >Color</label
                    >
                    <div class="flex items-center gap-2">
                        <input
                            type="color"
                            :value="modelValue?.color ?? '#ffffff'"
                            @input="
                                updateShapeDivider('color', $event.target.value)
                            "
                            class="w-8 h-8 rounded border cursor-pointer"
                        />
                        <input
                            type="text"
                            :value="modelValue?.color ?? '#ffffff'"
                            @input="
                                updateShapeDivider('color', $event.target.value)
                            "
                            class="flex-1 px-2 py-1.5 text-sm border border-gray-300 rounded"
                        />
                    </div>
                </div>
                <div class="mb-2">
                    <label class="block text-xs text-gray-500 mb-1"
                        >Height</label
                    >
                    <input
                        type="range"
                        :value="modelValue?.height ?? 50"
                        @input="
                            updateShapeDivider(
                                'height',
                                Number($event.target.value)
                            )
                        "
                        min="10"
                        max="200"
                        class="w-full"
                    />
                    <span class="text-xs text-gray-500"
                        >{{ modelValue?.height ?? 50 }}px</span
                    >
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            :checked="modelValue?.flip ?? false"
                            @change="
                                updateShapeDivider(
                                    'flip',
                                    $event.target.checked
                                )
                            "
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <span class="text-sm text-gray-600">Flip</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Responsive Visibility Control -->
        <div v-else-if="control.type === 'responsive_visibility'" class="space-y-3 p-3 bg-gray-50 rounded-md">
            <p class="text-xs text-gray-500 mb-2">Hide this element on specific devices</p>
            <label class="flex items-center gap-3 cursor-pointer">
                <input
                    type="checkbox"
                    :checked="modelValue?.hide_desktop ?? false"
                    @change="updateResponsiveVisibility('hide_desktop', $event.target.checked)"
                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700 flex items-center gap-2">
                    <span class="text-lg">🖥️</span> Hide on Desktop
                </span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input
                    type="checkbox"
                    :checked="modelValue?.hide_tablet ?? false"
                    @change="updateResponsiveVisibility('hide_tablet', $event.target.checked)"
                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700 flex items-center gap-2">
                    <span class="text-lg">📱</span> Hide on Tablet
                </span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input
                    type="checkbox"
                    :checked="modelValue?.hide_mobile ?? false"
                    @change="updateResponsiveVisibility('hide_mobile', $event.target.checked)"
                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700 flex items-center gap-2">
                    <span class="text-lg">📲</span> Hide on Mobile
                </span>
            </label>
        </div>

        <!-- Motion Effects Control -->
        <MotionEffects
            v-else-if="control.type === 'motion_effects'"
            :modelValue="modelValue || {}"
            @update:modelValue="$emit('update:modelValue', $event)"
        />

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
import { ref } from "vue";
import IconPicker from "./IconPicker.vue";
import MotionEffects from "./MotionEffects.vue";

const props = defineProps({
    control: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: [String, Number, Boolean, Object],
        default: null,
    },
});

const emit = defineEmits(["update:modelValue", "openMedia"]);

const wysiwygEditor = ref(null);

function execCommand(command) {
    document.execCommand(command, false, null);
    if (wysiwygEditor.value) {
        emit("update:modelValue", wysiwygEditor.value.innerHTML);
    }
}

function onWysiwygInput(event) {
    emit("update:modelValue", event.target.innerHTML);
}

function updateDimension(key, value) {
    const current = props.modelValue || {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0,
        unit: "px",
    };
    emit("update:modelValue", {
        ...current,
        [key]: value === "" ? "" : value,
    });
}

// URL control helper
function updateUrl(key, value) {
    const current =
        typeof props.modelValue === "object"
            ? props.modelValue
            : { url: props.modelValue || "" };
    emit("update:modelValue", {
        ...current,
        [key]: value,
    });
}

// Typography group control helper
function updateTypography(key, value) {
    const current = props.modelValue || {};
    emit("update:modelValue", {
        ...current,
        [key]: value,
    });
}

// Background group control helper
function updateBackground(key, value) {
    const current = props.modelValue || {};
    emit("update:modelValue", {
        ...current,
        [key]: value,
    });
}

// Border group control helpers
function updateBorder(key, value) {
    const current = props.modelValue || {};
    emit("update:modelValue", {
        ...current,
        [key]: value,
    });
}

function updateBorderWidth(side, value) {
    const current = props.modelValue || {};
    const width = current.width || { top: 1, right: 1, bottom: 1, left: 1 };
    emit("update:modelValue", {
        ...current,
        width: {
            ...width,
            [side]: value === "" ? "" : value,
        },
    });
}

function updateBorderRadius(corner, value) {
    const current = props.modelValue || {};
    const radius = current.radius || {
        topLeft: 0,
        topRight: 0,
        bottomRight: 0,
        bottomLeft: 0,
    };
    emit("update:modelValue", {
        ...current,
        radius: {
            ...radius,
            [corner]: value === "" ? "" : value,
        },
    });
}

// Box shadow group control helper
function updateBoxShadow(key, value) {
    const current = props.modelValue || {};
    emit("update:modelValue", {
        ...current,
        [key]: value,
    });
}

// Gaps control helpers
function updateGap(key, value) {
    const current = props.modelValue || { column: 20, row: 20, linked: false };
    const numValue = value === "" ? "" : value;

    if (current.linked) {
        emit("update:modelValue", {
            ...current,
            column: numValue,
            row: numValue,
        });
    } else {
        emit("update:modelValue", {
            ...current,
            [key]: numValue,
        });
    }
}

function toggleGapLink() {
    const current = props.modelValue || { column: 20, row: 20, linked: false };
    const newLinked = !current.linked;

    emit("update:modelValue", {
        ...current,
        linked: newLinked,
        // If linking, sync row to column value
        ...(newLinked ? { row: current.column } : {}),
    });
}

// Size control helper
function updateSize(key, value) {
    const current = props.modelValue || {};
    emit("update:modelValue", {
        ...current,
        [key]: value,
    });
}

// Position control helper
function updatePosition(key, value) {
    const current = props.modelValue || {};
    emit("update:modelValue", {
        ...current,
        [key]: value,
    });
}

// Shape divider control helper
function updateShapeDivider(key, value) {
    const current = props.modelValue || {};
    emit("update:modelValue", {
        ...current,
        [key]: value,
    });
}

// Responsive visibility control helper
function updateResponsiveVisibility(key, value) {
    const current = props.modelValue || {};
    emit("update:modelValue", {
        ...current,
        [key]: value,
    });
}
</script>
