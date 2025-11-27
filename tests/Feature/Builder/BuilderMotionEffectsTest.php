<?php

namespace Tests\Feature\Builder;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderMotionEffectsTest extends TestCase
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
    public function builder_saves_fade_in_animation(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'motion_effects' => [
                    'entrance_animation' => 'fadeIn',
                    'animation_duration' => 500,
                    'animation_delay' => 0
                ],
                'elements' => []
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);

        $response->assertStatus(200);
        $this->page->refresh();

        $this->assertEquals('fadeIn', $this->page->content[0]['motion_effects']['entrance_animation']);
    }

    /** @test */
    public function builder_saves_scroll_effects(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'motion_effects' => [
                    'scroll_effects' => true,
                    'vertical_scroll' => 'translateY',
                    'horizontal_scroll' => 'translateX',
                    'transparency' => 'opacity',
                    'blur' => 'blur'
                ],
                'elements' => []
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);

        $response->assertStatus(200);
        $this->page->refresh();

        $effects = $this->page->content[0]['motion_effects'];
        $this->assertTrue($effects['scroll_effects']);
        $this->assertEquals('translateY', $effects['vertical_scroll']);
    }

    /** @test */
    public function builder_saves_sticky_position(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'motion_effects' => [
                    'sticky' => 'top',
                    'sticky_offset' => 0,
                    'sticky_effects_offset' => 100
                ],
                'elements' => []
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);

        $response->assertStatus(200);
        $this->page->refresh();

        $this->assertEquals('top', $this->page->content[0]['motion_effects']['sticky']);
        $this->assertEquals(0, $this->page->content[0]['motion_effects']['sticky_offset']);
    }

    /** @test */
    public function builder_saves_parallax_effect(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'settings' => [
                    'background_image' => 'https://example.com/bg.jpg'
                ],
                'motion_effects' => [
                    'parallax' => true,
                    'parallax_speed' => 0.5
                ],
                'elements' => []
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);

        $response->assertStatus(200);
        $this->page->refresh();

        $this->assertTrue($this->page->content[0]['motion_effects']['parallax']);
        $this->assertEquals(0.5, $this->page->content[0]['motion_effects']['parallax_speed']);
    }

    /** @test */
    public function builder_saves_complex_entrance_animation(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'motion_effects' => [
                    'entrance_animation' => 'zoomIn',
                    'animation_duration' => 1000,
                    'animation_delay' => 200,
                    'animation_repeat' => false
                ],
                'elements' => []
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);

        $response->assertStatus(200);
        $this->page->refresh();

        $effects = $this->page->content[0]['motion_effects'];
        $this->assertEquals('zoomIn', $effects['entrance_animation']);
        $this->assertEquals(1000, $effects['animation_duration']);
        $this->assertEquals(200, $effects['animation_delay']);
        $this->assertFalse($effects['animation_repeat']);
    }

    /** @test */
    public function builder_saves_rotate_effect(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'motion_effects' => [
                    'scroll_effects' => true,
                    'rotate' => [
                        'x' => 10,
                        'y' => 20,
                        'z' => 30
                    ]
                ],
                'elements' => []
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);

        $response->assertStatus(200);
        $this->page->refresh();

        $rotate = $this->page->content[0]['motion_effects']['rotate'];
        $this->assertEquals(10, $rotate['x']);
        $this->assertEquals(20, $rotate['y']);
        $this->assertEquals(30, $rotate['z']);
    }

    /** @test */
    public function builder_saves_scale_effect(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'motion_effects' => [
                    'scroll_effects' => true,
                    'scale' => [
                        'x' => 1.2,
                        'y' => 1.2,
                        'z' => 1.0
                    ]
                ],
                'elements' => []
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);

        $response->assertStatus(200);
        $this->page->refresh();

        $scale = $this->page->content[0]['motion_effects']['scale'];
        $this->assertEquals(1.2, $scale['x']);
        $this->assertEquals(1.2, $scale['y']);
    }

    /** @test */
    public function builder_saves_mouse_track_effect(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'motion_effects' => [
                    'mouse_track' => true,
                    'mouse_track_direction' => 'opposite',
                    'mouse_track_speed' => 0.7
                ],
                'elements' => []
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);

        $response->assertStatus(200);
        $this->page->refresh();

        $effects = $this->page->content[0]['motion_effects'];
        $this->assertTrue($effects['mouse_track']);
        $this->assertEquals('opposite', $effects['mouse_track_direction']);
    }

    /** @test */
    public function builder_preserves_multiple_effects(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'motion_effects' => [
                    'entrance_animation' => 'fadeInUp',
                    'animation_duration' => 800,
                    'scroll_effects' => true,
                    'vertical_scroll' => 'translateY',
                    'sticky' => 'top',
                    'parallax' => true
                ],
                'elements' => []
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);

        $response->assertStatus(200);
        $this->page->refresh();

        $effects = $this->page->content[0]['motion_effects'];
        $this->assertEquals('fadeInUp', $effects['entrance_animation']);
        $this->assertTrue($effects['scroll_effects']);
        $this->assertEquals('top', $effects['sticky']);
        $this->assertTrue($effects['parallax']);
    }

    /** @test */
    public function builder_saves_widget_level_animations(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'elements' => [
                    [
                        'id' => 'column-1',
                        'elType' => 'column',
                        'elements' => [
                            [
                                'id' => 'widget-1',
                                'elType' => 'widget',
                                'widgetType' => 'heading',
                                'settings' => ['title' => 'Test'],
                                'motion_effects' => [
                                    'entrance_animation' => 'slideInLeft',
                                    'animation_delay' => 300
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);

        $response->assertStatus(200);
        $this->page->refresh();

        $widget = $this->page->content[0]['elements'][0]['elements'][0];
        $this->assertEquals('slideInLeft', $widget['motion_effects']['entrance_animation']);
        $this->assertEquals(300, $widget['motion_effects']['animation_delay']);
    }
}
