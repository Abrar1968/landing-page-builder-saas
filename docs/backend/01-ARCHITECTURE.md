# Backend Architecture Documentation

## Tech Stack
- **Framework:** Laravel 11
- **Database:** MySQL
- **Design Patterns:** Service-Repository, Observer, Strategy

---

## Architecture Overview

```
app/
├── Contracts/
│   ├── Repositories/
│   │   └── RepositoryInterface.php
│   └── Services/
│       └── PaymentGatewayInterface.php
├── Exceptions/
│   └── Handler.php
├── Http/
│   └── Controllers/
├── Models/
│   ├── Page.php
│   ├── User.php
│   └── Subscription.php
├── Observers/
│   ├── PageObserver.php
│   ├── UserObserver.php
│   └── SubscriptionObserver.php
├── Providers/
│   ├── AppServiceProvider.php
│   └── RepositoryServiceProvider.php
├── Repositories/
│   ├── BaseRepository.php
│   ├── PageRepository.php
│   ├── UserRepository.php
│   └── SubscriptionRepository.php
├── Services/
│   ├── BaseService.php
│   ├── PageService.php
│   ├── UserService.php
│   ├── SubscriptionService.php
│   └── Payment/
│       ├── PaymentContext.php
│       ├── StripeGateway.php
│       └── PayPalGateway.php
└── Jobs/
    ├── ProcessPageGeneration.php
    ├── SendWelcomeEmail.php
    └── ProcessPayment.php
```

---

## Base Repository Interface & Implementation

### Repository Interface

```php
<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    /**
     * Get all records
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find record by ID
     */
    public function find(int $id, array $columns = ['*']): ?Model;

    /**
     * Find record by ID or fail
     */
    public function findOrFail(int $id, array $columns = ['*']): Model;

    /**
     * Find records by attribute
     */
    public function findBy(string $attribute, mixed $value, array $columns = ['*']): Collection;

    /**
     * Find first record by attribute
     */
    public function findFirstBy(string $attribute, mixed $value, array $columns = ['*']): ?Model;

    /**
     * Create a new record
     */
    public function create(array $data): Model;

    /**
     * Update a record
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a record
     */
    public function delete(int $id): bool;

    /**
     * Get records with relationships
     */
    public function with(array $relations): self;

    /**
     * Order records
     */
    public function orderBy(string $column, string $direction = 'asc'): self;

    /**
     * Get count of records
     */
    public function count(): int;
}
```

### Base Repository Implementation

```php
<?php

namespace App\Repositories;

use App\Contracts\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;
    protected Builder $query;

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->resetQuery();
    }

    protected function resetQuery(): void
    {
        $this->query = $this->model->newQuery();
    }

    public function all(array $columns = ['*']): Collection
    {
        $result = $this->query->get($columns);
        $this->resetQuery();
        return $result;
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        $result = $this->query->paginate($perPage, $columns);
        $this->resetQuery();
        return $result;
    }

    public function find(int $id, array $columns = ['*']): ?Model
    {
        $result = $this->query->find($id, $columns);
        $this->resetQuery();
        return $result;
    }

    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        $result = $this->query->findOrFail($id, $columns);
        $this->resetQuery();
        return $result;
    }

    public function findBy(string $attribute, mixed $value, array $columns = ['*']): Collection
    {
        $result = $this->query->where($attribute, $value)->get($columns);
        $this->resetQuery();
        return $result;
    }

    public function findFirstBy(string $attribute, mixed $value, array $columns = ['*']): ?Model
    {
        $result = $this->query->where($attribute, $value)->first($columns);
        $this->resetQuery();
        return $result;
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $record = $this->findOrFail($id);
        return $record->update($data);
    }

    public function delete(int $id): bool
    {
        $record = $this->findOrFail($id);
        return $record->delete();
    }

    public function with(array $relations): self
    {
        $this->query = $this->query->with($relations);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->query = $this->query->orderBy($column, $direction);
        return $this;
    }

    public function count(): int
    {
        $result = $this->query->count();
        $this->resetQuery();
        return $result;
    }
}
```

### Concrete Repository Examples

```php
<?php

namespace App\Repositories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Collection;

class PageRepository extends BaseRepository
{
    public function __construct(Page $model)
    {
        parent::__construct($model);
    }

    public function findByUser(int $userId): Collection
    {
        return $this->findBy('user_id', $userId);
    }

    public function findPublished(): Collection
    {
        return $this->query
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findBySlug(string $slug): ?Page
    {
        return $this->findFirstBy('slug', $slug);
    }
}
```

```php
<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findFirstBy('email', $email);
    }

    public function findActiveUsers()
    {
        return $this->query
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
```

```php
<?php

namespace App\Repositories;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionRepository extends BaseRepository
{
    public function __construct(Subscription $model)
    {
        parent::__construct($model);
    }

    public function findByUser(int $userId): Collection
    {
        return $this->findBy('user_id', $userId);
    }

    public function findActive(): Collection
    {
        return $this->query
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->get();
    }
}
```

---

## Base Service Class

```php
<?php

namespace App\Services;

use App\Contracts\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class BaseService
{
    protected RepositoryInterface $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->repository->all($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $columns);
    }

    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->repository->find($id, $columns);
    }

    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->repository->findOrFail($id, $columns);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            try {
                $model = $this->repository->create($data);
                Log::info(class_basename($this) . ': Record created', ['id' => $model->id]);
                return $model;
            } catch (Exception $e) {
                Log::error(class_basename($this) . ': Failed to create record', [
                    'data' => $data,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        });
    }

    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            try {
                $result = $this->repository->update($id, $data);
                Log::info(class_basename($this) . ': Record updated', ['id' => $id]);
                return $result;
            } catch (Exception $e) {
                Log::error(class_basename($this) . ': Failed to update record', [
                    'id' => $id,
                    'data' => $data,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            try {
                $result = $this->repository->delete($id);
                Log::info(class_basename($this) . ': Record deleted', ['id' => $id]);
                return $result;
            } catch (Exception $e) {
                Log::error(class_basename($this) . ': Failed to delete record', [
                    'id' => $id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        });
    }
}
```

### Concrete Service Examples

```php
<?php

namespace App\Services;

use App\Repositories\PageRepository;
use App\Models\Page;
use App\Jobs\ProcessPageGeneration;
use Illuminate\Support\Str;

class PageService extends BaseService
{
    public function __construct(PageRepository $repository)
    {
        parent::__construct($repository);
    }

    public function createPage(array $data): Page
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['status'] = $data['status'] ?? 'draft';

        $page = $this->create($data);

        // Dispatch job for async processing
        ProcessPageGeneration::dispatch($page);

        return $page;
    }

    public function publish(int $pageId): bool
    {
        return $this->update($pageId, [
            'status' => 'published',
            'published_at' => now()
        ]);
    }

    public function findBySlug(string $slug): ?Page
    {
        return $this->repository->findBySlug($slug);
    }
}
```

```php
<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Models\User;
use App\Jobs\SendWelcomeEmail;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    public function __construct(UserRepository $repository)
    {
        parent::__construct($repository);
    }

    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        $user = $this->create($data);

        SendWelcomeEmail::dispatch($user);

        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->repository->findByEmail($email);
    }
}
```

```php
<?php

namespace App\Services;

use App\Repositories\SubscriptionRepository;
use App\Models\Subscription;
use App\Services\Payment\PaymentContext;

class SubscriptionService extends BaseService
{
    protected PaymentContext $paymentContext;

    public function __construct(
        SubscriptionRepository $repository,
        PaymentContext $paymentContext
    ) {
        parent::__construct($repository);
        $this->paymentContext = $paymentContext;
    }

    public function subscribe(int $userId, string $plan, string $paymentMethod, array $paymentData): Subscription
    {
        // Process payment using strategy pattern
        $this->paymentContext->setGateway($paymentMethod);
        $paymentResult = $this->paymentContext->processPayment($paymentData);

        if (!$paymentResult['success']) {
            throw new \Exception('Payment failed: ' . $paymentResult['message']);
        }

        return $this->create([
            'user_id' => $userId,
            'plan' => $plan,
            'status' => 'active',
            'payment_id' => $paymentResult['transaction_id'],
            'expires_at' => now()->addMonth()
        ]);
    }

    public function cancel(int $subscriptionId): bool
    {
        return $this->update($subscriptionId, [
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);
    }
}
```

---

## Service Provider Registration

### Repository Service Provider

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Repositories\RepositoryInterface;
use App\Repositories\PageRepository;
use App\Repositories\UserRepository;
use App\Repositories\SubscriptionRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PageRepository::class, function ($app) {
            return new PageRepository(new \App\Models\Page());
        });

        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository(new \App\Models\User());
        });

        $this->app->bind(SubscriptionRepository::class, function ($app) {
            return new SubscriptionRepository(new \App\Models\Subscription());
        });
    }

    public function boot(): void
    {
        //
    }
}
```

### App Service Provider

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Page;
use App\Models\User;
use App\Models\Subscription;
use App\Observers\PageObserver;
use App\Observers\UserObserver;
use App\Observers\SubscriptionObserver;
use App\Contracts\Services\PaymentGatewayInterface;
use App\Services\Payment\StripeGateway;
use App\Services\Payment\PayPalGateway;
use App\Services\Payment\PaymentContext;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register Payment Gateways
        $this->app->bind('payment.stripe', function ($app) {
            return new StripeGateway();
        });

        $this->app->bind('payment.paypal', function ($app) {
            return new PayPalGateway();
        });

        // Register Payment Context
        $this->app->singleton(PaymentContext::class, function ($app) {
            return new PaymentContext($app);
        });

        // Register default payment gateway
        $this->app->bind(PaymentGatewayInterface::class, function ($app) {
            $default = config('payment.default', 'stripe');
            return $app->make("payment.{$default}");
        });
    }

    public function boot(): void
    {
        // Register Observers
        Page::observe(PageObserver::class);
        User::observe(UserObserver::class);
        Subscription::observe(SubscriptionObserver::class);
    }
}
```

Register providers in `bootstrap/providers.php`:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RepositoryServiceProvider::class,
];
```

---

## Model Observers

### Page Observer

```php
<?php

namespace App\Observers;

use App\Models\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PageObserver
{
    public function creating(Page $page): void
    {
        if (empty($page->slug)) {
            $page->slug = Str::slug($page->title);
        }

        if (empty($page->uuid)) {
            $page->uuid = Str::uuid();
        }

        Log::info('Page creating', ['title' => $page->title]);
    }

    public function created(Page $page): void
    {
        Cache::tags(['pages', "user:{$page->user_id}"])->flush();

        Log::info('Page created', ['id' => $page->id, 'title' => $page->title]);
    }

    public function updating(Page $page): void
    {
        if ($page->isDirty('title') && !$page->isDirty('slug')) {
            $page->slug = Str::slug($page->title);
        }

        Log::info('Page updating', ['id' => $page->id]);
    }

    public function updated(Page $page): void
    {
        Cache::tags(['pages', "page:{$page->id}", "user:{$page->user_id}"])->flush();

        Log::info('Page updated', ['id' => $page->id]);
    }

    public function deleting(Page $page): void
    {
        Log::info('Page deleting', ['id' => $page->id]);
    }

    public function deleted(Page $page): void
    {
        Cache::tags(['pages', "page:{$page->id}", "user:{$page->user_id}"])->flush();

        Log::info('Page deleted', ['id' => $page->id]);
    }

    public function restored(Page $page): void
    {
        Cache::tags(['pages', "user:{$page->user_id}"])->flush();

        Log::info('Page restored', ['id' => $page->id]);
    }

    public function forceDeleted(Page $page): void
    {
        Log::warning('Page force deleted', ['id' => $page->id]);
    }
}
```

### User Observer

```php
<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserObserver
{
    public function creating(User $user): void
    {
        if (empty($user->uuid)) {
            $user->uuid = Str::uuid();
        }

        Log::info('User creating', ['email' => $user->email]);
    }

    public function created(User $user): void
    {
        Cache::tags(['users'])->flush();

        Log::info('User created', [
            'id' => $user->id,
            'email' => $user->email
        ]);

        // Additional actions like sending verification email
        // can be triggered here or via events
    }

    public function updating(User $user): void
    {
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        Log::info('User updating', ['id' => $user->id]);
    }

    public function updated(User $user): void
    {
        Cache::tags(['users', "user:{$user->id}"])->flush();

        Log::info('User updated', ['id' => $user->id]);
    }

    public function deleting(User $user): void
    {
        Log::info('User deleting', ['id' => $user->id]);
    }

    public function deleted(User $user): void
    {
        Cache::tags(['users', "user:{$user->id}"])->flush();

        Log::info('User deleted', ['id' => $user->id]);
    }

    public function restored(User $user): void
    {
        Cache::tags(['users'])->flush();

        Log::info('User restored', ['id' => $user->id]);
    }

    public function forceDeleted(User $user): void
    {
        // Clean up all related data
        $user->pages()->forceDelete();
        $user->subscriptions()->forceDelete();

        Log::warning('User force deleted', ['id' => $user->id]);
    }
}
```

### Subscription Observer

```php
<?php

namespace App\Observers;

use App\Models\Subscription;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessPayment;

class SubscriptionObserver
{
    public function creating(Subscription $subscription): void
    {
        if (empty($subscription->started_at)) {
            $subscription->started_at = now();
        }

        Log::info('Subscription creating', [
            'user_id' => $subscription->user_id,
            'plan' => $subscription->plan
        ]);
    }

    public function created(Subscription $subscription): void
    {
        Cache::tags(['subscriptions', "user:{$subscription->user_id}"])->flush();

        Log::info('Subscription created', [
            'id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'plan' => $subscription->plan
        ]);

        // Dispatch payment processing job if needed
        if ($subscription->status === 'pending') {
            ProcessPayment::dispatch($subscription);
        }
    }

    public function updating(Subscription $subscription): void
    {
        // Track status changes
        if ($subscription->isDirty('status')) {
            $subscription->status_changed_at = now();
        }

        Log::info('Subscription updating', ['id' => $subscription->id]);
    }

    public function updated(Subscription $subscription): void
    {
        Cache::tags([
            'subscriptions',
            "subscription:{$subscription->id}",
            "user:{$subscription->user_id}"
        ])->flush();

        Log::info('Subscription updated', [
            'id' => $subscription->id,
            'status' => $subscription->status
        ]);
    }

    public function deleting(Subscription $subscription): void
    {
        Log::info('Subscription deleting', ['id' => $subscription->id]);
    }

    public function deleted(Subscription $subscription): void
    {
        Cache::tags(['subscriptions', "user:{$subscription->user_id}"])->flush();

        Log::info('Subscription deleted', ['id' => $subscription->id]);
    }
}
```

---

## Strategy Pattern for Payments

### Payment Gateway Interface

```php
<?php

namespace App\Contracts\Services;

interface PaymentGatewayInterface
{
    /**
     * Process a payment
     */
    public function charge(array $data): array;

    /**
     * Refund a payment
     */
    public function refund(string $transactionId, float $amount): array;

    /**
     * Create a subscription
     */
    public function createSubscription(array $data): array;

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(string $subscriptionId): array;

    /**
     * Get payment details
     */
    public function getPaymentDetails(string $transactionId): array;

    /**
     * Validate webhook signature
     */
    public function validateWebhook(string $payload, string $signature): bool;

    /**
     * Get gateway name
     */
    public function getName(): string;
}
```

### Stripe Gateway Implementation

```php
<?php

namespace App\Services\Payment;

use App\Contracts\Services\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class StripeGateway implements PaymentGatewayInterface
{
    protected string $apiKey;
    protected string $webhookSecret;

    public function __construct()
    {
        $this->apiKey = config('services.stripe.secret');
        $this->webhookSecret = config('services.stripe.webhook_secret');
    }

    public function charge(array $data): array
    {
        try {
            Log::info('Stripe: Processing charge', ['amount' => $data['amount']]);

            // Initialize Stripe
            \Stripe\Stripe::setApiKey($this->apiKey);

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $data['amount'] * 100, // Convert to cents
                'currency' => $data['currency'] ?? 'usd',
                'payment_method' => $data['payment_method_id'],
                'confirmation_method' => 'manual',
                'confirm' => true,
                'return_url' => $data['return_url'] ?? config('app.url') . '/payment/callback',
                'metadata' => $data['metadata'] ?? [],
            ]);

            Log::info('Stripe: Charge successful', ['transaction_id' => $paymentIntent->id]);

            return [
                'success' => true,
                'transaction_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'usd',
            ];
        } catch (\Stripe\Exception\CardException $e) {
            Log::error('Stripe: Card error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getStripeCode(),
            ];
        } catch (Exception $e) {
            Log::error('Stripe: Charge failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        try {
            \Stripe\Stripe::setApiKey($this->apiKey);

            $refund = \Stripe\Refund::create([
                'payment_intent' => $transactionId,
                'amount' => $amount * 100,
            ]);

            Log::info('Stripe: Refund successful', ['refund_id' => $refund->id]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => $refund->status,
            ];
        } catch (Exception $e) {
            Log::error('Stripe: Refund failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function createSubscription(array $data): array
    {
        try {
            \Stripe\Stripe::setApiKey($this->apiKey);

            // Create or get customer
            $customer = \Stripe\Customer::create([
                'email' => $data['email'],
                'payment_method' => $data['payment_method_id'],
                'invoice_settings' => [
                    'default_payment_method' => $data['payment_method_id'],
                ],
            ]);

            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->id,
                'items' => [
                    ['price' => $data['price_id']],
                ],
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            Log::info('Stripe: Subscription created', ['subscription_id' => $subscription->id]);

            return [
                'success' => true,
                'subscription_id' => $subscription->id,
                'customer_id' => $customer->id,
                'status' => $subscription->status,
            ];
        } catch (Exception $e) {
            Log::error('Stripe: Subscription creation failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function cancelSubscription(string $subscriptionId): array
    {
        try {
            \Stripe\Stripe::setApiKey($this->apiKey);

            $subscription = \Stripe\Subscription::retrieve($subscriptionId);
            $subscription->cancel();

            Log::info('Stripe: Subscription cancelled', ['subscription_id' => $subscriptionId]);

            return [
                'success' => true,
                'subscription_id' => $subscriptionId,
                'status' => 'cancelled',
            ];
        } catch (Exception $e) {
            Log::error('Stripe: Subscription cancellation failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getPaymentDetails(string $transactionId): array
    {
        try {
            \Stripe\Stripe::setApiKey($this->apiKey);

            $paymentIntent = \Stripe\PaymentIntent::retrieve($transactionId);

            return [
                'success' => true,
                'transaction_id' => $paymentIntent->id,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status,
                'created' => $paymentIntent->created,
            ];
        } catch (Exception $e) {
            Log::error('Stripe: Failed to get payment details', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function validateWebhook(string $payload, string $signature): bool
    {
        try {
            \Stripe\Webhook::constructEvent($payload, $signature, $this->webhookSecret);
            return true;
        } catch (Exception $e) {
            Log::error('Stripe: Webhook validation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getName(): string
    {
        return 'stripe';
    }
}
```

### PayPal Gateway Implementation

```php
<?php

namespace App\Services\Payment;

use App\Contracts\Services\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PayPalGateway implements PaymentGatewayInterface
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $baseUrl;
    protected ?string $accessToken = null;

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->baseUrl = config('services.paypal.sandbox')
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    protected function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->failed()) {
            throw new Exception('Failed to get PayPal access token');
        }

        $this->accessToken = $response->json('access_token');
        return $this->accessToken;
    }

    public function charge(array $data): array
    {
        try {
            Log::info('PayPal: Processing charge', ['amount' => $data['amount']]);

            $response = Http::withToken($this->getAccessToken())
                ->post("{$this->baseUrl}/v2/checkout/orders", [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [
                        [
                            'amount' => [
                                'currency_code' => $data['currency'] ?? 'USD',
                                'value' => number_format($data['amount'], 2, '.', ''),
                            ],
                            'description' => $data['description'] ?? 'Payment',
                        ],
                    ],
                    'application_context' => [
                        'return_url' => $data['return_url'] ?? config('app.url') . '/payment/success',
                        'cancel_url' => $data['cancel_url'] ?? config('app.url') . '/payment/cancel',
                    ],
                ]);

            if ($response->failed()) {
                throw new Exception($response->json('message') ?? 'PayPal charge failed');
            }

            $order = $response->json();

            Log::info('PayPal: Order created', ['order_id' => $order['id']]);

            return [
                'success' => true,
                'transaction_id' => $order['id'],
                'status' => $order['status'],
                'approval_url' => collect($order['links'])->firstWhere('rel', 'approve')['href'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('PayPal: Charge failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        try {
            // First get the capture ID from the order
            $orderResponse = Http::withToken($this->getAccessToken())
                ->get("{$this->baseUrl}/v2/checkout/orders/{$transactionId}");

            if ($orderResponse->failed()) {
                throw new Exception('Failed to get order details');
            }

            $captureId = $orderResponse->json('purchase_units.0.payments.captures.0.id');

            $response = Http::withToken($this->getAccessToken())
                ->post("{$this->baseUrl}/v2/payments/captures/{$captureId}/refund", [
                    'amount' => [
                        'value' => number_format($amount, 2, '.', ''),
                        'currency_code' => 'USD',
                    ],
                ]);

            if ($response->failed()) {
                throw new Exception($response->json('message') ?? 'Refund failed');
            }

            $refund = $response->json();

            Log::info('PayPal: Refund successful', ['refund_id' => $refund['id']]);

            return [
                'success' => true,
                'refund_id' => $refund['id'],
                'status' => $refund['status'],
            ];
        } catch (Exception $e) {
            Log::error('PayPal: Refund failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function createSubscription(array $data): array
    {
        try {
            $response = Http::withToken($this->getAccessToken())
                ->post("{$this->baseUrl}/v1/billing/subscriptions", [
                    'plan_id' => $data['plan_id'],
                    'subscriber' => [
                        'email_address' => $data['email'],
                    ],
                    'application_context' => [
                        'return_url' => $data['return_url'] ?? config('app.url') . '/subscription/success',
                        'cancel_url' => $data['cancel_url'] ?? config('app.url') . '/subscription/cancel',
                    ],
                ]);

            if ($response->failed()) {
                throw new Exception($response->json('message') ?? 'Subscription creation failed');
            }

            $subscription = $response->json();

            Log::info('PayPal: Subscription created', ['subscription_id' => $subscription['id']]);

            return [
                'success' => true,
                'subscription_id' => $subscription['id'],
                'status' => $subscription['status'],
                'approval_url' => collect($subscription['links'])->firstWhere('rel', 'approve')['href'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('PayPal: Subscription creation failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function cancelSubscription(string $subscriptionId): array
    {
        try {
            $response = Http::withToken($this->getAccessToken())
                ->post("{$this->baseUrl}/v1/billing/subscriptions/{$subscriptionId}/cancel", [
                    'reason' => 'Customer requested cancellation',
                ]);

            if ($response->failed()) {
                throw new Exception($response->json('message') ?? 'Cancellation failed');
            }

            Log::info('PayPal: Subscription cancelled', ['subscription_id' => $subscriptionId]);

            return [
                'success' => true,
                'subscription_id' => $subscriptionId,
                'status' => 'cancelled',
            ];
        } catch (Exception $e) {
            Log::error('PayPal: Subscription cancellation failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getPaymentDetails(string $transactionId): array
    {
        try {
            $response = Http::withToken($this->getAccessToken())
                ->get("{$this->baseUrl}/v2/checkout/orders/{$transactionId}");

            if ($response->failed()) {
                throw new Exception('Failed to get payment details');
            }

            $order = $response->json();

            return [
                'success' => true,
                'transaction_id' => $order['id'],
                'amount' => $order['purchase_units'][0]['amount']['value'],
                'currency' => $order['purchase_units'][0]['amount']['currency_code'],
                'status' => $order['status'],
            ];
        } catch (Exception $e) {
            Log::error('PayPal: Failed to get payment details', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function validateWebhook(string $payload, string $signature): bool
    {
        try {
            $webhookId = config('services.paypal.webhook_id');

            $response = Http::withToken($this->getAccessToken())
                ->post("{$this->baseUrl}/v1/notifications/verify-webhook-signature", [
                    'webhook_id' => $webhookId,
                    'webhook_event' => json_decode($payload, true),
                    'cert_url' => request()->header('PAYPAL-CERT-URL'),
                    'auth_algo' => request()->header('PAYPAL-AUTH-ALGO'),
                    'transmission_id' => request()->header('PAYPAL-TRANSMISSION-ID'),
                    'transmission_sig' => request()->header('PAYPAL-TRANSMISSION-SIG'),
                    'transmission_time' => request()->header('PAYPAL-TRANSMISSION-TIME'),
                ]);

            return $response->json('verification_status') === 'SUCCESS';
        } catch (Exception $e) {
            Log::error('PayPal: Webhook validation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getName(): string
    {
        return 'paypal';
    }
}
```

### Payment Context (Strategy Context)

```php
<?php

namespace App\Services\Payment;

use App\Contracts\Services\PaymentGatewayInterface;
use Illuminate\Contracts\Container\Container;
use Exception;

class PaymentContext
{
    protected Container $container;
    protected PaymentGatewayInterface $gateway;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->setGateway(config('payment.default', 'stripe'));
    }

    /**
     * Set the payment gateway
     */
    public function setGateway(string $gateway): self
    {
        $this->gateway = $this->container->make("payment.{$gateway}");
        return $this;
    }

    /**
     * Get current gateway
     */
    public function getGateway(): PaymentGatewayInterface
    {
        return $this->gateway;
    }

    /**
     * Process a payment
     */
    public function processPayment(array $data): array
    {
        return $this->gateway->charge($data);
    }

    /**
     * Process a refund
     */
    public function processRefund(string $transactionId, float $amount): array
    {
        return $this->gateway->refund($transactionId, $amount);
    }

    /**
     * Create a subscription
     */
    public function createSubscription(array $data): array
    {
        return $this->gateway->createSubscription($data);
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(string $subscriptionId): array
    {
        return $this->gateway->cancelSubscription($subscriptionId);
    }

    /**
     * Get payment details
     */
    public function getPaymentDetails(string $transactionId): array
    {
        return $this->gateway->getPaymentDetails($transactionId);
    }

    /**
     * Validate webhook
     */
    public function validateWebhook(string $payload, string $signature): bool
    {
        return $this->gateway->validateWebhook($payload, $signature);
    }
}
```

---

## Dependency Injection

### Controller Example

```php
<?php

namespace App\Http\Controllers;

use App\Services\PageService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    public function __construct(
        protected PageService $pageService,
        protected SubscriptionService $subscriptionService
    ) {}

    public function index(): JsonResponse
    {
        $pages = $this->pageService->paginate(15);
        return response()->json($pages);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $page = $this->pageService->createPage($validated);

        return response()->json($page, 201);
    }

    public function show(int $id): JsonResponse
    {
        $page = $this->pageService->findOrFail($id);
        return response()->json($page);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ]);

        $this->pageService->update($id, $validated);
        $page = $this->pageService->find($id);

        return response()->json($page);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->pageService->delete($id);
        return response()->json(null, 204);
    }

    public function publish(int $id): JsonResponse
    {
        $this->pageService->publish($id);
        $page = $this->pageService->find($id);

        return response()->json($page);
    }
}
```

### Form Request with Injection

```php
<?php

namespace App\Http\Requests;

use App\Services\PageService;
use Illuminate\Foundation\Http\FormRequest;

class StorePageRequest extends FormRequest
{
    public function __construct(
        protected PageService $pageService
    ) {
        parent::__construct();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
        ];
    }
}
```

---

## Exception Handling

### Custom Exceptions

```php
<?php

namespace App\Exceptions;

use Exception;

class PaymentException extends Exception
{
    protected string $gateway;
    protected ?string $transactionId;

    public function __construct(
        string $message,
        string $gateway,
        ?string $transactionId = null,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->gateway = $gateway;
        $this->transactionId = $transactionId;
    }

    public function getGateway(): string
    {
        return $this->gateway;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }
}
```

```php
<?php

namespace App\Exceptions;

use Exception;

class SubscriptionException extends Exception
{
    protected ?int $userId;
    protected ?string $plan;

    public function __construct(
        string $message,
        ?int $userId = null,
        ?string $plan = null,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->userId = $userId;
        $this->plan = $plan;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getPlan(): ?string
    {
        return $this->plan;
    }
}
```

```php
<?php

namespace App\Exceptions;

use Exception;

class PageNotFoundException extends Exception
{
    protected ?string $slug;

    public function __construct(
        string $message = 'Page not found',
        ?string $slug = null,
        int $code = 404,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->slug = $slug;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }
}
```

### Exception Handler

```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     */
    protected $levels = [
        PaymentException::class => 'error',
        SubscriptionException::class => 'warning',
    ];

    /**
     * A list of the exception types that are not reported.
     */
    protected $dontReport = [
        PageNotFoundException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'card_number',
        'cvv',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (PaymentException $e) {
            // Send to payment monitoring service
            logger()->channel('payments')->error($e->getMessage(), [
                'gateway' => $e->getGateway(),
                'transaction_id' => $e->getTransactionId(),
            ]);
        });

        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });

        $this->renderable(function (PaymentException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Payment Error',
                    'message' => $e->getMessage(),
                    'gateway' => $e->getGateway(),
                ], 422);
            }
        });

        $this->renderable(function (SubscriptionException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Subscription Error',
                    'message' => $e->getMessage(),
                ], 422);
            }
        });

        $this->renderable(function (PageNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Not Found',
                    'message' => $e->getMessage(),
                    'slug' => $e->getSlug(),
                ], 404);
            }
        });

        $this->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Resource Not Found',
                    'message' => 'The requested resource was not found.',
                ], 404);
            }
        });

        $this->renderable(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Validation Error',
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'You must be logged in to access this resource.',
                ], 401);
            }
        });
    }
}
```

---

## Logging

### Logging Configuration

```php
<?php
// config/logging.php

return [
    'default' => env('LOG_CHANNEL', 'stack'),

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'slack'],
            'ignore_exceptions' => false,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Landing Page Builder',
            'emoji' => ':boom:',
            'level' => env('LOG_SLACK_LEVEL', 'critical'),
        ],

        'payments' => [
            'driver' => 'daily',
            'path' => storage_path('logs/payments.log'),
            'level' => 'debug',
            'days' => 30,
        ],

        'subscriptions' => [
            'driver' => 'daily',
            'path' => storage_path('logs/subscriptions.log'),
            'level' => 'debug',
            'days' => 30,
        ],

        'pages' => [
            'driver' => 'daily',
            'path' => storage_path('logs/pages.log'),
            'level' => 'info',
            'days' => 14,
        ],

        'queue' => [
            'driver' => 'daily',
            'path' => storage_path('logs/queue.log'),
            'level' => 'debug',
            'days' => 7,
        ],

        'security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => 'info',
            'days' => 90,
        ],
    ],
];
```

### Logging Service

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LoggingService
{
    /**
     * Log payment activity
     */
    public static function payment(string $level, string $message, array $context = []): void
    {
        Log::channel('payments')->log($level, $message, array_merge($context, [
            'timestamp' => now()->toIso8601String(),
        ]));
    }

    /**
     * Log subscription activity
     */
    public static function subscription(string $level, string $message, array $context = []): void
    {
        Log::channel('subscriptions')->log($level, $message, array_merge($context, [
            'timestamp' => now()->toIso8601String(),
        ]));
    }

    /**
     * Log page activity
     */
    public static function page(string $level, string $message, array $context = []): void
    {
        Log::channel('pages')->log($level, $message, array_merge($context, [
            'timestamp' => now()->toIso8601String(),
        ]));
    }

    /**
     * Log queue activity
     */
    public static function queue(string $level, string $message, array $context = []): void
    {
        Log::channel('queue')->log($level, $message, array_merge($context, [
            'timestamp' => now()->toIso8601String(),
        ]));
    }

    /**
     * Log security events
     */
    public static function security(string $level, string $message, array $context = []): void
    {
        Log::channel('security')->log($level, $message, array_merge($context, [
            'timestamp' => now()->toIso8601String(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]));
    }
}
```

---

## Queue Structure

### Queue Configuration

```php
<?php
// config/queue.php

return [
    'default' => env('QUEUE_CONNECTION', 'redis'),

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],
    ],

    'batching' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'job_batches',
    ],

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],
];
```

### Job Classes

```php
<?php

namespace App\Jobs;

use App\Models\Page;
use App\Services\LoggingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Exception;

class ProcessPageGeneration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 120;

    public function __construct(
        protected Page $page
    ) {
        $this->onQueue('pages');
    }

    public function handle(): void
    {
        LoggingService::queue('info', 'Processing page generation', [
            'page_id' => $this->page->id,
            'job_id' => $this->job->getJobId(),
        ]);

        try {
            // Process page generation logic
            $this->page->update([
                'processed_at' => now(),
                'status' => 'processed',
            ]);

            LoggingService::page('info', 'Page processed successfully', [
                'page_id' => $this->page->id,
            ]);
        } catch (Exception $e) {
            LoggingService::queue('error', 'Page generation failed', [
                'page_id' => $this->page->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(Exception $exception): void
    {
        LoggingService::queue('error', 'Page generation job failed permanently', [
            'page_id' => $this->page->id,
            'error' => $exception->getMessage(),
        ]);

        $this->page->update(['status' => 'failed']);
    }
}
```

```php
<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\LoggingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use Exception;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        protected User $user
    ) {
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        LoggingService::queue('info', 'Sending welcome email', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
        ]);

        Mail::to($this->user->email)->send(new WelcomeEmail($this->user));

        LoggingService::queue('info', 'Welcome email sent', [
            'user_id' => $this->user->id,
        ]);
    }

    public function failed(Exception $exception): void
    {
        LoggingService::queue('error', 'Failed to send welcome email', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

```php
<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Services\Payment\PaymentContext;
use App\Services\LoggingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Exception;

class ProcessPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 120;
    public int $timeout = 60;

    public function __construct(
        protected Subscription $subscription
    ) {
        $this->onQueue('payments');
    }

    public function handle(PaymentContext $paymentContext): void
    {
        LoggingService::payment('info', 'Processing payment', [
            'subscription_id' => $this->subscription->id,
            'user_id' => $this->subscription->user_id,
        ]);

        try {
            $result = $paymentContext->processPayment([
                'amount' => $this->subscription->amount,
                'currency' => 'usd',
                'payment_method_id' => $this->subscription->payment_method_id,
                'metadata' => [
                    'subscription_id' => $this->subscription->id,
                    'user_id' => $this->subscription->user_id,
                ],
            ]);

            if ($result['success']) {
                $this->subscription->update([
                    'status' => 'active',
                    'payment_id' => $result['transaction_id'],
                    'paid_at' => now(),
                ]);

                LoggingService::payment('info', 'Payment processed successfully', [
                    'subscription_id' => $this->subscription->id,
                    'transaction_id' => $result['transaction_id'],
                ]);
            } else {
                throw new Exception($result['message'] ?? 'Payment failed');
            }
        } catch (Exception $e) {
            LoggingService::payment('error', 'Payment processing failed', [
                'subscription_id' => $this->subscription->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(Exception $exception): void
    {
        LoggingService::payment('error', 'Payment job failed permanently', [
            'subscription_id' => $this->subscription->id,
            'error' => $exception->getMessage(),
        ]);

        $this->subscription->update([
            'status' => 'payment_failed',
            'failed_at' => now(),
        ]);
    }
}
```

### Queue Worker Configuration

```bash
# Supervisor configuration for queue workers
# /etc/supervisor/conf.d/landing-page-builder.conf

[program:laravel-default-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work redis --queue=default --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker-default.log

[program:laravel-payments-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work redis --queue=payments --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker-payments.log

[program:laravel-emails-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work redis --queue=emails --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker-emails.log

[program:laravel-pages-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work redis --queue=pages --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker-pages.log
```

---

## Summary

This architecture documentation provides a complete implementation of:

1. **Service-Repository Pattern** - Clean separation of business logic and data access
2. **Observer Pattern** - Automatic handling of model events with caching and logging
3. **Strategy Pattern** - Flexible payment gateway switching (Stripe/PayPal)
4. **Dependency Injection** - Proper service container usage
5. **Exception Handling** - Custom exceptions with proper reporting
6. **Logging** - Comprehensive logging with dedicated channels
7. **Queue Structure** - Async processing with proper error handling

All code is namespace-compliant and ready for copy-paste implementation in Laravel 11.
