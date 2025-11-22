# Setup Documentation

Complete setup guide for the Landing Page Builder SaaS application.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Laravel Installation](#laravel-installation)
3. [Database Setup](#database-setup)
4. [TailwindCSS v4 Installation](#tailwindcss-v4-installation)
5. [AlpineJS Setup](#alpinejs-setup)
6. [Directory Structure](#directory-structure)
7. [Environment Configuration](#environment-configuration)
8. [Laravel Vite Setup](#laravel-vite-setup)
9. [IDE Configuration](#ide-configuration)
10. [Git Hooks](#git-hooks)
11. [Local SSL Setup](#local-ssl-setup)
12. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Required Software

| Software | Minimum Version | Recommended Version | Check Command |
|----------|----------------|---------------------|---------------|
| PHP | 8.2 | 8.3 | `php -v` |
| Composer | 2.5 | 2.7+ | `composer -V` |
| Node.js | 18.x | 20.x LTS | `node -v` |
| npm | 9.x | 10.x | `npm -v` |
| MySQL | 8.0 | 8.0+ | `mysql --version` |
| Git | 2.30 | 2.40+ | `git --version` |

### PHP Extensions Required

```bash
# Check installed extensions
php -m

# Required extensions
- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PCRE
- PDO
- PDO_MySQL
- Tokenizer
- XML
- Zip
```

### Install PHP Extensions (Ubuntu/Debian)

```bash
sudo apt update
sudo apt install -y php8.3-cli php8.3-fpm php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-gd \
    php8.3-intl php8.3-readline
```

### Install PHP Extensions (macOS with Homebrew)

```bash
brew install php@8.3
brew install php@8.3-mysql php@8.3-mbstring php@8.3-xml
```

### Install Node.js (Using nvm - Recommended)

```bash
# Install nvm
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash

# Reload shell
source ~/.bashrc  # or ~/.zshrc

# Install Node.js
nvm install 20
nvm use 20
nvm alias default 20
```

---

## Laravel Installation

### Create New Laravel 11 Project

```bash
# Using Composer
composer create-project laravel/laravel landing-page-builder-saas "11.*"

# Navigate to project
cd landing-page-builder-saas

# Verify Laravel version
php artisan --version
```

### Clone Existing Project

```bash
# Clone repository
git clone <repository-url> landing-page-builder-saas
cd landing-page-builder-saas

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Essential Laravel Packages

```bash
# Development packages
composer require --dev laravel/pint
composer require --dev larastan/larastan
composer require --dev pestphp/pest --with-all-dependencies
composer require --dev pestphp/pest-plugin-laravel

# Production packages
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require intervention/image
```

---

## Database Setup

### MySQL Installation

#### Ubuntu/Debian

```bash
sudo apt update
sudo apt install mysql-server
sudo mysql_secure_installation
```

#### macOS

```bash
brew install mysql
brew services start mysql
mysql_secure_installation
```

### Create Database and User

```bash
# Login to MySQL as root
sudo mysql -u root -p

# Run the following SQL commands:
```

```sql
-- Create database
CREATE DATABASE landing_page_builder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'lpb_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';

-- Grant privileges
GRANT ALL PRIVILEGES ON landing_page_builder.* TO 'lpb_user'@'localhost';

-- Apply privileges
FLUSH PRIVILEGES;

-- Verify
SHOW DATABASES;
SELECT User, Host FROM mysql.user;

-- Exit
EXIT;
```

### Run Migrations

```bash
# Run all migrations
php artisan migrate

# Run migrations with seed data
php artisan migrate --seed

# Fresh migration (drops all tables)
php artisan migrate:fresh --seed

# Check migration status
php artisan migrate:status
```

### Database Configuration Verification

```bash
# Test database connection
php artisan db:show

# Or use tinker
php artisan tinker
>>> DB::connection()->getPdo();
```

---

## TailwindCSS v4 Installation

### Install TailwindCSS v4

```bash
# Install TailwindCSS v4 and Vite plugin
npm install tailwindcss@^4.0.0 @tailwindcss/vite@^4.0.0
```

### Configure Vite for TailwindCSS v4

Update `vite.config.js`:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

### Create Main CSS File

Create/update `resources/css/app.css`:

```css
@import "tailwindcss";

/* Custom CSS Variables */
@theme {
    /* Colors */
    --color-primary-50: #eff6ff;
    --color-primary-100: #dbeafe;
    --color-primary-200: #bfdbfe;
    --color-primary-300: #93c5fd;
    --color-primary-400: #60a5fa;
    --color-primary-500: #3b82f6;
    --color-primary-600: #2563eb;
    --color-primary-700: #1d4ed8;
    --color-primary-800: #1e40af;
    --color-primary-900: #1e3a8a;
    --color-primary-950: #172554;

    --color-secondary-50: #f8fafc;
    --color-secondary-100: #f1f5f9;
    --color-secondary-200: #e2e8f0;
    --color-secondary-300: #cbd5e1;
    --color-secondary-400: #94a3b8;
    --color-secondary-500: #64748b;
    --color-secondary-600: #475569;
    --color-secondary-700: #334155;
    --color-secondary-800: #1e293b;
    --color-secondary-900: #0f172a;
    --color-secondary-950: #020617;

    --color-success: #10b981;
    --color-warning: #f59e0b;
    --color-danger: #ef4444;
    --color-info: #3b82f6;

    /* Fonts */
    --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
    --font-mono: 'Fira Code', ui-monospace, monospace;

    /* Spacing */
    --spacing-18: 4.5rem;
    --spacing-88: 22rem;
    --spacing-128: 32rem;

    /* Border Radius */
    --radius-4xl: 2rem;

    /* Shadows */
    --shadow-soft: 0 2px 15px -3px rgb(0 0 0 / 0.07), 0 10px 20px -2px rgb(0 0 0 / 0.04);
    --shadow-glow: 0 0 15px rgb(59 130 246 / 0.5);

    /* Animations */
    --animate-fade-in: fade-in 0.5s ease-out;
    --animate-slide-up: slide-up 0.5s ease-out;
    --animate-slide-down: slide-down 0.3s ease-out;
}

/* Custom Keyframes */
@keyframes fade-in {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slide-down {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Custom Utility Classes */
@utility container-narrow {
    max-width: 65ch;
    margin-inline: auto;
    padding-inline: 1rem;
}

@utility text-gradient {
    background: linear-gradient(to right, var(--color-primary-500), var(--color-primary-700));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

@utility glass {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Base Styles */
@layer base {
    html {
        scroll-behavior: smooth;
    }

    body {
        @apply antialiased;
    }

    h1, h2, h3, h4, h5, h6 {
        @apply font-bold tracking-tight;
    }

    a {
        @apply transition-colors duration-200;
    }

    button {
        @apply cursor-pointer;
    }

    input, textarea, select {
        @apply border-secondary-300 focus:border-primary-500 focus:ring-primary-500;
    }
}

/* Component Styles */
@layer components {
    .btn {
        @apply inline-flex items-center justify-center px-4 py-2 rounded-lg font-medium
               transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2;
    }

    .btn-primary {
        @apply btn bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500;
    }

    .btn-secondary {
        @apply btn bg-secondary-100 text-secondary-700 hover:bg-secondary-200 focus:ring-secondary-500;
    }

    .btn-outline {
        @apply btn border-2 border-primary-600 text-primary-600 hover:bg-primary-50 focus:ring-primary-500;
    }

    .btn-ghost {
        @apply btn text-secondary-600 hover:bg-secondary-100 focus:ring-secondary-500;
    }

    .btn-danger {
        @apply btn bg-danger text-white hover:bg-red-600 focus:ring-red-500;
    }

    .btn-sm {
        @apply px-3 py-1.5 text-sm;
    }

    .btn-lg {
        @apply px-6 py-3 text-lg;
    }

    .card {
        @apply bg-white rounded-xl shadow-soft border border-secondary-100 p-6;
    }

    .input {
        @apply w-full px-4 py-2 rounded-lg border border-secondary-300
               focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20
               transition-all duration-200;
    }

    .label {
        @apply block text-sm font-medium text-secondary-700 mb-1;
    }

    .alert {
        @apply p-4 rounded-lg border;
    }

    .alert-success {
        @apply alert bg-green-50 border-green-200 text-green-800;
    }

    .alert-error {
        @apply alert bg-red-50 border-red-200 text-red-800;
    }

    .alert-warning {
        @apply alert bg-yellow-50 border-yellow-200 text-yellow-800;
    }

    .alert-info {
        @apply alert bg-blue-50 border-blue-200 text-blue-800;
    }
}
```

### Install Google Fonts (Optional)

Add to your main layout `resources/views/layouts/app.blade.php`:

```html
<head>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
```

---

## AlpineJS Setup

### Install AlpineJS

```bash
npm install alpinejs
```

### Configure AlpineJS

Update `resources/js/app.js`:

```javascript
import './bootstrap';
import Alpine from 'alpinejs';

// Make Alpine available globally
window.Alpine = Alpine;

// Register Alpine plugins (if needed)
// import focus from '@alpinejs/focus';
// Alpine.plugin(focus);

// Register global Alpine data
Alpine.data('dropdown', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    }
}));

Alpine.data('modal', () => ({
    show: false,
    open() {
        this.show = true;
        document.body.classList.add('overflow-hidden');
    },
    close() {
        this.show = false;
        document.body.classList.remove('overflow-hidden');
    }
}));

Alpine.data('tabs', (defaultTab = '') => ({
    activeTab: defaultTab,
    setTab(tab) {
        this.activeTab = tab;
    },
    isActive(tab) {
        return this.activeTab === tab;
    }
}));

Alpine.data('toast', () => ({
    toasts: [],
    add(message, type = 'info', duration = 3000) {
        const id = Date.now();
        this.toasts.push({ id, message, type });
        setTimeout(() => this.remove(id), duration);
    },
    remove(id) {
        this.toasts = this.toasts.filter(t => t.id !== id);
    }
}));

// Register Alpine stores
Alpine.store('darkMode', {
    on: false,
    toggle() {
        this.on = !this.on;
        localStorage.setItem('darkMode', this.on);
        document.documentElement.classList.toggle('dark', this.on);
    },
    init() {
        this.on = localStorage.getItem('darkMode') === 'true';
        document.documentElement.classList.toggle('dark', this.on);
    }
});

// Start Alpine
Alpine.start();
```

### Install Alpine Plugins (Optional)

```bash
npm install @alpinejs/focus @alpinejs/collapse @alpinejs/intersect @alpinejs/persist
```

Update `resources/js/app.js` to include plugins:

```javascript
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';
import persist from '@alpinejs/persist';

Alpine.plugin(focus);
Alpine.plugin(collapse);
Alpine.plugin(intersect);
Alpine.plugin(persist);

window.Alpine = Alpine;
Alpine.start();
```

### AlpineJS Usage Examples in Blade

```html
<!-- Dropdown -->
<div x-data="dropdown" class="relative">
    <button @click="toggle()" class="btn-primary">
        Menu
    </button>
    <div x-show="open" @click.outside="close()" x-transition class="absolute mt-2 w-48 bg-white rounded-lg shadow-lg">
        <a href="#" class="block px-4 py-2 hover:bg-gray-100">Option 1</a>
        <a href="#" class="block px-4 py-2 hover:bg-gray-100">Option 2</a>
    </div>
</div>

<!-- Modal -->
<div x-data="modal">
    <button @click="open()" class="btn-primary">Open Modal</button>

    <div x-show="show" x-transition class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="close()"></div>
        <div class="relative bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h2 class="text-xl font-bold">Modal Title</h2>
            <p class="mt-2">Modal content goes here.</p>
            <button @click="close()" class="mt-4 btn-secondary">Close</button>
        </div>
    </div>
</div>

<!-- Tabs -->
<div x-data="tabs('tab1')">
    <div class="flex border-b">
        <button @click="setTab('tab1')" :class="isActive('tab1') ? 'border-primary-500' : 'border-transparent'" class="px-4 py-2 border-b-2">
            Tab 1
        </button>
        <button @click="setTab('tab2')" :class="isActive('tab2') ? 'border-primary-500' : 'border-transparent'" class="px-4 py-2 border-b-2">
            Tab 2
        </button>
    </div>
    <div x-show="isActive('tab1')">Content for Tab 1</div>
    <div x-show="isActive('tab2')">Content for Tab 2</div>
</div>
```

---

## Directory Structure

### Service-Repository Pattern Structure

```
app/
├── Console/
│   └── Commands/
├── Contracts/                    # Interfaces
│   ├── Repositories/
│   │   ├── BaseRepositoryInterface.php
│   │   ├── UserRepositoryInterface.php
│   │   ├── LandingPageRepositoryInterface.php
│   │   └── ...
│   └── Services/
│       ├── UserServiceInterface.php
│       ├── LandingPageServiceInterface.php
│       └── ...
├── DTOs/                         # Data Transfer Objects
│   ├── User/
│   │   ├── CreateUserDTO.php
│   │   └── UpdateUserDTO.php
│   └── LandingPage/
│       ├── CreateLandingPageDTO.php
│       └── UpdateLandingPageDTO.php
├── Exceptions/
│   ├── Handler.php
│   └── Custom/
│       ├── ResourceNotFoundException.php
│       └── ValidationException.php
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php
│   │   ├── Auth/
│   │   ├── Dashboard/
│   │   │   └── DashboardController.php
│   │   └── LandingPage/
│   │       └── LandingPageController.php
│   ├── Middleware/
│   ├── Requests/
│   │   ├── LandingPage/
│   │   │   ├── StoreLandingPageRequest.php
│   │   │   └── UpdateLandingPageRequest.php
│   │   └── User/
│   └── Resources/
│       ├── LandingPageResource.php
│       └── UserResource.php
├── Models/
│   ├── User.php
│   ├── LandingPage.php
│   └── Traits/
│       ├── HasUuid.php
│       └── Auditable.php
├── Policies/
│   ├── LandingPagePolicy.php
│   └── UserPolicy.php
├── Providers/
│   ├── AppServiceProvider.php
│   └── RepositoryServiceProvider.php
├── Repositories/                 # Repository Implementations
│   ├── BaseRepository.php
│   ├── UserRepository.php
│   └── LandingPageRepository.php
├── Services/                     # Service Implementations
│   ├── UserService.php
│   └── LandingPageService.php
└── Support/
    └── Helpers/
        └── helpers.php
```

### Create Directory Structure

```bash
# Create all directories
mkdir -p app/Contracts/{Repositories,Services}
mkdir -p app/DTOs/{User,LandingPage}
mkdir -p app/Exceptions/Custom
mkdir -p app/Http/Controllers/{Auth,Dashboard,LandingPage}
mkdir -p app/Http/Requests/{LandingPage,User}
mkdir -p app/Http/Resources
mkdir -p app/Models/Traits
mkdir -p app/Policies
mkdir -p app/Repositories
mkdir -p app/Services
mkdir -p app/Support/Helpers
```

### Base Repository Interface

Create `app/Contracts/Repositories/BaseRepositoryInterface.php`:

```php
<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*']): Collection;

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    public function find(int|string $id, array $columns = ['*']): ?Model;

    public function findOrFail(int|string $id, array $columns = ['*']): Model;

    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model;

    public function findWhere(array $where, array $columns = ['*']): Collection;

    public function create(array $attributes): Model;

    public function update(int|string $id, array $attributes): Model;

    public function delete(int|string $id): bool;

    public function with(array $relations): self;

    public function orderBy(string $column, string $direction = 'asc'): self;
}
```

### Base Repository Implementation

Create `app/Repositories/BaseRepository.php`:

```php
<?php

namespace App\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;
    protected Builder $query;

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->query = $model->newQuery();
    }

    protected function resetQuery(): void
    {
        $this->query = $this->model->newQuery();
    }

    public function all(array $columns = ['*']): Collection
    {
        $result = $this->query->get($columns);
        $this->resetQuery();
        return $result;
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        $result = $this->query->paginate($perPage, $columns);
        $this->resetQuery();
        return $result;
    }

    public function find(int|string $id, array $columns = ['*']): ?Model
    {
        $result = $this->query->find($id, $columns);
        $this->resetQuery();
        return $result;
    }

    public function findOrFail(int|string $id, array $columns = ['*']): Model
    {
        $result = $this->query->findOrFail($id, $columns);
        $this->resetQuery();
        return $result;
    }

    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        $result = $this->query->where($field, $value)->first($columns);
        $this->resetQuery();
        return $result;
    }

    public function findWhere(array $where, array $columns = ['*']): Collection
    {
        $result = $this->query->where($where)->get($columns);
        $this->resetQuery();
        return $result;
    }

    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    public function update(int|string $id, array $attributes): Model
    {
        $record = $this->findOrFail($id);
        $record->update($attributes);
        return $record->fresh();
    }

    public function delete(int|string $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    public function with(array $relations): self
    {
        $this->query = $this->query->with($relations);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->query = $this->query->orderBy($column, $direction);
        return $this;
    }
}
```

### Repository Service Provider

Create `app/Providers/RepositoryServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repositories
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\LandingPageRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\LandingPageRepository;

// Services
use App\Contracts\Services\UserServiceInterface;
use App\Contracts\Services\LandingPageServiceInterface;
use App\Services\UserService;
use App\Services\LandingPageService;

class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        // Repositories
        UserRepositoryInterface::class => UserRepository::class,
        LandingPageRepositoryInterface::class => LandingPageRepository::class,

        // Services
        UserServiceInterface::class => UserService::class,
        LandingPageServiceInterface::class => LandingPageService::class,
    ];

    public function register(): void
    {
        foreach ($this->bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }

    public function boot(): void
    {
        //
    }
}
```

Register the provider in `bootstrap/providers.php`:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RepositoryServiceProvider::class,
];
```

---

## Environment Configuration

### Complete .env Example

Create `.env` file:

```env
#--------------------------------------------------------------------
# APPLICATION
#--------------------------------------------------------------------
APP_NAME="Landing Page Builder"
APP_ENV=local
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

#--------------------------------------------------------------------
# MAINTENANCE
#--------------------------------------------------------------------
APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------
BCRYPT_ROUNDS=12

#--------------------------------------------------------------------
# LOGGING
#--------------------------------------------------------------------
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=landing_page_builder
DB_USERNAME=lpb_user
DB_PASSWORD=your_secure_password_here
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

#--------------------------------------------------------------------
# SESSION
#--------------------------------------------------------------------
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

#--------------------------------------------------------------------
# BROADCASTING
#--------------------------------------------------------------------
BROADCAST_CONNECTION=log

#--------------------------------------------------------------------
# FILESYSTEM
#--------------------------------------------------------------------
FILESYSTEM_DISK=local

#--------------------------------------------------------------------
# QUEUE
#--------------------------------------------------------------------
QUEUE_CONNECTION=database

#--------------------------------------------------------------------
# CACHE
#--------------------------------------------------------------------
CACHE_STORE=database
CACHE_PREFIX=lpb_

#--------------------------------------------------------------------
# REDIS (if using Redis)
#--------------------------------------------------------------------
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

#--------------------------------------------------------------------
# MAIL
#--------------------------------------------------------------------
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@landingpagebuilder.test"
MAIL_FROM_NAME="${APP_NAME}"

#--------------------------------------------------------------------
# AWS (if using S3)
#--------------------------------------------------------------------
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

#--------------------------------------------------------------------
# PUSHER (if using real-time features)
#--------------------------------------------------------------------
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

#--------------------------------------------------------------------
# SANCTUM
#--------------------------------------------------------------------
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1

#--------------------------------------------------------------------
# CUSTOM APPLICATION SETTINGS
#--------------------------------------------------------------------
# Landing Page Builder specific settings
LPB_MAX_PAGES_FREE=3
LPB_MAX_PAGES_PRO=50
LPB_MAX_PAGES_BUSINESS=unlimited
LPB_STORAGE_LIMIT_FREE=100
LPB_STORAGE_LIMIT_PRO=5000
LPB_STORAGE_LIMIT_BUSINESS=50000
LPB_DEFAULT_THEME=light

# Feature flags
FEATURE_AI_CONTENT=false
FEATURE_CUSTOM_DOMAINS=true
FEATURE_ANALYTICS=true
FEATURE_A_B_TESTING=false

# Third-party integrations
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=

GOOGLE_ANALYTICS_ID=
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=

# OpenAI (for AI features)
OPENAI_API_KEY=
OPENAI_MODEL=gpt-4
```

### Environment-Specific Configurations

Create `.env.testing`:

```env
APP_NAME="Landing Page Builder Tests"
APP_ENV=testing
APP_DEBUG=true

DB_CONNECTION=mysql
DB_DATABASE=landing_page_builder_test

CACHE_STORE=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
MAIL_MAILER=array
```

---

## Laravel Vite Setup

### Vite Configuration

Complete `vite.config.js`:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            '~': '/resources',
        },
    },
    build: {
        // Generate manifest for production
        manifest: true,
        // Output directory
        outDir: 'public/build',
        // Rollup options
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['alpinejs'],
                },
            },
        },
    },
    server: {
        // For Docker/Sail users
        host: '0.0.0.0',
        hmr: {
            host: 'localhost',
        },
    },
});
```

### Package.json Scripts

Update `package.json`:

```json
{
    "private": true,
    "type": "module",
    "scripts": {
        "dev": "vite",
        "build": "vite build",
        "preview": "vite preview",
        "lint": "eslint resources/js --ext .js,.vue",
        "lint:fix": "eslint resources/js --ext .js,.vue --fix"
    },
    "devDependencies": {
        "autoprefixer": "^10.4.19",
        "axios": "^1.7.4",
        "laravel-vite-plugin": "^1.0.0",
        "vite": "^6.0.0"
    },
    "dependencies": {
        "@tailwindcss/vite": "^4.0.0",
        "alpinejs": "^3.14.0",
        "tailwindcss": "^4.0.0"
    }
}
```

### Bootstrap.js Configuration

Create/update `resources/js/bootstrap.js`:

```javascript
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF Token setup
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Axios interceptors for error handling
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
        if (error.response?.status === 419) {
            window.location.reload();
        }
        return Promise.reject(error);
    }
);
```

### Main Layout Template

Create `resources/views/layouts/app.blade.php`:

```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Landing Page Builder') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="h-full bg-secondary-50 font-sans antialiased">
    <div id="app" class="min-h-full">
        @include('partials.navigation')

        <main>
            @yield('content')
        </main>

        @include('partials.footer')
    </div>

    @stack('scripts')
</body>
</html>
```

### Running Development Server

```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server
npm run dev

# Or use concurrently
npm install -D concurrently
# Add to package.json scripts:
# "start": "concurrently \"php artisan serve\" \"npm run dev\""
npm run start
```

---

## IDE Configuration

### VS Code Configuration

Create `.vscode/settings.json`:

```json
{
    "editor.formatOnSave": true,
    "editor.defaultFormatter": "esbenp.prettier-vscode",
    "editor.tabSize": 4,
    "editor.insertSpaces": true,
    "editor.wordWrap": "on",

    "files.associations": {
        "*.blade.php": "blade"
    },

    "emmet.includeLanguages": {
        "blade": "html"
    },

    "tailwindCSS.includeLanguages": {
        "blade": "html"
    },

    "tailwindCSS.experimental.classRegex": [
        ["@apply\\s+([^;]*)", "([\\w-]+)"],
        ["class\\s*=\\s*[\"']([^\"']*)[\"']", "([\\w-]+)"]
    ],

    "[php]": {
        "editor.defaultFormatter": "open-collective.prettier-vscode",
        "editor.formatOnSave": true
    },

    "[blade]": {
        "editor.defaultFormatter": "shufo.vscode-blade-formatter",
        "editor.formatOnSave": true
    },

    "[javascript]": {
        "editor.defaultFormatter": "esbenp.prettier-vscode"
    },

    "php.validate.executablePath": "/usr/bin/php",

    "intelephense.files.maxSize": 5000000,
    "intelephense.environment.phpVersion": "8.3.0",

    "search.exclude": {
        "**/vendor": true,
        "**/node_modules": true,
        "**/public/build": true,
        "**/storage": true,
        "**/bootstrap/cache": true
    }
}
```

### VS Code Extensions

Create `.vscode/extensions.json`:

```json
{
    "recommendations": [
        "bmewburn.vscode-intelephense-client",
        "shufo.vscode-blade-formatter",
        "onecentlin.laravel-blade",
        "amiralizadeh9480.laravel-extra-intellisense",
        "bradlc.vscode-tailwindcss",
        "austenc.tailwind-docs",
        "esbenp.prettier-vscode",
        "dbaeumer.vscode-eslint",
        "mikestead.dotenv",
        "editorconfig.editorconfig",
        "eamodio.gitlens",
        "usernamehw.errorlens",
        "streetsidesoftware.code-spell-checker",
        "christian-kohler.path-intellisense"
    ]
}
```

### EditorConfig

Create `.editorconfig`:

```ini
root = true

[*]
charset = utf-8
end_of_line = lf
indent_size = 4
indent_style = space
insert_final_newline = true
trim_trailing_whitespace = true

[*.md]
trim_trailing_whitespace = false

[*.{yml,yaml}]
indent_size = 2

[*.{js,jsx,ts,tsx,vue}]
indent_size = 2

[*.json]
indent_size = 2

[docker-compose.yml]
indent_size = 2
```

### PHPStorm Configuration

Create `.idea/php.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<project version="4">
  <component name="PhpProjectSharedConfiguration" php_language_level="8.3">
    <option name="suggestChangeDefaultLanguageLevel" value="false" />
  </component>
</project>
```

### Prettier Configuration

Create `.prettierrc`:

```json
{
    "semi": true,
    "singleQuote": true,
    "tabWidth": 4,
    "trailingComma": "es5",
    "printWidth": 100,
    "bracketSpacing": true,
    "arrowParens": "avoid",
    "endOfLine": "lf",
    "overrides": [
        {
            "files": ["*.yml", "*.yaml", "*.json"],
            "options": {
                "tabWidth": 2
            }
        }
    ]
}
```

Create `.prettierignore`:

```
vendor/
node_modules/
public/
storage/
bootstrap/cache/
*.blade.php
```

### ESLint Configuration

Create `eslint.config.js`:

```javascript
export default [
    {
        ignores: ['vendor/**', 'node_modules/**', 'public/**'],
    },
    {
        files: ['resources/js/**/*.js'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            globals: {
                window: 'readonly',
                document: 'readonly',
                Alpine: 'readonly',
                axios: 'readonly',
            },
        },
        rules: {
            'no-unused-vars': 'warn',
            'no-console': 'warn',
            'semi': ['error', 'always'],
            'quotes': ['error', 'single'],
        },
    },
];
```

---

## Git Hooks

### Install Husky

```bash
npm install -D husky lint-staged
npx husky init
```

### Pre-commit Hook

Create `.husky/pre-commit`:

```bash
#!/usr/bin/env sh
. "$(dirname -- "$0")/_/husky.sh"

# Run lint-staged
npx lint-staged

# Run PHP linting
./vendor/bin/pint --test

# Run PHPStan
./vendor/bin/phpstan analyse --memory-limit=2G
```

### Commit-msg Hook

Create `.husky/commit-msg`:

```bash
#!/usr/bin/env sh
. "$(dirname -- "$0")/_/husky.sh"

# Validate commit message format
commit_regex='^(feat|fix|docs|style|refactor|perf|test|chore|revert)(\(.+\))?: .{1,100}$'

if ! grep -qE "$commit_regex" "$1"; then
    echo "ERROR: Invalid commit message format."
    echo ""
    echo "Valid format: type(scope): description"
    echo ""
    echo "Types: feat, fix, docs, style, refactor, perf, test, chore, revert"
    echo ""
    echo "Examples:"
    echo "  feat(auth): add password reset functionality"
    echo "  fix(landing-page): resolve image upload issue"
    echo "  docs: update API documentation"
    echo ""
    exit 1
fi
```

### Lint-staged Configuration

Add to `package.json`:

```json
{
    "lint-staged": {
        "*.js": [
            "eslint --fix",
            "prettier --write"
        ],
        "*.{css,scss}": [
            "prettier --write"
        ],
        "*.php": [
            "./vendor/bin/pint"
        ],
        "*.blade.php": [
            "blade-formatter --write"
        ]
    }
}
```

### Laravel Pint Configuration

Create `pint.json`:

```json
{
    "preset": "laravel",
    "rules": {
        "blank_line_before_statement": {
            "statements": [
                "break",
                "continue",
                "declare",
                "return",
                "throw",
                "try"
            ]
        },
        "concat_space": {
            "spacing": "one"
        },
        "method_argument_space": {
            "on_multiline": "ensure_fully_multiline",
            "keep_multiple_spaces_after_comma": false
        },
        "not_operator_with_successor_space": true,
        "ordered_imports": {
            "sort_algorithm": "alpha"
        },
        "single_trait_insert_per_statement": true,
        "types_spaces": {
            "space": "none"
        }
    },
    "exclude": [
        "bootstrap",
        "storage",
        "vendor",
        "node_modules"
    ]
}
```

### PHPStan Configuration

Create `phpstan.neon`:

```neon
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app/
        - config/
        - database/
        - routes/

    level: 6

    ignoreErrors:
        - '#PHPDoc tag @var#'

    excludePaths:
        - app/Http/Middleware/RedirectIfAuthenticated.php
        - vendor/

    checkMissingIterableValueType: false
```

---

## Local SSL Setup

### Using Laravel Valet (macOS)

```bash
# Install Valet
composer global require laravel/valet
valet install

# Park directory
cd ~/Sites
valet park

# Secure site with SSL
cd landing-page-builder-saas
valet secure

# Site available at: https://landing-page-builder-saas.test
```

### Using mkcert (Cross-platform)

```bash
# Install mkcert
# macOS
brew install mkcert
brew install nss  # for Firefox

# Ubuntu/Debian
sudo apt install libnss3-tools
wget -O mkcert https://github.com/FiloSottile/mkcert/releases/download/v1.4.4/mkcert-v1.4.4-linux-amd64
chmod +x mkcert
sudo mv mkcert /usr/local/bin/

# Create local CA
mkcert -install

# Generate certificates
mkdir -p storage/certs
cd storage/certs
mkcert localhost 127.0.0.1 ::1 landingpagebuilder.test

# Generated files:
# - localhost+3.pem
# - localhost+3-key.pem
```

### Nginx Configuration with SSL

Create `/etc/nginx/sites-available/landing-page-builder`:

```nginx
server {
    listen 80;
    server_name landingpagebuilder.test;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name landingpagebuilder.test;
    root /home/user/landing-page-builder-saas/public;

    ssl_certificate /home/user/landing-page-builder-saas/storage/certs/localhost+3.pem;
    ssl_certificate_key /home/user/landing-page-builder-saas/storage/certs/localhost+3-key.pem;

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
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/landing-page-builder /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Add to /etc/hosts

```bash
echo "127.0.0.1 landingpagebuilder.test" | sudo tee -a /etc/hosts
```

### Laravel Herd (macOS)

```bash
# Download from https://herd.laravel.com
# Sites automatically get SSL at .test domain
# https://landing-page-builder-saas.test
```

---

## Troubleshooting

### Common Issues and Solutions

#### 1. Composer Memory Limit Error

```bash
# Error: Allowed memory size exhausted

# Solution: Increase memory limit
COMPOSER_MEMORY_LIMIT=-1 composer install

# Or update php.ini
memory_limit = 2G
```

#### 2. Permission Issues

```bash
# Fix storage and cache permissions
sudo chown -R $USER:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Fix permission issues after deployment
php artisan storage:link
chmod -R 755 public/storage
```

#### 3. Vite Not Loading Assets

```bash
# Error: Vite manifest not found

# Ensure Vite is running in development
npm run dev

# For production, build assets
npm run build

# Clear Laravel cache
php artisan optimize:clear
```

#### 4. Database Connection Issues

```bash
# Error: SQLSTATE[HY000] [2002] Connection refused

# Check MySQL is running
sudo systemctl status mysql

# Verify credentials
mysql -u lpb_user -p landing_page_builder

# Check .env configuration
DB_HOST=127.0.0.1  # Use IP instead of 'localhost'
```

#### 5. Class Not Found Errors

```bash
# Regenerate autoload files
composer dump-autoload

# Clear all caches
php artisan optimize:clear

# If using service-repository pattern
php artisan clear-compiled
```

#### 6. TailwindCSS Styles Not Applying

```bash
# Ensure CSS is imported correctly
# resources/css/app.css should have:
@import "tailwindcss";

# Clear browser cache
# Chrome: Ctrl+Shift+R or Cmd+Shift+R

# Rebuild assets
npm run build
```

#### 7. Alpine.js Not Working

```javascript
// Ensure Alpine is started in app.js
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();  // This must be called!

// Check for JavaScript errors in console
// Ensure @vite directive is in your layout
```

#### 8. Session/CSRF Issues

```bash
# Error: 419 Page Expired

# Ensure CSRF token in forms
<form method="POST">
    @csrf
    ...
</form>

# Check session configuration
php artisan session:table
php artisan migrate

# Clear session
php artisan session:clear
```

#### 9. Environment Variables Not Loading

```bash
# Clear config cache
php artisan config:clear

# Regenerate cache
php artisan config:cache

# Ensure .env file exists
cp .env.example .env
php artisan key:generate
```

#### 10. NPM Install Failures

```bash
# Clear npm cache
npm cache clean --force

# Remove node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Use specific Node version
nvm use 20
```

### Debug Commands Reference

```bash
# Laravel debugging
php artisan about                    # Show application info
php artisan route:list              # List all routes
php artisan config:show database    # Show database config
php artisan env                     # Show current environment

# Clear all caches
php artisan optimize:clear

# Regenerate caches
php artisan optimize

# Database
php artisan db:show                 # Show database info
php artisan migrate:status          # Show migration status
php artisan schema:dump             # Dump database schema

# Testing
php artisan test                    # Run tests
php artisan test --parallel         # Run tests in parallel
php artisan test --coverage         # Run with coverage

# Queue and jobs
php artisan queue:work              # Process queue jobs
php artisan queue:failed            # List failed jobs

# Maintenance
php artisan down                    # Put app in maintenance mode
php artisan up                      # Bring app back up
```

### Performance Optimization

```bash
# Production optimization
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
npm run build

# Development (clear caches)
php artisan optimize:clear
```

### Logging and Monitoring

```bash
# View logs
tail -f storage/logs/laravel.log

# Real-time error monitoring
php artisan pail

# Query debugging (add to AppServiceProvider boot method)
DB::listen(function ($query) {
    Log::info($query->sql, $query->bindings);
});
```

---

## Quick Start Summary

```bash
# 1. Clone and install dependencies
git clone <repository-url> landing-page-builder-saas
cd landing-page-builder-saas
composer install
npm install

# 2. Environment setup
cp .env.example .env
php artisan key:generate

# 3. Configure database in .env
# DB_DATABASE=landing_page_builder
# DB_USERNAME=lpb_user
# DB_PASSWORD=your_password

# 4. Run migrations
php artisan migrate --seed

# 5. Build assets
npm run build  # or npm run dev for development

# 6. Start server
php artisan serve

# Visit http://localhost:8000
```

---

## Next Steps

After completing the setup:

1. Review the [Architecture Documentation](./02-ARCHITECTURE.md)
2. Check the [API Documentation](./04-API.md)
3. Read the [Contributing Guidelines](./CONTRIBUTING.md)
4. Set up your IDE extensions
5. Configure git hooks
6. Run the test suite: `php artisan test`

For additional help, create an issue in the repository or contact the development team.
