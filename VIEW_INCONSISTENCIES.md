# View-Backend Inconsistencies

This document tracks all identified inconsistencies between views and backend components.

## CRITICAL ISSUES

### 1. ✅ Domain Model - Migration Mismatch
- **Files:** `app/Models/Domain.php`, `database/migrations/2024_01_01_000005_create_domains_table.php`
- **Issue:** Domain model defines `verification_token` and `ssl_provisioned_at` in `$fillable` and `$casts`, but these columns don't exist in the migration.
- **Fix:** Removed `verification_token` and `ssl_provisioned_at` from Domain model fillable and casts.

### 2. ✅ Domain SSL Status Enum Mismatch
- **Files:** `resources/views/dashboard/domains/index.blade.php:87-94`
- **Issue:** View checks for `ssl_status === 'provisioning'` but migration defines ssl_status as enum: `'pending', 'active', 'expired'`. Value `'provisioning'` is not valid.
- **Fix:** Changed `'provisioning'` to `'pending'` in the view.

### 3. ✅ User Model Missing `payments` Relationship
- **Files:** `app/Http/Controllers/BillingController.php:14`, `app/Models/User.php`
- **Issue:** BillingController calls `auth()->user()->payments()` but User model doesn't define this relationship.
- **Fix:** Added `payments()` HasMany relationship to User model.

### 4. ✅ Subscription Manage View - Wrong Route Name
- **Files:** `resources/views/dashboard/subscription/manage.blade.php:35`
- **Issue:** View posts to `route('subscription.cancel')` but this is a GET route. The correct POST route is `subscription.cancel.action`.
- **Fix:** Changed to `route('subscription.cancel.action')`.

### 5. ✅ Page Controller - Non-Existent Model Properties
- **Files:** `app/Http/Controllers/PageController.php:28-30`
- **Issue:** PageController returns properties that don't exist: `views`, `conversions`, `thumbnail`.
- **Fix:** Removed `conversions` and `thumbnail`, changed `views` to use `pageViews()->count()`.

### 6. ✅ Subscription Model has `stripe_customer_id` not in Migration
- **Files:** `app/Models/Subscription.php:19`
- **Issue:** Subscription model has `stripe_customer_id` in `$fillable` but migration doesn't define this column (it's on users table).
- **Fix:** Removed `stripe_customer_id` from Subscription model fillable.

## MODERATE ISSUES

### 7. ✅ Dashboard Index - Invalid `name` Property Fallback
- **Files:** `resources/views/dashboard/index.blade.php:130`
- **Issue:** View uses `$page->title ?? $page->name` but Page model doesn't have a `name` column.
- **Fix:** Removed fallback, now uses `$page->title` directly.

### 8. ✅ Sidebar Hardcoded Subscription Info
- **Files:** `resources/views/components/dashboard/sidebar-content.blade.php:53,59,63`
- **Issue:** Sidebar hardcodes "Free Plan" and "1 of 1 pages used" instead of dynamically checking user's subscription.
- **Fix:** Made sidebar dynamic based on user's actual subscription plan and limits.

### 9. ✅ Domain Modal Uses Non-Existent `verification_token`
- **Files:** `resources/views/dashboard/domains/index.blade.php:213`
- **Issue:** DNS instructions modal displays `selectedDomain?.verification_token` but field doesn't exist in database.
- **Fix:** Changed to generate token dynamically using domain ID.

### 10. ✅ Public Page URL Mismatch
- **Files:** `resources/views/dashboard/pages/index.blade.php:141`
- **Issue:** View generates link as `'/' + page.url` but actual route is `/p/{slug}`.
- **Fix:** Changed to `'/p/' + page.url`.

### 11. ✅ PageVersion Model timestamps mismatch
- **Files:** `app/Models/PageVersion.php`, `database/migrations/2024_01_01_000009_create_page_versions_table.php`
- **Issue:** Model sets `$timestamps = false` but migration has `timestamps()`.
- **Fix:** Changed to `const UPDATED_AT = null` to allow created_at but not updated_at.

## Progress Tracker

- Total Issues: 11
- Fixed: 11
- Remaining: 0

All inconsistencies have been resolved!
