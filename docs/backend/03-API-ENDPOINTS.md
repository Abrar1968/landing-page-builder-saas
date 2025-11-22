# API Endpoints Documentation

## Laravel 11 Backend API Reference

This document provides complete API endpoint documentation including route definitions, controllers, and Form Request validation classes.

---

## Table of Contents

1. [Route Organization](#route-organization)
2. [Authentication Routes](#authentication-routes)
3. [Page Builder API](#page-builder-api)
4. [Template API](#template-api)
5. [Media Library API](#media-library-api)
6. [Publishing API](#publishing-api)
7. [Subscription API](#subscription-api)
8. [Middleware](#middleware)

---

## Route Organization

### Route File Structure

```php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\PublishController;
use App\Http\Controllers\Api\SubscriptionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API Version Prefix
Route::prefix('v1')->group(function () {

    // Public Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->name('verification.verify');

    // Protected Routes
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {

        // Auth Routes
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/user', [AuthController::class, 'updateProfile']);
        Route::post('/user/password', [AuthController::class, 'updatePassword']);

        // Page Builder Routes
        Route::middleware(['subscription.check', 'throttle:api'])->group(function () {
            Route::apiResource('pages', PageController::class);
            Route::post('/pages/{page}/duplicate', [PageController::class, 'duplicate']);
            Route::post('/pages/{page}/auto-save', [PageController::class, 'autoSave']);
            Route::get('/pages/{page}/versions', [PageController::class, 'versions']);
            Route::post('/pages/{page}/restore/{version}', [PageController::class, 'restore']);
        });

        // Template Routes
        Route::get('/templates', [TemplateController::class, 'index']);
        Route::get('/templates/{template}', [TemplateController::class, 'show']);
        Route::middleware(['subscription.check:pro,enterprise'])->group(function () {
            Route::post('/templates', [TemplateController::class, 'store']);
            Route::put('/templates/{template}', [TemplateController::class, 'update']);
            Route::delete('/templates/{template}', [TemplateController::class, 'destroy']);
        });

        // Media Library Routes
        Route::middleware(['subscription.check', 'throttle:uploads'])->group(function () {
            Route::get('/media', [MediaController::class, 'index']);
            Route::post('/media', [MediaController::class, 'store']);
            Route::get('/media/{media}', [MediaController::class, 'show']);
            Route::put('/media/{media}', [MediaController::class, 'update']);
            Route::delete('/media/{media}', [MediaController::class, 'destroy']);
            Route::post('/media/bulk-delete', [MediaController::class, 'bulkDelete']);
        });

        // Publishing Routes
        Route::middleware(['subscription.check'])->group(function () {
            Route::post('/pages/{page}/publish', [PublishController::class, 'publish']);
            Route::post('/pages/{page}/unpublish', [PublishController::class, 'unpublish']);
            Route::get('/pages/{page}/publish-status', [PublishController::class, 'status']);
            Route::post('/pages/{page}/schedule', [PublishController::class, 'schedule']);
        });

        // Subscription Routes
        Route::get('/subscription', [SubscriptionController::class, 'index']);
        Route::get('/subscription/plans', [SubscriptionController::class, 'plans']);
        Route::post('/subscription/subscribe', [SubscriptionController::class, 'subscribe']);
        Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']);
        Route::post('/subscription/resume', [SubscriptionController::class, 'resume']);
        Route::put('/subscription/update-payment', [SubscriptionController::class, 'updatePayment']);
        Route::get('/subscription/invoices', [SubscriptionController::class, 'invoices']);
        Route::get('/subscription/usage', [SubscriptionController::class, 'usage']);
    });

    // Webhook Routes (no auth)
    Route::post('/webhooks/stripe', [SubscriptionController::class, 'handleWebhook']);
});
```

---

## Authentication Routes

### AuthController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * POST /api/v1/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful. Please verify your email.',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login user
     *
     * POST /api/v1/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // Revoke existing tokens if single session
        if ($request->single_session) {
            $user->tokens()->delete();
        }

        $token = $user->createToken('auth-token', ['*'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->load('subscription'),
            'token' => $token,
        ]);
    }

    /**
     * Logout user
     *
     * POST /api/v1/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated user
     *
     * GET /api/v1/user
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load(['subscription', 'subscription.plan']),
        ]);
    }

    /**
     * Update user profile
     *
     * PUT /api/v1/user
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update($request->validated());

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Update user password
     *
     * POST /api/v1/user/password
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password updated successfully',
        ]);
    }

    /**
     * Send password reset link
     *
     * POST /api/v1/forgot-password
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent to your email',
            ]);
        }

        return response()->json([
            'message' => 'Unable to send reset link',
        ], 400);
    }

    /**
     * Reset password
     *
     * POST /api/v1/reset-password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password reset successfully',
            ]);
        }

        return response()->json([
            'message' => 'Unable to reset password',
        ], 400);
    }

    /**
     * Verify email address
     *
     * POST /api/v1/verify-email/{id}/{hash}
     */
    public function verifyEmail(Request $request, $id, $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Invalid verification link',
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified',
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Email verified successfully',
        ]);
    }
}
```

### Auth Form Requests

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered.',
            'password.min' => 'Password must be at least 8 characters.',
        ];
    }
}
```

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'single_session' => ['boolean'],
        ];
    }
}
```

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'exists:users,email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => 'No account found with this email address.',
        ];
    }
}
```

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
```

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $this->user()->id],
            'company' => ['nullable', 'string', 'max:255'],
            'timezone' => ['nullable', 'string', 'timezone'],
        ];
    }
}
```

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_password.different' => 'New password must be different from current password.',
        ];
    }
}
```

---

## Page Builder API

### PageController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Page\StorePageRequest;
use App\Http\Requests\Page\UpdatePageRequest;
use App\Http\Requests\Page\AutoSaveRequest;
use App\Models\Page;
use App\Models\PageVersion;
use App\Services\PageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(
        protected PageService $pageService
    ) {}

    /**
     * List all pages for authenticated user
     *
     * GET /api/v1/pages
     */
    public function index(Request $request): JsonResponse
    {
        $pages = Page::where('user_id', $request->user()->id)
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->sort, function ($query, $sort) {
                $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
                $field = ltrim($sort, '-');
                $query->orderBy($field, $direction);
            }, function ($query) {
                $query->latest();
            })
            ->paginate($request->per_page ?? 15);

        return response()->json($pages);
    }

    /**
     * Create a new page
     *
     * POST /api/v1/pages
     */
    public function store(StorePageRequest $request): JsonResponse
    {
        // Check page limit based on subscription
        $pageCount = Page::where('user_id', $request->user()->id)->count();
        $limit = $request->user()->subscription?->plan->page_limit ?? 5;

        if ($pageCount >= $limit) {
            return response()->json([
                'message' => 'Page limit reached. Please upgrade your plan.',
            ], 403);
        }

        $page = Page::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'slug' => $request->slug ?? \Str::slug($request->title),
            'description' => $request->description,
            'content' => $request->content ?? [],
            'settings' => $request->settings ?? [],
            'status' => 'draft',
        ]);

        // Create initial version
        $this->pageService->createVersion($page, 'Page created');

        return response()->json([
            'message' => 'Page created successfully',
            'page' => $page,
        ], 201);
    }

    /**
     * Get a specific page
     *
     * GET /api/v1/pages/{page}
     */
    public function show(Request $request, Page $page): JsonResponse
    {
        $this->authorize('view', $page);

        return response()->json([
            'page' => $page->load(['versions' => function ($query) {
                $query->latest()->limit(10);
            }]),
        ]);
    }

    /**
     * Update a page
     *
     * PUT /api/v1/pages/{page}
     */
    public function update(UpdatePageRequest $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);

        $page->update($request->validated());

        // Create version on significant updates
        if ($request->has('content')) {
            $this->pageService->createVersion($page, $request->version_note ?? 'Content updated');
        }

        return response()->json([
            'message' => 'Page updated successfully',
            'page' => $page->fresh(),
        ]);
    }

    /**
     * Delete a page
     *
     * DELETE /api/v1/pages/{page}
     */
    public function destroy(Request $request, Page $page): JsonResponse
    {
        $this->authorize('delete', $page);

        // Unpublish if published
        if ($page->status === 'published') {
            $this->pageService->unpublish($page);
        }

        $page->delete();

        return response()->json([
            'message' => 'Page deleted successfully',
        ]);
    }

    /**
     * Duplicate a page
     *
     * POST /api/v1/pages/{page}/duplicate
     */
    public function duplicate(Request $request, Page $page): JsonResponse
    {
        $this->authorize('view', $page);

        // Check page limit
        $pageCount = Page::where('user_id', $request->user()->id)->count();
        $limit = $request->user()->subscription?->plan->page_limit ?? 5;

        if ($pageCount >= $limit) {
            return response()->json([
                'message' => 'Page limit reached. Please upgrade your plan.',
            ], 403);
        }

        $newPage = $page->replicate();
        $newPage->title = $request->title ?? $page->title . ' (Copy)';
        $newPage->slug = \Str::slug($newPage->title) . '-' . \Str::random(6);
        $newPage->status = 'draft';
        $newPage->published_at = null;
        $newPage->save();

        $this->pageService->createVersion($newPage, 'Duplicated from ' . $page->title);

        return response()->json([
            'message' => 'Page duplicated successfully',
            'page' => $newPage,
        ], 201);
    }

    /**
     * Auto-save page content
     *
     * POST /api/v1/pages/{page}/auto-save
     */
    public function autoSave(AutoSaveRequest $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);

        $page->update([
            'content' => $request->content,
            'auto_saved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Auto-saved successfully',
            'auto_saved_at' => $page->auto_saved_at,
        ]);
    }

    /**
     * Get page versions
     *
     * GET /api/v1/pages/{page}/versions
     */
    public function versions(Request $request, Page $page): JsonResponse
    {
        $this->authorize('view', $page);

        $versions = PageVersion::where('page_id', $page->id)
            ->latest()
            ->paginate(20);

        return response()->json($versions);
    }

    /**
     * Restore page to a specific version
     *
     * POST /api/v1/pages/{page}/restore/{version}
     */
    public function restore(Request $request, Page $page, PageVersion $version): JsonResponse
    {
        $this->authorize('update', $page);

        if ($version->page_id !== $page->id) {
            return response()->json([
                'message' => 'Version does not belong to this page',
            ], 400);
        }

        $page->update([
            'content' => $version->content,
            'settings' => $version->settings,
        ]);

        $this->pageService->createVersion($page, 'Restored to version ' . $version->id);

        return response()->json([
            'message' => 'Page restored successfully',
            'page' => $page->fresh(),
        ]);
    }
}
```

### Page Form Requests

```php
<?php

namespace App\Http\Requests\Page;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('pages')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id);
                }),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'array'],
            'content.*.type' => ['required_with:content', 'string'],
            'content.*.data' => ['required_with:content', 'array'],
            'settings' => ['nullable', 'array'],
            'settings.seo' => ['nullable', 'array'],
            'settings.seo.title' => ['nullable', 'string', 'max:60'],
            'settings.seo.description' => ['nullable', 'string', 'max:160'],
            'settings.custom_css' => ['nullable', 'string', 'max:50000'],
            'settings.custom_js' => ['nullable', 'string', 'max:50000'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug must contain only lowercase letters, numbers, and hyphens.',
            'slug.unique' => 'This slug is already in use.',
        ];
    }
}
```

```php
<?php

namespace App\Http\Requests\Page;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('pages')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id);
                })->ignore($this->page->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'content' => ['sometimes', 'array'],
            'content.*.type' => ['required_with:content', 'string'],
            'content.*.data' => ['required_with:content', 'array'],
            'settings' => ['sometimes', 'array'],
            'version_note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
```

```php
<?php

namespace App\Http\Requests\Page;

use Illuminate\Foundation\Http\FormRequest;

class AutoSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'array'],
        ];
    }
}
```

---

## Template API

### TemplateController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Template\StoreTemplateRequest;
use App\Http\Requests\Template\UpdateTemplateRequest;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * List all templates
     *
     * GET /api/v1/templates
     */
    public function index(Request $request): JsonResponse
    {
        $templates = Template::query()
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->type === 'user', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->when($request->type === 'system', function ($query) {
                $query->whereNull('user_id');
            })
            ->when(!$request->type, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->whereNull('user_id')
                      ->orWhere('user_id', $request->user()->id);
                });
            })
            ->orderBy('name')
            ->paginate($request->per_page ?? 20);

        return response()->json($templates);
    }

    /**
     * Get a specific template
     *
     * GET /api/v1/templates/{template}
     */
    public function show(Template $template): JsonResponse
    {
        return response()->json([
            'template' => $template,
        ]);
    }

    /**
     * Create a new template (Pro/Enterprise only)
     *
     * POST /api/v1/templates
     */
    public function store(StoreTemplateRequest $request): JsonResponse
    {
        $template = Template::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'thumbnail' => $request->thumbnail,
            'content' => $request->content,
            'settings' => $request->settings ?? [],
        ]);

        return response()->json([
            'message' => 'Template created successfully',
            'template' => $template,
        ], 201);
    }

    /**
     * Update a template
     *
     * PUT /api/v1/templates/{template}
     */
    public function update(UpdateTemplateRequest $request, Template $template): JsonResponse
    {
        // Only owner can update their templates
        if ($template->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to update this template',
            ], 403);
        }

        $template->update($request->validated());

        return response()->json([
            'message' => 'Template updated successfully',
            'template' => $template->fresh(),
        ]);
    }

    /**
     * Delete a template
     *
     * DELETE /api/v1/templates/{template}
     */
    public function destroy(Request $request, Template $template): JsonResponse
    {
        // Only owner can delete their templates
        if ($template->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to delete this template',
            ], 403);
        }

        $template->delete();

        return response()->json([
            'message' => 'Template deleted successfully',
        ]);
    }
}
```

### Template Form Requests

```php
<?php

namespace App\Http\Requests\Template;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'category' => ['required', 'string', 'in:landing,portfolio,business,ecommerce,blog,other'],
            'thumbnail' => ['nullable', 'string', 'url'],
            'content' => ['required', 'array'],
            'content.*.type' => ['required', 'string'],
            'content.*.data' => ['required', 'array'],
            'settings' => ['nullable', 'array'],
        ];
    }
}
```

```php
<?php

namespace App\Http\Requests\Template;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'category' => ['sometimes', 'string', 'in:landing,portfolio,business,ecommerce,blog,other'],
            'thumbnail' => ['nullable', 'string', 'url'],
            'content' => ['sometimes', 'array'],
            'settings' => ['sometimes', 'array'],
        ];
    }
}
```

---

## Media Library API

### MediaController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaRequest;
use App\Http\Requests\Media\UpdateMediaRequest;
use App\Http\Requests\Media\BulkDeleteMediaRequest;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

    /**
     * List all media files for authenticated user
     *
     * GET /api/v1/media
     */
    public function index(Request $request): JsonResponse
    {
        $media = Media::where('user_id', $request->user()->id)
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->folder, function ($query, $folder) {
                $query->where('folder', $folder);
            })
            ->latest()
            ->paginate($request->per_page ?? 30);

        return response()->json($media);
    }

    /**
     * Upload new media file
     *
     * POST /api/v1/media
     */
    public function store(StoreMediaRequest $request): JsonResponse
    {
        // Check storage limit
        $usedStorage = Media::where('user_id', $request->user()->id)->sum('size');
        $storageLimit = $request->user()->subscription?->plan->storage_limit ?? 100 * 1024 * 1024; // 100MB default
        $fileSize = $request->file('file')->getSize();

        if (($usedStorage + $fileSize) > $storageLimit) {
            return response()->json([
                'message' => 'Storage limit exceeded. Please upgrade your plan.',
                'used' => $usedStorage,
                'limit' => $storageLimit,
            ], 403);
        }

        $media = $this->mediaService->upload(
            $request->file('file'),
            $request->user()->id,
            $request->folder ?? 'uploads'
        );

        return response()->json([
            'message' => 'File uploaded successfully',
            'media' => $media,
        ], 201);
    }

    /**
     * Get a specific media file
     *
     * GET /api/v1/media/{media}
     */
    public function show(Request $request, Media $media): JsonResponse
    {
        if ($media->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'media' => $media,
        ]);
    }

    /**
     * Update media metadata
     *
     * PUT /api/v1/media/{media}
     */
    public function update(UpdateMediaRequest $request, Media $media): JsonResponse
    {
        if ($media->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $media->update($request->validated());

        return response()->json([
            'message' => 'Media updated successfully',
            'media' => $media->fresh(),
        ]);
    }

    /**
     * Delete a media file
     *
     * DELETE /api/v1/media/{media}
     */
    public function destroy(Request $request, Media $media): JsonResponse
    {
        if ($media->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->mediaService->delete($media);

        return response()->json([
            'message' => 'Media deleted successfully',
        ]);
    }

    /**
     * Bulk delete media files
     *
     * POST /api/v1/media/bulk-delete
     */
    public function bulkDelete(BulkDeleteMediaRequest $request): JsonResponse
    {
        $deleted = 0;
        $errors = [];

        foreach ($request->ids as $id) {
            $media = Media::find($id);

            if (!$media) {
                $errors[] = "Media {$id} not found";
                continue;
            }

            if ($media->user_id !== $request->user()->id) {
                $errors[] = "Unauthorized to delete media {$id}";
                continue;
            }

            $this->mediaService->delete($media);
            $deleted++;
        }

        return response()->json([
            'message' => "{$deleted} files deleted successfully",
            'deleted' => $deleted,
            'errors' => $errors,
        ]);
    }
}
```

### Media Form Requests

```php
<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,gif,webp,svg,mp4,webm,pdf',
            ],
            'folder' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'File size must not exceed 10MB.',
            'file.mimes' => 'File type not supported.',
        ];
    }
}
```

```php
<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'folder' => ['nullable', 'string', 'max:100'],
        ];
    }
}
```

```php
<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:media,id'],
        ];
    }
}
```

---

## Publishing API

### PublishController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Publish\SchedulePublishRequest;
use App\Models\Page;
use App\Services\PublishService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublishController extends Controller
{
    public function __construct(
        protected PublishService $publishService
    ) {}

    /**
     * Publish a page
     *
     * POST /api/v1/pages/{page}/publish
     */
    public function publish(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);

        if ($page->status === 'published') {
            return response()->json([
                'message' => 'Page is already published',
            ], 400);
        }

        $result = $this->publishService->publish($page);

        return response()->json([
            'message' => 'Page published successfully',
            'page' => $page->fresh(),
            'url' => $result['url'],
        ]);
    }

    /**
     * Unpublish a page
     *
     * POST /api/v1/pages/{page}/unpublish
     */
    public function unpublish(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);

        if ($page->status !== 'published') {
            return response()->json([
                'message' => 'Page is not published',
            ], 400);
        }

        $this->publishService->unpublish($page);

        return response()->json([
            'message' => 'Page unpublished successfully',
            'page' => $page->fresh(),
        ]);
    }

    /**
     * Get publish status
     *
     * GET /api/v1/pages/{page}/publish-status
     */
    public function status(Request $request, Page $page): JsonResponse
    {
        $this->authorize('view', $page);

        return response()->json([
            'status' => $page->status,
            'published_at' => $page->published_at,
            'scheduled_at' => $page->scheduled_at,
            'url' => $page->status === 'published' ? $page->public_url : null,
            'ssl_enabled' => $page->ssl_enabled ?? false,
            'custom_domain' => $page->custom_domain,
        ]);
    }

    /**
     * Schedule page publication
     *
     * POST /api/v1/pages/{page}/schedule
     */
    public function schedule(SchedulePublishRequest $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);

        $page->update([
            'status' => 'scheduled',
            'scheduled_at' => $request->publish_at,
        ]);

        // Dispatch job to publish at scheduled time
        $this->publishService->schedule($page, $request->publish_at);

        return response()->json([
            'message' => 'Page scheduled for publication',
            'page' => $page->fresh(),
            'scheduled_at' => $request->publish_at,
        ]);
    }
}
```

### Publish Form Requests

```php
<?php

namespace App\Http\Requests\Publish;

use Illuminate\Foundation\Http\FormRequest;

class SchedulePublishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'publish_at' => ['required', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'publish_at.after' => 'Scheduled time must be in the future.',
        ];
    }
}
```

---

## Subscription API

### SubscriptionController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\SubscribeRequest;
use App\Http\Requests\Subscription\UpdatePaymentRequest;
use App\Models\Plan;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscriptionController extends Controller
{
    public function __construct(
        protected StripeService $stripeService
    ) {}

    /**
     * Get current subscription
     *
     * GET /api/v1/subscription
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription('default');

        return response()->json([
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'plan' => $user->subscription?->plan,
                'status' => $subscription->stripe_status,
                'trial_ends_at' => $subscription->trial_ends_at,
                'ends_at' => $subscription->ends_at,
                'on_trial' => $subscription->onTrial(),
                'canceled' => $subscription->canceled(),
                'on_grace_period' => $subscription->onGracePeriod(),
            ] : null,
        ]);
    }

    /**
     * List available plans
     *
     * GET /api/v1/subscription/plans
     */
    public function plans(): JsonResponse
    {
        $plans = Plan::where('active', true)
            ->orderBy('price')
            ->get();

        return response()->json([
            'plans' => $plans,
        ]);
    }

    /**
     * Subscribe to a plan
     *
     * POST /api/v1/subscription/subscribe
     */
    public function subscribe(SubscribeRequest $request): JsonResponse
    {
        $user = $request->user();
        $plan = Plan::findOrFail($request->plan_id);

        try {
            if ($user->subscribed('default')) {
                // Swap plan
                $user->subscription('default')->swap($plan->stripe_price_id);
            } else {
                // New subscription
                $subscription = $user->newSubscription('default', $plan->stripe_price_id);

                if ($plan->trial_days > 0 && !$user->hasEverSubscribed()) {
                    $subscription->trialDays($plan->trial_days);
                }

                $subscription->create($request->payment_method);
            }

            return response()->json([
                'message' => 'Subscription successful',
                'subscription' => $user->subscription('default'),
            ]);
        } catch (IncompletePayment $e) {
            return response()->json([
                'message' => 'Payment requires additional action',
                'payment_intent' => $e->payment->asStripePaymentIntent()->client_secret,
            ], 402);
        }
    }

    /**
     * Cancel subscription
     *
     * POST /api/v1/subscription/cancel
     */
    public function cancel(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->subscribed('default')) {
            return response()->json([
                'message' => 'No active subscription',
            ], 400);
        }

        $user->subscription('default')->cancel();

        return response()->json([
            'message' => 'Subscription cancelled. Access continues until end of billing period.',
            'ends_at' => $user->subscription('default')->ends_at,
        ]);
    }

    /**
     * Resume cancelled subscription
     *
     * POST /api/v1/subscription/resume
     */
    public function resume(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription('default');

        if (!$subscription || !$subscription->onGracePeriod()) {
            return response()->json([
                'message' => 'No subscription to resume',
            ], 400);
        }

        $subscription->resume();

        return response()->json([
            'message' => 'Subscription resumed successfully',
            'subscription' => $subscription->fresh(),
        ]);
    }

    /**
     * Update payment method
     *
     * PUT /api/v1/subscription/update-payment
     */
    public function updatePayment(UpdatePaymentRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->updateDefaultPaymentMethod($request->payment_method);

        return response()->json([
            'message' => 'Payment method updated successfully',
        ]);
    }

    /**
     * Get invoices
     *
     * GET /api/v1/subscription/invoices
     */
    public function invoices(Request $request): JsonResponse
    {
        $user = $request->user();

        $invoices = $user->invoices()->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'date' => $invoice->date()->toDateString(),
                'total' => $invoice->total(),
                'status' => $invoice->status,
                'pdf_url' => $invoice->invoicePdf(),
            ];
        });

        return response()->json([
            'invoices' => $invoices,
        ]);
    }

    /**
     * Get usage statistics
     *
     * GET /api/v1/subscription/usage
     */
    public function usage(Request $request): JsonResponse
    {
        $user = $request->user();
        $plan = $user->subscription?->plan;

        return response()->json([
            'usage' => [
                'pages' => [
                    'used' => $user->pages()->count(),
                    'limit' => $plan->page_limit ?? 5,
                ],
                'storage' => [
                    'used' => $user->media()->sum('size'),
                    'limit' => $plan->storage_limit ?? 100 * 1024 * 1024,
                ],
                'bandwidth' => [
                    'used' => $user->bandwidth_used ?? 0,
                    'limit' => $plan->bandwidth_limit ?? 1024 * 1024 * 1024,
                ],
            ],
        ]);
    }

    /**
     * Handle Stripe webhooks
     *
     * POST /api/v1/webhooks/stripe
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = $this->stripeService->constructWebhookEvent($payload, $signature);

            $this->stripeService->handleEvent($event);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
```

### Subscription Form Requests

```php
<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'payment_method' => ['required_without:using_existing_method', 'string'],
            'using_existing_method' => ['boolean'],
        ];
    }
}
```

```php
<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'string'],
        ];
    }
}
```

---

## Middleware

### Authentication Middleware

```php
<?php

// Laravel Sanctum is used for API authentication
// Configuration in config/sanctum.php

// In bootstrap/app.php (Laravel 11)
return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
    })
    ->create();
```

### Subscription Check Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$plans  Allowed plan slugs (empty means any active subscription)
     */
    public function handle(Request $request, Closure $next, string ...$plans): Response
    {
        $user = $request->user();

        // Check if user has any active subscription
        if (!$user->subscribed('default') && !$user->onTrial()) {
            return response()->json([
                'message' => 'Active subscription required',
                'error' => 'subscription_required',
            ], 403);
        }

        // Check for specific plan requirement
        if (!empty($plans)) {
            $userPlan = $user->subscription?->plan?->slug;

            if (!in_array($userPlan, $plans)) {
                return response()->json([
                    'message' => 'This feature requires a ' . implode(' or ', $plans) . ' plan',
                    'error' => 'plan_upgrade_required',
                    'required_plans' => $plans,
                    'current_plan' => $userPlan,
                ], 403);
            }
        }

        return $next($request);
    }
}
```

### Rate Limiting Middleware

```php
<?php

// In bootstrap/app.php (Laravel 11)

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        // Default API rate limiting
        RateLimiter::for('api', function (Request $request) {
            $user = $request->user();

            // Different limits based on subscription
            $limit = match ($user?->subscription?->plan?->slug) {
                'enterprise' => 1000,
                'pro' => 300,
                'starter' => 100,
                default => 60,
            };

            return Limit::perMinute($limit)->by($user?->id ?: $request->ip());
        });

        // Upload-specific rate limiting
        RateLimiter::for('uploads', function (Request $request) {
            $user = $request->user();

            $limit = match ($user?->subscription?->plan?->slug) {
                'enterprise' => 100,
                'pro' => 50,
                'starter' => 20,
                default => 10,
            };

            return Limit::perMinute($limit)->by($user?->id ?: $request->ip());
        });

        // Auto-save rate limiting (more permissive)
        RateLimiter::for('autosave', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });
    })
    ->create();
```

### Register Middleware in Application

```php
<?php

// bootstrap/app.php (Laravel 11)

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckSubscription;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'subscription.check' => CheckSubscription::class,
        ]);

        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
```

---

## API Response Format

### Success Response

```json
{
    "message": "Operation successful",
    "data": { }
}
```

### Error Response

```json
{
    "message": "Error description",
    "error": "error_code",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

### Pagination Response

```json
{
    "data": [],
    "links": {
        "first": "http://api.example.com/resource?page=1",
        "last": "http://api.example.com/resource?page=10",
        "prev": null,
        "next": "http://api.example.com/resource?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 10,
        "per_page": 15,
        "to": 15,
        "total": 150
    }
}
```

---

## HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 402 | Payment Required |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |
