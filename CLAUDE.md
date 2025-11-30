# CLAUDE.md - Landing Page Builder SaaS

## Project Overview

A SaaS landing page builder application (similar to Webflow/Squarespace) with a **Vue.js 3 powered page builder** featuring 22+ Elementor-inspired widgets. The application combines Laravel backend services with a modern Vue.js SPA for the page builder interface.

**Status**: ✅ Core application implemented with Vue.js builder
**Current Phase**: Completing widget system (6 widgets remaining from Elementor 28)
**Architecture**: Laravel 12 + Vue.js 3 + Pinia

---

## Technology Stack

### Backend
- **Framework**: Laravel 12 (PHP 8.2+, recommended 8.3+)
- **Database**: MySQL 8.0+ (UTF-8MB4)
- **Cache**: Redis 7.0+ (optional)
- **Auth**: Laravel Sanctum
- **Architecture**: Service-Repository Pattern

### Frontend

#### **Page Builder (Vue.js 3 SPA)**
- **Framework**: Vue.js 3 (Composition API)
- **State Management**: Pinia
- **Build Tool**: Vite 5.x
- **UI Framework**: TailwindCSS v4
- **Widget System**: 22+ Elementor-inspired widgets

#### **Marketing Pages (Blade)**
- **Templating**: Laravel Blade
- **CSS**: TailwindCSS v4
- **Interactivity**: Minimal vanilla JS

### External Services
- **Payments**: Stripe API
- **Email**: SMTP
- **SSL**: Let's Encrypt
- **Storage**: Local/S3 for media

---

## Development Commands

### Frontend (Terminal 1)
```bash
npm run dev       # Vite dev server with hot reload (Vue.js + Blade)
npm run build     # Production build
npm run lint      # ESLint check
npm run lint:fix  # Auto-fix lint issues
```

### Backend (Terminal 2)
```bash
php artisan serve              # Dev server at localhost:8000
php artisan migrate            # Run migrations
php artisan migrate --seed     # Migrate + seed
php artisan test               # Run tests
php artisan test --coverage    # Tests with coverage
php artisan optimize:clear     # Clear all caches
```

### Code Quality
```bash
./vendor/bin/pint              # Format PHP (PSR-12)
./vendor/bin/phpstan analyse   # Static analysis
npx eslint resources/js        # Lint JS
npx prettier --write .         # Format all
```

---

## Architecture Patterns

### Service-Repository Pattern (Required)
```
Controller → Service → Repository → Model
```

- **Controllers**: HTTP handling, validation, responses only
- **Services**: Business logic, orchestration
- **Repositories**: Database queries, CRUD operations
- **Models**: Eloquent relationships only

**✅ IMPLEMENTED** across all features (Page, Template, Media, User, etc.)

### Observer Pattern
Model events trigger observers for:
- Activity logging
- Email notifications
- Cache invalidation

**✅ IMPLEMENTED** for core models

### Strategy Pattern
Used for payment processing with pluggable gateways (Stripe, PayPal).

---

## Directory Structure

### Backend (✅ Implemented)
```
app/
├── Contracts/           # ✅ Interfaces for DI
│   ├── Repositories/    # ✅ Repository interfaces
│   └── Services/        # Service contracts
├── Http/
│   ├── Controllers/     # ✅ Route handlers (Page, Builder, Template, etc.)
│   ├── Middleware/      # ✅ Auth, CORS, security
│   ├── Requests/        # Form validation
│   └── Resources/       # API responses
├── Models/              # ✅ Eloquent models
│   ├── Page.php         # ✅ Stores widget content as JSON
│   ├── Template.php     # ✅ Template definitions
│   ├── Media.php        # ✅ File management
│   └── User.php         # ✅ Authentication
├── Observers/           # ✅ Model event listeners
├── Repositories/        # ✅ Data access layer
│   ├── PageRepository.php
│   ├── TemplateRepository.php
│   └── MediaRepository.php
├── Services/            # ✅ Business logic
│   ├── PageService.php
│   ├── TemplateService.php
│   ├── MediaService.php
│   ├── PageRenderer.php # Server-side widget rendering
│   └── SubscriptionService.php
└── Policies/            # Authorization
```

### Frontend (✅ Implemented)

#### **Vue.js Builder (SPA)**
```
resources/js/builder/
├── main.js              # ✅ Vue app entry point
├── App.vue              # ✅ Main builder component
├── widgets/
│   └── registry.js      # ✅ 22+ widgets registered
├── components/
│   ├── WidgetRenderer.vue    # ✅ Renders widgets
│   ├── ControlRenderer.vue   # ✅ Property controls
│   └── MediaLibraryModal.vue # ✅ Media picker
└── stores/
    └── builder.js       # ✅ Pinia state (undo/redo, selection, etc.)
```

#### **Blade Views**
```
resources/views/
├── layouts/             # ✅ Master templates
├── components/          # ✅ Blade components
├── builder/
│   ├── edit.blade.php   # ✅ Builder SPA mount point
│   └── preview.blade.php
├── dashboard/           # ✅ User dashboard
└── pages/               # ✅ Page management
```

---

## Code Conventions

### Naming
- **Models**: Singular PascalCase (`User`, `Page`)
- **Tables**: Plural snake_case (`users`, `pages`)
- **Controllers**: Singular (`PageController`)
- **Routes**: Plural kebab-case (`/pages`, `/templates`)
- **Variables**: camelCase
- **Constants**: UPPER_SNAKE_CASE
- **Vue Components**: PascalCase (`WidgetRenderer.vue`)

### Standards
- PHP: PSR-12 (enforced via Pint)
- JS: ESLint + Prettier
- Vue.js: Composition API preferred
- Use eager loading (prevent N+1 queries)
- One responsibility per class

### Commit Messages
```
type(scope): description
```
Types: feat, fix, docs, style, refactor, test, chore

---

## Core Database Tables

### ✅ Implemented Tables

- **users**: Authentication, roles, preferences
- **pages**: User content with **JSON widget structure**
- **templates**: System and user templates
- **media**: Uploaded files and metadata
- **domains**: Custom domain management
- **subscriptions**: Stripe subscription data
- **page_views**: Analytics data
- **form_submissions**: Form data collection
- **page_versions**: Version history
- **payments**: Payment transactions

### Page Content Structure (JSON)
```json
{
  "content": [
    {
      "id": "abc123",
      "elType": "section",
      "elements": [
        {
          "id": "def456",
          "elType": "widget",
          "widgetType": "heading",
          "settings": {
            "title": "Welcome",
            "size": "h1",
            "text_color": "#000000"
          }
        }
      ]
    }
  ]
}
```

---

## Widget System (Vue.js)

### ✅ Implemented Widgets (22+)

**Basic Widgets (14):**
✅ Heading, Text Editor, Image, Button, Video, Divider, Spacer, Icon, Icon Box, Counter, Progress Bar, Testimonial, Social Icons, Alert

**Media Widgets (3):**
✅ Image Box, Star Rating, Google Maps

**Interactive Widgets (3):**
✅ Tabs, Accordion, Countdown

**Marketing Widgets (3):**
✅ Call to Action, Flip Box, Price Table

### ❌ Missing from Elementor 28 Basic (6 widgets)
- Toggle, Icon List, Text Path
- Image Carousel, Basic Gallery, SoundCloud

### ❌ Missing Layout/Advanced (6 widgets)
- Container, Inner Section, Menu Anchor, Sidebar
- HTML, Shortcode

**Reference:** See `docs/REVISED-WIDGET-IMPLEMENTATION-PLAN.md` for implementation guide

---

## API Structure

All APIs follow RESTful conventions under `/api/`:

### Page Management
- `GET /api/pages` - List pages
- `POST /api/pages` - Create page
- `GET /api/pages/{id}` - Get page
- `PUT /api/pages/{id}` - Update page
- `DELETE /api/pages/{id}` - Delete page
- `POST /api/pages/{id}/publish` - Publish page
- `POST /api/pages/{id}/duplicate` - Duplicate page

### Builder Routes (Web)
- `GET /builder/{page}/edit` - Load Vue.js builder
- `POST /builder/{page}/save` - Auto-save
- `POST /builder/{page}/publish` - Publish
- `GET /builder/{page}/preview` - Preview

### Media Management
- `GET /api/media` - List media
- `POST /api/media` - Upload file
- `DELETE /api/media/{id}` - Delete file

---

## Key Documentation

### **Current Implementation Status**

| File | Status | Purpose |
|------|--------|---------|
| `CLAUDE.md` | ✅ **Updated** | This file - project overview |
| `docs/REVISED-WIDGET-IMPLEMENTATION-PLAN.md` | ✅ Complete | **3-4 day plan** for remaining widgets |
| `docs/features/07-WIDGET-SYSTEM.md` | ✅ Complete | All 28 Elementor widget specs |
| `docs/backend/02-DATABASE-SCHEMA.md` | ✅ Updated | Database schema with widget JSON |
| `docs/backend/03-API-ENDPOINTS.md` | ✅ Updated | API documentation |

### **Original Documentation (Outdated - Used AlpineJS)**

| File | Status | Notes |
|------|--------|-------|
| `docs/01-SRS.md` | ⚠️ Outdated | References AlpineJS |
| `docs/02-PLAN.md` | ⚠️ Outdated | 14-day plan for AlpineJS |
| `docs/04-IMPLEMENTATION-FLOW.md` | ⚠️ Outdated | AlpineJS implementation |
| `docs/frontend/02-COMPONENTS.md` | ⚠️ Outdated | AlpineJS components |
| `docs/frontend/03-DRAG-DROP-BUILDER.md` | ⚠️ Outdated | AlpineJS builder |
| `docs/steps/` | ⚠️ Outdated | AlpineJS step-by-step guides |

**⚠️ Important:** Original docs assumed AlpineJS but codebase uses **Vue.js 3 + Pinia**

---

## Current Development Status

### ✅ COMPLETED FEATURES

#### **Backend Services**
- ✅ Service-Repository pattern fully implemented
- ✅ PageService, TemplateService, MediaService
- ✅ PageRenderer for published pages
- ✅ Stripe subscription integration
- ✅ Domain management
- ✅ Analytics tracking

#### **Frontend Builder (Vue.js 3)**
- ✅ Vue.js 3 SPA with Pinia state management
- ✅ 22+ widgets working with full editing
- ✅ Widget registry system
- ✅ Property panel with Content/Style/Advanced tabs
- ✅ Undo/redo functionality
- ✅ Auto-save
- ✅ Media library integration
- ✅ Preview modes (desktop/tablet/mobile)

#### **Database**
- ✅ All core tables migrated
- ✅ Page content stored as JSON
- ✅ Performance indexes added

### ❌ REMAINING TASKS (3-4 days)

#### **Day 1: Add Missing Vue.js Widgets (6 widgets)**
- [ ] Toggle widget
- [ ] Icon List widget
- [ ] Text Path widget
- [ ] Image Carousel widget
- [ ] Basic Gallery widget
- [ ] SoundCloud widget

#### **Day 2: Layout & Advanced Widgets**
- [ ] Container widget
- [ ] Inner Section widget
- [ ] Menu Anchor widget
- [ ] Sidebar widget
- [ ] HTML widget
- [ ] Shortcode widget
- [ ] Vue rendering components

#### **Day 3: Backend Widget API**
- [ ] WidgetRegistry backend service
- [ ] WidgetRenderer service
- [ ] Widget API controller
- [ ] API routes (`/api/widgets`)

#### **Day 4: Testing & Polish**
- [ ] Test all 28+ widgets
- [ ] Browser compatibility
- [ ] Performance optimization
- [ ] Documentation updates

**Reference:** `docs/REVISED-WIDGET-IMPLEMENTATION-PLAN.md`

---

## User Tiers

- **Free**: 1 page, 100MB storage
- **Pro**: 10 pages, 5GB storage, custom domains
- **Business**: Unlimited pages, 50GB storage, priority support

---

## Quality Targets

- **Test Coverage**: 60%+ overall (80% services, 70% features)
- **Performance**: Dashboard < 1s, Builder init < 2s, API < 200ms
- **Security**: 0 critical vulnerabilities

---

## Security Requirements

- Bcrypt passwords (cost 12)
- Rate limiting on auth endpoints
- CSRF protection on all forms
- Input validation everywhere
- SQL injection prevention via Eloquent
- XSS prevention via Vue.js sanitization + Blade escaping

---

## Important Notes for AI Assistants

### ✅ **ACTUAL IMPLEMENTATION (Use This!)**

1. **Frontend is Vue.js 3 + Pinia** - NOT AlpineJS!
2. **22+ widgets already working** - Only 6 missing from Elementor 28
3. **Service-Repository pattern** - Fully implemented across backend
4. **Page builder is SPA** - Vue.js mounted in `builder/edit.blade.php`
5. **Widget content stored as JSON** - In `pages.content` column
6. **3-4 day timeline** - To complete remaining widgets

### ⚠️ **IGNORE OUTDATED DOCS**

1. ❌ Don't follow AlpineJS documentation
2. ❌ Don't follow 14-day implementation plan
3. ❌ Don't create elements table (using JSON storage)
4. ❌ Don't use Blade for builder UI (it's Vue.js SPA)

### ✅ **FOLLOW THESE DOCS**

1. ✅ `docs/REVISED-WIDGET-IMPLEMENTATION-PLAN.md` - Current plan
2. ✅ `docs/features/07-WIDGET-SYSTEM.md` - Widget specifications
3. ✅ Existing codebase in `resources/js/builder/` - Vue.js reference
4. ✅ `app/Services/` - Backend service examples

---

## Quick Reference Paths

### **Code Locations**

```
/home/user/landing-page-builder-saas/
├── app/
│   ├── Services/            # ✅ Backend services
│   │   ├── PageService.php
│   │   ├── TemplateService.php
│   │   └── PageRenderer.php
│   ├── Repositories/        # ✅ Data access
│   └── Models/              # ✅ Eloquent models
│
├── resources/
│   ├── js/
│   │   ├── app.js           # Blade pages entry
│   │   └── builder/         # ✅ Vue.js 3 SPA
│   │       ├── main.js      # Vue app entry
│   │       ├── App.vue      # Main component
│   │       ├── widgets/registry.js  # ✅ 22 widgets
│   │       ├── components/  # Vue components
│   │       └── stores/      # Pinia stores
│   │
│   └── views/
│       ├── builder/
│       │   └── edit.blade.php  # ✅ Vue SPA mount point
│       └── dashboard/       # Blade templates
│
├── docs/
│   ├── REVISED-WIDGET-IMPLEMENTATION-PLAN.md  # ✅ USE THIS
│   ├── features/07-WIDGET-SYSTEM.md           # ✅ Widget specs
│   └── [other outdated docs with AlpineJS]    # ⚠️ IGNORE
│
└── CLAUDE.md                # This file (✅ UPDATED)
```

---

## Development Workflow

### Starting Development

```bash
# Terminal 1: Frontend (Vue.js + Vite)
npm run dev

# Terminal 2: Backend (Laravel)
php artisan serve

# Terminal 3: Queue Worker (if needed)
php artisan queue:work
```

### Accessing the Builder

1. Register/login at `http://localhost:8000/register`
2. Create a new page at `/pages/create`
3. Click "Edit" to open Vue.js builder
4. Builder loads at `/builder/{page-id}/edit`
5. Vue.js SPA initializes with Pinia store
6. 22+ widgets available in left panel

### Adding New Widgets

1. Add widget config to `resources/js/builder/widgets/registry.js`
2. Create Vue component in `resources/js/builder/components/widgets/`
3. Test in browser
4. Add backend rendering in `app/Services/WidgetRenderer.php`

**Full guide:** `docs/REVISED-WIDGET-IMPLEMENTATION-PLAN.md`

---

## Support & Resources

### **For Current Implementation:**
- Vue.js 3 Docs: https://vuejs.org/
- Pinia Docs: https://pinia.vuejs.org/
- Laravel 12 Docs: https://laravel.com/docs/12.x
- TailwindCSS v4: https://tailwindcss.com/

### **Project-Specific:**
- Widget Implementation: `docs/REVISED-WIDGET-IMPLEMENTATION-PLAN.md`
- Widget Specifications: `docs/features/07-WIDGET-SYSTEM.md`
- Existing Widget Registry: `resources/js/builder/widgets/registry.js`

---

## Summary

This is a **Vue.js 3 + Pinia powered page builder** with Laravel backend using Service-Repository pattern. The builder features 22+ working widgets with 6 remaining to complete the Elementor 28 Basic set. Follow the **REVISED-WIDGET-IMPLEMENTATION-PLAN.md** for accurate implementation guidance based on the actual codebase architecture.

**Status:** ✅ 75% complete | 🔨 Completing widget system (3-4 days)
