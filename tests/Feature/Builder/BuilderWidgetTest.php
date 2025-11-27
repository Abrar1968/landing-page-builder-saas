<?php

namespace Tests\Feature\Builder;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Page $page;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->page = Page::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function builder_saves_heading_widget(): void
    {
        $content = $this->createWidgetContent('heading', [
            'title' => 'Test Heading',
            'size' => 'h2',
            'text_color' => '#333333',
            'alignment' => 'left'
        ]);

        $response = $this->saveContent($content);
        $response->assertStatus(200);

        $this->assertWidgetSaved('heading', 'title', 'Test Heading');
    }

    /** @test */
    public function builder_saves_text_editor_widget(): void
    {
        $content = $this->createWidgetContent('text-editor', [
            'editor' => '<p>This is test content</p>',
            'text_color' => '#000000'
        ]);

        $response = $this->saveContent($content);
        $response->assertStatus(200);

        $this->assertWidgetSaved('text-editor', 'editor', '<p>This is test content</p>');
    }

    /** @test */
    public function builder_saves_button_widget(): void
    {
        $content = $this->createWidgetContent('button', [
            'text' => 'Click Me',
            'link' => 'https://example.com',
            'target' => '_blank',
            'background_color' => '#4f46e5',
            'text_color' => '#ffffff'
        ]);

        $response = $this->saveContent($content);
        $response->assertStatus(200);

        $this->assertWidgetSaved('button', 'text', 'Click Me');
        $this->assertWidgetSaved('button', 'link', 'https://example.com');
    }

    /** @test */
    public function builder_saves_image_widget(): void
    {
        $content = $this->createWidgetContent('image', [
            'image_url' => 'https://example.com/image.jpg',
            'alt_text' => 'Test Image',
            'width' => 100,
            'caption' => 'Image caption'
        ]);

        $response = $this->saveContent($content);
        $response->assertStatus(200);

        $this->assertWidgetSaved('image', 'image_url', 'https://example.com/image.jpg');
    }

    /** @test */
    public function builder_saves_video_widget(): void
    {
        $content = $this->createWidgetContent('video', [
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'aspect_ratio' => '16:9',
            'width' => 100
        ]);

        $response = $this->saveContent($content);
        $response->assertStatus(200);

        $this->assertWidgetSaved('video', 'youtube_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
    }

    /** @test */
    public function builder_saves_icon_widget(): void
    {
        $content = $this->createWidgetContent('icon', [
            'icon' => '★',
            'size' => 50,
            'primary_color' => '#4f46e5',
            'link' => 'https://example.com',
            'alignment' => 'center'
        ]);

        $response = $this->saveContent($content);
        $response->assertStatus(200);

        $this->assertWidgetSaved('icon', 'icon', '★');
    }

    /** @test */
    public function builder_saves_form_widget(): void
    {
        $content = $this->createWidgetContent('form', [
            'form_name' => 'Contact Form',
            'show_labels' => true,
            'name_field' => true,
            'email_field' => true,
            'message_field' => true,
            'button_text' => 'Send Message',
            'success_message' => 'Thank you for your message!'
        ]);

        $response = $this->saveContent($content);
        $response->assertStatus(200);

        $this->assertWidgetSaved('form', 'form_name', 'Contact Form');
        $this->assertWidgetSaved('form', 'button_text', 'Send Message');
    }

    /** @test */
    public function builder_saves_slider_widget(): void
    {
        $content = $this->createWidgetContent('slider', [
            'slide1_title' => 'First Slide',
            'slide1_description' => 'First slide content',
            'slide2_title' => 'Second Slide',
            'slide3_title' => 'Third Slide',
            'autoplay' => true,
            'autoplay_speed' => 3000,
            'show_arrows' => true,
            'show_dots' => true,
            'height' => 500
        ]);

        $response = $this->saveContent($content);
        $response->assertStatus(200);

        $this->assertWidgetSaved('slider', 'slide1_title', 'First Slide');
        $this->assertWidgetSaved('slider', 'autoplay', true);
    }

    /** @test */
    public function builder_saves_counter_widget(): void
    {
        $content = $this->createWidgetContent('counter', [
            'ending_number' => 1000,
            'prefix' => '$',
            'suffix' => '+',
            'title' => 'Happy Customers',
            'number_size' => 48,
            'number_color' => '#4f46e5'
        ]);

        $response = $this->saveContent($content);
        $response->assertStatus(200);

        $this->assertWidgetSaved('counter', 'ending_number', 1000);
    }

    /** @test */
    public function builder_saves_progress_bar_widget(): void
    {
        $content = $this->createWidgetContent('progress-bar', [
            'title' => 'Completion',
            'percent' => 75,
            'display_percent' => true,
            'bar_color' => '#4f46e5',
            'bg_color' => '#e5e7eb',
            'height' => 12
        ]);

        $response = $this->saveContent($content);
        $response->assertStatus(200);

        $this->assertWidgetSaved('progress-bar', 'percent', 75);
    }

    /** @test */
    public function builder_saves_testimonial_widget(): void
    {
        $content = $this->createWidgetContent('testimonial', [
            'content' => 'This is a great product!',
            'name' => 'John Doe',
            'title' => 'CEO',
            'image_url' => 'https://example.com/avatar.jpg',
            'name_color' => '#1f2937'
        ]);

        $response = $this->saveContent($content);
        $response->assertStatus(200);

        $this->assertWidgetSaved('testimonial', 'name', 'John Doe');
    }

    /** @test */
    public function builder_saves_price_table_widget(): void
    {
        $content = $this->createWidgetContent('price-table', [
            'title' => 'Pro Plan',
            'price' => '$99',
            'period' => '/month',
            'features' => "Feature 1\nFeature 2\nFeature 3",
            'button_text' => 'Get Started',
            'featured' => true,
            'ribbon_text' => 'Popular'
        ]);

        $response = $this->saveContent($content);
        $response->assertStatus(200);

        $this->assertWidgetSaved('price-table', 'title', 'Pro Plan');
        $this->assertWidgetSaved('price-table', 'featured', true);
    }

    /** @test */
    public function builder_saves_widget_with_hover_settings(): void
    {
        $widget = [
            'id' => 'widget-1',
            'elType' => 'widget',
            'widgetType' => 'button',
            'settings' => [
                'text' => 'Hover Me',
                'background_color' => '#4f46e5'
            ],
            'hover_settings' => [
                'background_color' => '#6366f1',
                'text_color' => '#ffffff'
            ]
        ];

        $content = [
            ['id' => 'section-1', 'elType' => 'section', 'elements' => [
                ['id' => 'column-1', 'elType' => 'column', 'elements' => [$widget]]
            ]]
        ];

        $response = $this->saveContent($content);
        $response->assertStatus(200);

        $this->page->refresh();
        $savedWidget = $this->page->content[0]['elements'][0]['elements'][0];

        $this->assertArrayHasKey('hover_settings', $savedWidget);
        $this->assertEquals('#6366f1', $savedWidget['hover_settings']['background_color']);
    }

    protected function createWidgetContent(string $widgetType, array $settings): array
    {
        return [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'elements' => [
                    [
                        'id' => 'column-1',
                        'elType' => 'column',
                        'settings' => ['_column_size' => 100],
                        'elements' => [
                            [
                                'id' => 'widget-1',
                                'elType' => 'widget',
                                'widgetType' => $widgetType,
                                'settings' => $settings
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    protected function saveContent(array $content)
    {
        return $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);
    }

    protected function assertWidgetSaved(string $widgetType, string $settingKey, $expectedValue): void
    {
        $this->page->refresh();
        $widget = $this->page->content[0]['elements'][0]['elements'][0];

        $this->assertEquals($widgetType, $widget['widgetType']);
        $this->assertEquals($expectedValue, $widget['settings'][$settingKey]);
    }
}
