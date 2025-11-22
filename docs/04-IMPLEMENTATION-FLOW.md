# Implementation Flow - Landing Page Builder SaaS

## Tech Stack (STRICT - NO EXCEPTIONS)

- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** TailwindCSS v4, AlpineJS, HTML in Blade files
- **Database:** MySQL 8.0+
- **Authentication:** Laravel Breeze/Fortify
- **Payments:** Stripe/PayPal with Strategy Pattern
- **Testing:** PHPUnit

---

## Day 1: Laravel Setup & Migrations

### Project Initialization

```bash
composer create-project laravel/laravel landing-page-builder
cd landing-page-builder
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install -D tailwindcss@latest @tailwindcss/forms alpinejs
```

### Database Migrations

```php
// database/migrations/2024_01_01_000001_create_users_table.php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->enum('role', ['user', 'admin'])->default('user');
    $table->string('subscription_tier')->default('free');
    $table->rememberToken();
    $table->timestamps();
});

// database/migrations/2024_01_01_000002_create_pages_table.php
Schema::create('pages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('title');
    $table->string('slug')->unique();
    $table->json('content')->nullable();
    $table->json('settings')->nullable();
    $table->enum('status', ['draft', 'published'])->default('draft');
    $table->string('custom_domain')->nullable();
    $table->timestamps();
});

// database/migrations/2024_01_01_000003_create_templates_table.php
Schema::create('templates', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('category');
    $table->json('content');
    $table->string('thumbnail')->nullable();
    $table->boolean('is_premium')->default(false);
    $table->timestamps();
});

// database/migrations/2024_01_01_000004_create_media_table.php
Schema::create('media', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('filename');
    $table->string('path');
    $table->string('mime_type');
    $table->unsignedBigInteger('size');
    $table->timestamps();
});

// database/migrations/2024_01_01_000005_create_subscriptions_table.php
Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('provider'); // stripe, paypal
    $table->string('provider_id');
    $table->string('plan');
    $table->timestamp('starts_at');
    $table->timestamp('ends_at')->nullable();
    $table->enum('status', ['active', 'cancelled', 'expired'])->default('active');
    $table->timestamps();
});
```

### Models

```php
// app/Models/Page.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    protected $fillable = [
        'user_id', 'title', 'slug', 'content', 'settings', 'status', 'custom_domain'
    ];

    protected $casts = [
        'content' => 'array',
        'settings' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
```

---

## Day 2: Authentication with Laravel Breeze

### Auth Controllers

```php
// app/Http/Controllers/Auth/RegisteredUserController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        auth()->login($user);

        return redirect()->route('dashboard');
    }
}
```

### Auth Blade Views

```blade
{{-- resources/views/auth/login.blade.php --}}
<x-guest-layout>
    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" id="password" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600">
                <span class="ml-2 text-sm text-gray-600">Remember me</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                Forgot password?
            </a>
        </div>

        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Sign in
        </button>
    </form>
</x-guest-layout>
```

---

## Day 3: Dashboard Blade Views

### Dashboard Controller

```php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $pages = auth()->user()->pages()->latest()->paginate(10);
        $stats = [
            'total_pages' => auth()->user()->pages()->count(),
            'published_pages' => auth()->user()->pages()->where('status', 'published')->count(),
            'draft_pages' => auth()->user()->pages()->where('status', 'draft')->count(),
        ];

        return view('dashboard.index', compact('pages', 'stats'));
    }
}
```

### Dashboard View

```blade
{{-- resources/views/dashboard/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Pages</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total_pages'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Published</div>
                    <div class="mt-1 text-3xl font-semibold text-green-600">{{ $stats['published_pages'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Drafts</div>
                    <div class="mt-1 text-3xl font-semibold text-yellow-600">{{ $stats['draft_pages'] }}</div>
                </div>
            </div>

            {{-- Pages List --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Your Pages</h3>
                    <a href="{{ route('pages.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Create New Page
                    </a>
                </div>

                <div class="divide-y divide-gray-200">
                    @forelse($pages as $page)
                        <div class="p-6 flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ $page->title }}</h4>
                                <p class="text-sm text-gray-500">{{ $page->slug }}</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $page->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($page->status) }}
                                </span>
                                <a href="{{ route('builder.edit', $page) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <a href="{{ route('pages.preview', $page) }}" class="text-gray-600 hover:text-gray-900">Preview</a>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500">
                            No pages yet. Create your first page!
                        </div>
                    @endforelse
                </div>

                <div class="p-6 border-t border-gray-200">
                    {{ $pages->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

---

## Day 4-5: Page Builder with AlpineJS x-data

### Builder Controller

```php
// app/Http/Controllers/BuilderController.php
namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Template;
use Illuminate\Http\Request;

class BuilderController extends Controller
{
    public function edit(Page $page)
    {
        $this->authorize('update', $page);

        return view('builder.edit', [
            'page' => $page,
            'elements' => config('builder.elements'),
        ]);
    }

    public function save(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'content' => 'required|array',
            'settings' => 'nullable|array',
        ]);

        $page->update($validated);

        return response()->json(['success' => true, 'message' => 'Page saved successfully']);
    }
}
```

### Builder View with AlpineJS

```blade
{{-- resources/views/builder/edit.blade.php --}}
<x-app-layout>
    <div x-data="pageBuilder({{ json_encode($page->content ?? []) }})" class="h-screen flex flex-col">
        {{-- Toolbar --}}
        <div class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <input type="text" x-model="pageTitle" class="border-0 text-lg font-semibold focus:ring-0" placeholder="Page Title">
            </div>
            <div class="flex items-center space-x-3">
                <button @click="preview()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Preview
                </button>
                <button @click="save()" :disabled="saving" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 disabled:opacity-50">
                    <span x-show="!saving">Save</span>
                    <span x-show="saving">Saving...</span>
                </button>
                <button @click="publish()" class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                    Publish
                </button>
            </div>
        </div>

        <div class="flex-1 flex overflow-hidden">
            {{-- Elements Sidebar --}}
            <div class="w-64 bg-gray-50 border-r border-gray-200 overflow-y-auto p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Elements</h3>
                <div class="space-y-2">
                    <template x-for="element in availableElements" :key="element.type">
                        <button @click="addElement(element.type)" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md flex items-center">
                            <span x-html="element.icon" class="w-5 h-5 mr-2"></span>
                            <span x-text="element.label"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Canvas --}}
            <div class="flex-1 overflow-y-auto bg-gray-100 p-8">
                <div class="max-w-4xl mx-auto bg-white shadow-lg min-h-full">
                    <template x-for="(element, index) in elements" :key="element.id">
                        <div class="relative group" :class="{ 'ring-2 ring-indigo-500': selectedElement === element.id }">
                            {{-- Element Controls --}}
                            <div class="absolute -top-3 right-2 hidden group-hover:flex items-center space-x-1 bg-white shadow-sm rounded-md p-1">
                                <button @click="moveElement(index, -1)" :disabled="index === 0" class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-30">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                    </svg>
                                </button>
                                <button @click="moveElement(index, 1)" :disabled="index === elements.length - 1" class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-30">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <button @click="duplicateElement(index)" class="p-1 text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                                <button @click="removeElement(index)" class="p-1 text-red-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Element Content --}}
                            <div @click="selectElement(element.id)" class="cursor-pointer">
                                <template x-if="element.type === 'heading'">
                                    <div class="p-6">
                                        <h2 x-text="element.content.text" :class="element.content.size" class="font-bold" :style="{ color: element.content.color }"></h2>
                                    </div>
                                </template>
                                <template x-if="element.type === 'text'">
                                    <div class="p-6">
                                        <p x-text="element.content.text" :style="{ color: element.content.color }"></p>
                                    </div>
                                </template>
                                <template x-if="element.type === 'image'">
                                    <div class="p-6">
                                        <img :src="element.content.src" :alt="element.content.alt" class="max-w-full h-auto">
                                    </div>
                                </template>
                                <template x-if="element.type === 'button'">
                                    <div class="p-6">
                                        <button x-text="element.content.text" class="px-6 py-3 rounded-md font-semibold" :style="{ backgroundColor: element.content.bgColor, color: element.content.textColor }"></button>
                                    </div>
                                </template>
                                <template x-if="element.type === 'hero'">
                                    <div class="p-12 text-center" :style="{ backgroundColor: element.content.bgColor }">
                                        <h1 x-text="element.content.title" class="text-4xl font-bold mb-4" :style="{ color: element.content.titleColor }"></h1>
                                        <p x-text="element.content.subtitle" class="text-xl mb-6" :style="{ color: element.content.subtitleColor }"></p>
                                        <button x-text="element.content.buttonText" class="px-8 py-3 rounded-md font-semibold" :style="{ backgroundColor: element.content.buttonBgColor, color: element.content.buttonTextColor }"></button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div x-show="elements.length === 0" class="p-12 text-center text-gray-400">
                        Click an element from the sidebar to add it here
                    </div>
                </div>
            </div>

            {{-- Properties Panel --}}
            <div x-show="selectedElement" class="w-80 bg-white border-l border-gray-200 overflow-y-auto p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Properties</h3>
                <template x-if="getSelectedElement()">
                    <div class="space-y-4">
                        {{-- Dynamic properties based on element type --}}
                        <template x-if="getSelectedElement().type === 'heading'">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Text</label>
                                    <input type="text" x-model="getSelectedElement().content.text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Size</label>
                                    <select x-model="getSelectedElement().content.size" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="text-2xl">Small</option>
                                        <option value="text-3xl">Medium</option>
                                        <option value="text-4xl">Large</option>
                                        <option value="text-5xl">Extra Large</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                                    <input type="color" x-model="getSelectedElement().content.color" class="w-full h-10 rounded-md border-gray-300">
                                </div>
                            </div>
                        </template>
                        <template x-if="getSelectedElement().type === 'button'">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                                    <input type="text" x-model="getSelectedElement().content.text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Link URL</label>
                                    <input type="url" x-model="getSelectedElement().content.url" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Background Color</label>
                                    <input type="color" x-model="getSelectedElement().content.bgColor" class="w-full h-10 rounded-md border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Text Color</label>
                                    <input type="color" x-model="getSelectedElement().content.textColor" class="w-full h-10 rounded-md border-gray-300">
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function pageBuilder(initialContent) {
            return {
                pageTitle: '{{ $page->title }}',
                elements: initialContent || [],
                selectedElement: null,
                saving: false,
                availableElements: [
                    { type: 'heading', label: 'Heading', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/></svg>' },
                    { type: 'text', label: 'Text Block', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>' },
                    { type: 'image', label: 'Image', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>' },
                    { type: 'button', label: 'Button', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>' },
                    { type: 'hero', label: 'Hero Section', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>' },
                ],

                addElement(type) {
                    const defaults = {
                        heading: { text: 'New Heading', size: 'text-3xl', color: '#000000' },
                        text: { text: 'Enter your text here...', color: '#374151' },
                        image: { src: '/images/placeholder.jpg', alt: 'Image' },
                        button: { text: 'Click Me', url: '#', bgColor: '#4F46E5', textColor: '#FFFFFF' },
                        hero: {
                            title: 'Welcome to Our Site',
                            subtitle: 'Build amazing landing pages',
                            buttonText: 'Get Started',
                            bgColor: '#F3F4F6',
                            titleColor: '#111827',
                            subtitleColor: '#6B7280',
                            buttonBgColor: '#4F46E5',
                            buttonTextColor: '#FFFFFF'
                        },
                    };

                    this.elements.push({
                        id: Date.now().toString(),
                        type: type,
                        content: { ...defaults[type] }
                    });
                },

                selectElement(id) {
                    this.selectedElement = id;
                },

                getSelectedElement() {
                    return this.elements.find(el => el.id === this.selectedElement);
                },

                moveElement(index, direction) {
                    const newIndex = index + direction;
                    if (newIndex >= 0 && newIndex < this.elements.length) {
                        const temp = this.elements[index];
                        this.elements[index] = this.elements[newIndex];
                        this.elements[newIndex] = temp;
                    }
                },

                duplicateElement(index) {
                    const element = JSON.parse(JSON.stringify(this.elements[index]));
                    element.id = Date.now().toString();
                    this.elements.splice(index + 1, 0, element);
                },

                removeElement(index) {
                    if (this.elements[index].id === this.selectedElement) {
                        this.selectedElement = null;
                    }
                    this.elements.splice(index, 1);
                },

                async save() {
                    this.saving = true;
                    try {
                        const response = await fetch('{{ route("builder.save", $page) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                content: this.elements,
                                settings: { title: this.pageTitle }
                            })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.$dispatch('notify', { message: 'Page saved successfully', type: 'success' });
                        }
                    } catch (error) {
                        this.$dispatch('notify', { message: 'Failed to save page', type: 'error' });
                    }
                    this.saving = false;
                },

                preview() {
                    window.open('{{ route("pages.preview", $page) }}', '_blank');
                },

                async publish() {
                    await this.save();
                    // Additional publish logic
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
```

---

## Day 6: Builder Elements as Blade Components

### Element Components

```php
// app/View/Components/Builder/Heading.php
namespace App\View\Components\Builder;

use Illuminate\View\Component;

class Heading extends Component
{
    public string $text;
    public string $size;
    public string $color;

    public function __construct(string $text = '', string $size = 'text-3xl', string $color = '#000000')
    {
        $this->text = $text;
        $this->size = $size;
        $this->color = $color;
    }

    public function render()
    {
        return view('components.builder.heading');
    }
}
```

```blade
{{-- resources/views/components/builder/heading.blade.php --}}
<h2 class="{{ $size }} font-bold" style="color: {{ $color }}">
    {{ $text }}
</h2>
```

```blade
{{-- resources/views/components/builder/button.blade.php --}}
@props(['text' => 'Click Me', 'url' => '#', 'bgColor' => '#4F46E5', 'textColor' => '#FFFFFF'])

<a href="{{ $url }}"
   class="inline-block px-6 py-3 rounded-md font-semibold transition-colors"
   style="background-color: {{ $bgColor }}; color: {{ $textColor }}">
    {{ $text }}
</a>
```

```blade
{{-- resources/views/components/builder/hero.blade.php --}}
@props([
    'title' => 'Welcome',
    'subtitle' => '',
    'buttonText' => 'Get Started',
    'buttonUrl' => '#',
    'bgColor' => '#F3F4F6',
    'titleColor' => '#111827',
    'subtitleColor' => '#6B7280',
    'buttonBgColor' => '#4F46E5',
    'buttonTextColor' => '#FFFFFF'
])

<section class="py-20 px-4 text-center" style="background-color: {{ $bgColor }}">
    <h1 class="text-5xl font-bold mb-4" style="color: {{ $titleColor }}">
        {{ $title }}
    </h1>
    @if($subtitle)
        <p class="text-xl mb-8" style="color: {{ $subtitleColor }}">
            {{ $subtitle }}
        </p>
    @endif
    <a href="{{ $buttonUrl }}"
       class="inline-block px-8 py-4 rounded-md font-semibold text-lg transition-colors"
       style="background-color: {{ $buttonBgColor }}; color: {{ $buttonTextColor }}">
        {{ $buttonText }}
    </a>
</section>
```

### Page Renderer Service

```php
// app/Services/PageRenderer.php
namespace App\Services;

use Illuminate\Support\Facades\Blade;

class PageRenderer
{
    public function render(array $elements): string
    {
        $html = '';

        foreach ($elements as $element) {
            $html .= $this->renderElement($element);
        }

        return $html;
    }

    protected function renderElement(array $element): string
    {
        $type = $element['type'];
        $content = $element['content'];

        return match($type) {
            'heading' => view('components.builder.heading', $content)->render(),
            'text' => view('components.builder.text', $content)->render(),
            'image' => view('components.builder.image', $content)->render(),
            'button' => view('components.builder.button', $content)->render(),
            'hero' => view('components.builder.hero', $content)->render(),
            default => '',
        };
    }
}
```

---

## Day 7: Templates

### Template Controller

```php
// app/Http/Controllers/TemplateController.php
namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Page;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::all()->groupBy('category');

        return view('templates.index', compact('templates'));
    }

    public function use(Template $template)
    {
        $page = auth()->user()->pages()->create([
            'title' => $template->name . ' - Copy',
            'slug' => \Str::slug($template->name . '-' . time()),
            'content' => $template->content,
            'status' => 'draft',
        ]);

        return redirect()->route('builder.edit', $page);
    }
}
```

### Templates View

```blade
{{-- resources/views/templates/index.blade.php --}}
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-8">Choose a Template</h1>

            @foreach($templates as $category => $categoryTemplates)
                <div class="mb-12">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">{{ $category }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($categoryTemplates as $template)
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                                <img src="{{ $template->thumbnail ?? '/images/template-placeholder.jpg' }}"
                                     alt="{{ $template->name }}"
                                     class="w-full h-48 object-cover">
                                <div class="p-4">
                                    <h3 class="font-medium text-gray-900">{{ $template->name }}</h3>
                                    <div class="mt-4 flex items-center justify-between">
                                        @if($template->is_premium)
                                            <span class="text-xs font-medium text-yellow-600">Premium</span>
                                        @else
                                            <span class="text-xs font-medium text-green-600">Free</span>
                                        @endif
                                        <form action="{{ route('templates.use', $template) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                                Use Template
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
```

---

## Day 8: Media Library

### Media Controller

```php
// app/Http/Controllers/MediaController.php
namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index()
    {
        $media = auth()->user()->media()->latest()->paginate(24);

        return view('media.index', compact('media'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('media/' . auth()->id(), 'public');

        $media = auth()->user()->media()->create([
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'success' => true,
            'media' => $media,
            'url' => Storage::url($path),
        ]);
    }

    public function destroy(Media $media)
    {
        $this->authorize('delete', $media);

        Storage::disk('public')->delete($media->path);
        $media->delete();

        return back()->with('success', 'Media deleted successfully');
    }
}
```

### Media Library View with AlpineJS Upload

```blade
{{-- resources/views/media/index.blade.php --}}
<x-app-layout>
    <div x-data="mediaLibrary()" class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Media Library</h1>
                <button @click="$refs.fileInput.click()" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Upload Files
                </button>
                <input type="file" x-ref="fileInput" @change="uploadFiles" multiple accept="image/*" class="hidden">
            </div>

            {{-- Upload Progress --}}
            <div x-show="uploading" class="mb-6 bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-center">
                    <svg class="animate-spin h-5 w-5 text-indigo-600 mr-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Uploading...</span>
                </div>
            </div>

            {{-- Media Grid --}}
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($media as $item)
                    <div class="relative group bg-white rounded-lg shadow-sm overflow-hidden">
                        <img src="{{ Storage::url($item->path) }}"
                             alt="{{ $item->filename }}"
                             class="w-full h-32 object-cover">
                        <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center space-x-2">
                            <button @click="copyUrl('{{ Storage::url($item->path) }}')" class="p-2 bg-white rounded-full text-gray-600 hover:text-gray-900">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                            <form action="{{ route('media.destroy', $item) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 bg-white rounded-full text-red-600 hover:text-red-900">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $media->links() }}
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function mediaLibrary() {
            return {
                uploading: false,

                async uploadFiles(event) {
                    const files = event.target.files;
                    if (!files.length) return;

                    this.uploading = true;

                    for (const file of files) {
                        const formData = new FormData();
                        formData.append('file', file);

                        try {
                            await fetch('{{ route("media.store") }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: formData
                            });
                        } catch (error) {
                            console.error('Upload failed:', error);
                        }
                    }

                    this.uploading = false;
                    window.location.reload();
                },

                copyUrl(url) {
                    navigator.clipboard.writeText(url);
                    alert('URL copied to clipboard!');
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
```

---

## Day 9: Publishing

### Publish Controller

```php
// app/Http/Controllers/PublishController.php
namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PageRenderer;
use Illuminate\Http\Request;

class PublishController extends Controller
{
    public function __construct(protected PageRenderer $renderer)
    {
    }

    public function publish(Page $page)
    {
        $this->authorize('update', $page);

        $page->update(['status' => 'published']);

        return back()->with('success', 'Page published successfully!');
    }

    public function unpublish(Page $page)
    {
        $this->authorize('update', $page);

        $page->update(['status' => 'draft']);

        return back()->with('success', 'Page unpublished.');
    }

    public function preview(Page $page)
    {
        $html = $this->renderer->render($page->content ?? []);

        return view('pages.preview', [
            'page' => $page,
            'content' => $html,
        ]);
    }

    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $html = $this->renderer->render($page->content ?? []);

        return view('pages.show', [
            'page' => $page,
            'content' => $html,
        ]);
    }
}
```

### Published Page View

```blade
{{-- resources/views/pages/show.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->settings['seo_title'] ?? $page->title }}</title>
    <meta name="description" content="{{ $page->settings['seo_description'] ?? '' }}">
    @vite(['resources/css/app.css'])
</head>
<body>
    {!! $content !!}
</body>
</html>
```

---

## Day 10: Custom Domains

### Domain Controller

```php
// app/Http/Controllers/DomainController.php
namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function update(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'custom_domain' => 'nullable|string|max:255|unique:pages,custom_domain,' . $page->id,
        ]);

        $page->update($validated);

        return back()->with('success', 'Custom domain updated. Please configure your DNS settings.');
    }

    public function verify(Page $page)
    {
        $this->authorize('update', $page);

        if (!$page->custom_domain) {
            return back()->with('error', 'No custom domain configured.');
        }

        // Check DNS configuration
        $records = dns_get_record($page->custom_domain, DNS_CNAME);
        $isValid = collect($records)->contains(function ($record) {
            return $record['target'] === config('app.domain');
        });

        if ($isValid) {
            $page->update(['domain_verified_at' => now()]);
            return back()->with('success', 'Domain verified successfully!');
        }

        return back()->with('error', 'DNS not configured correctly. Please add a CNAME record pointing to ' . config('app.domain'));
    }
}
```

### Domain Middleware

```php
// app/Http/Middleware/CustomDomainMiddleware.php
namespace App\Http\Middleware;

use App\Models\Page;
use Closure;
use Illuminate\Http\Request;

class CustomDomainMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        // Skip for main domain
        if ($host === config('app.domain')) {
            return $next($request);
        }

        // Find page by custom domain
        $page = Page::where('custom_domain', $host)
            ->where('status', 'published')
            ->whereNotNull('domain_verified_at')
            ->first();

        if ($page) {
            $request->merge(['custom_domain_page' => $page]);
        }

        return $next($request);
    }
}
```

---

## Day 11-12: Payments with Strategy Pattern

### Payment Strategy Interface

```php
// app/Contracts/PaymentGateway.php
namespace App\Contracts;

interface PaymentGateway
{
    public function createCustomer(array $data): string;
    public function createSubscription(string $customerId, string $planId): array;
    public function cancelSubscription(string $subscriptionId): bool;
    public function handleWebhook(array $payload): void;
}
```

### Stripe Implementation

```php
// app/Services/Payments/StripeGateway.php
namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use Stripe\StripeClient;

class StripeGateway implements PaymentGateway
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createCustomer(array $data): string
    {
        $customer = $this->stripe->customers->create([
            'email' => $data['email'],
            'name' => $data['name'],
        ]);

        return $customer->id;
    }

    public function createSubscription(string $customerId, string $planId): array
    {
        $subscription = $this->stripe->subscriptions->create([
            'customer' => $customerId,
            'items' => [['price' => $planId]],
        ]);

        return [
            'id' => $subscription->id,
            'status' => $subscription->status,
            'current_period_end' => $subscription->current_period_end,
        ];
    }

    public function cancelSubscription(string $subscriptionId): bool
    {
        $this->stripe->subscriptions->cancel($subscriptionId);
        return true;
    }

    public function handleWebhook(array $payload): void
    {
        // Handle Stripe webhook events
    }
}
```

### PayPal Implementation

```php
// app/Services/Payments/PayPalGateway.php
namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use Illuminate\Support\Facades\Http;

class PayPalGateway implements PaymentGateway
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $secret;

    public function __construct()
    {
        $this->baseUrl = config('services.paypal.sandbox')
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
        $this->clientId = config('services.paypal.client_id');
        $this->secret = config('services.paypal.secret');
    }

    protected function getAccessToken(): string
    {
        $response = Http::withBasicAuth($this->clientId, $this->secret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        return $response->json('access_token');
    }

    public function createCustomer(array $data): string
    {
        // PayPal doesn't have a direct customer concept
        return $data['email'];
    }

    public function createSubscription(string $customerId, string $planId): array
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/v1/billing/subscriptions", [
                'plan_id' => $planId,
                'subscriber' => ['email_address' => $customerId],
            ]);

        return [
            'id' => $response->json('id'),
            'status' => $response->json('status'),
            'approval_url' => collect($response->json('links'))->firstWhere('rel', 'approve')['href'],
        ];
    }

    public function cancelSubscription(string $subscriptionId): bool
    {
        $token = $this->getAccessToken();

        Http::withToken($token)
            ->post("{$this->baseUrl}/v1/billing/subscriptions/{$subscriptionId}/cancel", [
                'reason' => 'User requested cancellation',
            ]);

        return true;
    }

    public function handleWebhook(array $payload): void
    {
        // Handle PayPal webhook events
    }
}
```

### Payment Service Provider

```php
// app/Providers/PaymentServiceProvider.php
namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Services\Payments\StripeGateway;
use App\Services\Payments\PayPalGateway;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function ($app) {
            return match(config('payments.default')) {
                'stripe' => new StripeGateway(),
                'paypal' => new PayPalGateway(),
                default => new StripeGateway(),
            };
        });
    }
}
```

### Subscription Controller

```php
// app/Http/Controllers/SubscriptionController.php
namespace App\Http\Controllers;

use App\Contracts\PaymentGateway;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(protected PaymentGateway $gateway)
    {
    }

    public function index()
    {
        $plans = config('payments.plans');
        $currentSubscription = auth()->user()->subscription;

        return view('subscriptions.index', compact('plans', 'currentSubscription'));
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:basic,pro,enterprise',
        ]);

        $user = auth()->user();
        $planId = config("payments.plans.{$request->plan}.stripe_price_id");

        // Create or get customer
        if (!$user->stripe_customer_id) {
            $customerId = $this->gateway->createCustomer([
                'email' => $user->email,
                'name' => $user->name,
            ]);
            $user->update(['stripe_customer_id' => $customerId]);
        }

        // Create subscription
        $subscription = $this->gateway->createSubscription($user->stripe_customer_id, $planId);

        // Save to database
        $user->subscription()->create([
            'provider' => config('payments.default'),
            'provider_id' => $subscription['id'],
            'plan' => $request->plan,
            'starts_at' => now(),
            'ends_at' => isset($subscription['current_period_end'])
                ? \Carbon\Carbon::createFromTimestamp($subscription['current_period_end'])
                : null,
            'status' => 'active',
        ]);

        $user->update(['subscription_tier' => $request->plan]);

        return redirect()->route('dashboard')->with('success', 'Subscription activated!');
    }

    public function cancel()
    {
        $subscription = auth()->user()->subscription;

        if ($subscription) {
            $this->gateway->cancelSubscription($subscription->provider_id);
            $subscription->update(['status' => 'cancelled']);
            auth()->user()->update(['subscription_tier' => 'free']);
        }

        return back()->with('success', 'Subscription cancelled.');
    }
}
```

### Pricing Page View

```blade
{{-- resources/views/subscriptions/index.blade.php --}}
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-3xl font-bold text-gray-900">Choose Your Plan</h1>
                <p class="mt-4 text-lg text-gray-600">Scale your landing pages as you grow</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($plans as $key => $plan)
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden {{ $key === 'pro' ? 'ring-2 ring-indigo-500' : '' }}">
                        @if($key === 'pro')
                            <div class="bg-indigo-500 text-white text-center py-2 text-sm font-medium">
                                Most Popular
                            </div>
                        @endif
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $plan['name'] }}</h3>
                            <div class="mt-4">
                                <span class="text-4xl font-bold text-gray-900">${{ $plan['price'] }}</span>
                                <span class="text-gray-500">/month</span>
                            </div>
                            <ul class="mt-6 space-y-3">
                                @foreach($plan['features'] as $feature)
                                    <li class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                            <form action="{{ route('subscriptions.subscribe') }}" method="POST" class="mt-6">
                                @csrf
                                <input type="hidden" name="plan" value="{{ $key }}">
                                <button type="submit"
                                        class="w-full py-3 px-4 rounded-md font-semibold {{ $key === 'pro' ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-gray-100 text-gray-900 hover:bg-gray-200' }}"
                                        {{ $currentSubscription?->plan === $key ? 'disabled' : '' }}>
                                    {{ $currentSubscription?->plan === $key ? 'Current Plan' : 'Subscribe' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
```

---

## Day 13: PHPUnit Testing

### Feature Tests

```php
// tests/Feature/PageTest.php
namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('pages.store'), [
            'title' => 'Test Page',
            'slug' => 'test-page',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pages', [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_update_page(): void
    {
        $user = User::factory()->create();
        $page = Page::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('pages.update', $page), [
            'title' => 'Updated Title',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_cannot_update_others_page(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $page = Page::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->put(route('pages.update', $page), [
            'title' => 'Hacked Title',
        ]);

        $response->assertForbidden();
    }

    public function test_builder_saves_content(): void
    {
        $user = User::factory()->create();
        $page = Page::factory()->create(['user_id' => $user->id]);

        $content = [
            ['id' => '1', 'type' => 'heading', 'content' => ['text' => 'Hello']],
        ];

        $response = $this->actingAs($user)->postJson(route('builder.save', $page), [
            'content' => $content,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertEquals($content, $page->fresh()->content);
    }

    public function test_published_page_is_accessible(): void
    {
        $page = Page::factory()->create(['status' => 'published']);

        $response = $this->get('/p/' . $page->slug);

        $response->assertOk();
    }

    public function test_draft_page_is_not_accessible(): void
    {
        $page = Page::factory()->create(['status' => 'draft']);

        $response = $this->get('/p/' . $page->slug);

        $response->assertNotFound();
    }
}
```

### Unit Tests

```php
// tests/Unit/PageRendererTest.php
namespace Tests\Unit;

use App\Services\PageRenderer;
use Tests\TestCase;

class PageRendererTest extends TestCase
{
    protected PageRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new PageRenderer();
    }

    public function test_renders_heading_element(): void
    {
        $elements = [
            ['type' => 'heading', 'content' => ['text' => 'Test', 'size' => 'text-3xl', 'color' => '#000']],
        ];

        $html = $this->renderer->render($elements);

        $this->assertStringContainsString('Test', $html);
        $this->assertStringContainsString('text-3xl', $html);
    }

    public function test_renders_multiple_elements(): void
    {
        $elements = [
            ['type' => 'heading', 'content' => ['text' => 'Title', 'size' => 'text-3xl', 'color' => '#000']],
            ['type' => 'text', 'content' => ['text' => 'Paragraph', 'color' => '#333']],
        ];

        $html = $this->renderer->render($elements);

        $this->assertStringContainsString('Title', $html);
        $this->assertStringContainsString('Paragraph', $html);
    }
}
```

### Run Tests

```bash
php artisan test
php artisan test --coverage
```

---

## Day 14: Deployment

### Production Configuration

```php
// config/app.php (production settings)
'env' => env('APP_ENV', 'production'),
'debug' => env('APP_DEBUG', false),
```

### Deployment Script

```bash
#!/bin/bash
# deploy.sh

set -e

echo "Deploying Landing Page Builder..."

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Build assets
npm ci
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
php artisan queue:restart

echo "Deployment complete!"
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/landing-page-builder/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Environment Variables

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=landing_page_builder
DB_USERNAME=your_username
DB_PASSWORD=your_password

STRIPE_KEY=pk_live_xxx
STRIPE_SECRET=sk_live_xxx

PAYPAL_CLIENT_ID=xxx
PAYPAL_SECRET=xxx
PAYPAL_SANDBOX=false
```

---

## Summary

This 14-day implementation plan provides a complete Landing Page Builder SaaS using:

- **Laravel 11** for backend logic and API
- **Blade templates** for server-side rendering
- **AlpineJS** for reactive UI interactions
- **TailwindCSS v4** for styling
- **MySQL** for data persistence
- **PHPUnit** for testing
- **Strategy Pattern** for payment gateway flexibility

All code examples are PHP/Blade/AlpineJS compliant with no React, Vue, TypeScript, or other JavaScript frameworks.
