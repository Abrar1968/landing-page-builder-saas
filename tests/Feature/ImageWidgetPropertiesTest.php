<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageWidgetPropertiesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Page $page;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->page = Page::factory()->create(['user_id' => $this->user->id]);
    }

    private function updatePageWithWidget(array $settings): Page
    {
        $content = [
            'sections' => [
                [
                    'id' => 'section-1',
                    'columns' => [
                        [
                            'id' => 'column-1',
                            'widgets' => [
                                [
                                    'id' => 'widget-1',
                                    'widgetType' => 'image',
                                    'settings' => $settings,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->page->update(['content' => $content]);
        return $this->page->fresh();
    }

    private function getWidgetSettings(Page $page): array
    {
        return $page->content['sections'][0]['columns'][0]['widgets'][0]['settings'];
    }

    /** @test */
    public function image_saves_image_url(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('https://example.com/image.jpg', $settings['image_url']);
    }

    /** @test */
    public function image_saves_alt_text(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'alt_text' => 'A beautiful sunset over the ocean',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('A beautiful sunset over the ocean', $settings['alt_text']);
    }

    /** @test */
    public function image_saves_caption(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'caption' => 'Photo by John Doe',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('Photo by John Doe', $settings['caption']);
    }

    /** @test */
    public function image_saves_link_settings(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'link' => 'https://example.com/gallery',
            'link_target' => true,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('https://example.com/gallery', $settings['link']);
        $this->assertTrue($settings['link_target']);
    }

    /** @test */
    public function image_saves_width_percentage(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'width' => 75,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(75, $settings['width']);
    }

    /** @test */
    public function image_saves_max_width(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'max_width' => 800,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(800, $settings['max_width']);
    }

    /** @test */
    public function image_saves_alignment(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'alignment' => 'center',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('center', $settings['alignment']);
    }

    /** @test */
    public function image_saves_opacity(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'opacity' => 0.7,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(0.7, $settings['opacity']);
    }

    /** @test */
    public function image_saves_filter_blur(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'filter_blur' => 5,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(5, $settings['filter_blur']);
    }

    /** @test */
    public function image_saves_filter_brightness(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'filter_brightness' => 120,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(120, $settings['filter_brightness']);
    }

    /** @test */
    public function image_saves_filter_contrast(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'filter_contrast' => 150,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(150, $settings['filter_contrast']);
    }

    /** @test */
    public function image_saves_filter_saturation(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'filter_saturation' => 80,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(80, $settings['filter_saturation']);
    }

    /** @test */
    public function image_saves_filter_hue(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'filter_hue' => 180,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(180, $settings['filter_hue']);
    }

    /** @test */
    public function image_saves_hover_animation_zoom(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'hover_animation' => 'zoom',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('zoom', $settings['hover_animation']);
    }

    /** @test */
    public function image_saves_hover_animation_zoom_out(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'hover_animation' => 'zoom_out',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('zoom_out', $settings['hover_animation']);
    }

    /** @test */
    public function image_saves_hover_animation_grayscale(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'hover_animation' => 'grayscale',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('grayscale', $settings['hover_animation']);
    }

    /** @test */
    public function image_saves_hover_animation_blur(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'hover_animation' => 'blur',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('blur', $settings['hover_animation']);
    }

    /** @test */
    public function image_saves_hover_animation_brightness(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'hover_animation' => 'brightness',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('brightness', $settings['hover_animation']);
    }

    /** @test */
    public function image_saves_margin_settings(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'margin' => [
                'top' => 20,
                'right' => 15,
                'bottom' => 20,
                'left' => 15,
                'linked' => false,
            ],
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(20, $settings['margin']['top']);
        $this->assertEquals(15, $settings['margin']['right']);
        $this->assertEquals(20, $settings['margin']['bottom']);
        $this->assertEquals(15, $settings['margin']['left']);
    }

    /** @test */
    public function image_saves_padding_settings(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'padding' => [
                'top' => 10,
                'right' => 10,
                'bottom' => 10,
                'left' => 10,
                'linked' => true,
            ],
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(10, $settings['padding']['top']);
        $this->assertTrue($settings['padding']['linked']);
    }

    /** @test */
    public function image_saves_z_index(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'z_index' => 50,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(50, $settings['z_index']);
    }

    /** @test */
    public function image_saves_css_classes(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'css_classes' => 'hero-image rounded-lg shadow-xl',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('hero-image rounded-lg shadow-xl', $settings['css_classes']);
    }

    /** @test */
    public function image_saves_css_id(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'css_id' => 'main-hero-image',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('main-hero-image', $settings['css_id']);
    }

    /** @test */
    public function image_saves_classic_background(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'background' => [
                'type' => 'classic',
                'color' => '#f0f0f0',
            ],
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('classic', $settings['background']['type']);
        $this->assertEquals('#f0f0f0', $settings['background']['color']);
    }

    /** @test */
    public function image_saves_gradient_background(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'background' => [
                'type' => 'gradient',
                'color1' => '#ff0000',
                'color2' => '#0000ff',
                'angle' => 45,
            ],
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('gradient', $settings['background']['type']);
        $this->assertEquals('#ff0000', $settings['background']['color1']);
        $this->assertEquals('#0000ff', $settings['background']['color2']);
        $this->assertEquals(45, $settings['background']['angle']);
    }

    /** @test */
    public function image_saves_border_settings(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'border' => [
                'type' => 'solid',
                'width' => [
                    'top' => 2,
                    'right' => 2,
                    'bottom' => 2,
                    'left' => 2,
                ],
                'color' => '#333333',
                'radius' => [
                    'topLeft' => 10,
                    'topRight' => 10,
                    'bottomRight' => 10,
                    'bottomLeft' => 10,
                ],
            ],
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('solid', $settings['border']['type']);
        $this->assertEquals(2, $settings['border']['width']['top']);
        $this->assertEquals('#333333', $settings['border']['color']);
        $this->assertEquals(10, $settings['border']['radius']['topLeft']);
    }

    /** @test */
    public function image_saves_box_shadow(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'box_shadow' => [
                'horizontal' => 5,
                'vertical' => 10,
                'blur' => 20,
                'spread' => 2,
                'color' => 'rgba(0,0,0,0.3)',
            ],
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(5, $settings['box_shadow']['horizontal']);
        $this->assertEquals(10, $settings['box_shadow']['vertical']);
        $this->assertEquals(20, $settings['box_shadow']['blur']);
        $this->assertEquals(2, $settings['box_shadow']['spread']);
        $this->assertEquals('rgba(0,0,0,0.3)', $settings['box_shadow']['color']);
    }

    /** @test */
    public function image_saves_responsive_visibility(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'responsive_visibility' => [
                'hide_desktop' => false,
                'hide_tablet' => true,
                'hide_mobile' => true,
            ],
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertFalse($settings['responsive_visibility']['hide_desktop']);
        $this->assertTrue($settings['responsive_visibility']['hide_tablet']);
        $this->assertTrue($settings['responsive_visibility']['hide_mobile']);
    }

    /** @test */
    public function image_saves_entrance_animation(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'motion_effects' => [
                'entrance_animation' => 'fadeInUp',
                'animation_duration' => 1000,
                'animation_delay' => 200,
            ],
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('fadeInUp', $settings['motion_effects']['entrance_animation']);
        $this->assertEquals(1000, $settings['motion_effects']['animation_duration']);
        $this->assertEquals(200, $settings['motion_effects']['animation_delay']);
    }

    /** @test */
    public function image_saves_sticky_settings(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'motion_effects' => [
                'sticky' => true,
                'sticky_position' => 'top',
                'sticky_offset' => 50,
            ],
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertTrue($settings['motion_effects']['sticky']);
        $this->assertEquals('top', $settings['motion_effects']['sticky_position']);
        $this->assertEquals(50, $settings['motion_effects']['sticky_offset']);
    }

    /** @test */
    public function image_saves_all_css_filters_combined(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/image.jpg',
            'filter_blur' => 2,
            'filter_brightness' => 110,
            'filter_contrast' => 120,
            'filter_saturation' => 90,
            'filter_hue' => 30,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(2, $settings['filter_blur']);
        $this->assertEquals(110, $settings['filter_brightness']);
        $this->assertEquals(120, $settings['filter_contrast']);
        $this->assertEquals(90, $settings['filter_saturation']);
        $this->assertEquals(30, $settings['filter_hue']);
    }

    /** @test */
    public function image_saves_complete_settings(): void
    {
        $page = $this->updatePageWithWidget([
            'image_url' => 'https://example.com/hero.jpg',
            'alt_text' => 'Hero banner image',
            'caption' => 'Welcome to our site',
            'link' => 'https://example.com',
            'link_target' => true,
            'width' => 80,
            'max_width' => 1000,
            'alignment' => 'center',
            'opacity' => 0.95,
            'filter_blur' => 0,
            'filter_brightness' => 105,
            'filter_contrast' => 100,
            'filter_saturation' => 110,
            'filter_hue' => 0,
            'hover_animation' => 'zoom',
            'margin' => [
                'top' => 30,
                'right' => 0,
                'bottom' => 30,
                'left' => 0,
                'linked' => false,
            ],
            'padding' => [
                'top' => 20,
                'right' => 20,
                'bottom' => 20,
                'left' => 20,
                'linked' => true,
            ],
            'z_index' => 10,
            'css_classes' => 'hero-image animate-fade',
            'css_id' => 'main-hero',
            'background' => [
                'type' => 'classic',
                'color' => '#ffffff',
            ],
            'border' => [
                'type' => 'solid',
                'width' => ['top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1],
                'color' => '#e5e7eb',
                'radius' => ['topLeft' => 8, 'topRight' => 8, 'bottomRight' => 8, 'bottomLeft' => 8],
            ],
            'box_shadow' => [
                'horizontal' => 0,
                'vertical' => 4,
                'blur' => 15,
                'spread' => 0,
                'color' => 'rgba(0,0,0,0.1)',
            ],
            'responsive_visibility' => [
                'hide_desktop' => false,
                'hide_tablet' => false,
                'hide_mobile' => false,
            ],
            'motion_effects' => [
                'entrance_animation' => 'fadeIn',
                'animation_duration' => 800,
                'animation_delay' => 100,
            ],
        ]);

        $settings = $this->getWidgetSettings($page);

        // Content
        $this->assertEquals('https://example.com/hero.jpg', $settings['image_url']);
        $this->assertEquals('Hero banner image', $settings['alt_text']);
        $this->assertEquals('Welcome to our site', $settings['caption']);
        $this->assertEquals('https://example.com', $settings['link']);
        $this->assertTrue($settings['link_target']);

        // Style
        $this->assertEquals(80, $settings['width']);
        $this->assertEquals(1000, $settings['max_width']);
        $this->assertEquals('center', $settings['alignment']);
        $this->assertEquals(0.95, $settings['opacity']);
        $this->assertEquals('zoom', $settings['hover_animation']);

        // Filters
        $this->assertEquals(105, $settings['filter_brightness']);
        $this->assertEquals(110, $settings['filter_saturation']);

        // Advanced
        $this->assertEquals(30, $settings['margin']['top']);
        $this->assertTrue($settings['padding']['linked']);
        $this->assertEquals(10, $settings['z_index']);
        $this->assertEquals('hero-image animate-fade', $settings['css_classes']);
        $this->assertEquals('main-hero', $settings['css_id']);

        // Background & Border
        $this->assertEquals('classic', $settings['background']['type']);
        $this->assertEquals('solid', $settings['border']['type']);
        $this->assertEquals(15, $settings['box_shadow']['blur']);

        // Motion
        $this->assertEquals('fadeIn', $settings['motion_effects']['entrance_animation']);
    }
}
