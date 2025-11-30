<?php

namespace Tests\Feature\Builder;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderHoverStateTest extends TestCase
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
    public function builder_saves_button_hover_state(): void
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
                                'widgetType' => 'button',
                                'settings' => [
                                    'text' => 'Hover Me',
                                    'background_color' => '#4f46e5',
                                    'text_color' => '#ffffff'
                                ],
                                'hover_settings' => [
                                    'background_color' => '#6366f1',
                                    'text_color' => '#f3f4f6'
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
        $this->assertArrayHasKey('hover_settings', $widget);
        $this->assertEquals('#6366f1', $widget['hover_settings']['background_color']);
    }

    /** @test */
    public function builder_saves_section_hover_state(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'settings' => [
                    'background_color' => '#ffffff',
                    'border_width' => '1px',
                    'border_color' => '#e5e7eb'
                ],
                'hover_settings' => [
                    'background_color' => '#f9fafb',
                    'border_color' => '#d1d5db'
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

        $section = $this->page->content[0];
        $this->assertArrayHasKey('hover_settings', $section);
        $this->assertEquals('#f9fafb', $section['hover_settings']['background_color']);
    }

    /** @test */
    public function builder_saves_image_hover_effects(): void
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
                                'widgetType' => 'image',
                                'settings' => [
                                    'image_url' => 'https://example.com/image.jpg',
                                    'opacity' => 1,
                                    'brightness' => 100
                                ],
                                'hover_settings' => [
                                    'opacity' => 0.8,
                                    'brightness' => 110,
                                    'transform' => 'scale(1.1)'
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
        $this->assertEquals(0.8, $widget['hover_settings']['opacity']);
        $this->assertEquals('scale(1.1)', $widget['hover_settings']['transform']);
    }

    /** @test */
    public function builder_saves_hover_transition_duration(): void
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
                                'widgetType' => 'button',
                                'settings' => [
                                    'text' => 'Button',
                                    'transition_duration' => '0.3s'
                                ],
                                'hover_settings' => [
                                    'background_color' => '#000000'
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
        $this->assertEquals('0.3s', $widget['settings']['transition_duration']);
    }

    /** @test */
    public function builder_preserves_hover_and_normal_states(): void
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
                                'widgetType' => 'button',
                                'settings' => [
                                    'text' => 'Button',
                                    'background_color' => '#4f46e5',
                                    'text_color' => '#ffffff',
                                    'border_width' => '2px'
                                ],
                                'hover_settings' => [
                                    'background_color' => '#6366f1',
                                    'text_color' => '#f3f4f6',
                                    'border_width' => '3px'
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

        // Normal state
        $this->assertEquals('#4f46e5', $widget['settings']['background_color']);
        $this->assertEquals('#ffffff', $widget['settings']['text_color']);

        // Hover state
        $this->assertEquals('#6366f1', $widget['hover_settings']['background_color']);
        $this->assertEquals('#f3f4f6', $widget['hover_settings']['text_color']);
    }

    /** @test */
    public function builder_saves_hover_box_shadow(): void
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
                                'widgetType' => 'image-box',
                                'settings' => [
                                    'box_shadow' => 'none'
                                ],
                                'hover_settings' => [
                                    'box_shadow' => '0 10px 30px rgba(0,0,0,0.1)'
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
        $this->assertEquals('0 10px 30px rgba(0,0,0,0.1)', $widget['hover_settings']['box_shadow']);
    }

    /** @test */
    public function builder_handles_icon_hover_color(): void
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
                                'widgetType' => 'icon',
                                'settings' => [
                                    'icon' => '★',
                                    'primary_color' => '#4f46e5'
                                ],
                                'hover_settings' => [
                                    'primary_color' => '#f59e0b'
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
        $this->assertEquals('#4f46e5', $widget['settings']['primary_color']);
        $this->assertEquals('#f59e0b', $widget['hover_settings']['primary_color']);
    }

    /** @test */
    public function builder_saves_hover_transform_effects(): void
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
                                'widgetType' => 'button',
                                'settings' => [
                                    'text' => 'Button',
                                    'transform' => 'none'
                                ],
                                'hover_settings' => [
                                    'transform' => 'translateY(-5px)',
                                    'box_shadow' => '0 5px 15px rgba(0,0,0,0.2)'
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
        $this->assertEquals('translateY(-5px)', $widget['hover_settings']['transform']);
    }
}
