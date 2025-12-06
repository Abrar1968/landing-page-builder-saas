<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ButtonWidgetPropertiesTest extends TestCase
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
                                    'widgetType' => 'button',
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
    public function button_saves_text(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Get Started Now',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('Get Started Now', $settings['text']);
    }

    /** @test */
    public function button_saves_link(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Click Me',
            'link' => 'https://example.com/signup',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('https://example.com/signup', $settings['link']);
    }

    /** @test */
    public function button_saves_target_blank(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'External Link',
            'link' => 'https://google.com',
            'target' => true,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertTrue($settings['target']);
    }

    /** @test */
    public function button_saves_icon(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Download',
            'icon' => '⬇',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('⬇', $settings['icon']);
    }

    /** @test */
    public function button_saves_icon_position(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Next',
            'icon' => '→',
            'icon_position' => 'right',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('right', $settings['icon_position']);
    }

    /** @test */
    public function button_saves_icon_spacing(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Action',
            'icon' => '★',
            'icon_spacing' => 12,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(12, $settings['icon_spacing']);
    }

    /** @test */
    public function button_saves_alignment(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Centered Button',
            'alignment' => 'center',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('center', $settings['alignment']);
    }

    /** @test */
    public function button_saves_background_color(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Blue Button',
            'background_color' => '#3b82f6',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('#3b82f6', $settings['background_color']);
    }

    /** @test */
    public function button_saves_text_color(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Dark Text Button',
            'text_color' => '#1f2937',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('#1f2937', $settings['text_color']);
    }

    /** @test */
    public function button_saves_border_color(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Bordered Button',
            'border_color' => '#10b981',
            'border_width' => 2,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('#10b981', $settings['border_color']);
        $this->assertEquals(2, $settings['border_width']);
    }

    /** @test */
    public function button_saves_hover_background_color(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Hover Button',
            'background_color' => '#4f46e5',
            'hover_background_color' => '#4338ca',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('#4338ca', $settings['hover_background_color']);
    }

    /** @test */
    public function button_saves_hover_text_color(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Hover Text',
            'text_color' => '#ffffff',
            'hover_text_color' => '#f3f4f6',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('#f3f4f6', $settings['hover_text_color']);
    }

    /** @test */
    public function button_saves_hover_border_color(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Hover Border',
            'border_color' => '#4f46e5',
            'hover_border_color' => '#6366f1',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('#6366f1', $settings['hover_border_color']);
    }

    /** @test */
    public function button_saves_border_width(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Thick Border',
            'border_width' => 3,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(3, $settings['border_width']);
    }

    /** @test */
    public function button_saves_border_radius(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Rounded Button',
            'border_radius' => 25,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(25, $settings['border_radius']);
    }

    /** @test */
    public function button_saves_horizontal_padding(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Wide Button',
            'padding_horizontal' => 48,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(48, $settings['padding_horizontal']);
    }

    /** @test */
    public function button_saves_vertical_padding(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Tall Button',
            'padding_vertical' => 20,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(20, $settings['padding_vertical']);
    }

    /** @test */
    public function button_saves_typography_settings(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Styled Button',
            'typography' => [
                'font_family' => 'Inter',
                'size' => 18,
                'weight' => '600',
                'line_height' => '1.5',
                'letter_spacing' => 1,
                'text_transform' => 'uppercase',
            ],
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('Inter', $settings['typography']['font_family']);
        $this->assertEquals(18, $settings['typography']['size']);
        $this->assertEquals('600', $settings['typography']['weight']);
        $this->assertEquals('uppercase', $settings['typography']['text_transform']);
    }

    /** @test */
    public function button_saves_margin_settings(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Spaced Button',
            'margin' => [
                'top' => 20,
                'right' => 10,
                'bottom' => 20,
                'left' => 10,
                'linked' => false,
            ],
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(20, $settings['margin']['top']);
        $this->assertEquals(10, $settings['margin']['right']);
    }

    /** @test */
    public function button_saves_z_index(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Layered Button',
            'z_index' => 100,
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(100, $settings['z_index']);
    }

    /** @test */
    public function button_saves_css_classes(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Custom Button',
            'css_classes' => 'cta-button primary-action',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('cta-button primary-action', $settings['css_classes']);
    }

    /** @test */
    public function button_saves_css_id(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'ID Button',
            'css_id' => 'main-cta-button',
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('main-cta-button', $settings['css_id']);
    }

    /** @test */
    public function button_saves_classic_background(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Bg Button',
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
    public function button_saves_border_settings(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Full Border Button',
            'border' => [
                'type' => 'solid',
                'width' => [
                    'top' => 2,
                    'right' => 2,
                    'bottom' => 2,
                    'left' => 2,
                ],
                'color' => '#ef4444',
                'radius' => [
                    'topLeft' => 8,
                    'topRight' => 8,
                    'bottomRight' => 8,
                    'bottomLeft' => 8,
                ],
            ],
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('solid', $settings['border']['type']);
        $this->assertEquals(2, $settings['border']['width']['top']);
        $this->assertEquals('#ef4444', $settings['border']['color']);
        $this->assertEquals(8, $settings['border']['radius']['topLeft']);
    }

    /** @test */
    public function button_saves_box_shadow(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Shadow Button',
            'box_shadow' => [
                'horizontal' => 0,
                'vertical' => 4,
                'blur' => 15,
                'spread' => 0,
                'color' => 'rgba(79,70,229,0.4)',
            ],
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals(0, $settings['box_shadow']['horizontal']);
        $this->assertEquals(4, $settings['box_shadow']['vertical']);
        $this->assertEquals(15, $settings['box_shadow']['blur']);
        $this->assertEquals('rgba(79,70,229,0.4)', $settings['box_shadow']['color']);
    }

    /** @test */
    public function button_saves_responsive_visibility(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Responsive Button',
            'responsive_visibility' => [
                'hide_desktop' => false,
                'hide_tablet' => false,
                'hide_mobile' => true,
            ],
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertFalse($settings['responsive_visibility']['hide_desktop']);
        $this->assertTrue($settings['responsive_visibility']['hide_mobile']);
    }

    /** @test */
    public function button_saves_entrance_animation(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Animated Button',
            'motion_effects' => [
                'entrance_animation' => 'bounceIn',
                'animation_duration' => 1000,
                'animation_delay' => 300,
            ],
        ]);

        $settings = $this->getWidgetSettings($page);
        $this->assertEquals('bounceIn', $settings['motion_effects']['entrance_animation']);
        $this->assertEquals(1000, $settings['motion_effects']['animation_duration']);
        $this->assertEquals(300, $settings['motion_effects']['animation_delay']);
    }

    /** @test */
    public function button_saves_complete_settings(): void
    {
        $page = $this->updatePageWithWidget([
            'text' => 'Complete CTA Button',
            'link' => 'https://example.com/signup',
            'target' => true,
            'icon' => '→',
            'icon_position' => 'right',
            'icon_spacing' => 10,
            'alignment' => 'center',
            'background_color' => '#4f46e5',
            'text_color' => '#ffffff',
            'border_color' => '#4f46e5',
            'hover_background_color' => '#4338ca',
            'hover_text_color' => '#f3f4f6',
            'hover_border_color' => '#4338ca',
            'border_width' => 2,
            'border_radius' => 8,
            'padding_horizontal' => 32,
            'padding_vertical' => 16,
            'typography' => [
                'font_family' => 'Inter',
                'size' => 16,
                'weight' => '600',
                'text_transform' => 'none',
            ],
            'margin' => [
                'top' => 20,
                'right' => 0,
                'bottom' => 20,
                'left' => 0,
                'linked' => false,
            ],
            'z_index' => 10,
            'css_classes' => 'cta-button',
            'css_id' => 'signup-cta',
            'box_shadow' => [
                'horizontal' => 0,
                'vertical' => 4,
                'blur' => 10,
                'spread' => 0,
                'color' => 'rgba(0,0,0,0.15)',
            ],
            'responsive_visibility' => [
                'hide_desktop' => false,
                'hide_tablet' => false,
                'hide_mobile' => false,
            ],
            'motion_effects' => [
                'entrance_animation' => 'fadeInUp',
                'animation_duration' => 800,
            ],
        ]);

        $settings = $this->getWidgetSettings($page);
        
        // Content
        $this->assertEquals('Complete CTA Button', $settings['text']);
        $this->assertEquals('https://example.com/signup', $settings['link']);
        $this->assertTrue($settings['target']);
        $this->assertEquals('→', $settings['icon']);
        $this->assertEquals('right', $settings['icon_position']);
        
        // Colors
        $this->assertEquals('#4f46e5', $settings['background_color']);
        $this->assertEquals('#ffffff', $settings['text_color']);
        $this->assertEquals('#4338ca', $settings['hover_background_color']);
        
        // Style
        $this->assertEquals(8, $settings['border_radius']);
        $this->assertEquals(32, $settings['padding_horizontal']);
        $this->assertEquals('600', $settings['typography']['weight']);
        
        // Advanced
        $this->assertEquals(20, $settings['margin']['top']);
        $this->assertEquals(10, $settings['z_index']);
        $this->assertEquals('cta-button', $settings['css_classes']);
        $this->assertEquals('rgba(0,0,0,0.15)', $settings['box_shadow']['color']);
        $this->assertEquals('fadeInUp', $settings['motion_effects']['entrance_animation']);
    }
}
