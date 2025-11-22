<div class="p-4 border-b border-gray-200">
    <h3 class="font-semibold text-gray-900">Properties</h3>
</div>

<div class="p-4 space-y-4" x-show="getSelectedElement()">
    <template x-if="getSelectedElement()">
        <div>
            {{-- Common Properties --}}
            <div class="space-y-3">
                {{-- Heading Properties --}}
                <template x-if="getSelectedElement().type === 'heading'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Text</label>
                            <input type="text" x-model="getSelectedElement().props.text" @input="markDirty()" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                            <select x-model="getSelectedElement().props.level" @change="markDirty()" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="h1">H1</option>
                                <option value="h2">H2</option>
                                <option value="h3">H3</option>
                                <option value="h4">H4</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                            <input type="color" x-model="getSelectedElement().props.color" @input="markDirty()" class="w-full h-10 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Font Size</label>
                            <input type="text" x-model="getSelectedElement().props.fontSize" @input="markDirty()" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="32px">
                        </div>
                    </div>
                </template>

                {{-- Paragraph Properties --}}
                <template x-if="getSelectedElement().type === 'paragraph'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Text</label>
                            <textarea x-model="getSelectedElement().props.text" @input="markDirty()" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                            <input type="color" x-model="getSelectedElement().props.color" @input="markDirty()" class="w-full h-10 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Font Size</label>
                            <input type="text" x-model="getSelectedElement().props.fontSize" @input="markDirty()" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="16px">
                        </div>
                    </div>
                </template>

                {{-- Button Properties --}}
                <template x-if="getSelectedElement().type === 'button'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Text</label>
                            <input type="text" x-model="getSelectedElement().props.text" @input="markDirty()" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                            <input type="text" x-model="getSelectedElement().props.url" @input="markDirty()" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="https://">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Background Color</label>
                            <input type="color" x-model="getSelectedElement().props.backgroundColor" @input="markDirty()" class="w-full h-10 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Text Color</label>
                            <input type="color" x-model="getSelectedElement().props.textColor" @input="markDirty()" class="w-full h-10 rounded-md">
                        </div>
                    </div>
                </template>

                {{-- Image Properties --}}
                <template x-if="getSelectedElement().type === 'image'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                            <input type="text" x-model="getSelectedElement().props.src" @input="markDirty()" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="https://">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alt Text</label>
                            <input type="text" x-model="getSelectedElement().props.alt" @input="markDirty()" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Width</label>
                            <input type="text" x-model="getSelectedElement().props.width" @input="markDirty()" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="100%">
                        </div>
                    </div>
                </template>

                {{-- Video Properties --}}
                <template x-if="getSelectedElement().type === 'video'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Video URL</label>
                            <input type="text" x-model="getSelectedElement().props.src" @input="markDirty()" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="YouTube or Vimeo URL">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                            <select x-model="getSelectedElement().props.provider" @change="markDirty()" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="youtube">YouTube</option>
                                <option value="vimeo">Vimeo</option>
                            </select>
                        </div>
                    </div>
                </template>

                {{-- Spacer Properties --}}
                <template x-if="getSelectedElement().type === 'spacer'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Height</label>
                            <input type="text" x-model="getSelectedElement().props.height" @input="markDirty()" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="40px">
                        </div>
                    </div>
                </template>

                {{-- Divider Properties --}}
                <template x-if="getSelectedElement().type === 'divider'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                            <input type="color" x-model="getSelectedElement().props.color" @input="markDirty()" class="w-full h-10 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Thickness</label>
                            <input type="text" x-model="getSelectedElement().props.thickness" @input="markDirty()" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="1px">
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>
