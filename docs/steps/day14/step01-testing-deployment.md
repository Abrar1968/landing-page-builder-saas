# Day 14 - Step 1: Testing & Deployment

## Objective
Complete testing suite and deploy to production.

## Tasks

### 1.1 Feature Tests
```php
// tests/Feature/PageTest.php
public function test_user_can_create_page(): void
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('pages.store'), [
        'title' => 'Test Page',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('pages', ['title' => 'Test Page']);
}

public function test_user_cannot_update_others_page(): void
{
    $user = User::factory()->create();
    $page = Page::factory()->create();

    $response = $this->actingAs($user)->put(route('pages.update', $page), [
        'title' => 'Hacked',
    ]);

    $response->assertForbidden();
}
```

### 1.2 Unit Tests
```php
// tests/Unit/PageRendererTest.php
public function test_renders_heading_element(): void
{
    $elements = [
        ['type' => 'heading', 'content' => ['text' => 'Test', 'size' => 'text-3xl']],
    ];

    $html = $this->renderer->render($elements);

    $this->assertStringContainsString('Test', $html);
}
```

### 1.3 Run Test Suite
```bash
php artisan test
php artisan test --coverage
```

Target: 60%+ overall coverage

### 1.4 Deployment Script
```bash
#!/bin/bash
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

### 1.5 Production Environment
- Configure `.env` for production
- Set up Nginx/Apache
- Configure SSL (Let's Encrypt)
- Set up monitoring (Laravel Telescope, logs)
- Configure backups

### 1.6 Final Checklist
- [ ] All tests passing
- [ ] 0 critical security vulnerabilities
- [ ] Performance targets met
- [ ] Monitoring active
- [ ] Backup system working
- [ ] Documentation complete

## Reference Documentation
- `docs/07-TESTING.md` - Testing strategy
- `docs/05-DEPLOYMENT.md` - Deployment guide

## Expected Deliverables
- [ ] Test suite complete (60%+ coverage)
- [ ] All tests passing
- [ ] Deployment script ready
- [ ] Production environment configured
- [ ] Monitoring active

## Day 14 Complete - Milestone M5: Production Ready

## Project Complete!
The Landing Page Builder SaaS is now ready for production use.
