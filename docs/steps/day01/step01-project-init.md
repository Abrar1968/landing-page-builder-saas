# Day 1 - Step 1: Project Initialization

## Objective
Initialize Laravel 12 project with required packages and basic configuration.

## Tasks

### 1.1 Create Laravel Project
```bash
composer create-project laravel/laravel landing-page-builder
cd landing-page-builder
```

### 1.2 Install Required Packages
```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
```

### 1.3 Install Frontend Dependencies
```bash
npm install -D tailwindcss@latest @tailwindcss/forms alpinejs sortablejs chart.js
```

### 1.4 Configure Environment
- Copy `.env.example` to `.env`
- Set `APP_NAME`, `APP_URL`
- Configure database connection (MySQL 8.0+)

## Reference Documentation
- `docs/03-SETUP.md` - Complete setup guide
- `docs/04-IMPLEMENTATION-FLOW.md` - Day 1 section

## Expected Deliverables
- [ ] Laravel 12 project created
- [ ] Laravel Breeze installed with Blade
- [ ] npm dependencies installed
- [ ] Environment configured

## Next Step
→ `step02-database-config.md`
