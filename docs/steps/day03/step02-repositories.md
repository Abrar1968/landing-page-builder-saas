# Day 3 - Step 2: Repositories & Seeders

## Objective
Create repositories for all models and database seeders.

## Tasks

### 2.1 PageRepository
```php
// app/Repositories/PageRepository.php
class PageRepository extends BaseRepository
{
    public function getByUser(int $userId, int $perPage = 10)
    {
        return $this->model
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function findBySlug(string $slug): ?Page
    {
        return $this->model->where('slug', $slug)->first();
    }
}
```

### 2.2 Create All Repositories
- TemplateRepository, MediaRepository
- DomainRepository, SubscriptionRepository

### 2.3 Database Seeders
- UserSeeder with admin and test users
- TemplateSeeder with 5+ starter templates
- Model factories for all models

### 2.4 PageObserver for Versioning
```php
public function updating(Page $page): void
{
    if ($page->isDirty('content')) {
        // Create version before update
    }
}
```

## Reference Documentation
- `docs/backend/04-MODELS-REPOSITORIES.md` - Repository implementations
- `docs/features/03-TEMPLATES.md` - Template content structure

## Expected Deliverables
- [x] All repositories created
- [x] Seeders working
- [x] PageObserver with versioning
- [x] `php artisan migrate --seed` works

## Day 3 Complete
→ Proceed to Day 4: Template System
