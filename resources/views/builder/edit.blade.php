@extends('layouts.builder')

@section('content')
<div x-data="builderApp()" x-init="init()" class="h-screen flex flex-col bg-gray-100">
    {{-- Page Data --}}
    @php
        $pageData = [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'elements' => $page->content ?? [],
            'settings' => $page->settings ?? [],
            'status' => $page->status,
        ];
    @endphp
    <script id="page-data" type="application/json">{!! json_encode($pageData) !!}</script>

    {{-- Top Toolbar --}}
    <header class="h-14 bg-gray-900 text-white flex items-center justify-between px-4 shrink-0">
        <div class="flex items-center gap-4">
            <a href="{{ route('pages.index') }}" class="flex items-center gap-2 text-gray-300 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="h-6 w-px bg-gray-700"></div>
            <input type="text" x-model="documentTitle" @input="isDirty = true"
                   class="bg-transparent text-white text-sm font-medium focus:outline-none px-2 py-1 rounded"
                   placeholder="Page Title">
        </div>

        <div class="flex items-center gap-2">
            {{-- History --}}
            <button @click="undo()" :disabled="historyIndex <= 0" class="p-2 text-gray-300 hover:text-white disabled:opacity-30" title="Undo">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
            </button>
            <button @click="redo()" :disabled="historyIndex >= history.length - 1" class="p-2 text-gray-300 hover:text-white disabled:opacity-30" title="Redo">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"/></svg>
            </button>
            <div class="h-6 w-px bg-gray-700"></div>

            {{-- Preview modes --}}
            <button @click="previewMode = 'desktop'" :class="previewMode === 'desktop' ? 'text-white' : 'text-gray-400'" class="p-2 hover:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </button>
            <button @click="previewMode = 'tablet'" :class="previewMode === 'tablet' ? 'text-white' : 'text-gray-400'" class="p-2 hover:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </button>
            <button @click="previewMode = 'mobile'" :class="previewMode === 'mobile' ? 'text-white' : 'text-gray-400'" class="p-2 hover:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </button>
            <div class="h-6 w-px bg-gray-700"></div>

            {{-- Status --}}
            <div class="text-xs text-gray-400">
                <span x-show="isDirty" class="text-yellow-400">Unsaved</span>
                <span x-show="lastSaved && !isDirty" x-text="'Saved ' + lastSaved"></span>
                <span x-show="isSaving" class="text-blue-400">Saving...</span>
            </div>
            <div class="h-6 w-px bg-gray-700"></div>

            {{-- Actions --}}
            <button @click="preview()" class="p-2 text-gray-300 hover:text-white"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
            <button @click="save()" :disabled="isSaving" class="px-3 py-1.5 text-sm bg-gray-700 text-white rounded hover:bg-gray-600 disabled:opacity-50">Save</button>
            <button @click="publish()" :disabled="isSaving" class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50">Publish</button>

            {{-- More Menu --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="p-2 text-gray-300 hover:text-white">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border py-1 z-50">
                    <button @click="exportJSON(); open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export JSON</button>
                    <label class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 block cursor-pointer">Import JSON<input type="file" accept=".json" @change="importJSON($event); open = false" class="hidden"></label>
                    <hr class="my-1">
                    <button @click="showDeleteConfirm = true; open = false" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete Page</button>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <div class="flex-1 flex overflow-hidden">
        {{-- Left Panel --}}
        <aside class="w-72 bg-white border-r border-gray-200 flex flex-col shrink-0">
            <div class="flex border-b border-gray-200">
                <button @click="leftPanel = 'widgets'" :class="leftPanel === 'widgets' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'" class="flex-1 py-3 text-sm font-medium border-b-2">Widgets</button>
                <button @click="leftPanel = 'navigator'" :class="leftPanel === 'navigator' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'" class="flex-1 py-3 text-sm font-medium border-b-2">Navigator</button>
            </div>

            {{-- Widgets --}}
            <div x-show="leftPanel === 'widgets'" class="flex-1 overflow-y-auto p-4">
                <div class="mb-4">
                    <input type="text" x-model="widgetSearch" placeholder="Search..." class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Structure</div>
                    <div class="grid grid-cols-3 gap-2">
                        <button @click="addSection('100')" class="p-2 border rounded hover:bg-gray-50"><div class="h-6 bg-gray-200 rounded"></div></button>
                        <button @click="addSection('50-50')" class="p-2 border rounded hover:bg-gray-50"><div class="h-6 flex gap-0.5"><div class="flex-1 bg-gray-200 rounded"></div><div class="flex-1 bg-gray-200 rounded"></div></div></button>
                        <button @click="addSection('33-33-33')" class="p-2 border rounded hover:bg-gray-50"><div class="h-6 flex gap-0.5"><div class="flex-1 bg-gray-200 rounded"></div><div class="flex-1 bg-gray-200 rounded"></div><div class="flex-1 bg-gray-200 rounded"></div></div></button>
                    </div>
                </div>
                <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Basic</div>
                <div data-widget-palette class="grid grid-cols-3 gap-2">
                    <template x-for="(widget, key) in filteredWidgets" :key="key">
                        <div :data-widget-type="key" draggable="true" @click="clickAddWidget(key)" class="p-3 border rounded text-center cursor-move hover:border-indigo-300 hover:bg-indigo-50">
                            <div class="text-lg mb-1" x-text="widget.icon"></div>
                            <div class="text-xs text-gray-600" x-text="widget.title"></div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Navigator --}}
            <div x-show="leftPanel === 'navigator'" class="flex-1 overflow-y-auto p-4" x-cloak>
                <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Structure</div>
                <template x-for="section in content" :key="section.id">
                    <div class="border rounded mb-1">
                        <div @click="selectElement(section.id, 'section')" :class="selectedElement === section.id ? 'bg-indigo-50 text-indigo-700' : 'hover:bg-gray-50'" class="px-3 py-2 text-sm cursor-pointer flex justify-between">
                            <span>Section</span>
                            <button @click.stop="deleteElement(section.id)" class="text-gray-400 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <div class="pl-4">
                            <template x-for="column in section.elements" :key="column.id">
                                <div class="border-l">
                                    <div @click="selectElement(column.id, 'column')" :class="selectedElement === column.id ? 'bg-indigo-50 text-indigo-700' : 'hover:bg-gray-50'" class="px-3 py-1.5 text-xs cursor-pointer">Column (<span x-text="column.settings._column_size || 100"></span>%)</div>
                                    <template x-for="widget in column.elements" :key="widget.id">
                                        <div @click="selectElement(widget.id, 'widget')" :class="selectedElement === widget.id ? 'bg-indigo-50 text-indigo-700' : 'hover:bg-gray-50'" class="px-3 py-1.5 text-xs cursor-pointer pl-6 flex justify-between">
                                            <span x-text="widget.widgetType"></span>
                                            <button @click.stop="deleteElement(widget.id)" class="text-gray-400 hover:text-red-500"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </aside>

        {{-- Canvas --}}
        <main class="flex-1 bg-gray-200 overflow-auto p-8">
            <div :class="previewMode === 'desktop' ? 'max-w-5xl' : previewMode === 'tablet' ? 'max-w-lg' : 'max-w-sm'" class="mx-auto transition-all">
                <div data-sections class="bg-white min-h-[600px] shadow-lg">
                    <div x-show="content.length === 0" class="flex flex-col items-center justify-center h-96 text-gray-400">
                        <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        <p class="text-lg mb-2">Start building</p>
                        <p class="text-sm">Add a structure or drag widgets</p>
                    </div>
                    <template x-for="section in content" :key="section.id">
                        <div class="section-container relative group" :data-element-id="section.id" data-element-type="section" @click.stop="selectElement(section.id, 'section')" :class="selectedElement === section.id ? 'ring-2 ring-indigo-500' : ''">
                            <div class="section-handle absolute -left-10 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 cursor-move">
                                <div class="p-2 bg-indigo-600 text-white rounded"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg></div>
                            </div>
                            <div data-columns class="flex" :style="`min-height: ${section.settings.min_height || 100}px`">
                                <template x-for="column in section.elements" :key="column.id">
                                    <div class="column-container relative group/col border border-dashed border-transparent hover:border-gray-300" :style="`width: ${column.settings._column_size || 100}%`" :data-element-id="column.id" data-element-type="column" @click.stop="selectElement(column.id, 'column')" :class="selectedElement === column.id ? 'ring-2 ring-blue-500' : ''">
                                        <div data-widgets :data-column-id="column.id" class="min-h-[100px] p-4">
                                            <div x-show="column.elements.length === 0" class="h-24 border-2 border-dashed border-gray-300 rounded flex items-center justify-center text-gray-400 text-sm">+ Widget</div>
                                            <template x-for="widget in column.elements" :key="widget.id">
                                                <div class="widget-container relative group/widget mb-4" :data-element-id="widget.id" data-element-type="widget" @click.stop="selectElement(widget.id, 'widget')" :class="selectedElement === widget.id ? 'ring-2 ring-green-500' : ''">
                                                    <div class="widget-handle absolute -top-6 left-1/2 -translate-x-1/2 opacity-0 group-hover/widget:opacity-100">
                                                        <div class="flex items-center gap-1 bg-green-600 text-white text-xs px-2 py-1 rounded shadow">
                                                            <span x-text="widget.widgetType" class="capitalize"></span>
                                                            <button @click.stop="duplicateElement(widget.id)" class="hover:text-green-200"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></button>
                                                            <button @click.stop="deleteElement(widget.id)" class="hover:text-red-300"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                                        </div>
                                                    </div>
                                                    <div x-html="renderElement(widget)"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </main>

        {{-- Right Panel --}}
        <aside class="w-80 bg-white border-l border-gray-200 flex flex-col shrink-0" x-show="selectedElement" x-cloak>
            <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
                <h3 class="font-semibold text-gray-900 capitalize" x-text="selectedType + ' Settings'"></h3>
                <button @click="selectedElement = null" class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="flex border-b">
                <button @click="activeTab = 'content'" :class="activeTab === 'content' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'" class="flex-1 py-3 text-sm font-medium border-b-2">Content</button>
                <button @click="activeTab = 'style'" :class="activeTab === 'style' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'" class="flex-1 py-3 text-sm font-medium border-b-2">Style</button>
                <button @click="activeTab = 'advanced'" :class="activeTab === 'advanced' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'" class="flex-1 py-3 text-sm font-medium border-b-2">Advanced</button>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <template x-for="control in getControls()" :key="control.name">
                    <div class="mb-4">
                        <template x-if="control.type === 'text'">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1.5" x-text="control.label"></label><input type="text" :value="getSetting(control.name) ?? control.default ?? ''" @input="updateSetting(control.name, $event.target.value)" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md"></div>
                        </template>
                        <template x-if="control.type === 'textarea'">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1.5" x-text="control.label"></label><textarea :rows="control.rows || 4" @input="updateSetting(control.name, $event.target.value)" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md" x-text="getSetting(control.name) ?? control.default ?? ''"></textarea></div>
                        </template>
                        <template x-if="control.type === 'wysiwyg'">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1.5" x-text="control.label"></label><div class="border border-gray-300 rounded-md overflow-hidden"><div class="flex gap-1 p-2 bg-gray-50 border-b border-gray-300"><button type="button" @click="document.execCommand('bold')" class="px-2 py-1 text-sm font-bold hover:bg-gray-200 rounded">B</button><button type="button" @click="document.execCommand('italic')" class="px-2 py-1 text-sm italic hover:bg-gray-200 rounded">I</button><button type="button" @click="document.execCommand('underline')" class="px-2 py-1 text-sm underline hover:bg-gray-200 rounded">U</button><button type="button" @click="document.execCommand('insertUnorderedList')" class="px-2 py-1 text-sm hover:bg-gray-200 rounded">•</button><button type="button" @click="document.execCommand('insertOrderedList')" class="px-2 py-1 text-sm hover:bg-gray-200 rounded">1.</button></div><div contenteditable="true" @input="updateSetting(control.name, $event.target.innerHTML)" x-html="getSetting(control.name) ?? control.default ?? ''" class="p-3 min-h-[120px] focus:outline-none prose prose-sm max-w-none"></div></div></div>
                        </template>
                        <template x-if="control.type === 'number'">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1.5" x-text="control.label"></label><div class="flex items-center gap-2"><input type="number" :value="getSetting(control.name) ?? control.default ?? 0" @input="updateSetting(control.name, parseFloat($event.target.value))" :min="control.min" :max="control.max" :step="control.step || 1" class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md"><span x-show="control.unit" class="text-sm text-gray-500" x-text="control.unit"></span></div></div>
                        </template>
                        <template x-if="control.type === 'slider'">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1.5" x-text="control.label"></label><div class="flex items-center gap-3"><input type="range" :value="getSetting(control.name) ?? control.default ?? 0" @input="updateSetting(control.name, parseFloat($event.target.value))" :min="control.min || 0" :max="control.max || 100" :step="control.step || 1" class="flex-1"><span class="text-sm text-gray-600 w-12 text-right" x-text="(getSetting(control.name) ?? control.default ?? 0) + (control.unit || '')"></span></div></div>
                        </template>
                        <template x-if="control.type === 'select'">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1.5" x-text="control.label"></label><select @change="updateSetting(control.name, $event.target.value)" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md"><template x-for="(label, value) in control.options" :key="value"><option :value="value" :selected="(getSetting(control.name) ?? control.default) === value" x-text="label"></option></template></select></div>
                        </template>
                        <template x-if="control.type === 'switcher'">
                            <div class="flex items-center justify-between"><label class="text-xs font-medium text-gray-600" x-text="control.label"></label><button @click="updateSetting(control.name, !getSetting(control.name))" :class="getSetting(control.name) ? 'bg-indigo-600' : 'bg-gray-200'" class="relative w-10 h-5 rounded-full"><span :class="getSetting(control.name) ? 'translate-x-5' : 'translate-x-0.5'" class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform"></span></button></div>
                        </template>
                        <template x-if="control.type === 'choose'">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1.5" x-text="control.label"></label><div class="flex gap-1"><template x-for="(opt, key) in control.options" :key="key"><button @click="updateSetting(control.name, key)" :class="(getSetting(control.name) ?? control.default) === key ? 'bg-indigo-100 text-indigo-700 border-indigo-300' : 'bg-white text-gray-600 border-gray-300'" class="flex-1 py-2 border rounded text-sm" x-text="opt.icon"></button></template></div></div>
                        </template>
                        <template x-if="control.type === 'color'">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1.5" x-text="control.label"></label><div class="flex items-center gap-2"><input type="color" :value="getSetting(control.name) ?? control.default ?? '#000000'" @input="updateSetting(control.name, $event.target.value)" class="w-10 h-10 rounded border cursor-pointer"><input type="text" :value="getSetting(control.name) ?? control.default ?? '#000000'" @input="updateSetting(control.name, $event.target.value)" class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md"></div></div>
                        </template>
                        <template x-if="control.type === 'media'">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1.5" x-text="control.label"></label><div x-show="getSetting(control.name)" class="mb-2 relative"><img :src="getSetting(control.name)" class="w-full h-32 object-cover rounded border"><button @click="updateSetting(control.name, '')" class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div><button @click="openMediaLibrary(control.name)" class="w-full mb-2 px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 flex items-center justify-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Select from Media</button><input type="url" :value="getSetting(control.name) ?? ''" @input="updateSetting(control.name, $event.target.value)" placeholder="Or enter URL" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md"></div>
                        </template>
                        <template x-if="control.type === 'dimensions'">
                            <div><div class="flex items-center justify-between mb-1.5"><label class="text-xs font-medium text-gray-600" x-text="control.label"></label><button @click="toggleLinked(control.name)" :class="isLinked(control.name) ? 'text-indigo-600' : 'text-gray-400'" class="p-1 hover:bg-gray-100 rounded"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg></button></div><div class="grid grid-cols-4 gap-2"><div><label class="block text-xs text-gray-500 mb-1 text-center">T</label><input type="number" :value="getDimension(control.name, 'top')" @input="updateDimension(control.name, 'top', $event.target.value)" class="w-full px-2 py-1.5 text-sm text-center border border-gray-300 rounded-md"></div><div><label class="block text-xs text-gray-500 mb-1 text-center">R</label><input type="number" :value="getDimension(control.name, 'right')" @input="updateDimension(control.name, 'right', $event.target.value)" class="w-full px-2 py-1.5 text-sm text-center border border-gray-300 rounded-md"></div><div><label class="block text-xs text-gray-500 mb-1 text-center">B</label><input type="number" :value="getDimension(control.name, 'bottom')" @input="updateDimension(control.name, 'bottom', $event.target.value)" class="w-full px-2 py-1.5 text-sm text-center border border-gray-300 rounded-md"></div><div><label class="block text-xs text-gray-500 mb-1 text-center">L</label><input type="number" :value="getDimension(control.name, 'left')" @input="updateDimension(control.name, 'left', $event.target.value)" class="w-full px-2 py-1.5 text-sm text-center border border-gray-300 rounded-md"></div></div></div>
                        </template>
                        <template x-if="control.type === 'typography'">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1.5" x-text="control.label"></label><div class="space-y-2"><select @change="updateTypography(control.name, 'family', $event.target.value)" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md"><option value="">Default</option><option value="Inter">Inter</option><option value="Roboto">Roboto</option><option value="Open Sans">Open Sans</option><option value="Montserrat">Montserrat</option><option value="Poppins">Poppins</option></select><div class="grid grid-cols-2 gap-2"><input type="number" placeholder="Size" :value="getTypography(control.name, 'size')" @input="updateTypography(control.name, 'size', $event.target.value)" class="px-3 py-2 text-sm border border-gray-300 rounded-md"><select @change="updateTypography(control.name, 'weight', $event.target.value)" class="px-3 py-2 text-sm border border-gray-300 rounded-md"><option value="300">Light</option><option value="400">Normal</option><option value="500">Medium</option><option value="600">Semi</option><option value="700">Bold</option></select></div></div></div>
                        </template>
                        <template x-if="control.type === 'background'">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1.5" x-text="control.label"></label><div class="space-y-2"><div class="flex gap-1"><button @click="setBgType(control.name, 'classic')" :class="getBgType(control.name) === 'classic' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'" class="flex-1 py-1.5 text-xs rounded">Classic</button><button @click="setBgType(control.name, 'gradient')" :class="getBgType(control.name) === 'gradient' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'" class="flex-1 py-1.5 text-xs rounded">Gradient</button></div><div x-show="getBgType(control.name) === 'classic'"><div class="flex items-center gap-2 mb-2"><input type="color" :value="getBgColor(control.name)" @input="setBgColor(control.name, $event.target.value)" class="w-8 h-8 rounded border cursor-pointer"><input type="text" :value="getBgColor(control.name)" @input="setBgColor(control.name, $event.target.value)" class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-md"></div><input type="url" :value="getBgImage(control.name)" @input="setBgImage(control.name, $event.target.value)" placeholder="Image URL" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md"></div></div></div>
                        </template>
                        <template x-if="control.type === 'border'">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1.5" x-text="control.label"></label><div class="flex gap-2"><select @change="updateBorder(control.name, 'style', $event.target.value)" class="flex-1 px-2 py-1.5 text-sm border border-gray-300 rounded-md"><option value="">None</option><option value="solid">Solid</option><option value="dashed">Dashed</option><option value="dotted">Dotted</option></select><input type="number" placeholder="W" :value="getBorderWidth(control.name)" @input="updateBorder(control.name, 'width', $event.target.value)" class="w-14 px-2 py-1.5 text-sm border border-gray-300 rounded-md"><input type="color" :value="getBorderColor(control.name)" @input="updateBorder(control.name, 'color', $event.target.value)" class="w-8 h-8 rounded border cursor-pointer"></div></div>
                        </template>
                    </div>
                </template>
                <div x-show="getControls().length === 0" class="text-center text-gray-400 py-8"><p class="text-sm">No controls</p></div>
            </div>
        </aside>
    </div>

    {{-- Context Menu --}}
    <div x-show="contextMenu.show" x-cloak :style="`position: fixed; left: ${contextMenu.x}px; top: ${contextMenu.y}px;`" class="bg-white rounded-md shadow-lg border py-1 z-50 min-w-[160px]">
        <button @click="copy(); contextMenu.show = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Copy</button>
        <button @click="cut(); contextMenu.show = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cut</button>
        <button @click="paste(); contextMenu.show = false" :disabled="!clipboard" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 disabled:opacity-50">Paste</button>
        <hr class="my-1">
        <button @click="duplicateElement(contextMenu.element); contextMenu.show = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Duplicate</button>
        <button @click="deleteElement(contextMenu.element); contextMenu.show = false" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete</button>
    </div>

    {{-- Delete Modal --}}
    <div x-show="showDeleteConfirm" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteConfirm = false"></div>
            <div class="relative bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center"><svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
                    <div class="ml-4"><h3 class="text-lg font-medium text-gray-900">Delete Page</h3><p class="mt-2 text-sm text-gray-500">Are you sure? This cannot be undone.</p></div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="showDeleteConfirm = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                    <button @click="deletePage()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">Delete</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Media Library Modal --}}
    <div x-show="showMediaLibrary" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showMediaLibrary = false"></div>
            <div class="relative bg-white rounded-lg max-w-4xl w-full shadow-xl flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Media Library</h3>
                    <button @click="showMediaLibrary = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-4 border-b">
                    <label class="flex items-center justify-center w-full py-6 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition-colors">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <p class="mt-2 text-sm text-gray-600">Click to upload or drag and drop</p>
                            <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF, SVG up to 10MB</p>
                        </div>
                        <input type="file" accept="image/*,video/*" @change="uploadMedia($event)" class="hidden">
                    </label>
                </div>
                <div class="flex-1 overflow-y-auto p-4">
                    <div x-show="mediaLoading" class="flex items-center justify-center py-12">
                        <svg class="animate-spin h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                    <div x-show="!mediaLoading && mediaItems.length === 0" class="text-center py-12 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="mt-2">No media files yet</p>
                        <p class="text-sm">Upload images to see them here</p>
                    </div>
                    <div x-show="!mediaLoading && mediaItems.length > 0" class="grid grid-cols-4 gap-4">
                        <template x-for="item in mediaItems" :key="item.id || item.url">
                            <div @click="selectMediaItem(item.url || '/storage/' + item.path)" class="aspect-square rounded-lg overflow-hidden cursor-pointer hover:ring-2 hover:ring-indigo-500 bg-gray-100">
                                <img :src="item.url || '/storage/' + item.path" :alt="item.name || item.filename" class="w-full h-full object-cover">
                            </div>
                        </template>
                    </div>
                </div>
                <div class="px-6 py-4 border-t flex justify-end">
                    <button @click="showMediaLibrary = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
