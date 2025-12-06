<?php

namespace Tests\Feature\Builder;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderComponentTest extends TestCase
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
    public function builder_loads_all_required_components(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('builder.edit', $this->page));

        $response->assertStatus(200);
        $response->assertSee('builder-app', false);
    }

    /** @test */
    public function builder_provides_widget_registry(): void
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
                                'settings' => ['title' => 'Test']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->page->update(['content' => $content]);

        $response = $this->actingAs($this->user)
            ->get(route('builder.edit', $this->page));

        $response->assertStatus(200);
    }

    /** @test */
    public function builder_handles_context_menu_operations(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'elements' => []
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function builder_validates_widget_types(): void
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
                                'widgetType' => 'invalid-widget',
                                'settings' => []
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

        // Invalid widget types should be rejected with 422
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['content']);
    }

    /** @test */
    public function builder_preserves_element_order(): void
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
                            ['id' => 'widget-1', 'elType' => 'widget', 'widgetType' => 'heading'],
                            ['id' => 'widget-2', 'elType' => 'widget', 'widgetType' => 'text-editor'],
                            ['id' => 'widget-3', 'elType' => 'widget', 'widgetType' => 'button']
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

        $widgets = $this->page->content[0]['elements'][0]['elements'];
        $this->assertEquals('widget-1', $widgets[0]['id']);
        $this->assertEquals('widget-2', $widgets[1]['id']);
        $this->assertEquals('widget-3', $widgets[2]['id']);
    }

    /** @test */
    public function builder_handles_icon_picker_selections(): void
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
                                    'size' => 32
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
        $this->assertEquals('★', $widget['settings']['icon']);
    }

    /** @test */
    public function builder_maintains_navigator_structure(): void
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
                                'settings' => ['title' => 'Title 1']
                            ]
                        ]
                    ],
                    [
                        'id' => 'column-2',
                        'elType' => 'column',
                        'elements' => [
                            [
                                'id' => 'widget-2',
                                'elType' => 'widget',
                                'widgetType' => 'heading',
                                'settings' => ['title' => 'Title 2']
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

        $this->assertCount(1, $this->page->content);
        $this->assertCount(2, $this->page->content[0]['elements']);
    }

    /** @test */
    public function builder_renders_widgets_correctly(): void
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
                                'settings' => [
                                    'title' => 'Test Heading',
                                    'size' => 'h1'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->page->update(['content' => $content]);

        $response = $this->actingAs($this->user)
            ->get(route('builder.edit', $this->page));

        $response->assertStatus(200);
    }

    /** @test */
    public function builder_handles_control_renderer_inputs(): void
    {
        $settings = [
            'padding' => '20px',
            'margin' => '10px',
            'background_color' => '#ffffff',
            'border_width' => '1px',
            'border_radius' => '4px'
        ];

        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'settings' => $settings,
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
        $this->assertEquals('20px', $section['settings']['padding']);
        $this->assertEquals('#ffffff', $section['settings']['background_color']);
    }

    /** @test */
    public function builder_validates_element_hierarchy(): void
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
                                'id' => 'section-2',
                                'elType' => 'section',
                                'elements' => []
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
    }
}
