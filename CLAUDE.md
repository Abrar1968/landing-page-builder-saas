# CLAUDE.md - Landing Page Builder SaaS

## Project Overview

A SaaS landing page builder application (similar to Webflow/Squarespace) currently in the **documentation phase**. The repository contains comprehensive specifications across 24 markdown files but no application code yet.

**Status**: Documentation complete, ready for implementation
**Timeline**: 2-week MVP development plan

## Technology Stack

### Backend
- **Framework**: Laravel 11 (PHP 8.2+, recommended 8.3+)
- **Database**: MySQL 8.0+ (UTF-8MB4)
- **Cache**: Redis 7.0+ (optional)
- **Auth**: Laravel Sanctum

### Frontend
- **CSS**: TailwindCSS v4 (via Vite plugin)
- **Templating**: Laravel Blade
- **Interactivity**: AlpineJS 3.x
- **Drag-and-drop**: SortableJS 1.15
- **Charts**: Chart.js 4.x
- **Build**: Vite

### External Services
- **Payments**: Stripe API
- **Email**: SMTP
- **SSL**: Let's Encrypt

## Development Commands

### Frontend (Terminal 1)
```bash
npm run dev       # Vite dev server with hot reload
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

## Architecture Patterns

### Service-Repository Pattern (Required)
```
Controller → Service → Repository → Model
```

- **Controllers**: HTTP handling, validation, responses only
- **Services**: Business logic, orchestration
- **Repositories**: Database queries, CRUD operations
- **Models**: Eloquent relationships only

### Observer Pattern
Model events trigger observers for:
- Activity logging
- Email notifications
- Cache invalidation

### Strategy Pattern
Used for payment processing with pluggable gateways (Stripe, PayPal).

## Directory Structure

### Backend (to be created)
```
app/
├── Contracts/           # Interfaces for DI
│   ├── Repositories/
│   └── Services/
├── Http/
│   ├── Controllers/     # Route handlers
│   ├── Middleware/
│   ├── Requests/        # Form validation
│   └── Resources/       # API responses
├── Models/              # Eloquent models
├── Observers/           # Model event listeners
├── Repositories/        # Data access layer
├── Services/            # Business logic
└── Policies/            # Authorization
```

### Frontend
```
resources/
├── css/app.css          # TailwindCSS
├── js/
│   ├── app.js           # AlpineJS init
│   └── modules/         # Feature JS
└── views/
    ├── layouts/         # Master templates
    ├── components/      # Blade components
    └── [feature]/       # Feature views
```

## Code Conventions

### Naming
- **Models**: Singular PascalCase (`User`, `Page`)
- **Tables**: Plural snake_case (`users`, `pages`)
- **Controllers**: Singular (`PageController`)
- **Routes**: Plural kebab-case (`/pages`, `/templates`)
- **Variables**: camelCase
- **Constants**: UPPER_SNAKE_CASE

### Standards
- PHP: PSR-12 (enforced via Pint)
- JS: ESLint + Prettier
- Use eager loading (prevent N+1 queries)
- One responsibility per class

### Commit Messages
```
type(scope): description
```
Types: feat, fix, docs, style, refactor, test, chore

## Core Database Tables

- **users**: Authentication, roles, preferences
- **pages**: User content with JSON structure
- **templates**: System and user templates
- **media**: Uploaded files and metadata
- **domains**: Custom domain management
- **subscriptions**: Stripe subscription data
- **page_views**: Analytics data
- **form_submissions**: Form data collection
- **page_versions**: Version history

## API Structure

All APIs follow RESTful conventions under `/api/`:
- `GET /api/pages` - List
- `POST /api/pages` - Create
- `GET /api/pages/{id}` - Show
- `PUT /api/pages/{id}` - Update
- `DELETE /api/pages/{id}` - Delete

Custom actions use POST:
- `POST /api/pages/{id}/publish`
- `POST /api/pages/{id}/duplicate`

## Key Documentation

| File | Purpose |
|------|---------|
| `docs/01-SRS.md` | Full requirements specification |
| `docs/02-PLAN.md` | 14-day implementation timeline |
| `docs/03-SETUP.md` | Development environment setup |
| `docs/04-IMPLEMENTATION-FLOW.md` | Day-by-day implementation guide |
| `docs/backend/01-ARCHITECTURE.md` | Design patterns and structure |
| `docs/backend/02-DATABASE-SCHEMA.md` | ER diagrams and tables |
| `docs/backend/03-API-ENDPOINTS.md` | API documentation |
| `docs/features/` | Detailed feature specifications |

## User Tiers

- **Free**: 1 page, 100MB storage
- **Pro**: 10 pages, 5GB storage, custom domains
- **Business**: Unlimited pages, 50GB storage, priority support

## Quality Targets

- **Test Coverage**: 60%+ overall (80% services, 70% features)
- **Performance**: Dashboard < 1s, Builder init < 2s, API < 200ms
- **Security**: 0 critical vulnerabilities

## Security Requirements

- Bcrypt passwords (cost 12)
- Rate limiting on auth endpoints
- CSRF protection on all forms
- Input validation everywhere
- SQL injection prevention via Eloquent
- XSS prevention via Blade escaping

## Important Notes for AI Assistants

1. **No code exists yet** - This is documentation only
2. **Follow Service-Repository pattern** - Never put business logic in controllers
3. **Check docs first** - Most implementation details are documented
4. **Use Blade + AlpineJS** - Not React (recent architecture change)
5. **TailwindCSS v4** - Uses new Vite plugin, not PostCSS
6. **Stripe for payments** - Don't implement custom payment processing

## Quick Reference Paths

```
/home/user/landing-page-builder-saas/
├── docs/                    # All documentation
├── CLAUDE.md               # This file
└── (code to be created)    # Laravel app structure
```

When implementing features, always reference:
1. The relevant feature doc in `docs/features/`
2. Database schema in `docs/backend/02-DATABASE-SCHEMA.md`
3. API specs in `docs/backend/03-API-ENDPOINTS.md`
