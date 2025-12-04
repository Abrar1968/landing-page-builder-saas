# GitHub Copilot Instructions for Landing Page Builder SaaS

## 1. Project Context & Architecture
- **Stack**: Laravel 12 (Backend), Vue.js 3 + Pinia (Frontend Builder), TailwindCSS v4.
- **Core Pattern**: **Service-Repository Pattern** is strictly enforced.
  - Flow: `Controller` -> `Service` -> `Repository` -> `Model`.
  - **Controllers**: Handle HTTP, validation, and responses only. No business logic.
  - **Services**: Contain all business logic (`app/Services`).
  - **Repositories**: Handle all database interactions (`app/Repositories`).
  - **Models**: Lightweight Eloquent models with relationships only (`app/Models`).
- **Frontend Architecture**:
  - **Builder**: Vue.js 3 SPA mounted in `resources/views/builder/edit.blade.php`.
  - **State**: Pinia stores in `resources/js/builder/stores/`.
  - **Widgets**: Defined in `resources/js/builder/widgets/registry.js` and rendered via `WidgetRenderer.vue`.
- **Data Storage**: Page content is stored as a JSON structure in the `pages` table (`content` column).

## 2. Critical Workflows & Commands
- **Development**:
  - Frontend: `npm run dev` (Vite hot reload).
  - Backend: `php artisan serve` (Laravel server).
  - Queue: `php artisan queue:work` (if needed).
- **Testing**:
  - Run all tests: `php artisan test`.
  - Run specific test: `php artisan test tests/Feature/PageServiceTest.php`.
- **Code Quality**:
  - PHP Formatting: `./vendor/bin/pint`.
  - JS Linting: `npm run lint` (if configured) or `npx eslint resources/js`.
  - Static Analysis: `./vendor/bin/phpstan analyse`.

## 3. Coding Standards & Patterns
- **Backend (Laravel)**:
  - **Dependency Injection**: Inject Services into Controllers, and Repositories into Services.
  - **Strict Typing**: Use strict types in method signatures.
  - **DTOs**: Use DTOs for complex data transfer (e.g., `app/DTOs`).
  - **Naming**:
    - Services: `NameService` (e.g., `PageService`).
    - Repositories: `NameRepository` (e.g., `PageRepository`).
    - Interfaces: `NameInterface` (e.g., `PaymentGatewayInterface`).
- **Frontend (Vue.js)**:
  - **Composition API**: Use `<script setup>` syntax exclusively.
  - **Components**: PascalCase (e.g., `WidgetRenderer.vue`).
  - **Tailwind**: Use utility classes; avoid custom CSS unless necessary.
  - **Widget Implementation**:
    1. Register in `resources/js/builder/widgets/registry.js`.
    2. Create component in `resources/js/builder/components/widgets/`.
    3. Ensure `props` match the widget schema.

## 4. Key Files & Directories
- **Business Logic**: `app/Services/` (e.g., `PageService.php`, `TemplateService.php`).
- **Data Access**: `app/Repositories/` (e.g., `PageRepository.php`).
- **Vue Builder Entry**: `resources/js/builder/main.js`.
- **Widget Registry**: `resources/js/builder/widgets/registry.js`.
- **Widget Components**: `resources/js/builder/components/widgets/`.
- **Routes**: `routes/web.php` (Builder/Dashboard), `routes/api.php` (API).

## 5. Common Tasks
- **Adding a New Widget**:
  - Define widget config (icon, label, default settings) in `registry.js`.
  - Create the Vue component in `components/widgets/`.
  - Add backend rendering logic in `app/Services/PageRenderer.php` (if SSR is needed).
- **Creating an API Endpoint**:
  - Create Controller: `php artisan make:controller Api/NameController`.
  - Create Service/Repository if new domain logic is needed.
  - Define route in `routes/api.php`.
  - Return `JsonResource` or standardized JSON response.

## 6. Important Notes
- **Ignore AlpineJS**: Old documentation references AlpineJS. The project is now **Vue.js 3**.
- **Widget JSON Structure**: Understand the JSON schema in `pages.content` before manipulating page data.
- **Elementor Compatibility**: Widgets are inspired by Elementor; follow their naming/behavior where possible.
