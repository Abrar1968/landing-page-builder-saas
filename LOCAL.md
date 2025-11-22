# Local Development Setup Guide

This guide will help you set up the Landing Page Builder SaaS project on your local machine.

## Prerequisites

- **PHP 8.2+** (recommended 8.3)
- **Composer 2.x**
- **Node.js 18+** and npm
- **MySQL 8.0+**
- **Redis** (optional, for caching)
- **VS Code** with extensions:
  - PHP Intelephense
  - Laravel Blade Snippets
  - Tailwind CSS IntelliSense
  - Alpine.js IntelliSense

## Step 1: Clone the Repository

```bash
git clone <repository-url>
cd landing-page-builder-saas
```

## Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

## Step 3: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Configure `.env` file:

```env
APP_NAME="PageBuilder"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pagebuilder
DB_USERNAME=root
DB_PASSWORD=your_password

# Optional: Use Redis for caching
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# For Stripe testing (use test keys)
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
STRIPE_PRO_PRICE_ID=price_xxx
STRIPE_BUSINESS_PRICE_ID=price_xxx
```

## Step 4: Database Setup

```bash
# Create the database
mysql -u root -p -e "CREATE DATABASE pagebuilder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Seed with sample data (optional)
php artisan db:seed
```

## Step 5: Build Frontend Assets

```bash
# Development build with hot reload
npm run dev
```

## Step 6: Start Development Servers

Open **two terminal windows**:

### Terminal 1 - Laravel Server
```bash
php artisan serve
```
This starts the backend at `http://localhost:8000`

### Terminal 2 - Vite Dev Server
```bash
npm run dev
```
This enables hot reload for frontend changes

## Step 7: Access the Application

1. Open `http://localhost:8000` in your browser
2. Register a new account
3. Explore the dashboard

## Testing the Application

### Run All Tests
```bash
php artisan test
```

### Run with Coverage
```bash
php artisan test --coverage
```

### Run Specific Test Files
```bash
# Feature tests
php artisan test tests/Feature/PageTest.php
php artisan test tests/Feature/AuthTest.php
php artisan test tests/Feature/TemplateTest.php
php artisan test tests/Feature/SubscriptionTest.php

# Unit tests
php artisan test tests/Unit/SubscriptionServiceTest.php
php artisan test tests/Unit/CacheServiceTest.php
```

### Run Tests in VS Code
1. Install "Better PHPUnit" extension
2. Open a test file
3. Use `Cmd+Shift+T` (Mac) or `Ctrl+Shift+T` (Windows) to run tests

## Features to Test

### 1. Authentication
- Register: `http://localhost:8000/register`
- Login: `http://localhost:8000/login`
- Profile: `http://localhost:8000/profile`

### 2. Dashboard
- View stats: `http://localhost:8000/dashboard`

### 3. Pages
- List pages: `http://localhost:8000/pages`
- Create page (click "New Page")
- Edit in builder
- Publish/Unpublish

### 4. Templates
- Browse: `http://localhost:8000/templates`
- Apply template to create page
- Create template from page

### 5. Media Library
- Upload files: `http://localhost:8000/media`
- Organize with folders
- Use in builder

### 6. Analytics
- View stats: `http://localhost:8000/analytics`
- Page-specific analytics

### 7. Domains
- Add custom domain: `http://localhost:8000/domains`
- DNS verification

### 8. Subscriptions
- Pricing page: `http://localhost:8000/pricing`
- Manage subscription: `http://localhost:8000/subscription/manage`
- Billing history: `http://localhost:8000/billing/history`

## Code Quality Commands

```bash
# Format PHP code (PSR-12)
./vendor/bin/pint

# Static analysis
./vendor/bin/phpstan analyse

# Lint JavaScript
npm run lint

# Fix lint issues
npm run lint:fix
```

## Useful Artisan Commands

```bash
# Clear all caches
php artisan optimize:clear

# View all routes
php artisan route:list

# Create new migration
php artisan make:migration create_xxx_table

# Create new model with factory
php artisan make:model ModelName -mf

# Fresh migration with seeds
php artisan migrate:fresh --seed
```

## Project Structure

```
├── app/
│   ├── Contracts/          # Interfaces
│   ├── DTOs/               # Data Transfer Objects
│   ├── Http/
│   │   ├── Controllers/    # Request handlers
│   │   └── Middleware/     # Request middleware
│   ├── Models/             # Eloquent models
│   ├── Observers/          # Model observers
│   ├── Policies/           # Authorization
│   └── Services/           # Business logic
├── config/
│   └── subscription.php    # Plan configuration
├── database/
│   ├── factories/          # Test factories
│   └── migrations/         # Database schema
├── resources/
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript
│   └── views/             # Blade templates
├── routes/
│   └── web.php            # Web routes
├── scripts/
│   ├── deploy.sh          # Deployment script
│   └── rollback.sh        # Rollback script
└── tests/
    ├── Feature/           # Feature tests
    └── Unit/              # Unit tests
```

## Troubleshooting

### Database Connection Error
```bash
# Check MySQL is running
sudo service mysql status

# Verify credentials in .env
```

### Vite Not Loading Assets
```bash
# Make sure Vite is running
npm run dev

# Check browser console for errors
```

### Permission Issues
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
```

### Clear All Caches
```bash
php artisan optimize:clear
composer dump-autoload
npm run build
```

### Reset Everything
```bash
php artisan migrate:fresh --seed
php artisan optimize:clear
```

## Stripe Testing

For testing payments locally:

1. Get test API keys from [Stripe Dashboard](https://dashboard.stripe.com/test/apikeys)
2. Use test card numbers:
   - Success: `4242 4242 4242 4242`
   - Decline: `4000 0000 0000 0002`
3. Set up webhook forwarding with Stripe CLI:
   ```bash
   stripe listen --forward-to localhost:8000/webhook/stripe
   ```

## VS Code Recommended Settings

Add to `.vscode/settings.json`:

```json
{
    "editor.formatOnSave": true,
    "php.validate.executablePath": "/usr/bin/php",
    "tailwindCSS.includeLanguages": {
        "blade": "html"
    },
    "files.associations": {
        "*.blade.php": "blade"
    }
}
```

## Need Help?

- Check `CLAUDE.md` for project overview
- Review `docs/` folder for detailed documentation
- Run `php artisan` for available commands

Happy coding! 🚀
