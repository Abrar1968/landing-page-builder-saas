<?php

namespace Database\Seeders;

use App\Models\Template;
use App\Models\TemplateCategory;
use App\Models\TemplateTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Create Categories
        $categories = [
            ['name' => 'SaaS', 'slug' => 'saas', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>', 'sort_order' => 1],
            ['name' => 'Mobile App', 'slug' => 'mobile-app', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>', 'sort_order' => 2],
            ['name' => 'Coming Soon', 'slug' => 'coming-soon', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'sort_order' => 3],
            ['name' => 'Lead Generation', 'slug' => 'lead-generation', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>', 'sort_order' => 4],
            ['name' => 'E-commerce', 'slug' => 'ecommerce', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>', 'sort_order' => 5],
        ];

        $categoryIds = [];
        foreach ($categories as $category) {
            $created = TemplateCategory::create($category);
            $categoryIds[$category['slug']] = $created->id;
        }

        // Create Tags
        $tags = ['modern', 'minimal', 'bold', 'professional', 'creative', 'dark', 'light', 'gradient', 'animated', 'responsive'];
        foreach ($tags as $tag) {
            TemplateTag::create(['name' => $tag, 'slug' => Str::slug($tag)]);
        }

        // Create Templates
        $templates = [
            [
                'category_id' => $categoryIds['saas'],
                'name' => 'SaaS Pro',
                'slug' => 'saas-pro',
                'description' => 'Professional SaaS landing page with hero, features, pricing, and CTA sections.',
                'is_featured' => true,
                'is_public' => true,
                'usage_count' => 1250,
                'rating' => 4.8,
                'rating_count' => 156,
                'tags' => ['modern', 'professional', 'gradient'],
                'content' => [
                    ['id' => Str::uuid()->toString(), 'type' => 'hero', 'components' => [
                        ['id' => Str::uuid()->toString(), 'type' => 'heading', 'content' => 'Build better products'],
                        ['id' => Str::uuid()->toString(), 'type' => 'text', 'content' => 'Streamline your workflow and ship faster.'],
                        ['id' => Str::uuid()->toString(), 'type' => 'button-group', 'buttons' => [
                            ['text' => 'Start Free Trial', 'variant' => 'primary', 'link' => '#signup'],
                        ]],
                    ]],
                    ['id' => Str::uuid()->toString(), 'type' => 'features', 'components' => [
                        ['id' => Str::uuid()->toString(), 'type' => 'section-header', 'title' => 'Features', 'subtitle' => 'Everything you need'],
                        ['id' => Str::uuid()->toString(), 'type' => 'feature-grid', 'features' => [
                            ['title' => 'Fast', 'description' => 'Lightning fast performance'],
                            ['title' => 'Secure', 'description' => 'Enterprise-grade security'],
                            ['title' => 'Scalable', 'description' => 'Grows with your business'],
                        ]],
                    ]],
                    ['id' => Str::uuid()->toString(), 'type' => 'pricing', 'components' => [
                        ['id' => Str::uuid()->toString(), 'type' => 'section-header', 'title' => 'Pricing'],
                        ['id' => Str::uuid()->toString(), 'type' => 'pricing-grid', 'plans' => [
                            ['name' => 'Starter', 'monthlyPrice' => 29, 'features' => ['5 users', '10GB']],
                            ['name' => 'Pro', 'monthlyPrice' => 79, 'featured' => true, 'features' => ['Unlimited', '100GB']],
                        ]],
                    ]],
                    ['id' => Str::uuid()->toString(), 'type' => 'cta', 'components' => [
                        ['id' => Str::uuid()->toString(), 'type' => 'heading', 'content' => 'Ready to start?'],
                        ['id' => Str::uuid()->toString(), 'type' => 'button-group', 'buttons' => [
                            ['text' => 'Get Started', 'variant' => 'primary', 'link' => '#signup'],
                        ]],
                    ]],
                ],
                'settings' => ['font' => 'Inter', 'primaryColor' => '#4F46E5'],
            ],
            [
                'category_id' => $categoryIds['mobile-app'],
                'name' => 'App Launch',
                'slug' => 'app-launch',
                'description' => 'Perfect for mobile app launches with app store badges.',
                'is_featured' => true,
                'is_public' => true,
                'usage_count' => 890,
                'rating' => 4.6,
                'rating_count' => 98,
                'tags' => ['modern', 'minimal', 'responsive'],
                'content' => [
                    ['id' => Str::uuid()->toString(), 'type' => 'hero', 'components' => [
                        ['id' => Str::uuid()->toString(), 'type' => 'heading', 'content' => 'Your tasks, simplified'],
                        ['id' => Str::uuid()->toString(), 'type' => 'text', 'content' => 'The most intuitive task app.'],
                        ['id' => Str::uuid()->toString(), 'type' => 'button-group', 'buttons' => [
                            ['text' => 'Download iOS', 'variant' => 'primary', 'link' => '#ios'],
                            ['text' => 'Download Android', 'variant' => 'outline', 'link' => '#android'],
                        ]],
                    ]],
                    ['id' => Str::uuid()->toString(), 'type' => 'features', 'components' => [
                        ['id' => Str::uuid()->toString(), 'type' => 'section-header', 'title' => 'Features'],
                        ['id' => Str::uuid()->toString(), 'type' => 'feature-grid', 'features' => [
                            ['title' => 'Smart Lists', 'description' => 'Auto-organize tasks'],
                            ['title' => 'Reminders', 'description' => 'Never miss deadlines'],
                            ['title' => 'Cloud Sync', 'description' => 'Access anywhere'],
                        ]],
                    ]],
                ],
                'settings' => ['font' => 'Inter', 'primaryColor' => '#10B981'],
            ],
            [
                'category_id' => $categoryIds['lead-generation'],
                'name' => 'Lead Capture',
                'slug' => 'lead-capture',
                'description' => 'Optimized for lead generation with forms and social proof.',
                'is_featured' => true,
                'is_public' => true,
                'usage_count' => 720,
                'rating' => 4.7,
                'rating_count' => 112,
                'tags' => ['professional', 'bold', 'responsive'],
                'content' => [
                    ['id' => Str::uuid()->toString(), 'type' => 'hero', 'components' => [
                        ['id' => Str::uuid()->toString(), 'type' => 'heading', 'content' => 'Grow your business'],
                        ['id' => Str::uuid()->toString(), 'type' => 'text', 'content' => 'Get a free consultation today.'],
                        ['id' => Str::uuid()->toString(), 'type' => 'button-group', 'buttons' => [
                            ['text' => 'Get Free Consultation', 'variant' => 'primary', 'link' => '#contact'],
                        ]],
                    ]],
                    ['id' => Str::uuid()->toString(), 'type' => 'features', 'components' => [
                        ['id' => Str::uuid()->toString(), 'type' => 'section-header', 'title' => 'Why choose us?', 'subtitle' => 'Trusted by 500+ businesses'],
                        ['id' => Str::uuid()->toString(), 'type' => 'feature-grid', 'features' => [
                            ['title' => '3x More Leads', 'description' => 'Proven strategies'],
                            ['title' => 'Save Time', 'description' => 'Automated nurturing'],
                            ['title' => 'Better ROI', 'description' => 'Lower cost per lead'],
                        ]],
                    ]],
                ],
                'settings' => ['font' => 'Inter', 'primaryColor' => '#F59E0B'],
            ],
            [
                'category_id' => $categoryIds['ecommerce'],
                'name' => 'Product Launch',
                'slug' => 'product-launch',
                'description' => 'E-commerce product launch page with purchase CTA.',
                'is_featured' => false,
                'is_public' => true,
                'usage_count' => 380,
                'rating' => 4.5,
                'rating_count' => 54,
                'tags' => ['modern', 'creative', 'light'],
                'content' => [
                    ['id' => Str::uuid()->toString(), 'type' => 'hero', 'components' => [
                        ['id' => Str::uuid()->toString(), 'type' => 'heading', 'content' => 'Introducing the all-new product'],
                        ['id' => Str::uuid()->toString(), 'type' => 'text', 'content' => 'Revolutionary design meets performance.'],
                        ['id' => Str::uuid()->toString(), 'type' => 'button-group', 'buttons' => [
                            ['text' => 'Buy Now', 'variant' => 'primary', 'link' => '#buy'],
                            ['text' => 'Learn More', 'variant' => 'outline', 'link' => '#features'],
                        ]],
                    ]],
                    ['id' => Str::uuid()->toString(), 'type' => 'features', 'components' => [
                        ['id' => Str::uuid()->toString(), 'type' => 'section-header', 'title' => 'Premium features'],
                        ['id' => Str::uuid()->toString(), 'type' => 'feature-grid', 'features' => [
                            ['title' => 'Quality', 'description' => 'Handcrafted with care'],
                            ['title' => 'Free Shipping', 'description' => 'Orders over $50'],
                            ['title' => '30-Day Returns', 'description' => 'No questions asked'],
                        ]],
                    ]],
                ],
                'settings' => ['font' => 'Inter', 'primaryColor' => '#EC4899'],
            ],
            [
                'category_id' => $categoryIds['coming-soon'],
                'name' => 'Launch Countdown',
                'slug' => 'launch-countdown',
                'description' => 'Coming soon page with countdown and email signup.',
                'is_featured' => false,
                'is_public' => true,
                'usage_count' => 450,
                'rating' => 4.4,
                'rating_count' => 67,
                'tags' => ['minimal', 'dark', 'animated'],
                'content' => [
                    ['id' => Str::uuid()->toString(), 'type' => 'hero', 'components' => [
                        ['id' => Str::uuid()->toString(), 'type' => 'heading', 'content' => 'Something amazing is coming'],
                        ['id' => Str::uuid()->toString(), 'type' => 'text', 'content' => 'Be the first to know when we launch.'],
                        ['id' => Str::uuid()->toString(), 'type' => 'button-group', 'buttons' => [
                            ['text' => 'Notify Me', 'variant' => 'primary', 'link' => '#notify'],
                        ]],
                    ]],
                ],
                'settings' => ['font' => 'Inter', 'primaryColor' => '#8B5CF6'],
            ],
        ];

        foreach ($templates as $templateData) {
            $tagNames = $templateData['tags'] ?? [];
            unset($templateData['tags']);

            $template = Template::create($templateData);

            if (!empty($tagNames)) {
                $tagIds = TemplateTag::whereIn('name', $tagNames)->pluck('id');
                $template->tags()->sync($tagIds);
            }
        }
    }
}
