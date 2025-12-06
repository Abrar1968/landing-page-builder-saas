<?php

namespace Tests\Feature\Builder;

use Tests\TestCase;
use App\Models\User;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HeadingWidgetPropertiesTest extends TestCase
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

    private function createHeadingWidget(array $settings): array
    {
        return [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'settings' => [],
                'elements' => [
                    [
                        'id' => 'column-1',
                        'elType' => 'column',
                        'settings' => [],
                        'elements' => [
                            [
                                'id' => 'widget-heading-1',
                                'elType' => 'widget',
                                'widgetType' => 'heading',
                                'settings' => $settings,
                                'hover_settings' => $settings['_hover'] ?? []
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private function saveAndVerify(array $content): Page
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);

        $response->assertStatus(200);
        $this->page->refresh();
        return $this->page;
    }

    /** @test */
    public function heading_saves_title_text(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'My Custom Heading',
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];

        $this->assertEquals('My Custom Heading', $widget['settings']['title']);
    }

    /** @test */
    public function heading_saves_html_tag(): void
    {
        foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $tag) {
            $content = $this->createHeadingWidget([
                'title' => 'Heading',
                'size' => $tag,
            ]);

            $page = $this->saveAndVerify($content);
            $widget = $page->content[0]['elements'][0]['elements'][0];

            $this->assertEquals($tag, $widget['settings']['size']);
        }
    }

    /** @test */
    public function heading_saves_alignment(): void
    {
        foreach (['left', 'center', 'right'] as $alignment) {
            $content = $this->createHeadingWidget([
                'title' => 'Heading',
                'alignment' => $alignment,
            ]);

            $page = $this->saveAndVerify($content);
            $widget = $page->content[0]['elements'][0]['elements'][0];

            $this->assertEquals($alignment, $widget['settings']['alignment']);
        }
    }

    /** @test */
    public function heading_saves_text_color(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'text_color' => '#1b73ee',
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];

        $this->assertEquals('#1b73ee', $widget['settings']['text_color']);
    }

    /** @test */
    public function heading_saves_typography_settings(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'typography' => [
                'family' => 'Playfair Display',
                'size' => 32,
                'sizeUnit' => 'px',
                'weight' => '600',
                'style' => 'italic',
                'transform' => 'uppercase',
                'letterSpacing' => 0.5,
                'lineHeight' => 1.2,
            ],
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];
        $typography = $widget['settings']['typography'];

        $this->assertEquals('Playfair Display', $typography['family']);
        $this->assertEquals(32, $typography['size']);
        $this->assertEquals('px', $typography['sizeUnit']);
        $this->assertEquals('600', $typography['weight']);
        $this->assertEquals('italic', $typography['style']);
        $this->assertEquals('uppercase', $typography['transform']);
        $this->assertEquals(0.5, $typography['letterSpacing']);
        $this->assertEquals(1.2, $typography['lineHeight']);
    }

    /** @test */
    public function heading_saves_link_settings(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'link' => [
                'url' => 'https://example.com',
                'is_external' => true,
                'nofollow' => true,
            ],
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];
        $link = $widget['settings']['link'];

        $this->assertEquals('https://example.com', $link['url']);
        $this->assertTrue($link['is_external']);
        $this->assertTrue($link['nofollow']);
    }

    /** @test */
    public function heading_saves_margin_settings(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'margin' => [
                'top' => 45,
                'right' => 19,
                'bottom' => 12,
                'left' => 0,
            ],
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];
        $margin = $widget['settings']['margin'];

        $this->assertEquals(45, $margin['top']);
        $this->assertEquals(19, $margin['right']);
        $this->assertEquals(12, $margin['bottom']);
        $this->assertEquals(0, $margin['left']);
    }

    /** @test */
    public function heading_saves_padding_settings(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'padding' => [
                'top' => 10,
                'right' => 20,
                'bottom' => 30,
                'left' => 40,
            ],
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];
        $padding = $widget['settings']['padding'];

        $this->assertEquals(10, $padding['top']);
        $this->assertEquals(20, $padding['right']);
        $this->assertEquals(30, $padding['bottom']);
        $this->assertEquals(40, $padding['left']);
    }

    /** @test */
    public function heading_saves_z_index(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'z_index' => 50,
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];

        $this->assertEquals(50, $widget['settings']['z_index']);
    }

    /** @test */
    public function heading_saves_css_classes(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'css_classes' => 'my-custom-class another-class',
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];

        $this->assertEquals('my-custom-class another-class', $widget['settings']['css_classes']);
    }

    /** @test */
    public function heading_saves_css_id(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'css_id' => 'my-heading-id',
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];

        $this->assertEquals('my-heading-id', $widget['settings']['css_id']);
    }

    /** @test */
    public function heading_saves_classic_background(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'background' => [
                'type' => 'classic',
                'color' => '#ff0000',
            ],
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];
        $background = $widget['settings']['background'];

        $this->assertEquals('classic', $background['type']);
        $this->assertEquals('#ff0000', $background['color']);
    }

    /** @test */
    public function heading_saves_gradient_background(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'background' => [
                'type' => 'gradient',
                'color1' => '#000000',
                'color2' => '#4f46e5',
                'angle' => 180,
            ],
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];
        $background = $widget['settings']['background'];

        $this->assertEquals('gradient', $background['type']);
        $this->assertEquals('#000000', $background['color1']);
        $this->assertEquals('#4f46e5', $background['color2']);
        $this->assertEquals(180, $background['angle']);
    }

    /** @test */
    public function heading_saves_border_settings(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'border' => [
                'type' => 'dashed',
                'width' => [
                    'top' => 1,
                    'right' => 1,
                    'bottom' => 1,
                    'left' => 1,
                ],
                'color' => '#131010',
                'radius' => [
                    'topLeft' => 0,
                    'topRight' => 0,
                    'bottomRight' => 0,
                    'bottomLeft' => 0,
                ],
            ],
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];
        $border = $widget['settings']['border'];

        $this->assertEquals('dashed', $border['type']);
        $this->assertEquals('#131010', $border['color']);
        $this->assertEquals(1, $border['width']['top']);
    }

    /** @test */
    public function heading_saves_box_shadow(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'box_shadow' => [
                'horizontal' => 5,
                'vertical' => 10,
                'blur' => 15,
                'spread' => 2,
                'color' => 'rgba(0,0,0,0.3)',
            ],
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];
        $shadow = $widget['settings']['box_shadow'];

        $this->assertEquals(5, $shadow['horizontal']);
        $this->assertEquals(10, $shadow['vertical']);
        $this->assertEquals(15, $shadow['blur']);
        $this->assertEquals(2, $shadow['spread']);
        $this->assertEquals('rgba(0,0,0,0.3)', $shadow['color']);
    }

    /** @test */
    public function heading_saves_responsive_visibility(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'responsive_visibility' => [
                'hide_desktop' => false,
                'hide_tablet' => true,
                'hide_mobile' => true,
            ],
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];
        $visibility = $widget['settings']['responsive_visibility'];

        $this->assertFalse($visibility['hide_desktop']);
        $this->assertTrue($visibility['hide_tablet']);
        $this->assertTrue($visibility['hide_mobile']);
    }

    /** @test */
    public function heading_saves_entrance_animation(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'motion_effects' => [
                'entrance_animation' => 'fadeInUp',
                'animation_duration' => 1000,
                'animation_delay' => 200,
            ],
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];
        $motion = $widget['settings']['motion_effects'];

        $this->assertEquals('fadeInUp', $motion['entrance_animation']);
        $this->assertEquals(1000, $motion['animation_duration']);
        $this->assertEquals(200, $motion['animation_delay']);
    }

    /** @test */
    public function heading_saves_sticky_settings(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'motion_effects' => [
                'sticky' => true,
                'sticky_position' => 'top',
                'sticky_offset' => 0,
            ],
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];
        $motion = $widget['settings']['motion_effects'];

        $this->assertTrue($motion['sticky']);
        $this->assertEquals('top', $motion['sticky_position']);
        $this->assertEquals(0, $motion['sticky_offset']);
    }

    /** @test */
    public function heading_saves_parallax_settings(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'motion_effects' => [
                'parallax' => true,
                'parallax_speed' => 0.5,
            ],
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];
        $motion = $widget['settings']['motion_effects'];

        $this->assertTrue($motion['parallax']);
        $this->assertEquals(0.5, $motion['parallax_speed']);
    }

    /** @test */
    public function heading_saves_hover_text_color(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Heading',
            'text_color' => '#000000',
        ]);

        // Add hover settings
        $content[0]['elements'][0]['elements'][0]['hover_settings'] = [
            'text_color' => '#ff0000',
        ];

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];

        $this->assertEquals('#000000', $widget['settings']['text_color']);
        $this->assertEquals('#ff0000', $widget['hover_settings']['text_color']);
    }

    /** @test */
    public function heading_saves_complete_settings(): void
    {
        $content = $this->createHeadingWidget([
            'title' => 'Complete Heading Test',
            'size' => 'h1',
            'alignment' => 'center',
            'text_color' => '#1b73ee',
            'typography' => [
                'family' => 'Playfair Display',
                'size' => 48,
                'weight' => '700',
                'style' => 'italic',
                'transform' => 'uppercase',
                'letterSpacing' => 2,
            ],
            'margin' => ['top' => 45, 'right' => 19, 'bottom' => 12, 'left' => 0],
            'padding' => ['top' => 10, 'right' => 10, 'bottom' => 10, 'left' => 10],
            'background' => [
                'type' => 'gradient',
                'color1' => '#000000',
                'color2' => '#4f46e5',
                'angle' => 180,
            ],
            'border' => [
                'type' => 'dashed',
                'width' => ['top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1],
                'color' => '#131010',
            ],
            'motion_effects' => [
                'sticky' => true,
                'sticky_position' => 'top',
                'parallax' => true,
                'parallax_speed' => 0.5,
            ],
            'responsive_visibility' => [
                'hide_desktop' => false,
                'hide_tablet' => false,
                'hide_mobile' => false,
            ],
        ]);

        $page = $this->saveAndVerify($content);
        $widget = $page->content[0]['elements'][0]['elements'][0];
        $settings = $widget['settings'];

        $this->assertEquals('Complete Heading Test', $settings['title']);
        $this->assertEquals('h1', $settings['size']);
        $this->assertEquals('center', $settings['alignment']);
        $this->assertEquals('#1b73ee', $settings['text_color']);
        $this->assertEquals('gradient', $settings['background']['type']);
        $this->assertEquals('dashed', $settings['border']['type']);
        $this->assertTrue($settings['motion_effects']['sticky']);
        $this->assertTrue($settings['motion_effects']['parallax']);
    }
}
