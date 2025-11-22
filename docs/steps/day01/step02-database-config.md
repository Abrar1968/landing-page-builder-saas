# Day 1 - Step 2: Database Configuration

## Objective
Configure MySQL database and create all migration files.

## Tasks

### 2.1 Create Database
```sql
CREATE DATABASE landing_page_builder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2.2 Create Migrations
Create the following migration files in order:

1. **users** (extend default)
2. **pages** - User landing pages
3. **templates** - System and user templates
4. **media** - Uploaded files
5. **subscriptions** - Stripe data
6. **domains** - Custom domains
7. **page_views** - Analytics
8. **form_submissions** - Form data
9. **page_versions** - Version history

### 2.3 Run Migrations
```bash
php artisan migrate
```

## Reference Documentation
- `docs/backend/02-DATABASE-SCHEMA.md` - Complete ER diagrams and table definitions
- `docs/04-IMPLEMENTATION-FLOW.md` - Migration code examples

## Key Schema Details

### Pages Table
```php
Schema::create('pages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('title');
    $table->string('slug')->unique();
    $table->json('content')->nullable();
    $table->json('settings')->nullable();
    $table->enum('status', ['draft', 'published'])->default('draft');
    $table->timestamps();
});
```

## Expected Deliverables
- [x] All 9 core tables migrated
- [x] Foreign keys properly set
- [x] Indexes on frequently queried columns
- [x] UTF-8MB4 encoding enabled

## Next Step
→ `step03-tailwind-alpine-setup.md`
