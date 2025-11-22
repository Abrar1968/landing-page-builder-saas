# AlpineJS Components Documentation

## Table of Contents
1. [UI Components](#ui-components)
2. [Form Components](#form-components)
3. [Navigation Components](#navigation-components)
4. [Dashboard Components](#dashboard-components)
5. [Builder Components](#builder-components)

---

## UI Components

### Modal Component

```javascript
// resources/js/components/modal.js
export default function modal() {
    return {
        open: false,
        title: '',
        content: '',
        size: 'md', // sm, md, lg, xl

        show(options = {}) {
            this.title = options.title || '';
            this.content = options.content || '';
            this.size = options.size || 'md';
            this.open = true;
            document.body.classList.add('overflow-hidden');
        },

        close() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        },

        handleEscape(e) {
            if (e.key === 'Escape' && this.open) {
                this.close();
            }
        },

        get sizeClasses() {
            return {
                'sm': 'max-w-sm',
                'md': 'max-w-md',
                'lg': 'max-w-lg',
                'xl': 'max-w-xl',
                '2xl': 'max-w-2xl'
            }[this.size];
        }
    }
}
```

```html
<!-- resources/views/components/modal.blade.php -->
<div x-data="modal()"
     x-on:open-modal.window="show($event.detail)"
     x-on:keydown.escape.window="handleEscape"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true">

    <!-- Backdrop -->
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
         @click="close"></div>

    <!-- Modal Panel -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             :class="sizeClasses"
             class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:p-6">

            <!-- Close Button -->
            <div class="absolute right-0 top-0 pr-4 pt-4">
                <button @click="close" type="button" class="rounded-md bg-white text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="mt-3 text-center sm:mt-0 sm:text-left">
                <h3 x-text="title" class="text-lg font-semibold leading-6 text-gray-900" id="modal-title"></h3>
                <div class="mt-2" x-html="content"></div>
            </div>

            <!-- Slot for custom content -->
            {{ $slot ?? '' }}
        </div>
    </div>
</div>
```

### Dropdown Component

```javascript
// resources/js/components/dropdown.js
export default function dropdown() {
    return {
        open: false,
        selected: null,
        options: [],
        searchable: false,
        searchQuery: '',

        init() {
            this.$watch('searchQuery', () => this.filterOptions());
        },

        toggle() {
            this.open = !this.open;
            if (this.open && this.searchable) {
                this.$nextTick(() => this.$refs.search?.focus());
            }
        },

        close() {
            this.open = false;
            this.searchQuery = '';
        },

        select(option) {
            this.selected = option;
            this.$dispatch('dropdown-selected', option);
            this.close();
        },

        get filteredOptions() {
            if (!this.searchQuery) return this.options;
            return this.options.filter(option =>
                option.label.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        },

        get displayValue() {
            return this.selected?.label || 'Select an option';
        }
    }
}
```

```html
<!-- resources/views/components/dropdown.blade.php -->
@props(['options' => [], 'searchable' => false, 'placeholder' => 'Select an option'])

<div x-data="{
        ...dropdown(),
        options: {{ json_encode($options) }},
        searchable: {{ $searchable ? 'true' : 'false' }}
     }"
     @click.away="close"
     class="relative">

    <!-- Trigger -->
    <button @click="toggle"
            type="button"
            class="relative w-full cursor-pointer rounded-md bg-white py-2 pl-3 pr-10 text-left shadow-sm ring-1 ring-inset ring-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-600 sm:text-sm">
        <span x-text="displayValue" class="block truncate"></span>
        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3z" clip-rule="evenodd" />
            </svg>
        </span>
    </button>

    <!-- Dropdown Panel -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">

        <!-- Search Input -->
        <template x-if="searchable">
            <div class="px-2 py-1">
                <input x-ref="search"
                       x-model="searchQuery"
                       type="text"
                       placeholder="Search..."
                       class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </template>

        <!-- Options -->
        <template x-for="option in filteredOptions" :key="option.value">
            <div @click="select(option)"
                 :class="{ 'bg-indigo-600 text-white': selected?.value === option.value, 'text-gray-900': selected?.value !== option.value }"
                 class="relative cursor-pointer select-none py-2 pl-3 pr-9 hover:bg-indigo-50">
                <span x-text="option.label" class="block truncate"></span>
            </div>
        </template>
    </div>
</div>
```

### Toast Component

```javascript
// resources/js/components/toast.js
export default function toast() {
    return {
        toasts: [],

        show(options) {
            const id = Date.now();
            const toast = {
                id,
                message: options.message || '',
                type: options.type || 'info', // success, error, warning, info
                duration: options.duration || 5000
            };

            this.toasts.push(toast);

            if (toast.duration > 0) {
                setTimeout(() => this.remove(id), toast.duration);
            }
        },

        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },

        success(message, duration = 5000) {
            this.show({ message, type: 'success', duration });
        },

        error(message, duration = 5000) {
            this.show({ message, type: 'error', duration });
        },

        warning(message, duration = 5000) {
            this.show({ message, type: 'warning', duration });
        },

        info(message, duration = 5000) {
            this.show({ message, type: 'info', duration });
        },

        getTypeClasses(type) {
            return {
                'success': 'bg-green-50 text-green-800 border-green-200',
                'error': 'bg-red-50 text-red-800 border-red-200',
                'warning': 'bg-yellow-50 text-yellow-800 border-yellow-200',
                'info': 'bg-blue-50 text-blue-800 border-blue-200'
            }[type];
        },

        getIcon(type) {
            return {
                'success': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'error': 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                'warning': 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                'info': 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
            }[type];
        }
    }
}
```

```html
<!-- resources/views/components/toast.blade.php -->
<div x-data="toast()"
     @toast.window="show($event.detail)"
     class="fixed top-4 right-4 z-50 space-y-2">

    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="true"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             :class="getTypeClasses(toast.type)"
             class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg border shadow-lg">

            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIcon(toast.type)" />
                        </svg>
                    </div>
                    <div class="ml-3 w-0 flex-1">
                        <p x-text="toast.message" class="text-sm font-medium"></p>
                    </div>
                    <div class="ml-4 flex flex-shrink-0">
                        <button @click="remove(toast.id)" class="inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
```

### Tabs Component

```javascript
// resources/js/components/tabs.js
export default function tabs(defaultTab = '') {
    return {
        activeTab: defaultTab,
        tabs: [],

        init() {
            if (!this.activeTab && this.tabs.length > 0) {
                this.activeTab = this.tabs[0].id;
            }
        },

        registerTab(tab) {
            this.tabs.push(tab);
            if (!this.activeTab) {
                this.activeTab = tab.id;
            }
        },

        selectTab(tabId) {
            this.activeTab = tabId;
            this.$dispatch('tab-changed', tabId);
        },

        isActive(tabId) {
            return this.activeTab === tabId;
        }
    }
}
```

```html
<!-- resources/views/components/tabs.blade.php -->
@props(['tabs' => [], 'default' => ''])

<div x-data="{
        ...tabs('{{ $default }}'),
        tabs: {{ json_encode($tabs) }}
     }"
     x-init="init()"
     class="w-full">

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <template x-for="tab in tabs" :key="tab.id">
                <button @click="selectTab(tab.id)"
                        :class="{
                            'border-indigo-500 text-indigo-600': isActive(tab.id),
                            'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700': !isActive(tab.id)
                        }"
                        class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    <span x-text="tab.label"></span>
                </button>
            </template>
        </nav>
    </div>

    <!-- Tab Panels -->
    <div class="mt-4">
        {{ $slot }}
    </div>
</div>

<!-- Tab Panel Component -->
<!-- resources/views/components/tab-panel.blade.php -->
@props(['id'])

<div x-show="activeTab === '{{ $id }}'"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100">
    {{ $slot }}
</div>
```

### Accordion Component

```javascript
// resources/js/components/accordion.js
export default function accordion(allowMultiple = false) {
    return {
        openItems: [],
        allowMultiple,

        toggle(itemId) {
            if (this.isOpen(itemId)) {
                this.openItems = this.openItems.filter(id => id !== itemId);
            } else {
                if (this.allowMultiple) {
                    this.openItems.push(itemId);
                } else {
                    this.openItems = [itemId];
                }
            }
        },

        isOpen(itemId) {
            return this.openItems.includes(itemId);
        },

        openAll(itemIds) {
            this.openItems = [...itemIds];
        },

        closeAll() {
            this.openItems = [];
        }
    }
}
```

```html
<!-- resources/views/components/accordion.blade.php -->
@props(['items' => [], 'allowMultiple' => false])

<div x-data="accordion({{ $allowMultiple ? 'true' : 'false' }})"
     class="divide-y divide-gray-200 rounded-lg border border-gray-200">

    @foreach($items as $index => $item)
    <div class="accordion-item">
        <!-- Header -->
        <button @click="toggle('{{ $item['id'] }}')"
                class="flex w-full items-center justify-between px-4 py-4 text-left hover:bg-gray-50">
            <span class="text-sm font-medium text-gray-900">{{ $item['title'] }}</span>
            <svg :class="{ 'rotate-180': isOpen('{{ $item['id'] }}') }"
                 class="h-5 w-5 text-gray-500 transition-transform duration-200"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Content -->
        <div x-show="isOpen('{{ $item['id'] }}')"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="px-4 pb-4">
            <p class="text-sm text-gray-600">{{ $item['content'] }}</p>
        </div>
    </div>
    @endforeach
</div>
```

---

## Form Components

### Form Validation Component

```javascript
// resources/js/components/form-validation.js
export default function formValidation(rules = {}) {
    return {
        formData: {},
        errors: {},
        rules,
        touched: {},
        isSubmitting: false,

        init() {
            // Initialize form data from rules
            Object.keys(this.rules).forEach(field => {
                this.formData[field] = '';
                this.errors[field] = '';
                this.touched[field] = false;
            });
        },

        validate(field = null) {
            if (field) {
                return this.validateField(field);
            }

            let isValid = true;
            Object.keys(this.rules).forEach(f => {
                if (!this.validateField(f)) {
                    isValid = false;
                }
            });
            return isValid;
        },

        validateField(field) {
            const value = this.formData[field];
            const fieldRules = this.rules[field] || [];
            this.errors[field] = '';

            for (const rule of fieldRules) {
                const error = this.applyRule(rule, value, field);
                if (error) {
                    this.errors[field] = error;
                    return false;
                }
            }
            return true;
        },

        applyRule(rule, value, field) {
            if (rule === 'required' && (!value || value.trim() === '')) {
                return `${this.formatFieldName(field)} is required`;
            }

            if (rule === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                return 'Please enter a valid email address';
            }

            if (rule.startsWith('min:')) {
                const min = parseInt(rule.split(':')[1]);
                if (value && value.length < min) {
                    return `${this.formatFieldName(field)} must be at least ${min} characters`;
                }
            }

            if (rule.startsWith('max:')) {
                const max = parseInt(rule.split(':')[1]);
                if (value && value.length > max) {
                    return `${this.formatFieldName(field)} must not exceed ${max} characters`;
                }
            }

            if (rule.startsWith('matches:')) {
                const otherField = rule.split(':')[1];
                if (value !== this.formData[otherField]) {
                    return `${this.formatFieldName(field)} must match ${this.formatFieldName(otherField)}`;
                }
            }

            if (rule === 'numeric' && value && isNaN(value)) {
                return `${this.formatFieldName(field)} must be a number`;
            }

            if (rule === 'url' && value && !/^https?:\/\/.+/.test(value)) {
                return 'Please enter a valid URL';
            }

            return null;
        },

        formatFieldName(field) {
            return field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },

        onBlur(field) {
            this.touched[field] = true;
            this.validateField(field);
        },

        async submit(callback) {
            // Mark all fields as touched
            Object.keys(this.touched).forEach(f => this.touched[f] = true);

            if (!this.validate()) {
                return;
            }

            this.isSubmitting = true;

            try {
                await callback(this.formData);
            } catch (error) {
                if (error.response?.data?.errors) {
                    Object.assign(this.errors, error.response.data.errors);
                }
            } finally {
                this.isSubmitting = false;
            }
        },

        reset() {
            Object.keys(this.formData).forEach(field => {
                this.formData[field] = '';
                this.errors[field] = '';
                this.touched[field] = false;
            });
        }
    }
}
```

```html
<!-- resources/views/components/form.blade.php -->
@props(['action' => '', 'method' => 'POST', 'rules' => []])

<form x-data="formValidation({{ json_encode($rules) }})"
      x-init="init()"
      @submit.prevent="submit(async (data) => {
          const response = await fetch('{{ $action }}', {
              method: '{{ $method }}',
              headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
              },
              body: JSON.stringify(data)
          });
          if (response.ok) {
              $dispatch('toast', { message: 'Form submitted successfully!', type: 'success' });
              reset();
          }
      })"
      {{ $attributes->merge(['class' => 'space-y-6']) }}>

    {{ $slot }}

    <!-- Submit Button -->
    <div>
        <button type="submit"
                :disabled="isSubmitting"
                class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50">
            <span x-show="!isSubmitting">Submit</span>
            <span x-show="isSubmitting" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            </span>
        </button>
    </div>
</form>

<!-- Form Input Component -->
<!-- resources/views/components/form-input.blade.php -->
@props(['name', 'label', 'type' => 'text', 'placeholder' => ''])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium leading-6 text-gray-900">{{ $label }}</label>
    <div class="mt-2">
        <input type="{{ $type }}"
               id="{{ $name }}"
               x-model="formData.{{ $name }}"
               @blur="onBlur('{{ $name }}')"
               :class="{ 'ring-red-500': errors.{{ $name }} && touched.{{ $name }} }"
               placeholder="{{ $placeholder }}"
               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
    </div>
    <p x-show="errors.{{ $name }} && touched.{{ $name }}"
       x-text="errors.{{ $name }}"
       class="mt-2 text-sm text-red-600"></p>
</div>
```

### File Upload Component

```javascript
// resources/js/components/file-upload.js
export default function fileUpload(options = {}) {
    return {
        files: [],
        isDragging: false,
        isUploading: false,
        uploadProgress: 0,
        maxFiles: options.maxFiles || 5,
        maxSize: options.maxSize || 5 * 1024 * 1024, // 5MB
        accept: options.accept || '*',
        multiple: options.multiple !== false,
        uploadUrl: options.uploadUrl || '/api/upload',

        handleDrop(e) {
            this.isDragging = false;
            const droppedFiles = Array.from(e.dataTransfer.files);
            this.addFiles(droppedFiles);
        },

        handleSelect(e) {
            const selectedFiles = Array.from(e.target.files);
            this.addFiles(selectedFiles);
            e.target.value = '';
        },

        addFiles(newFiles) {
            for (const file of newFiles) {
                if (this.files.length >= this.maxFiles) {
                    this.$dispatch('toast', {
                        message: `Maximum ${this.maxFiles} files allowed`,
                        type: 'warning'
                    });
                    break;
                }

                if (file.size > this.maxSize) {
                    this.$dispatch('toast', {
                        message: `${file.name} exceeds maximum size of ${this.formatSize(this.maxSize)}`,
                        type: 'error'
                    });
                    continue;
                }

                this.files.push({
                    id: Date.now() + Math.random(),
                    file,
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    preview: this.isImage(file) ? URL.createObjectURL(file) : null,
                    progress: 0,
                    uploaded: false,
                    error: null
                });
            }
        },

        removeFile(fileId) {
            const index = this.files.findIndex(f => f.id === fileId);
            if (index > -1) {
                if (this.files[index].preview) {
                    URL.revokeObjectURL(this.files[index].preview);
                }
                this.files.splice(index, 1);
            }
        },

        async uploadFiles() {
            this.isUploading = true;

            for (const fileData of this.files) {
                if (fileData.uploaded) continue;

                const formData = new FormData();
                formData.append('file', fileData.file);

                try {
                    const response = await fetch(this.uploadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: formData
                    });

                    if (response.ok) {
                        fileData.uploaded = true;
                        fileData.progress = 100;
                    } else {
                        fileData.error = 'Upload failed';
                    }
                } catch (error) {
                    fileData.error = error.message;
                }
            }

            this.isUploading = false;
            this.$dispatch('files-uploaded', this.files.filter(f => f.uploaded));
        },

        isImage(file) {
            return file.type.startsWith('image/');
        },

        formatSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        clearAll() {
            this.files.forEach(f => {
                if (f.preview) URL.revokeObjectURL(f.preview);
            });
            this.files = [];
        }
    }
}
```

```html
<!-- resources/views/components/file-upload.blade.php -->
@props([
    'maxFiles' => 5,
    'maxSize' => 5242880,
    'accept' => '*',
    'multiple' => true,
    'uploadUrl' => '/api/upload'
])

<div x-data="fileUpload({
        maxFiles: {{ $maxFiles }},
        maxSize: {{ $maxSize }},
        accept: '{{ $accept }}',
        multiple: {{ $multiple ? 'true' : 'false' }},
        uploadUrl: '{{ $uploadUrl }}'
     })"
     class="w-full">

    <!-- Drop Zone -->
    <div @dragover.prevent="isDragging = true"
         @dragleave.prevent="isDragging = false"
         @drop.prevent="handleDrop"
         :class="{ 'border-indigo-500 bg-indigo-50': isDragging }"
         class="flex justify-center rounded-lg border border-dashed border-gray-300 px-6 py-10 transition-colors">
        <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="mt-4 flex text-sm leading-6 text-gray-600">
                <label class="relative cursor-pointer rounded-md bg-white font-semibold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500">
                    <span>Upload files</span>
                    <input type="file"
                           @change="handleSelect"
                           :accept="accept"
                           :multiple="multiple"
                           class="sr-only">
                </label>
                <p class="pl-1">or drag and drop</p>
            </div>
            <p class="text-xs leading-5 text-gray-600">
                Max <span x-text="maxFiles"></span> files, up to <span x-text="formatSize(maxSize)"></span> each
            </p>
        </div>
    </div>

    <!-- File List -->
    <ul x-show="files.length > 0" class="mt-4 divide-y divide-gray-200 rounded-lg border border-gray-200">
        <template x-for="file in files" :key="file.id">
            <li class="flex items-center justify-between py-3 px-4">
                <div class="flex items-center min-w-0">
                    <!-- Preview -->
                    <template x-if="file.preview">
                        <img :src="file.preview" class="h-10 w-10 flex-shrink-0 rounded object-cover">
                    </template>
                    <template x-if="!file.preview">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded bg-gray-100">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </template>

                    <div class="ml-3 min-w-0">
                        <p x-text="file.name" class="truncate text-sm font-medium text-gray-900"></p>
                        <p x-text="formatSize(file.size)" class="text-xs text-gray-500"></p>
                    </div>
                </div>

                <div class="ml-4 flex items-center space-x-2">
                    <template x-if="file.uploaded">
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    <template x-if="file.error">
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    <button @click="removeFile(file.id)" type="button" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </li>
        </template>
    </ul>

    <!-- Upload Button -->
    <div x-show="files.length > 0" class="mt-4 flex justify-end space-x-2">
        <button @click="clearAll" type="button" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Clear All
        </button>
        <button @click="uploadFiles"
                :disabled="isUploading"
                type="button"
                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">
            <span x-show="!isUploading">Upload Files</span>
            <span x-show="isUploading">Uploading...</span>
        </button>
    </div>
</div>
```

---

## Navigation Components

### Sidebar Component

```javascript
// resources/js/components/sidebar.js
export default function sidebar() {
    return {
        isOpen: true,
        isMobileOpen: false,
        activeItem: '',
        expandedGroups: [],

        init() {
            // Set active item based on current URL
            this.activeItem = window.location.pathname;

            // Handle responsive behavior
            this.handleResize();
            window.addEventListener('resize', () => this.handleResize());
        },

        handleResize() {
            if (window.innerWidth < 1024) {
                this.isOpen = false;
            } else {
                this.isOpen = true;
            }
        },

        toggle() {
            if (window.innerWidth < 1024) {
                this.isMobileOpen = !this.isMobileOpen;
            } else {
                this.isOpen = !this.isOpen;
            }
        },

        toggleGroup(groupId) {
            const index = this.expandedGroups.indexOf(groupId);
            if (index > -1) {
                this.expandedGroups.splice(index, 1);
            } else {
                this.expandedGroups.push(groupId);
            }
        },

        isGroupExpanded(groupId) {
            return this.expandedGroups.includes(groupId);
        },

        isActive(path) {
            return this.activeItem === path || this.activeItem.startsWith(path + '/');
        },

        navigate(path) {
            this.activeItem = path;
            if (window.innerWidth < 1024) {
                this.isMobileOpen = false;
            }
        }
    }
}
```

```html
<!-- resources/views/components/sidebar.blade.php -->
@props(['items' => []])

<div x-data="sidebar()" x-init="init()">
    <!-- Mobile Backdrop -->
    <div x-show="isMobileOpen"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="isMobileOpen = false"
         class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"></div>

    <!-- Sidebar -->
    <div :class="{
            'translate-x-0': isMobileOpen || isOpen,
            '-translate-x-full': !isMobileOpen && !isOpen
         }"
         class="fixed inset-y-0 left-0 z-50 w-64 transform bg-white shadow-lg transition-transform duration-300 ease-in-out lg:static lg:translate-x-0">

        <!-- Logo -->
        <div class="flex h-16 items-center justify-between px-4 border-b border-gray-200">
            <span class="text-xl font-bold text-indigo-600">Logo</span>
            <button @click="toggle" class="lg:hidden text-gray-500 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-3 py-4">
            @foreach($items as $item)
                @if(isset($item['children']))
                    <!-- Group -->
                    <div class="mb-2">
                        <button @click="toggleGroup('{{ $item['id'] }}')"
                                class="flex w-full items-center justify-between rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                            <div class="flex items-center">
                                @if(isset($item['icon']))
                                    <span class="mr-3">{!! $item['icon'] !!}</span>
                                @endif
                                <span>{{ $item['label'] }}</span>
                            </div>
                            <svg :class="{ 'rotate-90': isGroupExpanded('{{ $item['id'] }}') }"
                                 class="h-4 w-4 transition-transform"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <div x-show="isGroupExpanded('{{ $item['id'] }}')"
                             x-transition
                             class="mt-1 ml-4 space-y-1">
                            @foreach($item['children'] as $child)
                                <a href="{{ $child['path'] }}"
                                   @click="navigate('{{ $child['path'] }}')"
                                   :class="{ 'bg-indigo-50 text-indigo-600': isActive('{{ $child['path'] }}') }"
                                   class="block rounded-md px-3 py-2 text-sm text-gray-600 hover:bg-gray-100">
                                    {{ $child['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <!-- Single Item -->
                    <a href="{{ $item['path'] }}"
                       @click="navigate('{{ $item['path'] }}')"
                       :class="{ 'bg-indigo-50 text-indigo-600': isActive('{{ $item['path'] }}'), 'text-gray-700': !isActive('{{ $item['path'] }}') }"
                       class="mb-1 flex items-center rounded-md px-3 py-2 text-sm font-medium hover:bg-gray-100">
                        @if(isset($item['icon']))
                            <span class="mr-3">{!! $item['icon'] !!}</span>
                        @endif
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>
    </div>
</div>
```

### Navbar Component

```javascript
// resources/js/components/navbar.js
export default function navbar() {
    return {
        isProfileOpen: false,
        isNotificationsOpen: false,
        isMobileMenuOpen: false,
        notifications: [],
        unreadCount: 0,

        init() {
            this.loadNotifications();
        },

        async loadNotifications() {
            try {
                const response = await fetch('/api/notifications');
                const data = await response.json();
                this.notifications = data.notifications || [];
                this.unreadCount = this.notifications.filter(n => !n.read).length;
            } catch (error) {
                console.error('Failed to load notifications:', error);
            }
        },

        toggleProfile() {
            this.isProfileOpen = !this.isProfileOpen;
            this.isNotificationsOpen = false;
        },

        toggleNotifications() {
            this.isNotificationsOpen = !this.isNotificationsOpen;
            this.isProfileOpen = false;
        },

        async markAsRead(notificationId) {
            try {
                await fetch(`/api/notifications/${notificationId}/read`, { method: 'POST' });
                const notification = this.notifications.find(n => n.id === notificationId);
                if (notification) {
                    notification.read = true;
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                }
            } catch (error) {
                console.error('Failed to mark notification as read:', error);
            }
        },

        async markAllAsRead() {
            try {
                await fetch('/api/notifications/read-all', { method: 'POST' });
                this.notifications.forEach(n => n.read = true);
                this.unreadCount = 0;
            } catch (error) {
                console.error('Failed to mark all as read:', error);
            }
        }
    }
}
```

```html
<!-- resources/views/components/navbar.blade.php -->
@props(['user' => null])

<nav x-data="navbar()" x-init="init()" class="bg-white shadow">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <!-- Left Side -->
            <div class="flex">
                <!-- Mobile menu button -->
                <button @click="$dispatch('toggle-sidebar')"
                        class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 lg:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Search -->
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <div class="relative">
                        <input type="text"
                               placeholder="Search..."
                               class="w-64 rounded-md border-gray-300 pl-10 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side -->
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <div class="relative" @click.away="isNotificationsOpen = false">
                    <button @click="toggleNotifications"
                            class="relative rounded-full p-1 text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span x-show="unreadCount > 0"
                              x-text="unreadCount"
                              class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white"></span>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div x-show="isNotificationsOpen"
                         x-transition
                         class="absolute right-0 z-10 mt-2 w-80 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5">
                        <div class="flex items-center justify-between px-4 py-2 border-b">
                            <span class="text-sm font-medium">Notifications</span>
                            <button @click="markAllAsRead" class="text-xs text-indigo-600 hover:text-indigo-500">
                                Mark all as read
                            </button>
                        </div>
                        <div class="max-h-64 overflow-y-auto">
                            <template x-for="notification in notifications" :key="notification.id">
                                <div @click="markAsRead(notification.id)"
                                     :class="{ 'bg-indigo-50': !notification.read }"
                                     class="px-4 py-3 hover:bg-gray-50 cursor-pointer">
                                    <p x-text="notification.message" class="text-sm text-gray-900"></p>
                                    <p x-text="notification.time" class="text-xs text-gray-500 mt-1"></p>
                                </div>
                            </template>
                            <div x-show="notifications.length === 0" class="px-4 py-3 text-sm text-gray-500 text-center">
                                No notifications
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <div class="relative" @click.away="isProfileOpen = false">
                    <button @click="toggleProfile" class="flex items-center space-x-2">
                        <img class="h-8 w-8 rounded-full"
                             src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name ?? 'User') }}"
                             alt="Profile">
                        <span class="hidden text-sm font-medium text-gray-700 sm:block">{{ $user->name ?? 'User' }}</span>
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Profile Menu -->
                    <div x-show="isProfileOpen"
                         x-transition
                         class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5">
                        <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                        <a href="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                        <a href="/billing" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Billing</a>
                        <hr class="my-1">
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
```

---

## Dashboard Components

### Stats Component

```javascript
// resources/js/components/stats.js
export default function stats() {
    return {
        stats: [],
        loading: true,
        error: null,

        async init() {
            await this.loadStats();
        },

        async loadStats() {
            this.loading = true;
            try {
                const response = await fetch('/api/dashboard/stats');
                const data = await response.json();
                this.stats = data.stats || [];
            } catch (error) {
                this.error = 'Failed to load statistics';
            } finally {
                this.loading = false;
            }
        },

        formatValue(value, format) {
            switch (format) {
                case 'currency':
                    return new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD'
                    }).format(value);
                case 'percentage':
                    return `${value}%`;
                case 'number':
                    return new Intl.NumberFormat('en-US').format(value);
                default:
                    return value;
            }
        },

        getTrendClass(trend) {
            if (trend > 0) return 'text-green-600';
            if (trend < 0) return 'text-red-600';
            return 'text-gray-600';
        },

        getTrendIcon(trend) {
            if (trend > 0) return 'M5 10l7-7m0 0l7 7m-7-7v18';
            if (trend < 0) return 'M19 14l-7 7m0 0l-7-7m7 7V3';
            return 'M5 12h14';
        }
    }
}
```

```html
<!-- resources/views/components/stats.blade.php -->
@props(['stats' => []])

<div x-data="{ stats: {{ json_encode($stats) }} }"
     class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">

    <template x-for="stat in stats" :key="stat.id">
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500" x-text="stat.label"></dt>
            <dd class="mt-1 flex items-baseline justify-between">
                <div class="flex items-baseline text-2xl font-semibold text-indigo-600">
                    <span x-text="stat.value"></span>
                    <span x-show="stat.suffix" x-text="stat.suffix" class="ml-1 text-sm font-medium text-gray-500"></span>
                </div>

                <div x-show="stat.trend !== undefined"
                     :class="stat.trend >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                     class="inline-flex items-baseline rounded-full px-2.5 py-0.5 text-sm font-medium">
                    <svg :class="stat.trend >= 0 ? 'text-green-500' : 'text-red-500'"
                         class="-ml-1 mr-0.5 h-5 w-5 flex-shrink-0 self-center"
                         viewBox="0 0 20 20"
                         fill="currentColor">
                        <path x-show="stat.trend >= 0" fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        <path x-show="stat.trend < 0" fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span x-text="Math.abs(stat.trend) + '%'"></span>
                </div>
            </dd>
        </div>
    </template>
</div>
```

### Data Table Component

```javascript
// resources/js/components/data-table.js
export default function dataTable(options = {}) {
    return {
        data: [],
        columns: options.columns || [],
        loading: true,
        search: '',
        sortColumn: options.defaultSort || '',
        sortDirection: 'asc',
        currentPage: 1,
        perPage: options.perPage || 10,
        total: 0,
        selected: [],
        selectAll: false,

        async init() {
            await this.loadData();

            this.$watch('search', () => {
                this.currentPage = 1;
                this.loadData();
            });
        },

        async loadData() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    search: this.search,
                    sort: this.sortColumn,
                    direction: this.sortDirection
                });

                const response = await fetch(`${options.endpoint}?${params}`);
                const result = await response.json();

                this.data = result.data || [];
                this.total = result.total || 0;
            } catch (error) {
                console.error('Failed to load data:', error);
            } finally {
                this.loading = false;
            }
        },

        sort(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
            this.loadData();
        },

        toggleSelectAll() {
            if (this.selectAll) {
                this.selected = this.data.map(item => item.id);
            } else {
                this.selected = [];
            }
        },

        toggleSelect(id) {
            const index = this.selected.indexOf(id);
            if (index > -1) {
                this.selected.splice(index, 1);
            } else {
                this.selected.push(id);
            }
            this.selectAll = this.selected.length === this.data.length;
        },

        isSelected(id) {
            return this.selected.includes(id);
        },

        get totalPages() {
            return Math.ceil(this.total / this.perPage);
        },

        get paginationRange() {
            const range = [];
            const start = Math.max(1, this.currentPage - 2);
            const end = Math.min(this.totalPages, this.currentPage + 2);

            for (let i = start; i <= end; i++) {
                range.push(i);
            }
            return range;
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.loadData();
            }
        },

        async deleteSelected() {
            if (this.selected.length === 0) return;

            if (!confirm(`Are you sure you want to delete ${this.selected.length} items?`)) {
                return;
            }

            try {
                await fetch(options.endpoint + '/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ ids: this.selected })
                });

                this.selected = [];
                this.selectAll = false;
                await this.loadData();

                this.$dispatch('toast', { message: 'Items deleted successfully', type: 'success' });
            } catch (error) {
                this.$dispatch('toast', { message: 'Failed to delete items', type: 'error' });
            }
        }
    }
}
```

```html
<!-- resources/views/components/data-table.blade.php -->
@props(['columns' => [], 'endpoint' => '', 'perPage' => 10])

<div x-data="dataTable({
        columns: {{ json_encode($columns) }},
        endpoint: '{{ $endpoint }}',
        perPage: {{ $perPage }}
     })"
     x-init="init()"
     class="overflow-hidden bg-white shadow sm:rounded-lg">

    <!-- Toolbar -->
    <div class="border-b border-gray-200 px-4 py-4 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <!-- Search -->
            <div class="relative">
                <input x-model.debounce.300ms="search"
                       type="text"
                       placeholder="Search..."
                       class="rounded-md border-gray-300 pl-10 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center space-x-2">
                <button x-show="selected.length > 0"
                        @click="deleteSelected"
                        class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                    Delete Selected (<span x-text="selected.length"></span>)
                </button>
                {{ $actions ?? '' }}
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="w-12 px-6 py-3">
                        <input type="checkbox"
                               x-model="selectAll"
                               @change="toggleSelectAll"
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </th>
                    <template x-for="column in columns" :key="column.key">
                        <th @click="column.sortable && sort(column.key)"
                            :class="{ 'cursor-pointer hover:bg-gray-100': column.sortable }"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            <div class="flex items-center space-x-1">
                                <span x-text="column.label"></span>
                                <template x-if="column.sortable && sortColumn === column.key">
                                    <svg :class="{ 'rotate-180': sortDirection === 'desc' }"
                                         class="h-4 w-4 transition-transform"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                </template>
                            </div>
                        </th>
                    </template>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                <!-- Loading State -->
                <tr x-show="loading">
                    <td :colspan="columns.length + 2" class="px-6 py-12 text-center">
                        <svg class="mx-auto h-8 w-8 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </td>
                </tr>

                <!-- Data Rows -->
                <template x-for="row in data" :key="row.id">
                    <tr :class="{ 'bg-indigo-50': isSelected(row.id) }" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <input type="checkbox"
                                   :checked="isSelected(row.id)"
                                   @change="toggleSelect(row.id)"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <template x-for="column in columns" :key="column.key">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900" x-text="row[column.key]"></td>
                        </template>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                            <button @click="$dispatch('edit-row', row)" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                            <button @click="$dispatch('delete-row', row)" class="ml-4 text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                </template>

                <!-- Empty State -->
                <tr x-show="!loading && data.length === 0">
                    <td :colspan="columns.length + 2" class="px-6 py-12 text-center text-sm text-gray-500">
                        No data found
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing <span x-text="((currentPage - 1) * perPage) + 1"></span> to
                    <span x-text="Math.min(currentPage * perPage, total)"></span> of
                    <span x-text="total"></span> results
                </p>
            </div>
            <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                    <button @click="goToPage(currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <template x-for="page in paginationRange" :key="page">
                        <button @click="goToPage(page)"
                                :class="{ 'bg-indigo-600 text-white': page === currentPage, 'text-gray-900': page !== currentPage }"
                                class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            <span x-text="page"></span>
                        </button>
                    </template>
                    <button @click="goToPage(currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</div>
```

---

## Builder Components

### Element Selector Component

```javascript
// resources/js/components/element-selector.js
export default function elementSelector() {
    return {
        elements: [
            {
                category: 'Layout',
                items: [
                    { id: 'section', name: 'Section', icon: 'rectangle' },
                    { id: 'container', name: 'Container', icon: 'square' },
                    { id: 'columns', name: 'Columns', icon: 'columns' },
                    { id: 'spacer', name: 'Spacer', icon: 'arrows-expand' }
                ]
            },
            {
                category: 'Content',
                items: [
                    { id: 'heading', name: 'Heading', icon: 'heading' },
                    { id: 'paragraph', name: 'Paragraph', icon: 'align-left' },
                    { id: 'image', name: 'Image', icon: 'photograph' },
                    { id: 'video', name: 'Video', icon: 'video-camera' },
                    { id: 'button', name: 'Button', icon: 'cursor-click' },
                    { id: 'link', name: 'Link', icon: 'link' },
                    { id: 'list', name: 'List', icon: 'list-bullet' },
                    { id: 'divider', name: 'Divider', icon: 'minus' }
                ]
            },
            {
                category: 'Forms',
                items: [
                    { id: 'form', name: 'Form', icon: 'document-text' },
                    { id: 'input', name: 'Input', icon: 'pencil' },
                    { id: 'textarea', name: 'Textarea', icon: 'document' },
                    { id: 'select', name: 'Select', icon: 'selector' },
                    { id: 'checkbox', name: 'Checkbox', icon: 'check-circle' },
                    { id: 'radio', name: 'Radio', icon: 'dots-circle-horizontal' }
                ]
            },
            {
                category: 'Components',
                items: [
                    { id: 'navbar', name: 'Navbar', icon: 'menu' },
                    { id: 'hero', name: 'Hero', icon: 'template' },
                    { id: 'features', name: 'Features', icon: 'view-grid' },
                    { id: 'testimonials', name: 'Testimonials', icon: 'chat-alt' },
                    { id: 'pricing', name: 'Pricing', icon: 'currency-dollar' },
                    { id: 'cta', name: 'Call to Action', icon: 'speakerphone' },
                    { id: 'footer', name: 'Footer', icon: 'view-boards' }
                ]
            }
        ],
        searchQuery: '',
        activeCategory: null,
        draggedElement: null,

        get filteredElements() {
            if (!this.searchQuery) return this.elements;

            const query = this.searchQuery.toLowerCase();
            return this.elements.map(category => ({
                ...category,
                items: category.items.filter(item =>
                    item.name.toLowerCase().includes(query)
                )
            })).filter(category => category.items.length > 0);
        },

        startDrag(element, event) {
            this.draggedElement = element;
            event.dataTransfer.setData('element', JSON.stringify(element));
            event.dataTransfer.effectAllowed = 'copy';
        },

        endDrag() {
            this.draggedElement = null;
        },

        addElement(element) {
            this.$dispatch('add-element', element);
        },

        getIcon(iconName) {
            const icons = {
                'rectangle': 'M3 5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5z',
                'square': 'M5 3a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2H5z',
                'columns': 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2',
                'heading': 'M4 6h16M4 12h8m-8 6h16',
                'align-left': 'M4 6h16M4 12h10M4 18h16',
                'photograph': 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
                'video-camera': 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
                'cursor-click': 'M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122',
                'link': 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1',
                'menu': 'M4 6h16M4 12h16M4 18h16',
                'template': 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z',
                'view-grid': 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
                'currency-dollar': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
            };
            return icons[iconName] || icons['square'];
        }
    }
}
```

```html
<!-- resources/views/components/element-selector.blade.php -->
<div x-data="elementSelector()" class="h-full bg-white border-r border-gray-200">
    <!-- Search -->
    <div class="p-4 border-b border-gray-200">
        <div class="relative">
            <input x-model="searchQuery"
                   type="text"
                   placeholder="Search elements..."
                   class="w-full rounded-md border-gray-300 pl-10 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Elements List -->
    <div class="overflow-y-auto h-[calc(100%-80px)]">
        <template x-for="category in filteredElements" :key="category.category">
            <div class="border-b border-gray-200">
                <!-- Category Header -->
                <button @click="activeCategory = activeCategory === category.category ? null : category.category"
                        class="flex w-full items-center justify-between px-4 py-3 text-left hover:bg-gray-50">
                    <span class="text-sm font-medium text-gray-900" x-text="category.category"></span>
                    <svg :class="{ 'rotate-180': activeCategory === category.category }"
                         class="h-4 w-4 text-gray-400 transition-transform"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Elements Grid -->
                <div x-show="activeCategory === category.category || searchQuery"
                     x-transition
                     class="grid grid-cols-2 gap-2 px-4 pb-4">
                    <template x-for="element in category.items" :key="element.id">
                        <div draggable="true"
                             @dragstart="startDrag(element, $event)"
                             @dragend="endDrag"
                             @click="addElement(element)"
                             :class="{ 'ring-2 ring-indigo-500': draggedElement?.id === element.id }"
                             class="flex cursor-move flex-col items-center rounded-lg border border-gray-200 p-3 hover:border-indigo-300 hover:bg-indigo-50 transition-colors">
                            <svg class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIcon(element.icon)" />
                            </svg>
                            <span x-text="element.name" class="mt-1 text-xs text-gray-600"></span>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>
```

### Properties Panel Component

```javascript
// resources/js/components/properties-panel.js
export default function propertiesPanel() {
    return {
        selectedElement: null,
        activeTab: 'style',

        // Style properties
        styles: {
            // Layout
            width: '100%',
            height: 'auto',
            margin: { top: 0, right: 0, bottom: 0, left: 0 },
            padding: { top: 0, right: 0, bottom: 0, left: 0 },

            // Typography
            fontFamily: 'Inter',
            fontSize: 16,
            fontWeight: '400',
            lineHeight: 1.5,
            textAlign: 'left',
            color: '#000000',

            // Background
            backgroundColor: '#ffffff',
            backgroundImage: '',

            // Border
            borderWidth: 0,
            borderStyle: 'solid',
            borderColor: '#e5e7eb',
            borderRadius: 0,

            // Effects
            opacity: 100,
            boxShadow: 'none'
        },

        // Content properties
        content: {
            text: '',
            src: '',
            alt: '',
            href: '',
            target: '_self'
        },

        // Settings
        settings: {
            id: '',
            className: '',
            visibility: 'visible',
            responsive: {
                desktop: true,
                tablet: true,
                mobile: true
            }
        },

        init() {
            this.$watch('selectedElement', (element) => {
                if (element) {
                    this.loadElementProperties(element);
                }
            });
        },

        loadElementProperties(element) {
            // Load styles
            if (element.styles) {
                this.styles = { ...this.styles, ...element.styles };
            }

            // Load content
            if (element.content) {
                this.content = { ...this.content, ...element.content };
            }

            // Load settings
            if (element.settings) {
                this.settings = { ...this.settings, ...element.settings };
            }
        },

        updateProperty(category, property, value) {
            if (category === 'styles') {
                this.styles[property] = value;
            } else if (category === 'content') {
                this.content[property] = value;
            } else if (category === 'settings') {
                this.settings[property] = value;
            }

            this.$dispatch('update-element', {
                id: this.selectedElement.id,
                [category]: { [property]: value }
            });
        },

        updateMarginPadding(type, side, value) {
            this.styles[type][side] = parseInt(value) || 0;
            this.$dispatch('update-element', {
                id: this.selectedElement.id,
                styles: { [type]: this.styles[type] }
            });
        },

        // Preset shadows
        shadowPresets: [
            { name: 'None', value: 'none' },
            { name: 'Small', value: '0 1px 2px 0 rgb(0 0 0 / 0.05)' },
            { name: 'Medium', value: '0 4px 6px -1px rgb(0 0 0 / 0.1)' },
            { name: 'Large', value: '0 10px 15px -3px rgb(0 0 0 / 0.1)' },
            { name: 'XL', value: '0 20px 25px -5px rgb(0 0 0 / 0.1)' }
        ],

        // Font options
        fontFamilies: [
            'Inter', 'Roboto', 'Open Sans', 'Lato', 'Montserrat',
            'Poppins', 'Source Sans Pro', 'Raleway', 'Nunito', 'Playfair Display'
        ],

        fontWeights: [
            { label: 'Light', value: '300' },
            { label: 'Regular', value: '400' },
            { label: 'Medium', value: '500' },
            { label: 'Semibold', value: '600' },
            { label: 'Bold', value: '700' }
        ]
    }
}
```

```html
<!-- resources/views/components/properties-panel.blade.php -->
<div x-data="propertiesPanel()"
     @element-selected.window="selectedElement = $event.detail"
     x-init="init()"
     class="h-full w-80 bg-white border-l border-gray-200 overflow-y-auto">

    <!-- No Selection -->
    <div x-show="!selectedElement" class="flex h-full items-center justify-center p-4">
        <p class="text-sm text-gray-500 text-center">Select an element to edit its properties</p>
    </div>

    <!-- Properties -->
    <div x-show="selectedElement">
        <!-- Tabs -->
        <div class="flex border-b border-gray-200">
            <button @click="activeTab = 'style'"
                    :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'style' }"
                    class="flex-1 border-b-2 px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-700">
                Style
            </button>
            <button @click="activeTab = 'content'"
                    :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'content' }"
                    class="flex-1 border-b-2 px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-700">
                Content
            </button>
            <button @click="activeTab = 'settings'"
                    :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'settings' }"
                    class="flex-1 border-b-2 px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-700">
                Settings
            </button>
        </div>

        <!-- Style Tab -->
        <div x-show="activeTab === 'style'" class="p-4 space-y-6">
            <!-- Layout Section -->
            <div>
                <h3 class="text-sm font-medium text-gray-900 mb-3">Layout</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Width</label>
                        <input x-model="styles.width"
                               @input="updateProperty('styles', 'width', $event.target.value)"
                               type="text"
                               class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Height</label>
                        <input x-model="styles.height"
                               @input="updateProperty('styles', 'height', $event.target.value)"
                               type="text"
                               class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                </div>
            </div>

            <!-- Margin & Padding -->
            <div>
                <h3 class="text-sm font-medium text-gray-900 mb-3">Spacing</h3>

                <!-- Margin -->
                <label class="block text-xs text-gray-500 mb-2">Margin</label>
                <div class="grid grid-cols-4 gap-2 mb-3">
                    <input x-model="styles.margin.top"
                           @input="updateMarginPadding('margin', 'top', $event.target.value)"
                           type="number" placeholder="T" class="rounded-md border-gray-300 text-sm text-center">
                    <input x-model="styles.margin.right"
                           @input="updateMarginPadding('margin', 'right', $event.target.value)"
                           type="number" placeholder="R" class="rounded-md border-gray-300 text-sm text-center">
                    <input x-model="styles.margin.bottom"
                           @input="updateMarginPadding('margin', 'bottom', $event.target.value)"
                           type="number" placeholder="B" class="rounded-md border-gray-300 text-sm text-center">
                    <input x-model="styles.margin.left"
                           @input="updateMarginPadding('margin', 'left', $event.target.value)"
                           type="number" placeholder="L" class="rounded-md border-gray-300 text-sm text-center">
                </div>

                <!-- Padding -->
                <label class="block text-xs text-gray-500 mb-2">Padding</label>
                <div class="grid grid-cols-4 gap-2">
                    <input x-model="styles.padding.top"
                           @input="updateMarginPadding('padding', 'top', $event.target.value)"
                           type="number" placeholder="T" class="rounded-md border-gray-300 text-sm text-center">
                    <input x-model="styles.padding.right"
                           @input="updateMarginPadding('padding', 'right', $event.target.value)"
                           type="number" placeholder="R" class="rounded-md border-gray-300 text-sm text-center">
                    <input x-model="styles.padding.bottom"
                           @input="updateMarginPadding('padding', 'bottom', $event.target.value)"
                           type="number" placeholder="B" class="rounded-md border-gray-300 text-sm text-center">
                    <input x-model="styles.padding.left"
                           @input="updateMarginPadding('padding', 'left', $event.target.value)"
                           type="number" placeholder="L" class="rounded-md border-gray-300 text-sm text-center">
                </div>
            </div>

            <!-- Typography -->
            <div>
                <h3 class="text-sm font-medium text-gray-900 mb-3">Typography</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Font Family</label>
                        <select x-model="styles.fontFamily"
                                @change="updateProperty('styles', 'fontFamily', $event.target.value)"
                                class="w-full rounded-md border-gray-300 text-sm">
                            <template x-for="font in fontFamilies" :key="font">
                                <option :value="font" x-text="font"></option>
                            </template>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Size</label>
                            <input x-model="styles.fontSize"
                                   @input="updateProperty('styles', 'fontSize', $event.target.value)"
                                   type="number"
                                   class="w-full rounded-md border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Weight</label>
                            <select x-model="styles.fontWeight"
                                    @change="updateProperty('styles', 'fontWeight', $event.target.value)"
                                    class="w-full rounded-md border-gray-300 text-sm">
                                <template x-for="weight in fontWeights" :key="weight.value">
                                    <option :value="weight.value" x-text="weight.label"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Text Color</label>
                        <div class="flex items-center space-x-2">
                            <input x-model="styles.color"
                                   @input="updateProperty('styles', 'color', $event.target.value)"
                                   type="color"
                                   class="h-8 w-8 rounded border border-gray-300 cursor-pointer">
                            <input x-model="styles.color"
                                   @input="updateProperty('styles', 'color', $event.target.value)"
                                   type="text"
                                   class="flex-1 rounded-md border-gray-300 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Text Align</label>
                        <div class="flex rounded-md shadow-sm">
                            <button @click="updateProperty('styles', 'textAlign', 'left')"
                                    :class="{ 'bg-indigo-100 text-indigo-600': styles.textAlign === 'left' }"
                                    class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-l-md hover:bg-gray-50">
                                <svg class="w-4 h-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16" />
                                </svg>
                            </button>
                            <button @click="updateProperty('styles', 'textAlign', 'center')"
                                    :class="{ 'bg-indigo-100 text-indigo-600': styles.textAlign === 'center' }"
                                    class="flex-1 px-3 py-2 text-sm border-t border-b border-gray-300 hover:bg-gray-50">
                                <svg class="w-4 h-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M4 18h16" />
                                </svg>
                            </button>
                            <button @click="updateProperty('styles', 'textAlign', 'right')"
                                    :class="{ 'bg-indigo-100 text-indigo-600': styles.textAlign === 'right' }"
                                    class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-r-md hover:bg-gray-50">
                                <svg class="w-4 h-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M4 18h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Background -->
            <div>
                <h3 class="text-sm font-medium text-gray-900 mb-3">Background</h3>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Background Color</label>
                    <div class="flex items-center space-x-2">
                        <input x-model="styles.backgroundColor"
                               @input="updateProperty('styles', 'backgroundColor', $event.target.value)"
                               type="color"
                               class="h-8 w-8 rounded border border-gray-300 cursor-pointer">
                        <input x-model="styles.backgroundColor"
                               @input="updateProperty('styles', 'backgroundColor', $event.target.value)"
                               type="text"
                               class="flex-1 rounded-md border-gray-300 text-sm">
                    </div>
                </div>
            </div>

            <!-- Border -->
            <div>
                <h3 class="text-sm font-medium text-gray-900 mb-3">Border</h3>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Width</label>
                            <input x-model="styles.borderWidth"
                                   @input="updateProperty('styles', 'borderWidth', $event.target.value)"
                                   type="number"
                                   class="w-full rounded-md border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Radius</label>
                            <input x-model="styles.borderRadius"
                                   @input="updateProperty('styles', 'borderRadius', $event.target.value)"
                                   type="number"
                                   class="w-full rounded-md border-gray-300 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Border Color</label>
                        <div class="flex items-center space-x-2">
                            <input x-model="styles.borderColor"
                                   @input="updateProperty('styles', 'borderColor', $event.target.value)"
                                   type="color"
                                   class="h-8 w-8 rounded border border-gray-300 cursor-pointer">
                            <input x-model="styles.borderColor"
                                   @input="updateProperty('styles', 'borderColor', $event.target.value)"
                                   type="text"
                                   class="flex-1 rounded-md border-gray-300 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Effects -->
            <div>
                <h3 class="text-sm font-medium text-gray-900 mb-3">Effects</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Opacity</label>
                        <input x-model="styles.opacity"
                               @input="updateProperty('styles', 'opacity', $event.target.value)"
                               type="range" min="0" max="100"
                               class="w-full">
                        <div class="text-xs text-gray-500 text-right" x-text="styles.opacity + '%'"></div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Box Shadow</label>
                        <select x-model="styles.boxShadow"
                                @change="updateProperty('styles', 'boxShadow', $event.target.value)"
                                class="w-full rounded-md border-gray-300 text-sm">
                            <template x-for="shadow in shadowPresets" :key="shadow.name">
                                <option :value="shadow.value" x-text="shadow.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Tab -->
        <div x-show="activeTab === 'content'" class="p-4 space-y-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Text Content</label>
                <textarea x-model="content.text"
                          @input="updateProperty('content', 'text', $event.target.value)"
                          rows="4"
                          class="w-full rounded-md border-gray-300 text-sm"></textarea>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Image Source</label>
                <input x-model="content.src"
                       @input="updateProperty('content', 'src', $event.target.value)"
                       type="text"
                       placeholder="https://..."
                       class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Alt Text</label>
                <input x-model="content.alt"
                       @input="updateProperty('content', 'alt', $event.target.value)"
                       type="text"
                       class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Link URL</label>
                <input x-model="content.href"
                       @input="updateProperty('content', 'href', $event.target.value)"
                       type="text"
                       placeholder="https://..."
                       class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Link Target</label>
                <select x-model="content.target"
                        @change="updateProperty('content', 'target', $event.target.value)"
                        class="w-full rounded-md border-gray-300 text-sm">
                    <option value="_self">Same Window</option>
                    <option value="_blank">New Window</option>
                </select>
            </div>
        </div>

        <!-- Settings Tab -->
        <div x-show="activeTab === 'settings'" class="p-4 space-y-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Element ID</label>
                <input x-model="settings.id"
                       @input="updateProperty('settings', 'id', $event.target.value)"
                       type="text"
                       class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">CSS Classes</label>
                <input x-model="settings.className"
                       @input="updateProperty('settings', 'className', $event.target.value)"
                       type="text"
                       class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-2">Visibility</label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input x-model="settings.responsive.desktop"
                               @change="updateProperty('settings', 'responsive', settings.responsive)"
                               type="checkbox"
                               class="rounded border-gray-300 text-indigo-600">
                        <span class="ml-2 text-sm text-gray-600">Desktop</span>
                    </label>
                    <label class="flex items-center">
                        <input x-model="settings.responsive.tablet"
                               @change="updateProperty('settings', 'responsive', settings.responsive)"
                               type="checkbox"
                               class="rounded border-gray-300 text-indigo-600">
                        <span class="ml-2 text-sm text-gray-600">Tablet</span>
                    </label>
                    <label class="flex items-center">
                        <input x-model="settings.responsive.mobile"
                               @change="updateProperty('settings', 'responsive', settings.responsive)"
                               type="checkbox"
                               class="rounded border-gray-300 text-indigo-600">
                        <span class="ml-2 text-sm text-gray-600">Mobile</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## Usage Examples

### Initializing Components

```javascript
// resources/js/app.js
import Alpine from 'alpinejs'

// Import components
import modal from './components/modal'
import dropdown from './components/dropdown'
import toast from './components/toast'
import tabs from './components/tabs'
import accordion from './components/accordion'
import formValidation from './components/form-validation'
import fileUpload from './components/file-upload'
import sidebar from './components/sidebar'
import navbar from './components/navbar'
import stats from './components/stats'
import dataTable from './components/data-table'
import elementSelector from './components/element-selector'
import propertiesPanel from './components/properties-panel'

// Register components
Alpine.data('modal', modal)
Alpine.data('dropdown', dropdown)
Alpine.data('toast', toast)
Alpine.data('tabs', tabs)
Alpine.data('accordion', accordion)
Alpine.data('formValidation', formValidation)
Alpine.data('fileUpload', fileUpload)
Alpine.data('sidebar', sidebar)
Alpine.data('navbar', navbar)
Alpine.data('stats', stats)
Alpine.data('dataTable', dataTable)
Alpine.data('elementSelector', elementSelector)
Alpine.data('propertiesPanel', propertiesPanel)

// Start Alpine
Alpine.start()
```

### Dispatching Events

```javascript
// Show toast notification
$dispatch('toast', { message: 'Changes saved!', type: 'success' })

// Open modal
$dispatch('open-modal', {
    title: 'Confirm Action',
    content: '<p>Are you sure?</p>',
    size: 'sm'
})

// Select element in builder
$dispatch('element-selected', elementData)

// Add element from selector
$dispatch('add-element', { id: 'heading', name: 'Heading' })
```
