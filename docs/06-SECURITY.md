# Security Implementation Guide

## Table of Contents
1. [Authentication Security](#authentication-security)
2. [Authorization (Policies)](#authorization-policies)
3. [CSRF Protection](#csrf-protection)
4. [XSS Prevention](#xss-prevention)
5. [SQL Injection Prevention](#sql-injection-prevention)
6. [File Upload Security](#file-upload-security)
7. [API Security & Rate Limiting](#api-security--rate-limiting)
8. [Security Headers Middleware](#security-headers-middleware)
9. [Audit Logging](#audit-logging)

---

## Authentication Security

### Multi-Factor Authentication (MFA)

```php
// app/Services/TwoFactorAuthService.php
<?php

namespace App\Services;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class TwoFactorAuthService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function getQRCodeUrl(User $user, string $secretKey): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secretKey
        );
    }

    public function verify(User $user, string $code): bool
    {
        $isValid = $this->google2fa->verifyKey(
            $user->two_factor_secret,
            $code,
            2 // Window for time drift
        );

        if ($isValid) {
            $this->invalidateUsedCode($user, $code);
        }

        return $isValid;
    }

    protected function invalidateUsedCode(User $user, string $code): void
    {
        $cacheKey = "2fa_used_{$user->id}_{$code}";
        Cache::put($cacheKey, true, now()->addMinutes(2));
    }

    public function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    public function hashBackupCodes(array $codes): array
    {
        return array_map(fn($code) => Hash::make($code), $codes);
    }
}
```

### Secure Password Policy

```php
// app/Rules/SecurePassword.php
<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SecurePassword implements Rule
{
    protected string $message = '';

    public function passes($attribute, $value): bool
    {
        // Minimum length
        if (strlen($value) < 12) {
            $this->message = 'Password must be at least 12 characters.';
            return false;
        }

        // Complexity requirements
        if (!preg_match('/[A-Z]/', $value)) {
            $this->message = 'Password must contain at least one uppercase letter.';
            return false;
        }

        if (!preg_match('/[a-z]/', $value)) {
            $this->message = 'Password must contain at least one lowercase letter.';
            return false;
        }

        if (!preg_match('/[0-9]/', $value)) {
            $this->message = 'Password must contain at least one number.';
            return false;
        }

        if (!preg_match('/[^A-Za-z0-9]/', $value)) {
            $this->message = 'Password must contain at least one special character.';
            return false;
        }

        // Check against breached passwords (Have I Been Pwned API)
        if ($this->isBreachedPassword($value)) {
            $this->message = 'This password has been exposed in a data breach. Please choose a different password.';
            return false;
        }

        return true;
    }

    protected function isBreachedPassword(string $password): bool
    {
        $hash = strtoupper(sha1($password));
        $prefix = substr($hash, 0, 5);
        $suffix = substr($hash, 5);

        $cacheKey = "pwned_prefix_{$prefix}";

        $response = Cache::remember($cacheKey, 86400, function () use ($prefix) {
            return Http::timeout(5)
                ->get("https://api.pwnedpasswords.com/range/{$prefix}")
                ->body();
        });

        return str_contains($response, $suffix);
    }

    public function message(): string
    {
        return $this->message;
    }
}
```

### Session Security Configuration

```php
// config/session.php
<?php

return [
    'driver' => env('SESSION_DRIVER', 'redis'),
    'lifetime' => env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => true,
    'files' => storage_path('framework/sessions'),
    'connection' => env('SESSION_CONNECTION'),
    'table' => 'sessions',
    'store' => env('SESSION_STORE'),
    'lottery' => [2, 100],
    'cookie' => env('SESSION_COOKIE', 'landing_page_session'),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN'),
    'secure' => env('SESSION_SECURE_COOKIE', true),
    'http_only' => true,
    'same_site' => 'lax',
];
```

### Login Throttling

```php
// app/Http/Controllers/Auth/LoginController.php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $this->checkTooManyFailedAttempts($request);

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            $this->incrementLoginAttempts($request);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $this->clearLoginAttempts($request);

        $request->session()->regenerate();

        $user = Auth::user();

        // Check if 2FA is enabled
        if ($user->two_factor_enabled) {
            $request->session()->put('2fa:user:id', $user->id);
            Auth::logout();

            return response()->json([
                'requires_2fa' => true,
                'redirect' => route('2fa.challenge'),
            ]);
        }

        $this->logSuccessfulLogin($user, $request);

        return response()->json([
            'redirect' => route('dashboard'),
        ]);
    }

    protected function checkTooManyFailedAttempts(Request $request): void
    {
        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => __('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }
    }

    protected function incrementLoginAttempts(Request $request): void
    {
        RateLimiter::hit($this->throttleKey($request), 900); // 15 minutes
    }

    protected function clearLoginAttempts(Request $request): void
    {
        RateLimiter::clear($this->throttleKey($request));
    }

    protected function throttleKey(Request $request): string
    {
        return strtolower($request->input('email')) . '|' . $request->ip();
    }

    protected function logSuccessfulLogin(User $user, Request $request): void
    {
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        activity()
            ->causedBy($user)
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log('User logged in');
    }
}
```

---

## Authorization (Policies)

### Landing Page Policy

```php
// app/Policies/LandingPagePolicy.php
<?php

namespace App\Policies;

use App\Models\LandingPage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LandingPagePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LandingPage $landingPage): bool
    {
        // Owner can always view
        if ($user->id === $landingPage->user_id) {
            return true;
        }

        // Team members can view
        if ($this->isTeamMember($user, $landingPage)) {
            return true;
        }

        // Check if page is published and public
        return $landingPage->is_published && $landingPage->visibility === 'public';
    }

    public function create(User $user): bool
    {
        // Check subscription limits
        $plan = $user->subscription?->plan;

        if (!$plan) {
            return $user->landingPages()->count() < 3; // Free tier limit
        }

        $limit = $plan->features['max_pages'] ?? PHP_INT_MAX;
        return $user->landingPages()->count() < $limit;
    }

    public function update(User $user, LandingPage $landingPage): bool
    {
        if ($user->id === $landingPage->user_id) {
            return true;
        }

        return $this->hasTeamPermission($user, $landingPage, 'edit');
    }

    public function delete(User $user, LandingPage $landingPage): bool
    {
        if ($user->id === $landingPage->user_id) {
            return true;
        }

        return $this->hasTeamPermission($user, $landingPage, 'delete');
    }

    public function publish(User $user, LandingPage $landingPage): bool
    {
        if ($user->id === $landingPage->user_id) {
            return true;
        }

        return $this->hasTeamPermission($user, $landingPage, 'publish');
    }

    public function duplicate(User $user, LandingPage $landingPage): bool
    {
        // Must be able to view the original and create new pages
        return $this->view($user, $landingPage) && $this->create($user);
    }

    protected function isTeamMember(User $user, LandingPage $landingPage): bool
    {
        if (!$landingPage->team_id) {
            return false;
        }

        return $landingPage->team->members()->where('user_id', $user->id)->exists();
    }

    protected function hasTeamPermission(User $user, LandingPage $landingPage, string $permission): bool
    {
        if (!$landingPage->team_id) {
            return false;
        }

        $member = $landingPage->team->members()->where('user_id', $user->id)->first();

        if (!$member) {
            return false;
        }

        $role = $member->pivot->role;
        $permissions = config("teams.roles.{$role}.permissions", []);

        return in_array($permission, $permissions) || in_array('*', $permissions);
    }
}
```

### Register Policies

```php
// app/Providers/AuthServiceProvider.php
<?php

namespace App\Providers;

use App\Models\LandingPage;
use App\Models\Template;
use App\Models\Team;
use App\Models\Asset;
use App\Policies\LandingPagePolicy;
use App\Policies\TemplatePolicy;
use App\Policies\TeamPolicy;
use App\Policies\AssetPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        LandingPage::class => LandingPagePolicy::class,
        Template::class => TemplatePolicy::class,
        Team::class => TeamPolicy::class,
        Asset::class => AssetPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Super admin bypass
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
        });

        // Custom gates
        Gate::define('access-admin', function ($user) {
            return $user->hasRole(['admin', 'super-admin']);
        });

        Gate::define('manage-billing', function ($user) {
            return $user->id === $user->currentTeam?->owner_id;
        });
    }
}
```

### Middleware for Authorization

```php
// app/Http/Middleware/EnsureUserHasPermission.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$request->user()) {
            abort(401, 'Unauthenticated');
        }

        if (!$request->user()->hasPermission($permission)) {
            abort(403, 'Insufficient permissions');
        }

        return $next($request);
    }
}
```

---

## CSRF Protection

### CSRF Middleware Configuration

```php
// app/Http/Middleware/VerifyCsrfToken.php
<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * URIs that should be excluded from CSRF verification.
     * Keep this minimal - only for webhooks with their own verification
     */
    protected $except = [
        'stripe/webhook',
        'paddle/webhook',
    ];

    /**
     * Determine if the request has a valid CSRF token.
     */
    protected function tokensMatch($request): bool
    {
        $token = $this->getTokenFromRequest($request);

        return is_string($request->session()->token()) &&
               is_string($token) &&
               hash_equals($request->session()->token(), $token);
    }
}
```

### CSRF for SPA/API Requests

```php
// app/Http/Controllers/Auth/CsrfCookieController.php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CsrfCookieController extends Controller
{
    public function show(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return response()->noContent();
    }
}

// routes/api.php
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show'])
    ->middleware('web');
```

### Frontend CSRF Implementation

```javascript
// resources/js/utils/api.js
import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    withCredentials: true,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
});

// Add CSRF token to all requests
api.interceptors.request.use(async (config) => {
    // Get CSRF cookie for state-changing requests
    if (['post', 'put', 'patch', 'delete'].includes(config.method)) {
        await axios.get('/sanctum/csrf-cookie');
    }

    // Get token from meta tag or cookie
    const token = document.querySelector('meta[name="csrf-token"]')?.content
        || getCookie('XSRF-TOKEN');

    if (token) {
        config.headers['X-XSRF-TOKEN'] = decodeURIComponent(token);
    }

    return config;
});

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
        return parts.pop().split(';').shift();
    }
    return null;
}

export default api;
```

---

## XSS Prevention

### HTML Purifier Service

```php
// app/Services/HtmlPurifierService.php
<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlPurifierService
{
    protected HTMLPurifier $purifier;
    protected array $configs = [];

    public function __construct()
    {
        $this->initializeConfigs();
    }

    protected function initializeConfigs(): void
    {
        // Strict config - minimal HTML allowed
        $strictConfig = HTMLPurifier_Config::createDefault();
        $strictConfig->set('HTML.Allowed', 'p,br,strong,em,ul,ol,li,a[href|title]');
        $strictConfig->set('HTML.TargetBlank', true);
        $strictConfig->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $strictConfig->set('Attr.AllowedFrameTargets', ['_blank']);
        $strictConfig->set('AutoFormat.RemoveEmpty', true);
        $this->configs['strict'] = new HTMLPurifier($strictConfig);

        // Rich text config - for landing page content
        $richConfig = HTMLPurifier_Config::createDefault();
        $richConfig->set('HTML.Allowed',
            'p,br,strong,em,u,s,ul,ol,li,a[href|title|target],img[src|alt|width|height],' .
            'h1,h2,h3,h4,h5,h6,blockquote,pre,code,table,thead,tbody,tr,th,td,' .
            'div[class],span[class|style],iframe[src|width|height|frameborder|allowfullscreen]'
        );
        $richConfig->set('HTML.SafeIframe', true);
        $richConfig->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube\.com/embed/|player\.vimeo\.com/video/)%');
        $richConfig->set('CSS.AllowedProperties', 'font-weight,font-style,text-decoration,text-align,color,background-color');
        $richConfig->set('Attr.AllowedClasses', $this->getAllowedClasses());
        $this->configs['rich'] = new HTMLPurifier($richConfig);

        // Custom HTML config - for advanced users (still sanitized)
        $customConfig = HTMLPurifier_Config::createDefault();
        $customConfig->set('HTML.Trusted', false);
        $customConfig->set('CSS.Trusted', false);
        $customConfig->set('HTML.SafeObject', true);
        $customConfig->set('HTML.SafeEmbed', true);
        $customConfig->set('Output.FlashCompat', true);
        $this->configs['custom'] = new HTMLPurifier($customConfig);
    }

    protected function getAllowedClasses(): array
    {
        return [
            'text-center', 'text-left', 'text-right',
            'font-bold', 'font-italic',
            'mb-2', 'mb-4', 'mt-2', 'mt-4',
            'btn', 'btn-primary', 'btn-secondary',
        ];
    }

    public function purify(string $html, string $config = 'strict'): string
    {
        if (!isset($this->configs[$config])) {
            $config = 'strict';
        }

        return $this->configs[$config]->purify($html);
    }

    public function purifyArray(array $data, array $fields, string $config = 'strict'): array
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = $this->purify($data[$field], $config);
            }
        }

        return $data;
    }
}
```

### XSS Prevention Middleware

```php
// app/Http/Middleware/SanitizeInput.php
<?php

namespace App\Http\Middleware;

use App\Services\HtmlPurifierService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    public function __construct(
        protected HtmlPurifierService $purifier
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value, $key) {
            if (is_string($value)) {
                // Skip specific fields that need HTML
                $htmlFields = ['content', 'body', 'description', 'custom_html'];

                if (in_array($key, $htmlFields)) {
                    $value = $this->purifier->purify($value, 'rich');
                } else {
                    // Strip all HTML from other fields
                    $value = strip_tags($value);
                    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }
            }
        });

        $request->merge($input);

        return $next($request);
    }
}
```

### Blade Security Helpers

```php
// app/Providers/AppServiceProvider.php
<?php

namespace App\Providers;

use App\Services\HtmlPurifierService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Safe HTML output directive
        Blade::directive('safeHtml', function ($expression) {
            return "<?php echo app(\App\Services\HtmlPurifierService::class)->purify($expression, 'rich'); ?>";
        });

        // JSON encode for JavaScript
        Blade::directive('jsonEncode', function ($expression) {
            return "<?php echo json_encode($expression, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>";
        });
    }
}
```

### Content Security Policy

```php
// app/Http/Middleware/ContentSecurityPolicy.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $nonce = Str::random(32);
        $request->attributes->set('csp-nonce', $nonce);

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net https://js.stripe.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "img-src 'self' data: https: blob:",
            "font-src 'self' https://fonts.gstatic.com",
            "connect-src 'self' https://api.stripe.com wss:",
            "frame-src 'self' https://js.stripe.com https://www.youtube.com https://player.vimeo.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
```

---

## SQL Injection Prevention

### Secure Query Builder Usage

```php
// app/Repositories/LandingPageRepository.php
<?php

namespace App\Repositories;

use App\Models\LandingPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LandingPageRepository
{
    public function __construct(
        protected LandingPage $model
    ) {}

    /**
     * SECURE: Using Eloquent with parameter binding
     */
    public function findBySlug(string $slug): ?LandingPage
    {
        return $this->model
            ->where('slug', $slug) // Automatically escaped
            ->first();
    }

    /**
     * SECURE: Using whereIn with array - automatically parameterized
     */
    public function findByIds(array $ids): Collection
    {
        return $this->model
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * SECURE: Complex search with proper parameter binding
     */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('title', 'LIKE', $searchTerm)
                  ->orWhere('description', 'LIKE', $searchTerm);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Secure ordering - whitelist allowed columns
        $allowedSorts = ['created_at', 'updated_at', 'title', 'views_count'];
        $sortBy = in_array($filters['sort_by'] ?? '', $allowedSorts)
            ? $filters['sort_by']
            : 'created_at';
        $sortDir = ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * SECURE: Raw query with proper parameter binding
     */
    public function getPopularPages(int $limit = 10): Collection
    {
        return DB::select(
            'SELECT lp.*, COUNT(pv.id) as view_count
             FROM landing_pages lp
             LEFT JOIN page_views pv ON lp.id = pv.landing_page_id
             WHERE lp.is_published = ?
             AND lp.deleted_at IS NULL
             GROUP BY lp.id
             ORDER BY view_count DESC
             LIMIT ?',
            [true, $limit]
        );
    }

    /**
     * SECURE: Using query builder for complex joins
     */
    public function getWithAnalytics(int $userId): Collection
    {
        return $this->model
            ->select([
                'landing_pages.*',
                DB::raw('COUNT(DISTINCT page_views.id) as total_views'),
                DB::raw('COUNT(DISTINCT form_submissions.id) as total_submissions'),
            ])
            ->leftJoin('page_views', 'landing_pages.id', '=', 'page_views.landing_page_id')
            ->leftJoin('form_submissions', 'landing_pages.id', '=', 'form_submissions.landing_page_id')
            ->where('landing_pages.user_id', $userId)
            ->groupBy('landing_pages.id')
            ->get();
    }

    /**
     * SECURE: Dynamic column selection with whitelist
     */
    public function getColumns(array $columns): Collection
    {
        $allowedColumns = ['id', 'title', 'slug', 'status', 'created_at', 'updated_at'];
        $safeColumns = array_intersect($columns, $allowedColumns);

        if (empty($safeColumns)) {
            $safeColumns = ['id', 'title'];
        }

        return $this->model->select($safeColumns)->get();
    }
}
```

### Request Validation for SQL Safety

```php
// app/Http/Requests/SearchLandingPagesRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchLandingPagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
            'user_id' => 'nullable|integer|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'sort_by' => ['nullable', Rule::in(['created_at', 'updated_at', 'title', 'views_count'])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
```

---

## File Upload Security

### Secure File Upload Service

```php
// app/Services/FileUploadService.php
<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class FileUploadService
{
    protected array $allowedMimeTypes = [
        'image' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
        ],
        'document' => [
            'application/pdf',
        ],
        'video' => [
            'video/mp4',
            'video/webm',
        ],
    ];

    protected array $maxSizes = [
        'image' => 5 * 1024 * 1024,      // 5MB
        'document' => 10 * 1024 * 1024,   // 10MB
        'video' => 100 * 1024 * 1024,     // 100MB
    ];

    public function upload(UploadedFile $file, User $user, string $type = 'image'): Asset
    {
        // Validate file type
        $this->validateFile($file, $type);

        // Scan for malware (if ClamAV is available)
        $this->scanForMalware($file);

        // Generate secure filename
        $filename = $this->generateSecureFilename($file);

        // Determine storage path
        $path = $this->getStoragePath($user, $type);

        // Process and store file
        $storedPath = $this->processAndStore($file, $path, $filename, $type);

        // Create asset record
        return Asset::create([
            'user_id' => $user->id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'path' => $storedPath,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'type' => $type,
            'disk' => config('filesystems.default'),
        ]);
    }

    protected function validateFile(UploadedFile $file, string $type): void
    {
        // Check mime type
        $allowedMimes = $this->allowedMimeTypes[$type] ?? [];
        $actualMime = $file->getMimeType();

        if (!in_array($actualMime, $allowedMimes)) {
            throw new \InvalidArgumentException(
                "Invalid file type. Allowed types: " . implode(', ', $allowedMimes)
            );
        }

        // Double-check with finfo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($file->getPathname());

        if ($detectedMime !== $actualMime) {
            throw new \InvalidArgumentException('File type mismatch detected.');
        }

        // Check file size
        $maxSize = $this->maxSizes[$type] ?? 5 * 1024 * 1024;
        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException(
                "File too large. Maximum size: " . ($maxSize / 1024 / 1024) . "MB"
            );
        }

        // Check for PHP code in images
        if ($type === 'image') {
            $this->checkForPhpCode($file);
        }

        // Validate image dimensions
        if ($type === 'image' && strpos($actualMime, 'svg') === false) {
            $this->validateImageDimensions($file);
        }
    }

    protected function checkForPhpCode(UploadedFile $file): void
    {
        $content = file_get_contents($file->getPathname());

        $dangerousPatterns = [
            '/<\?php/i',
            '/<\?=/i',
            '/<script/i',
            '/\beval\s*\(/i',
            '/\bexec\s*\(/i',
            '/\bsystem\s*\(/i',
            '/\bpassthru\s*\(/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                throw new \InvalidArgumentException('Potentially malicious content detected.');
            }
        }
    }

    protected function validateImageDimensions(UploadedFile $file): void
    {
        $imageInfo = getimagesize($file->getPathname());

        if ($imageInfo === false) {
            throw new \InvalidArgumentException('Invalid image file.');
        }

        [$width, $height] = $imageInfo;

        $maxDimension = 4096;
        if ($width > $maxDimension || $height > $maxDimension) {
            throw new \InvalidArgumentException(
                "Image dimensions too large. Maximum: {$maxDimension}x{$maxDimension}"
            );
        }
    }

    protected function scanForMalware(UploadedFile $file): void
    {
        if (!config('services.clamav.enabled')) {
            return;
        }

        $socket = @fsockopen(
            config('services.clamav.host'),
            config('services.clamav.port'),
            $errno,
            $errstr,
            30
        );

        if (!$socket) {
            // Log error but don't block upload if ClamAV is unavailable
            \Log::warning('ClamAV unavailable', ['error' => $errstr]);
            return;
        }

        fwrite($socket, "nSCAN {$file->getPathname()}\n");
        $response = fgets($socket);
        fclose($socket);

        if (strpos($response, 'FOUND') !== false) {
            throw new \InvalidArgumentException('Malware detected in uploaded file.');
        }
    }

    protected function generateSecureFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $safeExtension = preg_replace('/[^a-zA-Z0-9]/', '', $extension);

        return Str::uuid() . '.' . strtolower($safeExtension);
    }

    protected function getStoragePath(User $user, string $type): string
    {
        return sprintf(
            'uploads/%s/%s/%s',
            $type,
            $user->id,
            date('Y/m')
        );
    }

    protected function processAndStore(
        UploadedFile $file,
        string $path,
        string $filename,
        string $type
    ): string {
        if ($type === 'image' && !Str::contains($file->getMimeType(), 'svg')) {
            // Strip EXIF data and re-encode image
            $image = Image::make($file)
                ->orientate()
                ->encode(null, 85);

            $fullPath = $path . '/' . $filename;
            Storage::put($fullPath, $image->stream());

            return $fullPath;
        }

        return $file->storeAs($path, $filename);
    }

    public function delete(Asset $asset): bool
    {
        Storage::delete($asset->path);
        return $asset->delete();
    }
}
```

### File Upload Request Validation

```php
// app/Http/Requests/UploadFileRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UploadFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('upload-files');
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                File::types(['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf'])
                    ->max(10 * 1024), // 10MB in KB
            ],
            'type' => 'required|in:image,document',
            'folder' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9\-_\/]+$/',
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'The file must not be larger than 10MB.',
            'folder.regex' => 'Folder name contains invalid characters.',
        ];
    }
}
```

---

## API Security & Rate Limiting

### Rate Limiting Configuration

```php
// app/Providers/RouteServiceProvider.php
<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        // Default API rate limit
        RateLimiter::for('api', function (Request $request) {
            $user = $request->user();

            if ($user) {
                // Rate limits based on subscription tier
                $limit = match ($user->subscription?->plan?->slug) {
                    'enterprise' => 1000,
                    'professional' => 300,
                    'starter' => 100,
                    default => 60,
                };

                return Limit::perMinute($limit)->by($user->id);
            }

            return Limit::perMinute(30)->by($request->ip());
        });

        // Strict rate limit for authentication endpoints
        RateLimiter::for('auth', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perHour(20)->by($request->ip()),
            ];
        });

        // Rate limit for file uploads
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limit for expensive operations
        RateLimiter::for('expensive', function (Request $request) {
            return Limit::perMinute(3)->by($request->user()?->id ?: $request->ip());
        });

        // Public page views (for analytics)
        RateLimiter::for('page-view', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}
```

### API Authentication Middleware

```php
// app/Http/Middleware/ApiAuthentication.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        // Validate API token format
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'Authentication required',
                'message' => 'Please provide a valid API token.',
            ], 401);
        }

        // Check token format (basic validation before DB lookup)
        if (strlen($token) < 40) {
            return response()->json([
                'error' => 'Invalid token format',
            ], 401);
        }

        // Verify token and check expiration
        $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

        if (!$personalAccessToken) {
            return response()->json([
                'error' => 'Invalid token',
            ], 401);
        }

        if ($personalAccessToken->expires_at && $personalAccessToken->expires_at->isPast()) {
            return response()->json([
                'error' => 'Token expired',
                'message' => 'Please generate a new API token.',
            ], 401);
        }

        // Update last used timestamp
        $personalAccessToken->forceFill(['last_used_at' => now()])->save();

        return $next($request);
    }
}
```

### API Response Security

```php
// app/Http/Middleware/SecureApiResponse.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureApiResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            // Remove sensitive headers
            $response->headers->remove('X-Powered-By');

            // Add security headers
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'DENY');

            // Remove internal data from error responses in production
            if (app()->isProduction() && $response->getStatusCode() >= 400) {
                $data = $response->getData(true);

                // Remove stack traces and internal info
                unset($data['exception'], $data['file'], $data['line'], $data['trace']);

                $response->setData($data);
            }
        }

        return $response;
    }
}
```

### API Versioning and Documentation Security

```php
// app/Http/Middleware/ApiVersion.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiVersion
{
    protected array $supportedVersions = ['v1', 'v2'];
    protected string $defaultVersion = 'v1';

    public function handle(Request $request, Closure $next): Response
    {
        $version = $request->header('X-API-Version', $this->defaultVersion);

        if (!in_array($version, $this->supportedVersions)) {
            return response()->json([
                'error' => 'Unsupported API version',
                'supported_versions' => $this->supportedVersions,
            ], 400);
        }

        $request->attributes->set('api-version', $version);

        $response = $next($request);
        $response->headers->set('X-API-Version', $version);

        return $response;
    }
}
```

---

## Security Headers Middleware

### Comprehensive Security Headers

```php
// app/Http/Middleware/SecurityHeaders.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // XSS Protection (legacy browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Clickjacking protection
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (formerly Feature Policy)
        $response->headers->set('Permissions-Policy', implode(', ', [
            'accelerometer=()',
            'camera=()',
            'geolocation=()',
            'gyroscope=()',
            'magnetometer=()',
            'microphone=()',
            'payment=(self)',
            'usb=()',
        ]));

        // HSTS (only in production with HTTPS)
        if (app()->isProduction() && $request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Remove server identification headers
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        // Cross-Origin policies
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        return $response;
    }
}
```

### Register Middleware

```php
// app/Http/Kernel.php
<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \App\Http\Middleware\SecurityHeaders::class,
        // ... other global middleware
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\ContentSecurityPolicy::class,
            // ... other web middleware
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \App\Http\Middleware\SecureApiResponse::class,
            \App\Http\Middleware\ApiVersion::class,
        ],
    ];

    protected $middlewareAliases = [
        'auth.api' => \App\Http\Middleware\ApiAuthentication::class,
        'permission' => \App\Http\Middleware\EnsureUserHasPermission::class,
        'sanitize' => \App\Http\Middleware\SanitizeInput::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];
}
```

---

## Audit Logging

### Audit Log Model

```php
// app/Models/AuditLog.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'tags',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'tags' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    public function scopeForModel($query, string $type, ?int $id = null)
    {
        $query->where('auditable_type', $type);

        if ($id) {
            $query->where('auditable_id', $id);
        }

        return $query;
    }
}
```

### Audit Logging Service

```php
// app/Services/AuditLogService.php
<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    protected array $sensitiveFields = [
        'password',
        'password_confirmation',
        'current_password',
        'secret',
        'token',
        'api_key',
        'credit_card',
        'cvv',
        'ssn',
    ];

    public function log(
        string $event,
        ?Model $model = null,
        array $oldValues = [],
        array $newValues = [],
        array $tags = []
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id' => $model?->getKey(),
            'old_values' => $this->sanitizeValues($oldValues),
            'new_values' => $this->sanitizeValues($newValues),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'tags' => $tags,
        ]);
    }

    protected function sanitizeValues(array $values): array
    {
        foreach ($this->sensitiveFields as $field) {
            if (isset($values[$field])) {
                $values[$field] = '[REDACTED]';
            }
        }

        return $values;
    }

    public function logModelCreated(Model $model, array $tags = []): AuditLog
    {
        return $this->log(
            'created',
            $model,
            [],
            $model->getAttributes(),
            $tags
        );
    }

    public function logModelUpdated(Model $model, array $tags = []): AuditLog
    {
        return $this->log(
            'updated',
            $model,
            $model->getOriginal(),
            $model->getChanges(),
            $tags
        );
    }

    public function logModelDeleted(Model $model, array $tags = []): AuditLog
    {
        return $this->log(
            'deleted',
            $model,
            $model->getAttributes(),
            [],
            $tags
        );
    }

    public function logAuthentication(string $event, ?int $userId = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'event' => $event,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'tags' => ['authentication'],
        ]);
    }

    public function logSecurityEvent(string $event, array $context = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'new_values' => $context,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'tags' => ['security'],
        ]);
    }
}
```

### Auditable Trait for Models

```php
// app/Traits/Auditable.php
<?php

namespace App\Traits;

use App\Services\AuditLogService;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            app(AuditLogService::class)->logModelCreated($model);
        });

        static::updated(function ($model) {
            if ($model->wasChanged()) {
                app(AuditLogService::class)->logModelUpdated($model);
            }
        });

        static::deleted(function ($model) {
            app(AuditLogService::class)->logModelDeleted($model);
        });
    }

    public function getAuditExclude(): array
    {
        return $this->auditExclude ?? [];
    }

    public function getAuditInclude(): array
    {
        return $this->auditInclude ?? [];
    }
}
```

### Usage in Models

```php
// app/Models/LandingPage.php
<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    use Auditable;

    protected array $auditExclude = [
        'views_count',
        'updated_at',
    ];
}
```

### Audit Log Viewer Controller

```php
// app/Http/Controllers/Admin/AuditLogController.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $query = AuditLog::with('user')
            ->latest();

        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        if ($request->filled('event')) {
            $query->forEvent($request->event);
        }

        if ($request->filled('model_type')) {
            $query->forModel($request->model_type, $request->model_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', $request->ip_address);
        }

        $logs = $query->paginate(50);

        return response()->json($logs);
    }

    public function show(AuditLog $auditLog)
    {
        $this->authorize('view', $auditLog);

        return response()->json($auditLog->load('user', 'auditable'));
    }

    public function export(Request $request)
    {
        $this->authorize('export', AuditLog::class);

        // Export logic for compliance/reporting
        // Returns CSV or PDF of audit logs
    }
}
```

### Database Migration for Audit Logs

```php
// database/migrations/2024_01_01_000000_create_audit_logs_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 100)->index();
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->ipAddress('ip_address')->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
```

---

## Security Best Practices Summary

### Environment Configuration

```env
# .env.example - Security-related settings

APP_DEBUG=false
APP_ENV=production

# Session Security
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

# Database
DB_CONNECTION=mysql
DB_STRICT_MODE=true

# Encryption
APP_KEY=

# Mail (for password resets)
MAIL_ENCRYPTION=tls

# API Rate Limiting
API_RATE_LIMIT=60

# File Uploads
MAX_UPLOAD_SIZE=10240
ALLOWED_FILE_TYPES=jpg,jpeg,png,gif,webp,svg,pdf

# ClamAV Malware Scanning
CLAMAV_ENABLED=true
CLAMAV_HOST=127.0.0.1
CLAMAV_PORT=3310

# Two-Factor Authentication
TWO_FACTOR_ENABLED=true

# Audit Logging
AUDIT_ENABLED=true
AUDIT_RETENTION_DAYS=365
```

### Security Checklist

- [ ] All user input is validated and sanitized
- [ ] Passwords meet complexity requirements
- [ ] Sessions are encrypted and use secure cookies
- [ ] CSRF protection is enabled for all state-changing requests
- [ ] Rate limiting is configured for all endpoints
- [ ] File uploads are validated and scanned
- [ ] SQL queries use parameter binding
- [ ] HTML output is escaped or purified
- [ ] Security headers are configured
- [ ] Audit logging captures all important events
- [ ] API tokens have expiration dates
- [ ] 2FA is available for user accounts
- [ ] Sensitive data is encrypted at rest
- [ ] HTTPS is enforced in production
- [ ] Error messages don't leak sensitive information
