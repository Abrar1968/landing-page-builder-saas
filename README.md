# Landing Page Builder SaaS

A modern SaaS landing page builder application powered by **Laravel 12** and **Vue.js 3**, featuring an Elementor-inspired drag-and-drop page builder with 22+ professional widgets.

## 🚀 Features

### Page Builder (Vue.js 3 SPA)
- ✅ **22+ Elementor-inspired widgets** (Heading, Image, Button, Video, Accordion, Tabs, etc.)
- ✅ **Real-time visual editing** with live preview
- ✅ **Property panels** with Content/Style/Advanced tabs
- ✅ **Undo/Redo** functionality
- ✅ **Auto-save** feature
- ✅ **Media library** integration
- ✅ **Responsive preview** modes (Desktop/Tablet/Mobile)
- ✅ **Widget registry** system for extensibility

### Backend Services
- ✅ **Service-Repository pattern** architecture
- ✅ **Page management** with versioning
- ✅ **Template system** with pre-built designs
- ✅ **Media management** with upload/organization
- ✅ **Domain management** for custom domains
- ✅ **Stripe integration** for subscriptions
- ✅ **Analytics tracking** for page views
- ✅ **Form submissions** collection

### User Features
- ✅ **Multi-tier subscriptions** (Free, Pro, Business)
- ✅ **Custom domains** support
- ✅ **Page publishing** workflow
- ✅ **Template library** for quick starts
- ✅ **User dashboard** with analytics

---

## 🛠 Technology Stack

### Backend
- **Laravel 12** (PHP 8.2+)
- **MySQL 8.0+** for data storage
- **Redis** for caching (optional)
- **Laravel Sanctum** for API authentication
- **Stripe API** for payments

### Frontend
- **Vue.js 3** (Composition API) - Page Builder SPA
- **Pinia** - State management
- **Vite 5.x** - Build tool & dev server
- **TailwindCSS v4** - Utility-first CSS
- **Laravel Blade** - Marketing pages & dashboard

### Development
- **Composer** - PHP dependencies
- **NPM** - JavaScript dependencies
- **Pint** - PHP code formatting (PSR-12)
- **ESLint** - JavaScript linting

---

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18.x or higher
- NPM 9.x or higher
- MySQL 8.0 or higher
- Redis (optional, for caching)

---

## 🚀 Quick Start

### 1. Clone the Repository

```bash
git clone https://github.com/Abrar1968/landing-page-builder-saas.git
cd landing-page-builder-saas
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env file
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### 4. Database Migration

```bash
# Run migrations
php artisan migrate

# (Optional) Seed database with sample data
php artisan migrate --seed
```

### 5. Start Development Servers

**Terminal 1 - Frontend (Vite):**
```bash
npm run dev
```

**Terminal 2 - Backend (Laravel):**
```bash
php artisan serve
```

**Terminal 3 - Queue Worker (Optional):**
```bash
php artisan queue:work
```

### 6. Access the Application

- **Application**: http://localhost:8000
- **Register**: http://localhost:8000/register
- **Login**: http://localhost:8000/login

---

## 📂 Project Structure

```
landing-page-builder-saas/
├── app/
│   ├── Http/Controllers/     # HTTP controllers
│   ├── Models/                # Eloquent models
│   ├── Services/              # Business logic layer
│   ├── Repositories/          # Data access layer
│   └── Observers/             # Model event observers
│
├── resources/
│   ├── js/
│   │   ├── app.js            # Blade pages entry
│   │   └── builder/          # Vue.js 3 SPA
│   │       ├── main.js       # Vue app entry
│   │       ├── App.vue       # Main builder component
│   │       ├── widgets/      # Widget registry
│   │       ├── components/   # Vue components
│   │       └── stores/       # Pinia stores
│   │
│   ├── views/                # Blade templates
│   │   ├── builder/          # Builder views
│   │   ├── dashboard/        # Dashboard views
│   │   └── layouts/          # Layout templates
│   │
│   └── css/                  # Stylesheets
│
├── routes/
│   ├── web.php               # Web routes
│   └── api.php               # API routes
│
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
│
└── docs/                     # Documentation
    ├── CLAUDE.md             # Project overview
    ├── REVISED-WIDGET-IMPLEMENTATION-PLAN.md  # Implementation plan
    ├── features/             # Feature specifications
    ├── backend/              # Backend documentation
    └── frontend/             # Frontend documentation
```

---

## 🎨 Architecture

### Service-Repository Pattern

```
HTTP Request
     ↓
Controller (validation, HTTP responses)
     ↓
Service (business logic)
     ↓
Repository (database queries)
     ↓
Model (Eloquent ORM)
     ↓
Database
```

### Page Builder Architecture

```
Vue.js SPA (resources/js/builder/)
     ↓
Pinia Store (state management)
     ↓
Widget Registry (22+ widgets)
     ↓
API Endpoints (Laravel backend)
     ↓
PageService → PageRepository
     ↓
Page Model (stores widgets as JSON)
```

---

## 🎯 Widget System

The page builder includes **22+ Elementor-inspired widgets**:

### Basic Widgets
✅ Heading, Text Editor, Image, Button, Video, Divider, Spacer, Icon, Icon Box, Counter, Progress Bar, Testimonial, Social Icons, Alert

### Media Widgets
✅ Image Box, Star Rating, Google Maps

### Interactive Widgets
✅ Tabs, Accordion, Countdown

### Marketing Widgets
✅ Call to Action, Flip Box, Price Table

### Missing Widgets (6)
To complete the Elementor 28 Basic set:
- Toggle, Icon List, Text Path
- Image Carousel, Basic Gallery, SoundCloud

**Reference:** `docs/REVISED-WIDGET-IMPLEMENTATION-PLAN.md`

---

## 📝 Development Commands

### Frontend Development

```bash
npm run dev        # Start Vite dev server with hot reload
npm run build      # Build for production
npm run lint       # Run ESLint
npm run lint:fix   # Auto-fix lint issues
```

### Backend Development

```bash
php artisan serve              # Start development server
php artisan migrate            # Run database migrations
php artisan migrate:fresh --seed  # Fresh migration with seeders
php artisan test               # Run tests
php artisan optimize:clear     # Clear all caches
```

### Code Quality

```bash
./vendor/bin/pint              # Format PHP code (PSR-12)
./vendor/bin/phpstan analyse   # Static analysis (if installed)
npx eslint resources/js        # Lint JavaScript
npx prettier --write .         # Format all files
```

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/PageServiceTest.php
```

---

## 📚 Documentation

- **[CLAUDE.md](CLAUDE.md)** - Comprehensive project overview
- **[REVISED-WIDGET-IMPLEMENTATION-PLAN.md](docs/REVISED-WIDGET-IMPLEMENTATION-PLAN.md)** - Implementation guide
- **[Widget System](docs/features/07-WIDGET-SYSTEM.md)** - Complete widget specifications
- **[Database Schema](docs/backend/02-DATABASE-SCHEMA.md)** - Database structure
- **[API Endpoints](docs/backend/03-API-ENDPOINTS.md)** - API documentation

---

## 🔑 Key Features Implementation Status

### ✅ Completed
- Page builder with Vue.js 3 + Pinia
- 22+ working widgets
- Service-Repository pattern
- Page management (CRUD)
- Template system
- Media library
- User authentication
- Subscription billing (Stripe)
- Domain management
- Analytics tracking
- Form submissions

### 🚧 In Progress (3-4 days)
- Complete missing 6 widgets
- Add layout widgets (Container, Inner Section, etc.)
- Backend Widget API
- Testing & documentation

### 📅 Planned
- A/B testing
- Email marketing integration
- CRM integrations
- Advanced SEO tools
- Mobile app

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'feat: add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Commit Message Convention

```
type(scope): description

Types: feat, fix, docs, style, refactor, test, chore
```

---

## 📄 License

This project is proprietary software. All rights reserved.

---

## 🆘 Support

For support and questions:
- Check the [documentation](docs/)
- Review [CLAUDE.md](CLAUDE.md) for architecture details
- See [REVISED-WIDGET-IMPLEMENTATION-PLAN.md](docs/REVISED-WIDGET-IMPLEMENTATION-PLAN.md) for development guidance

---

## 🎯 Project Status

**Status:** ✅ 75% Complete
**Current Phase:** Completing widget system
**Remaining Work:** 3-4 days to complete Elementor 28 widget set

### Technology Stack
- ✅ Laravel 12 + Vue.js 3 + Pinia
- ✅ Service-Repository pattern
- ✅ MySQL with JSON widget storage
- ✅ TailwindCSS v4
- ✅ Vite 5.x build system

### Widget Progress
- ✅ 22 widgets implemented
- 🔨 6 widgets remaining (Toggle, Icon List, Text Path, Image Carousel, Gallery, SoundCloud)
- 📋 6 layout/advanced widgets to add

---

## 🔗 Related Documentation

- [Vue.js 3 Documentation](https://vuejs.org/)
- [Pinia Documentation](https://pinia.vuejs.org/)
- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [TailwindCSS v4 Documentation](https://tailwindcss.com/)

---

Made with ❤️ using Laravel 12 + Vue.js 3
