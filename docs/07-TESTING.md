# Testing Documentation

## Tech Stack
- Laravel 12
- PHPUnit 11
- Laravel Dusk

---

## 1. PHPUnit Configuration

### phpunit.xml

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         cacheDirectory=".phpunit.cache"
         executionOrder="depends,defects"
         requireCoverageMetadata="false"
         beStrictAboutCoverageMetadata="true"
         beStrictAboutOutputDuringTests="true"
         failOnRisky="true"
         failOnWarning="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
        <testsuite name="Browser">
            <directory>tests/Browser</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
        <exclude>
            <directory>app/Console</directory>
        </exclude>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="APP_MAINTENANCE_DRIVER" value="file"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_STORE" value="array"/>
        <env name="DB_CONNECTION" value="mysql"/>
        <env name="DB_DATABASE" value="landing_page_builder_test"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="PULSE_ENABLED" value="false"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
    </php>
</phpunit>
```

---

## 2. Base Test Case

### tests/TestCase.php

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    protected function signIn($user = null)
    {
        $user = $user ?: \App\Models\User::factory()->create();

        $this->actingAs($user);

        return $user;
    }

    protected function signInAsAdmin()
    {
        return $this->signIn(
            \App\Models\User::factory()->admin()->create()
        );
    }

    protected function signInWithTeam($user = null)
    {
        $user = $this->signIn($user);

        $team = \App\Models\Team::factory()->create(['owner_id' => $user->id]);
        $user->teams()->attach($team, ['role' => 'owner']);
        $user->update(['current_team_id' => $team->id]);

        return $user;
    }
}
```

---

## 3. Test Factories

### database/factories/UserFactory.php

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'user',
            'current_team_id' => null,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    public function withSubscription(string $plan = 'pro'): static
    {
        return $this->afterCreating(function (User $user) use ($plan) {
            $user->subscriptions()->create([
                'name' => 'default',
                'stripe_id' => 'sub_' . Str::random(14),
                'stripe_status' => 'active',
                'stripe_price' => $plan === 'pro' ? 'price_pro' : 'price_enterprise',
                'quantity' => 1,
            ]);
        });
    }
}
```

### database/factories/TeamFactory.php

```php
<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'owner_id' => User::factory(),
            'personal_team' => false,
        ];
    }

    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'personal_team' => true,
        ]);
    }
}
```

### database/factories/LandingPageFactory.php

```php
<?php

namespace Database\Factories;

use App\Models\LandingPage;
use App\Models\User;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LandingPageFactory extends Factory
{
    protected $model = LandingPage::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'user_id' => User::factory(),
            'team_id' => Team::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(),
            'content' => $this->generateDefaultContent(),
            'settings' => [
                'seo' => [
                    'title' => $title,
                    'description' => fake()->sentence(),
                    'keywords' => fake()->words(5),
                ],
                'analytics' => [
                    'google_analytics_id' => null,
                    'facebook_pixel_id' => null,
                ],
            ],
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

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    public function withCustomDomain(string $domain = null): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_domain' => $domain ?? fake()->domainName(),
        ]);
    }

    protected function generateDefaultContent(): array
    {
        return [
            'sections' => [
                [
                    'id' => Str::uuid()->toString(),
                    'type' => 'hero',
                    'content' => [
                        'headline' => fake()->sentence(),
                        'subheadline' => fake()->paragraph(),
                        'cta_text' => 'Get Started',
                        'cta_url' => '#',
                    ],
                ],
            ],
        ];
    }
}
```

### database/factories/TemplateFactory.php

```php
<?php

namespace Database\Factories;

use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TemplateFactory extends Factory
{
    protected $model = Template::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'thumbnail' => 'templates/thumbnails/default.jpg',
            'content' => [
                'sections' => [],
                'styles' => [],
            ],
            'category' => fake()->randomElement(['business', 'portfolio', 'startup', 'saas']),
            'is_premium' => false,
            'is_active' => true,
        ];
    }

    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
```

### database/factories/ComponentFactory.php

```php
<?php

namespace Database\Factories;

use App\Models\Component;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ComponentFactory extends Factory
{
    protected $model = Component::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => fake()->randomElement(['hero', 'features', 'pricing', 'testimonials', 'cta', 'footer']),
            'schema' => [
                'fields' => [
                    [
                        'name' => 'title',
                        'type' => 'text',
                        'label' => 'Title',
                        'required' => true,
                    ],
                ],
            ],
            'default_content' => [
                'title' => 'Default Title',
            ],
            'is_active' => true,
        ];
    }
}
```

### database/factories/LeadFactory.php

```php
<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LandingPage;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'landing_page_id' => LandingPage::factory(),
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'data' => [
                'source' => fake()->randomElement(['organic', 'paid', 'social', 'referral']),
                'utm_source' => fake()->optional()->word(),
                'utm_medium' => fake()->optional()->word(),
                'utm_campaign' => fake()->optional()->word(),
            ],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
```

### database/factories/AnalyticsEventFactory.php

```php
<?php

namespace Database\Factories;

use App\Models\AnalyticsEvent;
use App\Models\LandingPage;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalyticsEventFactory extends Factory
{
    protected $model = AnalyticsEvent::class;

    public function definition(): array
    {
        return [
            'landing_page_id' => LandingPage::factory(),
            'event_type' => fake()->randomElement(['page_view', 'click', 'scroll', 'form_submit']),
            'event_data' => [
                'element' => fake()->optional()->word(),
                'value' => fake()->optional()->randomNumber(),
            ],
            'session_id' => fake()->uuid(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'referrer' => fake()->optional()->url(),
            'country' => fake()->countryCode(),
            'city' => fake()->city(),
        ];
    }

    public function pageView(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'page_view',
        ]);
    }

    public function conversion(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'form_submit',
        ]);
    }
}
```

---

## 4. Unit Tests for Services

### tests/Unit/Services/LandingPageServiceTest.php

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\LandingPageService;
use App\Models\LandingPage;
use App\Models\User;
use App\Models\Team;
use App\Models\Template;
use App\Exceptions\LandingPageLimitExceededException;
use Illuminate\Support\Facades\Storage;

class LandingPageServiceTest extends TestCase
{
    protected LandingPageService $service;
    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(LandingPageService::class);
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['owner_id' => $this->user->id]);
        $this->user->update(['current_team_id' => $this->team->id]);
    }

    public function test_can_create_landing_page(): void
    {
        $data = [
            'title' => 'Test Landing Page',
            'description' => 'Test description',
        ];

        $landingPage = $this->service->create($this->user, $data);

        $this->assertInstanceOf(LandingPage::class, $landingPage);
        $this->assertEquals('Test Landing Page', $landingPage->title);
        $this->assertEquals($this->user->id, $landingPage->user_id);
        $this->assertEquals($this->team->id, $landingPage->team_id);
        $this->assertEquals('draft', $landingPage->status);
    }

    public function test_generates_unique_slug(): void
    {
        LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'slug' => 'test-page',
        ]);

        $landingPage = $this->service->create($this->user, [
            'title' => 'Test Page',
        ]);

        $this->assertNotEquals('test-page', $landingPage->slug);
        $this->assertStringStartsWith('test-page-', $landingPage->slug);
    }

    public function test_can_create_from_template(): void
    {
        $template = Template::factory()->create([
            'content' => [
                'sections' => [
                    ['type' => 'hero', 'content' => ['title' => 'Template Title']],
                ],
            ],
        ]);

        $landingPage = $this->service->createFromTemplate($this->user, $template, [
            'title' => 'From Template',
        ]);

        $this->assertEquals('From Template', $landingPage->title);
        $this->assertEquals($template->content, $landingPage->content);
    }

    public function test_can_update_landing_page(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $updated = $this->service->update($landingPage, [
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);

        $this->assertEquals('Updated Title', $updated->title);
        $this->assertEquals('Updated description', $updated->description);
    }

    public function test_can_publish_landing_page(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'draft',
        ]);

        $published = $this->service->publish($landingPage);

        $this->assertEquals('published', $published->status);
        $this->assertNotNull($published->published_at);
    }

    public function test_can_unpublish_landing_page(): void
    {
        $landingPage = LandingPage::factory()->published()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $unpublished = $this->service->unpublish($landingPage);

        $this->assertEquals('draft', $unpublished->status);
    }

    public function test_can_duplicate_landing_page(): void
    {
        $original = LandingPage::factory()->published()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'title' => 'Original Page',
        ]);

        $duplicate = $this->service->duplicate($original);

        $this->assertNotEquals($original->id, $duplicate->id);
        $this->assertEquals('Original Page (Copy)', $duplicate->title);
        $this->assertEquals('draft', $duplicate->status);
        $this->assertEquals($original->content, $duplicate->content);
    }

    public function test_can_delete_landing_page(): void
    {
        Storage::fake('public');

        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $this->service->delete($landingPage);

        $this->assertSoftDeleted($landingPage);
    }

    public function test_enforces_page_limit_for_free_users(): void
    {
        config(['landing-pages.limits.free' => 3]);

        LandingPage::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $this->expectException(LandingPageLimitExceededException::class);

        $this->service->create($this->user, [
            'title' => 'Fourth Page',
        ]);
    }

    public function test_can_update_seo_settings(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $updated = $this->service->updateSeoSettings($landingPage, [
            'title' => 'SEO Title',
            'description' => 'SEO Description',
            'keywords' => ['keyword1', 'keyword2'],
            'og_image' => 'https://example.com/image.jpg',
        ]);

        $this->assertEquals('SEO Title', $updated->settings['seo']['title']);
        $this->assertEquals('SEO Description', $updated->settings['seo']['description']);
    }

    public function test_can_update_content(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $newContent = [
            'sections' => [
                [
                    'id' => 'section-1',
                    'type' => 'hero',
                    'content' => ['title' => 'New Hero'],
                ],
            ],
        ];

        $updated = $this->service->updateContent($landingPage, $newContent);

        $this->assertEquals($newContent, $updated->content);
    }
}
```

### tests/Unit/Services/TemplateServiceTest.php

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\TemplateService;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class TemplateServiceTest extends TestCase
{
    protected TemplateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TemplateService::class);
    }

    public function test_can_get_all_active_templates(): void
    {
        Template::factory()->count(3)->create(['is_active' => true]);
        Template::factory()->count(2)->inactive()->create();

        $templates = $this->service->getActiveTemplates();

        $this->assertCount(3, $templates);
    }

    public function test_can_filter_templates_by_category(): void
    {
        Template::factory()->create(['category' => 'business', 'is_active' => true]);
        Template::factory()->create(['category' => 'portfolio', 'is_active' => true]);
        Template::factory()->create(['category' => 'business', 'is_active' => true]);

        $templates = $this->service->getActiveTemplates('business');

        $this->assertCount(2, $templates);
    }

    public function test_can_get_premium_templates(): void
    {
        Template::factory()->count(2)->premium()->create(['is_active' => true]);
        Template::factory()->count(3)->create(['is_active' => true]);

        $templates = $this->service->getPremiumTemplates();

        $this->assertCount(2, $templates);
    }

    public function test_can_check_user_access_to_premium_template(): void
    {
        $premiumTemplate = Template::factory()->premium()->create();
        $freeUser = User::factory()->create();
        $proUser = User::factory()->withSubscription('pro')->create();

        $this->assertFalse($this->service->userCanAccessTemplate($freeUser, $premiumTemplate));
        $this->assertTrue($this->service->userCanAccessTemplate($proUser, $premiumTemplate));
    }

    public function test_can_create_template(): void
    {
        Storage::fake('public');

        $data = [
            'name' => 'New Template',
            'description' => 'Template description',
            'category' => 'startup',
            'content' => ['sections' => []],
        ];

        $template = $this->service->create($data);

        $this->assertEquals('New Template', $template->name);
        $this->assertEquals('new-template', $template->slug);
        $this->assertTrue($template->is_active);
    }

    public function test_can_preview_template(): void
    {
        $template = Template::factory()->create();

        $preview = $this->service->generatePreview($template);

        $this->assertArrayHasKey('html', $preview);
        $this->assertArrayHasKey('styles', $preview);
    }
}
```

### tests/Unit/Services/AnalyticsServiceTest.php

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AnalyticsService;
use App\Models\LandingPage;
use App\Models\AnalyticsEvent;
use App\Models\Lead;
use Carbon\Carbon;

class AnalyticsServiceTest extends TestCase
{
    protected AnalyticsService $service;
    protected LandingPage $landingPage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AnalyticsService::class);
        $this->landingPage = LandingPage::factory()->published()->create();
    }

    public function test_can_record_page_view(): void
    {
        $this->service->recordPageView($this->landingPage, [
            'session_id' => 'test-session',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $this->assertDatabaseHas('analytics_events', [
            'landing_page_id' => $this->landingPage->id,
            'event_type' => 'page_view',
            'session_id' => 'test-session',
        ]);
    }

    public function test_can_get_page_views_count(): void
    {
        AnalyticsEvent::factory()->count(10)->pageView()->create([
            'landing_page_id' => $this->landingPage->id,
        ]);

        $count = $this->service->getPageViewsCount($this->landingPage);

        $this->assertEquals(10, $count);
    }

    public function test_can_get_unique_visitors_count(): void
    {
        AnalyticsEvent::factory()->count(5)->pageView()->create([
            'landing_page_id' => $this->landingPage->id,
            'session_id' => 'session-1',
        ]);
        AnalyticsEvent::factory()->count(3)->pageView()->create([
            'landing_page_id' => $this->landingPage->id,
            'session_id' => 'session-2',
        ]);

        $count = $this->service->getUniqueVisitorsCount($this->landingPage);

        $this->assertEquals(2, $count);
    }

    public function test_can_calculate_conversion_rate(): void
    {
        AnalyticsEvent::factory()->count(100)->pageView()->create([
            'landing_page_id' => $this->landingPage->id,
        ]);
        Lead::factory()->count(5)->create([
            'landing_page_id' => $this->landingPage->id,
        ]);

        $rate = $this->service->getConversionRate($this->landingPage);

        $this->assertEquals(5.0, $rate);
    }

    public function test_can_get_analytics_for_date_range(): void
    {
        AnalyticsEvent::factory()->count(5)->pageView()->create([
            'landing_page_id' => $this->landingPage->id,
            'created_at' => Carbon::now()->subDays(3),
        ]);
        AnalyticsEvent::factory()->count(10)->pageView()->create([
            'landing_page_id' => $this->landingPage->id,
            'created_at' => Carbon::now(),
        ]);

        $analytics = $this->service->getAnalytics(
            $this->landingPage,
            Carbon::now()->subDays(7),
            Carbon::now()
        );

        $this->assertEquals(15, $analytics['total_views']);
    }

    public function test_can_get_top_referrers(): void
    {
        AnalyticsEvent::factory()->count(5)->pageView()->create([
            'landing_page_id' => $this->landingPage->id,
            'referrer' => 'https://google.com',
        ]);
        AnalyticsEvent::factory()->count(3)->pageView()->create([
            'landing_page_id' => $this->landingPage->id,
            'referrer' => 'https://facebook.com',
        ]);

        $referrers = $this->service->getTopReferrers($this->landingPage, 10);

        $this->assertCount(2, $referrers);
        $this->assertEquals('https://google.com', $referrers[0]['referrer']);
    }

    public function test_can_get_geographic_distribution(): void
    {
        AnalyticsEvent::factory()->count(10)->pageView()->create([
            'landing_page_id' => $this->landingPage->id,
            'country' => 'US',
        ]);
        AnalyticsEvent::factory()->count(5)->pageView()->create([
            'landing_page_id' => $this->landingPage->id,
            'country' => 'UK',
        ]);

        $distribution = $this->service->getGeographicDistribution($this->landingPage);

        $this->assertArrayHasKey('US', $distribution);
        $this->assertEquals(10, $distribution['US']);
    }

    public function test_can_get_daily_stats(): void
    {
        for ($i = 0; $i < 7; $i++) {
            AnalyticsEvent::factory()->count(10)->pageView()->create([
                'landing_page_id' => $this->landingPage->id,
                'created_at' => Carbon::now()->subDays($i),
            ]);
        }

        $stats = $this->service->getDailyStats($this->landingPage, 7);

        $this->assertCount(7, $stats);
    }
}
```

### tests/Unit/Services/LeadServiceTest.php

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\LeadService;
use App\Models\Lead;
use App\Models\LandingPage;
use App\Notifications\NewLeadNotification;
use Illuminate\Support\Facades\Notification;

class LeadServiceTest extends TestCase
{
    protected LeadService $service;
    protected LandingPage $landingPage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(LeadService::class);
        $this->landingPage = LandingPage::factory()->published()->create();
    }

    public function test_can_capture_lead(): void
    {
        Notification::fake();

        $data = [
            'email' => 'test@example.com',
            'name' => 'John Doe',
            'phone' => '123-456-7890',
        ];

        $lead = $this->service->capture($this->landingPage, $data, [
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $this->assertInstanceOf(Lead::class, $lead);
        $this->assertEquals('test@example.com', $lead->email);
        $this->assertEquals($this->landingPage->id, $lead->landing_page_id);
    }

    public function test_sends_notification_on_lead_capture(): void
    {
        Notification::fake();

        $this->service->capture($this->landingPage, [
            'email' => 'test@example.com',
            'name' => 'John Doe',
        ]);

        Notification::assertSentTo(
            $this->landingPage->user,
            NewLeadNotification::class
        );
    }

    public function test_can_export_leads_to_csv(): void
    {
        Lead::factory()->count(5)->create([
            'landing_page_id' => $this->landingPage->id,
        ]);

        $csv = $this->service->exportToCsv($this->landingPage);

        $this->assertStringContainsString('email', $csv);
        $this->assertStringContainsString('name', $csv);
    }

    public function test_can_get_leads_with_filters(): void
    {
        Lead::factory()->count(10)->create([
            'landing_page_id' => $this->landingPage->id,
            'created_at' => now()->subDays(5),
        ]);
        Lead::factory()->count(5)->create([
            'landing_page_id' => $this->landingPage->id,
            'created_at' => now(),
        ]);

        $leads = $this->service->getLeads($this->landingPage, [
            'from' => now()->subDays(2),
            'to' => now(),
        ]);

        $this->assertCount(5, $leads);
    }

    public function test_prevents_duplicate_leads(): void
    {
        Lead::factory()->create([
            'landing_page_id' => $this->landingPage->id,
            'email' => 'duplicate@example.com',
        ]);

        $lead = $this->service->capture($this->landingPage, [
            'email' => 'duplicate@example.com',
            'name' => 'Another Name',
        ]);

        $this->assertNull($lead);
        $this->assertDatabaseCount('leads', 1);
    }

    public function test_can_delete_lead(): void
    {
        $lead = Lead::factory()->create([
            'landing_page_id' => $this->landingPage->id,
        ]);

        $this->service->delete($lead);

        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }
}
```

### tests/Unit/Services/ComponentServiceTest.php

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ComponentService;
use App\Models\Component;

class ComponentServiceTest extends TestCase
{
    protected ComponentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ComponentService::class);
    }

    public function test_can_get_all_active_components(): void
    {
        Component::factory()->count(5)->create(['is_active' => true]);
        Component::factory()->count(2)->create(['is_active' => false]);

        $components = $this->service->getActiveComponents();

        $this->assertCount(5, $components);
    }

    public function test_can_get_components_by_type(): void
    {
        Component::factory()->create(['type' => 'hero', 'is_active' => true]);
        Component::factory()->create(['type' => 'hero', 'is_active' => true]);
        Component::factory()->create(['type' => 'footer', 'is_active' => true]);

        $components = $this->service->getComponentsByType('hero');

        $this->assertCount(2, $components);
    }

    public function test_can_render_component(): void
    {
        $component = Component::factory()->create([
            'type' => 'hero',
            'default_content' => [
                'title' => 'Default Title',
                'subtitle' => 'Default Subtitle',
            ],
        ]);

        $html = $this->service->render($component, [
            'title' => 'Custom Title',
        ]);

        $this->assertStringContainsString('Custom Title', $html);
    }

    public function test_can_validate_component_content(): void
    {
        $component = Component::factory()->create([
            'schema' => [
                'fields' => [
                    [
                        'name' => 'title',
                        'type' => 'text',
                        'required' => true,
                    ],
                    [
                        'name' => 'subtitle',
                        'type' => 'text',
                        'required' => false,
                    ],
                ],
            ],
        ]);

        $validContent = ['title' => 'Test Title'];
        $invalidContent = ['subtitle' => 'Only Subtitle'];

        $this->assertTrue($this->service->validateContent($component, $validContent));
        $this->assertFalse($this->service->validateContent($component, $invalidContent));
    }
}
```

### tests/Unit/Services/MediaServiceTest.php

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\MediaService;
use App\Models\User;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaServiceTest extends TestCase
{
    protected MediaService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->service = app(MediaService::class);
        $this->user = User::factory()->create();
    }

    public function test_can_upload_image(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);

        $media = $this->service->upload($this->user, $file);

        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals('image', $media->type);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_generates_thumbnails(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);

        $media = $this->service->upload($this->user, $file);

        $this->assertNotNull($media->thumbnails);
        $this->assertArrayHasKey('small', $media->thumbnails);
        $this->assertArrayHasKey('medium', $media->thumbnails);
    }

    public function test_validates_file_type(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $this->expectException(\App\Exceptions\InvalidMediaTypeException::class);

        $this->service->upload($this->user, $file, ['allowed_types' => ['image']]);
    }

    public function test_enforces_storage_limit(): void
    {
        config(['media.storage_limit' => 1000]);

        $this->user->media()->create([
            'path' => 'test.jpg',
            'type' => 'image',
            'size' => 900,
            'mime_type' => 'image/jpeg',
            'original_name' => 'test.jpg',
        ]);

        $file = UploadedFile::fake()->image('test2.jpg')->size(200);

        $this->expectException(\App\Exceptions\StorageLimitExceededException::class);

        $this->service->upload($this->user, $file);
    }

    public function test_can_delete_media(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $this->service->upload($this->user, $file);

        $this->service->delete($media);

        Storage::disk('public')->assertMissing($media->path);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_can_optimize_image(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);

        $media = $this->service->upload($this->user, $file, [
            'optimize' => true,
            'max_width' => 1200,
        ]);

        $this->assertLessThanOrEqual(1200, $media->width);
    }
}
```

---

## 5. Feature Tests

### tests/Feature/Auth/RegistrationTest.php

```php
<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegistrationTest extends TestCase
{
    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_users_cannot_register_with_invalid_email(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_users_cannot_register_with_existing_email(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_users_cannot_register_with_weak_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
```

### tests/Feature/Auth/AuthenticationTest.php

```php
<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;

class AuthenticationTest extends TestCase
{
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
```

### tests/Feature/LandingPage/LandingPageManagementTest.php

```php
<?php

namespace Tests\Feature\LandingPage;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\LandingPage;
use App\Models\Template;
use Illuminate\Support\Facades\Storage;

class LandingPageManagementTest extends TestCase
{
    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['owner_id' => $this->user->id]);
        $this->user->update(['current_team_id' => $this->team->id]);
    }

    public function test_user_can_view_landing_pages_index(): void
    {
        LandingPage::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard/landing-pages');

        $response->assertStatus(200);
        $response->assertViewHas('landingPages');
    }

    public function test_user_can_create_landing_page(): void
    {
        $response = $this->actingAs($this->user)->post('/dashboard/landing-pages', [
            'title' => 'New Landing Page',
            'description' => 'Page description',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('landing_pages', [
            'title' => 'New Landing Page',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_create_landing_page_from_template(): void
    {
        $template = Template::factory()->create();

        $response = $this->actingAs($this->user)->post('/dashboard/landing-pages', [
            'title' => 'From Template',
            'template_id' => $template->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('landing_pages', [
            'title' => 'From Template',
        ]);
    }

    public function test_user_can_view_landing_page_editor(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/dashboard/landing-pages/{$landingPage->id}/edit");

        $response->assertStatus(200);
    }

    public function test_user_can_update_landing_page(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put("/dashboard/landing-pages/{$landingPage->id}", [
                'title' => 'Updated Title',
                'description' => 'Updated description',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('landing_pages', [
            'id' => $landingPage->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_can_update_landing_page_content(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $content = [
            'sections' => [
                ['id' => 'section-1', 'type' => 'hero', 'content' => []],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->put("/dashboard/landing-pages/{$landingPage->id}/content", [
                'content' => $content,
            ]);

        $response->assertStatus(200);
        $landingPage->refresh();
        $this->assertEquals($content, $landingPage->content);
    }

    public function test_user_can_publish_landing_page(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)
            ->post("/dashboard/landing-pages/{$landingPage->id}/publish");

        $response->assertRedirect();
        $landingPage->refresh();
        $this->assertEquals('published', $landingPage->status);
    }

    public function test_user_can_unpublish_landing_page(): void
    {
        $landingPage = LandingPage::factory()->published()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/dashboard/landing-pages/{$landingPage->id}/unpublish");

        $response->assertRedirect();
        $landingPage->refresh();
        $this->assertEquals('draft', $landingPage->status);
    }

    public function test_user_can_duplicate_landing_page(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/dashboard/landing-pages/{$landingPage->id}/duplicate");

        $response->assertRedirect();
        $this->assertDatabaseCount('landing_pages', 2);
    }

    public function test_user_can_delete_landing_page(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/dashboard/landing-pages/{$landingPage->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted($landingPage);
    }

    public function test_user_cannot_access_other_users_landing_pages(): void
    {
        $otherUser = User::factory()->create();
        $landingPage = LandingPage::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/dashboard/landing-pages/{$landingPage->id}/edit");

        $response->assertStatus(403);
    }

    public function test_published_landing_page_is_publicly_accessible(): void
    {
        $landingPage = LandingPage::factory()->published()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $response = $this->get("/p/{$landingPage->slug}");

        $response->assertStatus(200);
    }

    public function test_draft_landing_page_is_not_publicly_accessible(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'draft',
        ]);

        $response = $this->get("/p/{$landingPage->slug}");

        $response->assertStatus(404);
    }
}
```

### tests/Feature/LandingPage/LandingPageSeoTest.php

```php
<?php

namespace Tests\Feature\LandingPage;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\LandingPage;

class LandingPageSeoTest extends TestCase
{
    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['owner_id' => $this->user->id]);
        $this->user->update(['current_team_id' => $this->team->id]);
    }

    public function test_user_can_update_seo_settings(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put("/dashboard/landing-pages/{$landingPage->id}/seo", [
                'title' => 'SEO Title',
                'description' => 'SEO Description',
                'keywords' => ['keyword1', 'keyword2'],
            ]);

        $response->assertRedirect();
        $landingPage->refresh();
        $this->assertEquals('SEO Title', $landingPage->settings['seo']['title']);
    }

    public function test_published_page_has_correct_meta_tags(): void
    {
        $landingPage = LandingPage::factory()->published()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'settings' => [
                'seo' => [
                    'title' => 'Custom SEO Title',
                    'description' => 'Custom meta description',
                    'keywords' => ['test', 'keywords'],
                ],
            ],
        ]);

        $response = $this->get("/p/{$landingPage->slug}");

        $response->assertSee('Custom SEO Title', false);
        $response->assertSee('Custom meta description', false);
    }

    public function test_published_page_has_open_graph_tags(): void
    {
        $landingPage = LandingPage::factory()->published()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'settings' => [
                'seo' => [
                    'title' => 'OG Title',
                    'description' => 'OG Description',
                    'og_image' => 'https://example.com/image.jpg',
                ],
            ],
        ]);

        $response = $this->get("/p/{$landingPage->slug}");

        $response->assertSee('og:title', false);
        $response->assertSee('og:description', false);
        $response->assertSee('og:image', false);
    }
}
```

### tests/Feature/Template/TemplateManagementTest.php

```php
<?php

namespace Tests\Feature\Template;

use Tests\TestCase;
use App\Models\User;
use App\Models\Template;

class TemplateManagementTest extends TestCase
{
    public function test_user_can_view_templates(): void
    {
        $user = User::factory()->create();
        Template::factory()->count(5)->create(['is_active' => true]);

        $response = $this->actingAs($user)->get('/dashboard/templates');

        $response->assertStatus(200);
        $response->assertViewHas('templates');
    }

    public function test_user_can_filter_templates_by_category(): void
    {
        $user = User::factory()->create();
        Template::factory()->create(['category' => 'business', 'is_active' => true]);
        Template::factory()->create(['category' => 'portfolio', 'is_active' => true]);

        $response = $this->actingAs($user)
            ->get('/dashboard/templates?category=business');

        $response->assertStatus(200);
    }

    public function test_user_can_preview_template(): void
    {
        $user = User::factory()->create();
        $template = Template::factory()->create();

        $response = $this->actingAs($user)
            ->get("/dashboard/templates/{$template->id}/preview");

        $response->assertStatus(200);
    }

    public function test_free_user_cannot_use_premium_template(): void
    {
        $user = User::factory()->create();
        $template = Template::factory()->premium()->create();

        $response = $this->actingAs($user)->post('/dashboard/landing-pages', [
            'title' => 'New Page',
            'template_id' => $template->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_pro_user_can_use_premium_template(): void
    {
        $user = User::factory()->withSubscription('pro')->create();
        $template = Template::factory()->premium()->create();

        $response = $this->actingAs($user)->post('/dashboard/landing-pages', [
            'title' => 'New Page',
            'template_id' => $template->id,
        ]);

        $response->assertRedirect();
    }

    public function test_admin_can_create_template(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/templates', [
            'name' => 'New Template',
            'description' => 'Template description',
            'category' => 'business',
            'content' => ['sections' => []],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('templates', ['name' => 'New Template']);
    }
}
```

### tests/Feature/Lead/LeadManagementTest.php

```php
<?php

namespace Tests\Feature\Lead;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\LandingPage;
use App\Models\Lead;
use App\Notifications\NewLeadNotification;
use Illuminate\Support\Facades\Notification;

class LeadManagementTest extends TestCase
{
    protected User $user;
    protected Team $team;
    protected LandingPage $landingPage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['owner_id' => $this->user->id]);
        $this->user->update(['current_team_id' => $this->team->id]);
        $this->landingPage = LandingPage::factory()->published()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);
    }

    public function test_visitor_can_submit_lead_form(): void
    {
        Notification::fake();

        $response = $this->post("/p/{$this->landingPage->slug}/lead", [
            'email' => 'lead@example.com',
            'name' => 'Lead Name',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('leads', [
            'landing_page_id' => $this->landingPage->id,
            'email' => 'lead@example.com',
        ]);
    }

    public function test_lead_submission_sends_notification(): void
    {
        Notification::fake();

        $this->post("/p/{$this->landingPage->slug}/lead", [
            'email' => 'lead@example.com',
            'name' => 'Lead Name',
        ]);

        Notification::assertSentTo(
            $this->user,
            NewLeadNotification::class
        );
    }

    public function test_user_can_view_leads(): void
    {
        Lead::factory()->count(5)->create([
            'landing_page_id' => $this->landingPage->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/dashboard/landing-pages/{$this->landingPage->id}/leads");

        $response->assertStatus(200);
        $response->assertViewHas('leads');
    }

    public function test_user_can_export_leads(): void
    {
        Lead::factory()->count(5)->create([
            'landing_page_id' => $this->landingPage->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/dashboard/landing-pages/{$this->landingPage->id}/leads/export");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_user_can_delete_lead(): void
    {
        $lead = Lead::factory()->create([
            'landing_page_id' => $this->landingPage->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/dashboard/leads/{$lead->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }

    public function test_user_cannot_view_other_users_leads(): void
    {
        $otherUser = User::factory()->create();
        $otherLandingPage = LandingPage::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/dashboard/landing-pages/{$otherLandingPage->id}/leads");

        $response->assertStatus(403);
    }
}
```

### tests/Feature/Analytics/AnalyticsTest.php

```php
<?php

namespace Tests\Feature\Analytics;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\LandingPage;
use App\Models\AnalyticsEvent;
use App\Models\Lead;

class AnalyticsTest extends TestCase
{
    protected User $user;
    protected Team $team;
    protected LandingPage $landingPage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['owner_id' => $this->user->id]);
        $this->user->update(['current_team_id' => $this->team->id]);
        $this->landingPage = LandingPage::factory()->published()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);
    }

    public function test_page_view_is_tracked(): void
    {
        $response = $this->get("/p/{$this->landingPage->slug}");

        $response->assertStatus(200);
        $this->assertDatabaseHas('analytics_events', [
            'landing_page_id' => $this->landingPage->id,
            'event_type' => 'page_view',
        ]);
    }

    public function test_user_can_view_analytics_dashboard(): void
    {
        AnalyticsEvent::factory()->count(10)->pageView()->create([
            'landing_page_id' => $this->landingPage->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/dashboard/landing-pages/{$this->landingPage->id}/analytics");

        $response->assertStatus(200);
        $response->assertViewHas('analytics');
    }

    public function test_analytics_includes_page_views(): void
    {
        AnalyticsEvent::factory()->count(100)->pageView()->create([
            'landing_page_id' => $this->landingPage->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/dashboard/landing-pages/{$this->landingPage->id}/analytics");

        $response->assertViewHas('analytics.total_views', 100);
    }

    public function test_analytics_includes_conversion_rate(): void
    {
        AnalyticsEvent::factory()->count(100)->pageView()->create([
            'landing_page_id' => $this->landingPage->id,
        ]);
        Lead::factory()->count(5)->create([
            'landing_page_id' => $this->landingPage->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/dashboard/landing-pages/{$this->landingPage->id}/analytics");

        $response->assertViewHas('analytics.conversion_rate', 5.0);
    }

    public function test_user_can_filter_analytics_by_date(): void
    {
        $response = $this->actingAs($this->user)
            ->get("/dashboard/landing-pages/{$this->landingPage->id}/analytics", [
                'from' => now()->subDays(7)->toDateString(),
                'to' => now()->toDateString(),
            ]);

        $response->assertStatus(200);
    }

    public function test_user_cannot_view_other_users_analytics(): void
    {
        $otherUser = User::factory()->create();
        $otherLandingPage = LandingPage::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/dashboard/landing-pages/{$otherLandingPage->id}/analytics");

        $response->assertStatus(403);
    }
}
```

### tests/Feature/Team/TeamManagementTest.php

```php
<?php

namespace Tests\Feature\Team;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Support\Facades\Notification;

class TeamManagementTest extends TestCase
{
    public function test_user_can_create_team(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/dashboard/teams', [
            'name' => 'New Team',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('teams', [
            'name' => 'New Team',
            'owner_id' => $user->id,
        ]);
    }

    public function test_owner_can_invite_member(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)
            ->post("/dashboard/teams/{$team->id}/invitations", [
                'email' => 'newmember@example.com',
                'role' => 'member',
            ]);

        $response->assertRedirect();
        Notification::assertSentTo(
            Notification::route('mail', 'newmember@example.com'),
            TeamInvitationNotification::class
        );
    }

    public function test_user_can_accept_invitation(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $invitee = User::factory()->create();

        $invitation = $team->invitations()->create([
            'email' => $invitee->email,
            'role' => 'member',
            'token' => 'test-token',
        ]);

        $response = $this->actingAs($invitee)
            ->post("/invitations/{$invitation->token}/accept");

        $response->assertRedirect();
        $this->assertTrue($team->hasUser($invitee));
    }

    public function test_owner_can_remove_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->users()->attach($member, ['role' => 'member']);

        $response = $this->actingAs($owner)
            ->delete("/dashboard/teams/{$team->id}/members/{$member->id}");

        $response->assertRedirect();
        $this->assertFalse($team->hasUser($member));
    }

    public function test_member_cannot_remove_other_members(): void
    {
        $owner = User::factory()->create();
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->users()->attach($member1, ['role' => 'member']);
        $team->users()->attach($member2, ['role' => 'member']);

        $response = $this->actingAs($member1)
            ->delete("/dashboard/teams/{$team->id}/members/{$member2->id}");

        $response->assertStatus(403);
    }

    public function test_owner_can_delete_team(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)
            ->delete("/dashboard/teams/{$team->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }
}
```

### tests/Feature/Subscription/SubscriptionTest.php

```php
<?php

namespace Tests\Feature\Subscription;

use Tests\TestCase;
use App\Models\User;
use Laravel\Cashier\Subscription;

class SubscriptionTest extends TestCase
{
    public function test_free_user_can_view_pricing_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard/billing');

        $response->assertStatus(200);
    }

    public function test_user_can_view_billing_portal(): void
    {
        $user = User::factory()->withSubscription('pro')->create();

        $response = $this->actingAs($user)->get('/dashboard/billing/portal');

        $response->assertRedirect();
    }

    public function test_free_user_has_limited_features(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->assertFalse($user->hasFeature('custom_domain'));
        $this->assertFalse($user->hasFeature('remove_branding'));
        $this->assertEquals(3, $user->getPageLimit());
    }

    public function test_pro_user_has_extended_features(): void
    {
        $user = User::factory()->withSubscription('pro')->create();

        $this->actingAs($user);

        $this->assertTrue($user->hasFeature('custom_domain'));
        $this->assertTrue($user->hasFeature('remove_branding'));
        $this->assertEquals(20, $user->getPageLimit());
    }

    public function test_webhook_handles_subscription_created(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/stripe/webhook', [
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'id' => 'sub_test123',
                    'customer' => $user->stripe_id,
                    'status' => 'active',
                    'items' => [
                        'data' => [
                            ['price' => ['id' => 'price_pro']],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200);
    }
}
```

---

## 6. API Tests

### tests/Feature/Api/LandingPageApiTest.php

```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\LandingPage;
use Laravel\Sanctum\Sanctum;

class LandingPageApiTest extends TestCase
{
    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['owner_id' => $this->user->id]);
        $this->user->update(['current_team_id' => $this->team->id]);
    }

    public function test_can_list_landing_pages(): void
    {
        Sanctum::actingAs($this->user);

        LandingPage::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $response = $this->getJson('/api/v1/landing-pages');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'slug', 'status', 'created_at'],
                ],
            ]);
    }

    public function test_can_create_landing_page(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/landing-pages', [
            'title' => 'API Created Page',
            'description' => 'Created via API',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'API Created Page');
    }

    public function test_can_show_landing_page(): void
    {
        Sanctum::actingAs($this->user);

        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $response = $this->getJson("/api/v1/landing-pages/{$landingPage->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $landingPage->id);
    }

    public function test_can_update_landing_page(): void
    {
        Sanctum::actingAs($this->user);

        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $response = $this->putJson("/api/v1/landing-pages/{$landingPage->id}", [
            'title' => 'Updated via API',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated via API');
    }

    public function test_can_update_landing_page_content(): void
    {
        Sanctum::actingAs($this->user);

        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $content = [
            'sections' => [
                ['id' => 'section-1', 'type' => 'hero', 'content' => []],
            ],
        ];

        $response = $this->putJson("/api/v1/landing-pages/{$landingPage->id}/content", [
            'content' => $content,
        ]);

        $response->assertStatus(200);
    }

    public function test_can_delete_landing_page(): void
    {
        Sanctum::actingAs($this->user);

        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $response = $this->deleteJson("/api/v1/landing-pages/{$landingPage->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted($landingPage);
    }

    public function test_can_publish_landing_page(): void
    {
        Sanctum::actingAs($this->user);

        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'draft',
        ]);

        $response = $this->postJson("/api/v1/landing-pages/{$landingPage->id}/publish");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'published');
    }

    public function test_returns_validation_errors(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/landing-pages', [
            'title' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_returns_404_for_nonexistent_page(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/landing-pages/99999');

        $response->assertStatus(404);
    }

    public function test_returns_403_for_unauthorized_access(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $landingPage = LandingPage::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->getJson("/api/v1/landing-pages/{$landingPage->id}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/landing-pages');

        $response->assertStatus(401);
    }
}
```

### tests/Feature/Api/LeadApiTest.php

```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\LandingPage;
use App\Models\Lead;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Notification;

class LeadApiTest extends TestCase
{
    protected User $user;
    protected Team $team;
    protected LandingPage $landingPage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['owner_id' => $this->user->id]);
        $this->user->update(['current_team_id' => $this->team->id]);
        $this->landingPage = LandingPage::factory()->published()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);
    }

    public function test_can_list_leads(): void
    {
        Sanctum::actingAs($this->user);

        Lead::factory()->count(5)->create([
            'landing_page_id' => $this->landingPage->id,
        ]);

        $response = $this->getJson("/api/v1/landing-pages/{$this->landingPage->id}/leads");

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_can_submit_lead(): void
    {
        Notification::fake();

        $response = $this->postJson("/api/v1/public/pages/{$this->landingPage->slug}/leads", [
            'email' => 'api-lead@example.com',
            'name' => 'API Lead',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('leads', [
            'landing_page_id' => $this->landingPage->id,
            'email' => 'api-lead@example.com',
        ]);
    }

    public function test_can_show_lead(): void
    {
        Sanctum::actingAs($this->user);

        $lead = Lead::factory()->create([
            'landing_page_id' => $this->landingPage->id,
        ]);

        $response = $this->getJson("/api/v1/leads/{$lead->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $lead->id);
    }

    public function test_can_delete_lead(): void
    {
        Sanctum::actingAs($this->user);

        $lead = Lead::factory()->create([
            'landing_page_id' => $this->landingPage->id,
        ]);

        $response = $this->deleteJson("/api/v1/leads/{$lead->id}");

        $response->assertStatus(204);
    }

    public function test_validates_lead_email(): void
    {
        $response = $this->postJson("/api/v1/public/pages/{$this->landingPage->slug}/leads", [
            'email' => 'invalid-email',
            'name' => 'Test',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
```

### tests/Feature/Api/AnalyticsApiTest.php

```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\LandingPage;
use App\Models\AnalyticsEvent;
use App\Models\Lead;
use Laravel\Sanctum\Sanctum;

class AnalyticsApiTest extends TestCase
{
    protected User $user;
    protected Team $team;
    protected LandingPage $landingPage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['owner_id' => $this->user->id]);
        $this->user->update(['current_team_id' => $this->team->id]);
        $this->landingPage = LandingPage::factory()->published()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);
    }

    public function test_can_get_analytics(): void
    {
        Sanctum::actingAs($this->user);

        AnalyticsEvent::factory()->count(50)->pageView()->create([
            'landing_page_id' => $this->landingPage->id,
        ]);
        Lead::factory()->count(5)->create([
            'landing_page_id' => $this->landingPage->id,
        ]);

        $response = $this->getJson("/api/v1/landing-pages/{$this->landingPage->id}/analytics");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_views',
                    'unique_visitors',
                    'conversion_rate',
                    'leads_count',
                ],
            ]);
    }

    public function test_can_filter_analytics_by_date(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/landing-pages/{$this->landingPage->id}/analytics", [
            'from' => now()->subDays(7)->toDateString(),
            'to' => now()->toDateString(),
        ]);

        $response->assertStatus(200);
    }

    public function test_can_track_event(): void
    {
        $response = $this->postJson("/api/v1/public/pages/{$this->landingPage->slug}/events", [
            'event_type' => 'click',
            'event_data' => ['element' => 'cta-button'],
            'session_id' => 'test-session',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('analytics_events', [
            'landing_page_id' => $this->landingPage->id,
            'event_type' => 'click',
        ]);
    }

    public function test_can_get_daily_stats(): void
    {
        Sanctum::actingAs($this->user);

        AnalyticsEvent::factory()->count(10)->pageView()->create([
            'landing_page_id' => $this->landingPage->id,
        ]);

        $response = $this->getJson("/api/v1/landing-pages/{$this->landingPage->id}/analytics/daily");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['date', 'views', 'visitors'],
                ],
            ]);
    }
}
```

### tests/Feature/Api/TemplateApiTest.php

```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Template;
use Laravel\Sanctum\Sanctum;

class TemplateApiTest extends TestCase
{
    public function test_can_list_templates(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Template::factory()->count(5)->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/templates');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_can_filter_templates_by_category(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Template::factory()->create(['category' => 'business', 'is_active' => true]);
        Template::factory()->create(['category' => 'portfolio', 'is_active' => true]);

        $response = $this->getJson('/api/v1/templates?category=business');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_show_template(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $template = Template::factory()->create();

        $response = $this->getJson("/api/v1/templates/{$template->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $template->id);
    }
}
```

---

## 7. Browser Tests with Dusk

### tests/DuskTestCase.php

```php
<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

abstract class DuskTestCase extends BaseTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ])->unless($this->hasHeadlessDisabled(), function ($items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
```

### tests/Browser/AuthenticationTest.php

```php
<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use App\Models\User;
use Laravel\Dusk\Browser;

class AuthenticationTest extends DuskTestCase
{
    public function test_user_can_register(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('name', 'Test User')
                ->type('email', 'test@example.com')
                ->type('password', 'password')
                ->type('password_confirmation', 'password')
                ->press('Register')
                ->assertPathIs('/dashboard')
                ->assertAuthenticated();
        });
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('Log in')
                ->assertPathIs('/dashboard')
                ->assertAuthenticated();
        });
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->click('@user-menu')
                ->clickLink('Log Out')
                ->assertPathIs('/')
                ->assertGuest();
        });
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'wrong-password')
                ->press('Log in')
                ->assertPathIs('/login')
                ->assertSee('credentials do not match');
        });
    }
}
```

### tests/Browser/LandingPageEditorTest.php

```php
<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\LandingPage;
use App\Models\Template;
use Laravel\Dusk\Browser;

class LandingPageEditorTest extends DuskTestCase
{
    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['owner_id' => $this->user->id]);
        $this->user->update(['current_team_id' => $this->team->id]);
    }

    public function test_user_can_create_landing_page(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/dashboard/landing-pages')
                ->press('@create-page-button')
                ->waitForText('Create Landing Page')
                ->type('title', 'New Test Page')
                ->type('description', 'Test description')
                ->press('Create')
                ->waitForLocation('/dashboard/landing-pages/*/edit')
                ->assertSee('New Test Page');
        });
    }

    public function test_user_can_create_page_from_template(): void
    {
        $template = Template::factory()->create(['is_active' => true]);

        $this->browse(function (Browser $browser) use ($template) {
            $browser->loginAs($this->user)
                ->visit('/dashboard/templates')
                ->click("@template-{$template->id}")
                ->press('Use Template')
                ->waitForText('Create from Template')
                ->type('title', 'From Template')
                ->press('Create')
                ->waitForLocation('/dashboard/landing-pages/*/edit');
        });
    }

    public function test_user_can_edit_page_content(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $this->browse(function (Browser $browser) use ($landingPage) {
            $browser->loginAs($this->user)
                ->visit("/dashboard/landing-pages/{$landingPage->id}/edit")
                ->waitFor('@editor-canvas')
                ->click('@add-section-button')
                ->waitForText('Add Section')
                ->click('@section-hero')
                ->waitFor('@section-hero-editor')
                ->type('@hero-headline', 'Test Headline')
                ->press('@save-button')
                ->waitForText('Saved');
        });
    }

    public function test_user_can_drag_and_drop_components(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $this->browse(function (Browser $browser) use ($landingPage) {
            $browser->loginAs($this->user)
                ->visit("/dashboard/landing-pages/{$landingPage->id}/edit")
                ->waitFor('@editor-canvas')
                ->drag('@component-hero', '@drop-zone')
                ->waitFor('@section-hero-editor')
                ->assertPresent('@editor-canvas @section-hero');
        });
    }

    public function test_user_can_preview_page(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $this->browse(function (Browser $browser) use ($landingPage) {
            $browser->loginAs($this->user)
                ->visit("/dashboard/landing-pages/{$landingPage->id}/edit")
                ->click('@preview-button')
                ->waitForNewWindow()
                ->switchToLastWindow()
                ->assertPathBeginsWith('/preview/');
        });
    }

    public function test_user_can_publish_page(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'draft',
        ]);

        $this->browse(function (Browser $browser) use ($landingPage) {
            $browser->loginAs($this->user)
                ->visit("/dashboard/landing-pages/{$landingPage->id}/edit")
                ->press('@publish-button')
                ->waitForText('Published')
                ->assertSee('Published');
        });
    }

    public function test_user_can_update_seo_settings(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $this->browse(function (Browser $browser) use ($landingPage) {
            $browser->loginAs($this->user)
                ->visit("/dashboard/landing-pages/{$landingPage->id}/edit")
                ->click('@seo-tab')
                ->waitFor('@seo-form')
                ->type('@seo-title', 'Custom SEO Title')
                ->type('@seo-description', 'Custom meta description')
                ->press('@save-seo-button')
                ->waitForText('SEO settings saved');
        });
    }

    public function test_user_can_undo_redo_changes(): void
    {
        $landingPage = LandingPage::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $this->browse(function (Browser $browser) use ($landingPage) {
            $browser->loginAs($this->user)
                ->visit("/dashboard/landing-pages/{$landingPage->id}/edit")
                ->waitFor('@editor-canvas')
                ->click('@add-section-button')
                ->click('@section-hero')
                ->waitFor('@section-hero-editor')
                ->click('@undo-button')
                ->assertMissing('@section-hero-editor')
                ->click('@redo-button')
                ->assertPresent('@section-hero-editor');
        });
    }
}
```

### tests/Browser/DashboardTest.php

```php
<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\LandingPage;
use Laravel\Dusk\Browser;

class DashboardTest extends DuskTestCase
{
    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['owner_id' => $this->user->id]);
        $this->user->update(['current_team_id' => $this->team->id]);
    }

    public function test_user_can_view_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/dashboard')
                ->assertSee('Dashboard')
                ->assertSee('Landing Pages')
                ->assertSee('Total Views');
        });
    }

    public function test_dashboard_shows_landing_pages(): void
    {
        LandingPage::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/dashboard')
                ->assertSee('3')
                ->assertSee('Landing Pages');
        });
    }

    public function test_user_can_navigate_to_landing_pages(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/dashboard')
                ->clickLink('Landing Pages')
                ->assertPathIs('/dashboard/landing-pages');
        });
    }

    public function test_user_can_view_analytics_overview(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/dashboard')
                ->assertPresent('@analytics-chart')
                ->assertSee('Views')
                ->assertSee('Conversions');
        });
    }
}
```

### tests/Browser/LeadCaptureTest.php

```php
<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\LandingPage;
use Laravel\Dusk\Browser;

class LeadCaptureTest extends DuskTestCase
{
    protected LandingPage $landingPage;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $user->id]);
        $this->landingPage = LandingPage::factory()->published()->create([
            'user_id' => $user->id,
            'team_id' => $team->id,
            'content' => [
                'sections' => [
                    [
                        'type' => 'hero',
                        'content' => [
                            'headline' => 'Test Page',
                            'show_form' => true,
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function test_visitor_can_submit_lead_form(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/p/{$this->landingPage->slug}")
                ->waitFor('@lead-form')
                ->type('@lead-email', 'visitor@example.com')
                ->type('@lead-name', 'Visitor Name')
                ->press('@submit-lead')
                ->waitForText('Thank you')
                ->assertSee('Thank you');
        });
    }

    public function test_shows_validation_errors_for_invalid_email(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/p/{$this->landingPage->slug}")
                ->waitFor('@lead-form')
                ->type('@lead-email', 'invalid-email')
                ->press('@submit-lead')
                ->waitForText('valid email')
                ->assertSee('valid email');
        });
    }

    public function test_shows_success_message_after_submission(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/p/{$this->landingPage->slug}")
                ->waitFor('@lead-form')
                ->type('@lead-email', 'test@example.com')
                ->press('@submit-lead')
                ->waitFor('@success-message')
                ->assertVisible('@success-message');
        });
    }
}
```

### tests/Browser/TeamManagementTest.php

```php
<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Team;
use Laravel\Dusk\Browser;

class TeamManagementTest extends DuskTestCase
{
    public function test_user_can_create_team(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard/teams')
                ->press('@create-team-button')
                ->waitForText('Create Team')
                ->type('name', 'New Team')
                ->press('Create')
                ->waitForText('New Team')
                ->assertSee('New Team');
        });
    }

    public function test_owner_can_invite_member(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $user->id]);
        $user->update(['current_team_id' => $team->id]);

        $this->browse(function (Browser $browser) use ($user, $team) {
            $browser->loginAs($user)
                ->visit("/dashboard/teams/{$team->id}")
                ->press('@invite-member-button')
                ->waitForText('Invite Member')
                ->type('email', 'newmember@example.com')
                ->select('role', 'member')
                ->press('Send Invitation')
                ->waitForText('Invitation sent');
        });
    }

    public function test_user_can_switch_teams(): void
    {
        $user = User::factory()->create();
        $team1 = Team::factory()->create(['owner_id' => $user->id, 'name' => 'Team One']);
        $team2 = Team::factory()->create(['owner_id' => $user->id, 'name' => 'Team Two']);
        $user->teams()->attach([$team1->id, $team2->id]);
        $user->update(['current_team_id' => $team1->id]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->click('@team-switcher')
                ->waitForText('Team Two')
                ->clickLink('Team Two')
                ->waitForText('Team Two')
                ->assertSee('Team Two');
        });
    }
}
```

---

## 8. Test Helpers

### tests/Helpers/TestHelpers.php

```php
<?php

namespace Tests\Helpers;

use App\Models\User;
use App\Models\Team;
use App\Models\LandingPage;
use Illuminate\Support\Str;

trait TestHelpers
{
    protected function createUserWithTeam(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $user->id]);
        $user->teams()->attach($team, ['role' => 'owner']);
        $user->update(['current_team_id' => $team->id]);

        return [$user, $team];
    }

    protected function createUserWithSubscription(string $plan = 'pro'): User
    {
        return User::factory()->withSubscription($plan)->create();
    }

    protected function createPublishedLandingPage(User $user, Team $team): LandingPage
    {
        return LandingPage::factory()->published()->create([
            'user_id' => $user->id,
            'team_id' => $team->id,
        ]);
    }

    protected function createDraftLandingPage(User $user, Team $team): LandingPage
    {
        return LandingPage::factory()->create([
            'user_id' => $user->id,
            'team_id' => $team->id,
            'status' => 'draft',
        ]);
    }

    protected function generatePageContent(): array
    {
        return [
            'sections' => [
                [
                    'id' => Str::uuid()->toString(),
                    'type' => 'hero',
                    'content' => [
                        'headline' => 'Test Headline',
                        'subheadline' => 'Test Subheadline',
                        'cta_text' => 'Get Started',
                        'cta_url' => '#signup',
                    ],
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'type' => 'features',
                    'content' => [
                        'title' => 'Features',
                        'features' => [
                            ['title' => 'Feature 1', 'description' => 'Description 1'],
                            ['title' => 'Feature 2', 'description' => 'Description 2'],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function assertUserCanAccess(User $user, string $route): void
    {
        $response = $this->actingAs($user)->get($route);
        $response->assertStatus(200);
    }

    protected function assertUserCannotAccess(User $user, string $route): void
    {
        $response = $this->actingAs($user)->get($route);
        $response->assertStatus(403);
    }

    protected function assertGuestCannotAccess(string $route): void
    {
        $response = $this->get($route);
        $response->assertRedirect('/login');
    }

    protected function mockStripeCustomer(User $user): void
    {
        $user->update([
            'stripe_id' => 'cus_' . Str::random(14),
        ]);
    }

    protected function assertEmailSent(string $to, string $notificationClass): void
    {
        \Illuminate\Support\Facades\Notification::assertSentTo(
            \Illuminate\Support\Facades\Notification::route('mail', $to),
            $notificationClass
        );
    }
}
```

### tests/Helpers/ApiTestHelpers.php

```php
<?php

namespace Tests\Helpers;

use App\Models\User;
use Laravel\Sanctum\Sanctum;

trait ApiTestHelpers
{
    protected function authenticateApi(User $user = null): User
    {
        $user = $user ?? User::factory()->create();
        Sanctum::actingAs($user);
        return $user;
    }

    protected function assertApiSuccess($response, int $status = 200): void
    {
        $response->assertStatus($status)
            ->assertJsonStructure(['data']);
    }

    protected function assertApiError($response, int $status, string $message = null): void
    {
        $response->assertStatus($status);

        if ($message) {
            $response->assertJsonPath('message', $message);
        }
    }

    protected function assertApiValidationError($response, array $fields): void
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors($fields);
    }

    protected function assertApiPaginated($response): void
    {
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
    }

    protected function getJsonApi(string $uri, array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->getJson($uri, array_merge([
            'Accept' => 'application/json',
        ], $headers));
    }

    protected function postJsonApi(string $uri, array $data = [], array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->postJson($uri, $data, array_merge([
            'Accept' => 'application/json',
        ], $headers));
    }

    protected function putJsonApi(string $uri, array $data = [], array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($uri, $data, array_merge([
            'Accept' => 'application/json',
        ], $headers));
    }

    protected function deleteJsonApi(string $uri, array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($uri, [], array_merge([
            'Accept' => 'application/json',
        ], $headers));
    }
}
```

### tests/Helpers/DuskHelpers.php

```php
<?php

namespace Tests\Helpers;

use Laravel\Dusk\Browser;
use Facebook\WebDriver\WebDriverKeys;

trait DuskHelpers
{
    protected function loginUser(Browser $browser, $user): Browser
    {
        return $browser->loginAs($user)
            ->visit('/dashboard')
            ->assertAuthenticated();
    }

    protected function waitForEditor(Browser $browser): Browser
    {
        return $browser->waitFor('@editor-canvas', 10)
            ->pause(500);
    }

    protected function selectComponent(Browser $browser, string $type): Browser
    {
        return $browser->click('@add-section-button')
            ->waitForText('Add Section')
            ->click("@section-{$type}")
            ->waitFor("@section-{$type}-editor");
    }

    protected function saveChanges(Browser $browser): Browser
    {
        return $browser->click('@save-button')
            ->waitForText('Saved')
            ->pause(300);
    }

    protected function pressKeyboardShortcut(Browser $browser, string $key, string $modifier = null): Browser
    {
        $keys = [];

        if ($modifier === 'ctrl') {
            $keys[] = WebDriverKeys::CONTROL;
        } elseif ($modifier === 'cmd') {
            $keys[] = WebDriverKeys::COMMAND;
        } elseif ($modifier === 'shift') {
            $keys[] = WebDriverKeys::SHIFT;
        }

        $keys[] = $key;

        return $browser->keys('body', $keys);
    }

    protected function assertToastMessage(Browser $browser, string $message): Browser
    {
        return $browser->waitFor('@toast-message')
            ->assertSeeIn('@toast-message', $message);
    }

    protected function dismissModal(Browser $browser): Browser
    {
        return $browser->click('@modal-close')
            ->waitUntilMissing('@modal');
    }

    protected function fillForm(Browser $browser, array $fields): Browser
    {
        foreach ($fields as $field => $value) {
            $browser->type($field, $value);
        }

        return $browser;
    }

    protected function selectDropdown(Browser $browser, string $selector, string $value): Browser
    {
        return $browser->click($selector)
            ->waitFor("{$selector}-options")
            ->click("{$selector}-option-{$value}");
    }

    protected function uploadFile(Browser $browser, string $selector, string $path): Browser
    {
        return $browser->attach($selector, $path)
            ->waitFor('@upload-progress')
            ->waitUntilMissing('@upload-progress');
    }

    protected function assertElementCount(Browser $browser, string $selector, int $count): Browser
    {
        $elements = $browser->elements($selector);
        $this->assertCount($count, $elements);
        return $browser;
    }

    protected function scrollToElement(Browser $browser, string $selector): Browser
    {
        $browser->script("document.querySelector('{$selector}').scrollIntoView()");
        return $browser->pause(300);
    }
}
```

---

## 9. Running Tests

### Run All Tests
```bash
php artisan test
```

### Run Unit Tests
```bash
php artisan test --testsuite=Unit
```

### Run Feature Tests
```bash
php artisan test --testsuite=Feature
```

### Run Browser Tests
```bash
php artisan dusk
```

### Run Specific Test File
```bash
php artisan test tests/Feature/LandingPage/LandingPageManagementTest.php
```

### Run Specific Test Method
```bash
php artisan test --filter=test_user_can_create_landing_page
```

### Run Tests with Coverage
```bash
php artisan test --coverage --min=80
```

### Run Tests in Parallel
```bash
php artisan test --parallel
```

### Run Dusk Tests with Screenshots
```bash
php artisan dusk --browse
```

---

## 10. CI/CD Configuration

### GitHub Actions (.github/workflows/tests.yml)

```yaml
name: Tests

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: testing
          MYSQL_ROOT_PASSWORD: password
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, ctype, iconv, intl, pdo_mysql, dom, filter, gd, json, libxml, pcre, phar, tokenizer
          coverage: xdebug

      - name: Install Dependencies
        run: composer install --no-ansi --no-interaction --no-scripts --prefer-dist

      - name: Copy Environment File
        run: cp .env.testing .env

      - name: Generate Application Key
        run: php artisan key:generate

      - name: Run Migrations
        run: php artisan migrate --force

      - name: Run Tests
        run: php artisan test --coverage --min=80

      - name: Upload Coverage
        uses: codecov/codecov-action@v3
        with:
          file: ./coverage.xml

  dusk:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, ctype, iconv, intl, pdo_mysql, dom

      - name: Install Dependencies
        run: composer install --no-ansi --no-interaction --no-scripts --prefer-dist

      - name: Copy Environment File
        run: cp .env.dusk.testing .env

      - name: Generate Application Key
        run: php artisan key:generate

      - name: Install Chrome Driver
        run: php artisan dusk:chrome-driver --detect

      - name: Start Chrome Driver
        run: ./vendor/laravel/dusk/bin/chromedriver-linux &

      - name: Run Laravel Server
        run: php artisan serve --no-reload &

      - name: Run Dusk Tests
        run: php artisan dusk

      - name: Upload Screenshots
        if: failure()
        uses: actions/upload-artifact@v3
        with:
          name: screenshots
          path: tests/Browser/screenshots

      - name: Upload Console Logs
        if: failure()
        uses: actions/upload-artifact@v3
        with:
          name: console
          path: tests/Browser/console
```
