# Models & Repositories

## Eloquent Models

### User Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'email_verified_at',
        'settings',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'settings' => 'array',
    ];

    // Relationships
    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function templates()
    {
        return $this->hasMany(Template::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeWithActiveSubscription($query)
    {
        return $query->whereHas('subscription', function ($q) {
            $q->where('status', 'active')
              ->where('ends_at', '>', now());
        });
    }

    public function scopeCreatedBetween($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    // Helpers
    public function hasActiveSubscription(): bool
    {
        return $this->subscription?->isActive() ?? false;
    }

    public function getCurrentPlan(): ?Plan
    {
        return $this->subscription?->plan;
    }

    public function canCreatePage(): bool
    {
        $limit = $this->getCurrentPlan()?->features['page_limit'] ?? 1;
        return $this->pages()->count() < $limit;
    }
}
```

### Page Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'template_id',
        'domain_id',
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'settings',
        'status',
        'published_at',
    ];

    protected $casts = [
        'content' => 'array',
        'settings' => 'array',
        'published_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function media()
    {
        return $this->belongsToMany(Media::class, 'page_media')
                    ->withTimestamps();
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('meta_description', 'like', "%{$term}%");
        });
    }

    // Helpers
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at !== null;
    }

    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function getFullUrl(): string
    {
        $domain = $this->domain?->domain ?? config('app.url');
        return "https://{$domain}/{$this->slug}";
    }
}
```

### Template Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'thumbnail',
        'content',
        'category',
        'is_premium',
        'is_public',
        'settings',
    ];

    protected $casts = [
        'content' => 'array',
        'settings' => 'array',
        'is_premium' => 'boolean',
        'is_public' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }
}
```

### Media Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'original_name',
        'mime_type',
        'size',
        'path',
        'disk',
        'metadata',
    ];

    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array',
    ];

    protected $appends = ['url'];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pages()
    {
        return $this->belongsToMany(Page::class, 'page_media')
                    ->withTimestamps();
    }

    // Accessors
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    public function scopeVideos($query)
    {
        return $query->where('mime_type', 'like', 'video/%');
    }

    public function scopeDocuments($query)
    {
        return $query->whereIn('mime_type', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    // Helpers
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function delete()
    {
        Storage::disk($this->disk)->delete($this->path);
        return parent::delete();
    }
}
```

### Domain Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'domain',
        'is_verified',
        'is_primary',
        'ssl_status',
        'dns_records',
        'verified_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_primary' => 'boolean',
        'dns_records' => 'array',
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePendingVerification($query)
    {
        return $query->where('is_verified', false);
    }

    // Helpers
    public function markAsVerified(): void
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }

    public function setAsPrimary(): void
    {
        $this->user->domains()->update(['is_primary' => false]);
        $this->update(['is_primary' => true]);
    }
}
```

### Plan Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'currency',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Helpers
    public function getPrice(string $interval = 'monthly'): float
    {
        return $interval === 'yearly'
            ? $this->price_yearly
            : $this->price_monthly;
    }

    public function hasFeature(string $feature): bool
    {
        return isset($this->features[$feature]) && $this->features[$feature];
    }
}
```

### Subscription Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'billing_interval',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'canceled_at',
        'payment_provider',
        'provider_subscription_id',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('ends_at', '>', now());
    }

    public function scopeCanceled($query)
    {
        return $query->whereNotNull('canceled_at');
    }

    public function scopeExpired($query)
    {
        return $query->where('ends_at', '<', now());
    }

    public function scopeOnTrial($query)
    {
        return $query->where('status', 'trialing')
                     ->where('trial_ends_at', '>', now());
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at > now();
    }

    public function onTrial(): bool
    {
        return $this->status === 'trialing' && $this->trial_ends_at > now();
    }

    public function isCanceled(): bool
    {
        return $this->canceled_at !== null;
    }

    public function cancel(): void
    {
        $this->update([
            'canceled_at' => now(),
            'status' => 'canceled',
        ]);
    }

    public function renew(int $days = 30): void
    {
        $this->update([
            'ends_at' => $this->ends_at->addDays($days),
            'status' => 'active',
        ]);
    }
}
```

### Payment Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'payment_provider',
        'provider_payment_id',
        'invoice_number',
        'billing_details',
        'paid_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'billing_details' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'succeeded');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRefunded($query)
    {
        return $query->whereNotNull('refunded_at');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    // Helpers
    public function isPaid(): bool
    {
        return $this->status === 'succeeded' && $this->paid_at !== null;
    }

    public function isRefunded(): bool
    {
        return $this->refunded_at !== null;
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'succeeded',
            'paid_at' => now(),
        ]);
    }

    public function refund(): void
    {
        $this->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);
    }
}
```

---

## Repository Interfaces

### Base Repository Interface

```php
<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Model;
    public function findOrFail(int $id): Model;
    public function create(array $data): Model;
    public function update(int $id, array $data): Model;
    public function delete(int $id): bool;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
}
```

### User Repository Interface

```php
<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function getActiveUsers(): Collection;
    public function getUsersWithActiveSubscription(): Collection;
    public function getRecentlyRegistered(int $days = 7): Collection;
}
```

### Page Repository Interface

```php
<?php

namespace App\Repositories\Contracts;

use App\Models\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PageRepositoryInterface extends RepositoryInterface
{
    public function getByUser(int $userId): Collection;
    public function getPublished(): Collection;
    public function findBySlug(string $slug): ?Page;
    public function searchByUser(int $userId, string $term): Collection;
    public function paginateByUser(int $userId, int $perPage = 15): LengthAwarePaginator;
}
```

### Template Repository Interface

```php
<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface TemplateRepositoryInterface extends RepositoryInterface
{
    public function getPublicTemplates(): Collection;
    public function getByCategory(string $category): Collection;
    public function getPremiumTemplates(): Collection;
    public function getFreeTemplates(): Collection;
    public function search(string $term): Collection;
}
```

### Subscription Repository Interface

```php
<?php

namespace App\Repositories\Contracts;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

interface SubscriptionRepositoryInterface extends RepositoryInterface
{
    public function getActiveByUser(int $userId): ?Subscription;
    public function getExpiring(int $days = 7): Collection;
    public function getByStatus(string $status): Collection;
    public function cancel(int $id): Subscription;
}
```

---

## Repository Implementations

### Base Repository

```php
<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);
        return $record->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }
}
```

### User Repository

```php
<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function getActiveUsers(): Collection
    {
        return $this->model->active()->get();
    }

    public function getUsersWithActiveSubscription(): Collection
    {
        return $this->model->withActiveSubscription()
                           ->with('subscription.plan')
                           ->get();
    }

    public function getRecentlyRegistered(int $days = 7): Collection
    {
        return $this->model->createdBetween(
            now()->subDays($days),
            now()
        )->get();
    }
}
```

### Page Repository

```php
<?php

namespace App\Repositories;

use App\Models\Page;
use App\Repositories\Contracts\PageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PageRepository extends BaseRepository implements PageRepositoryInterface
{
    public function __construct(Page $model)
    {
        parent::__construct($model);
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model->byUser($userId)
                           ->with(['template', 'domain'])
                           ->latest()
                           ->get();
    }

    public function getPublished(): Collection
    {
        return $this->model->published()
                           ->with(['user', 'domain'])
                           ->get();
    }

    public function findBySlug(string $slug): ?Page
    {
        return $this->model->where('slug', $slug)
                           ->published()
                           ->first();
    }

    public function searchByUser(int $userId, string $term): Collection
    {
        return $this->model->byUser($userId)
                           ->search($term)
                           ->get();
    }

    public function paginateByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->byUser($userId)
                           ->with(['template', 'domain'])
                           ->latest()
                           ->paginate($perPage);
    }

    public function duplicate(int $pageId): Page
    {
        $page = $this->findOrFail($pageId);

        return $this->create([
            'user_id' => $page->user_id,
            'template_id' => $page->template_id,
            'title' => $page->title . ' (Copy)',
            'slug' => $page->slug . '-copy-' . time(),
            'content' => $page->content,
            'settings' => $page->settings,
            'status' => 'draft',
        ]);
    }
}
```

### Template Repository

```php
<?php

namespace App\Repositories;

use App\Models\Template;
use App\Repositories\Contracts\TemplateRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TemplateRepository extends BaseRepository implements TemplateRepositoryInterface
{
    public function __construct(Template $model)
    {
        parent::__construct($model);
    }

    public function getPublicTemplates(): Collection
    {
        return $this->model->public()->get();
    }

    public function getByCategory(string $category): Collection
    {
        return $this->model->public()
                           ->byCategory($category)
                           ->get();
    }

    public function getPremiumTemplates(): Collection
    {
        return $this->model->public()
                           ->premium()
                           ->get();
    }

    public function getFreeTemplates(): Collection
    {
        return $this->model->public()
                           ->free()
                           ->get();
    }

    public function search(string $term): Collection
    {
        return $this->model->public()
                           ->search($term)
                           ->get();
    }
}
```

### Subscription Repository

```php
<?php

namespace App\Repositories;

use App\Models\Subscription;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionRepository extends BaseRepository implements SubscriptionRepositoryInterface
{
    public function __construct(Subscription $model)
    {
        parent::__construct($model);
    }

    public function getActiveByUser(int $userId): ?Subscription
    {
        return $this->model->where('user_id', $userId)
                           ->active()
                           ->with('plan')
                           ->first();
    }

    public function getExpiring(int $days = 7): Collection
    {
        return $this->model->active()
                           ->whereBetween('ends_at', [now(), now()->addDays($days)])
                           ->with(['user', 'plan'])
                           ->get();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
                           ->with(['user', 'plan'])
                           ->get();
    }

    public function cancel(int $id): Subscription
    {
        $subscription = $this->findOrFail($id);
        $subscription->cancel();
        return $subscription->fresh();
    }
}
```

---

## Model Factories

### UserFactory

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'avatar' => null,
            'settings' => [],
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
```

### PageFactory

```php
<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PageFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'user_id' => User::factory(),
            'template_id' => Template::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(5),
            'content' => [
                'blocks' => [
                    ['type' => 'hero', 'data' => ['title' => $title]],
                ],
            ],
            'meta_title' => $title,
            'meta_description' => fake()->paragraph(),
            'settings' => [],
            'status' => 'draft',
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
```

### TemplateFactory

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'thumbnail' => null,
            'content' => [
                'blocks' => [
                    ['type' => 'hero', 'data' => []],
                    ['type' => 'features', 'data' => []],
                ],
            ],
            'category' => fake()->randomElement(['business', 'portfolio', 'landing', 'blog']),
            'is_premium' => false,
            'is_public' => true,
            'settings' => [],
        ];
    }

    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => true,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }
}
```

### PlanFactory

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlanFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Starter', 'Pro', 'Enterprise']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'price_monthly' => fake()->randomElement([9.99, 29.99, 99.99]),
            'price_yearly' => fake()->randomElement([99.99, 299.99, 999.99]),
            'currency' => 'USD',
            'features' => [
                'page_limit' => fake()->randomElement([5, 25, 100]),
                'custom_domain' => fake()->boolean(),
                'analytics' => fake()->boolean(),
                'support_priority' => fake()->randomElement(['basic', 'priority', 'dedicated']),
            ],
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
```

### SubscriptionFactory

```php
<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'status' => 'active',
            'billing_interval' => fake()->randomElement(['monthly', 'yearly']),
            'trial_ends_at' => null,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'canceled_at' => null,
            'payment_provider' => 'stripe',
            'provider_subscription_id' => 'sub_' . Str::random(14),
        ];
    }

    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'canceled',
            'canceled_at' => now(),
        ]);
    }

    public function onTrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(14),
        ]);
    }
}
```

### PaymentFactory

```php
<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subscription_id' => Subscription::factory(),
            'amount' => fake()->randomFloat(2, 9.99, 999.99),
            'currency' => 'USD',
            'status' => 'succeeded',
            'payment_method' => 'card',
            'payment_provider' => 'stripe',
            'provider_payment_id' => 'pi_' . Str::random(24),
            'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
            'billing_details' => [
                'name' => fake()->name(),
                'email' => fake()->email(),
            ],
            'paid_at' => now(),
            'refunded_at' => null,
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'paid_at' => null,
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);
    }
}
```

---

## Service Provider Registration

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\{
    UserRepositoryInterface,
    PageRepositoryInterface,
    TemplateRepositoryInterface,
    SubscriptionRepositoryInterface
};
use App\Repositories\{
    UserRepository,
    PageRepository,
    TemplateRepository,
    SubscriptionRepository
};

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(PageRepositoryInterface::class, PageRepository::class);
        $this->app->bind(TemplateRepositoryInterface::class, TemplateRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
    }
}
```

Add to `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\RepositoryServiceProvider::class,
],
```
