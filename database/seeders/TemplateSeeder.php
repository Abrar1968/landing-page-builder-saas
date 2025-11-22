<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Starter Landing Page',
                'description' => 'A simple, clean landing page template perfect for getting started.',
                'category' => 'landing',
                'is_system' => true,
                'is_featured' => true,
                'content' => [
                    ['type' => 'hero', 'content' => ['title' => 'Welcome', 'subtitle' => 'Build something amazing', 'buttonText' => 'Get Started']],
                    ['type' => 'features', 'content' => ['title' => 'Features', 'items' => []]],
                    ['type' => 'cta', 'content' => ['title' => 'Ready to start?', 'buttonText' => 'Sign Up Now']],
                ],
            ],
            [
                'name' => 'Business Professional',
                'description' => 'Professional template for businesses and enterprises.',
                'category' => 'business',
                'is_system' => true,
                'is_featured' => true,
                'content' => [
                    ['type' => 'hero', 'content' => ['title' => 'Grow Your Business', 'subtitle' => 'Professional solutions for modern companies', 'buttonText' => 'Learn More']],
                    ['type' => 'features', 'content' => ['title' => 'Our Services', 'items' => []]],
                    ['type' => 'testimonials', 'content' => ['title' => 'What Our Clients Say', 'items' => []]],
                    ['type' => 'pricing', 'content' => ['title' => 'Pricing Plans', 'plans' => []]],
                ],
            ],
            [
                'name' => 'Portfolio Showcase',
                'description' => 'Showcase your work with this elegant portfolio template.',
                'category' => 'portfolio',
                'is_system' => true,
                'is_featured' => false,
                'content' => [
                    ['type' => 'hero', 'content' => ['title' => 'My Portfolio', 'subtitle' => 'Creative Designer & Developer', 'buttonText' => 'View Work']],
                    ['type' => 'gallery', 'content' => ['title' => 'Featured Projects', 'items' => []]],
                    ['type' => 'about', 'content' => ['title' => 'About Me', 'text' => '']],
                    ['type' => 'contact', 'content' => ['title' => 'Get in Touch']],
                ],
            ],
            [
                'name' => 'SaaS Product',
                'description' => 'Perfect for software and SaaS product launches.',
                'category' => 'landing',
                'is_system' => true,
                'is_featured' => true,
                'content' => [
                    ['type' => 'hero', 'content' => ['title' => 'The Best Tool for Your Workflow', 'subtitle' => 'Automate and streamline your processes', 'buttonText' => 'Start Free Trial']],
                    ['type' => 'features', 'content' => ['title' => 'Powerful Features', 'items' => []]],
                    ['type' => 'pricing', 'content' => ['title' => 'Simple Pricing', 'plans' => []]],
                    ['type' => 'faq', 'content' => ['title' => 'Frequently Asked Questions', 'items' => []]],
                ],
            ],
            [
                'name' => 'E-commerce Store',
                'description' => 'Launch your online store with this e-commerce template.',
                'category' => 'ecommerce',
                'is_system' => true,
                'is_featured' => false,
                'content' => [
                    ['type' => 'hero', 'content' => ['title' => 'Shop the Latest', 'subtitle' => 'Discover our new collection', 'buttonText' => 'Shop Now']],
                    ['type' => 'products', 'content' => ['title' => 'Featured Products', 'items' => []]],
                    ['type' => 'testimonials', 'content' => ['title' => 'Customer Reviews', 'items' => []]],
                    ['type' => 'newsletter', 'content' => ['title' => 'Join Our Newsletter']],
                ],
            ],
        ];

        foreach ($templates as $template) {
            Template::create(array_merge($template, ['is_published' => true]));
        }
    }
}
