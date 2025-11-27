<?php

namespace Tests\Feature\Builder;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderResponsiveTest extends TestCase
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
    public function builder_saves_desktop_settings(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'settings' => [
                    'padding' => '50px',
                    'margin' => '20px'
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

        $this->assertEquals('50px', $this->page->content[0]['settings']['padding']);
    }

    /** @test */
    public function builder_saves_tablet_responsive_settings(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'settings' => [
                    'padding' => '50px',
                    'padding_tablet' => '30px'
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

        $this->assertEquals('30px', $this->page->content[0]['settings']['padding_tablet']);
    }

    /** @test */
    public function builder_saves_mobile_responsive_settings(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'settings' => [
                    'padding' => '50px',
                    'padding_mobile' => '20px'
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

        $this->assertEquals('20px', $this->page->content[0]['settings']['padding_mobile']);
    }

    /** @test */
    public function builder_preserves_all_device_settings(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'settings' => [
                    'padding' => '50px',
                    'padding_tablet' => '30px',
                    'padding_mobile' => '20px',
                    'margin' => '40px',
                    'margin_tablet' => '25px',
                    'margin_mobile' => '15px'
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

        $settings = $this->page->content[0]['settings'];
        $this->assertEquals('50px', $settings['padding']);
        $this->assertEquals('30px', $settings['padding_tablet']);
        $this->assertEquals('20px', $settings['padding_mobile']);
    }

    /** @test */
    public function builder_handles_responsive_visibility(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'settings' => [
                    'hide_desktop' => false,
                    'hide_tablet' => true,
                    'hide_mobile' => false
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

        $settings = $this->page->content[0]['settings'];
        $this->assertFalse($settings['hide_desktop']);
        $this->assertTrue($settings['hide_tablet']);
    }

    /** @test */
    public function builder_saves_responsive_column_widths(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'elements' => [
                    [
                        'id' => 'column-1',
                        'elType' => 'column',
                        'settings' => [
                            '_column_size' => 50,
                            '_column_size_tablet' => 100,
                            '_column_size_mobile' => 100
                        ],
                        'elements' => []
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

        $column = $this->page->content[0]['elements'][0];
        $this->assertEquals(50, $column['settings']['_column_size']);
        $this->assertEquals(100, $column['settings']['_column_size_tablet']);
        $this->assertEquals(100, $column['settings']['_column_size_mobile']);
    }

    /** @test */
    public function builder_handles_responsive_typography(): void
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
                                    'title' => 'Test',
                                    'font_size' => '48px',
                                    'font_size_tablet' => '36px',
                                    'font_size_mobile' => '24px'
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
        $this->assertEquals('48px', $widget['settings']['font_size']);
        $this->assertEquals('36px', $widget['settings']['font_size_tablet']);
        $this->assertEquals('24px', $widget['settings']['font_size_mobile']);
    }

    /** @test */
    public function builder_maintains_responsive_order(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'elements' => [
                    [
                        'id' => 'column-1',
                        'elType' => 'column',
                        'settings' => [
                            'order' => 1,
                            'order_mobile' => 2
                        ],
                        'elements' => []
                    ],
                    [
                        'id' => 'column-2',
                        'elType' => 'column',
                        'settings' => [
                            'order' => 2,
                            'order_mobile' => 1
                        ],
                        'elements' => []
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

        $column1 = $this->page->content[0]['elements'][0];
        $column2 = $this->page->content[0]['elements'][1];

        $this->assertEquals(1, $column1['settings']['order']);
        $this->assertEquals(2, $column1['settings']['order_mobile']);
        $this->assertEquals(2, $column2['settings']['order']);
        $this->assertEquals(1, $column2['settings']['order_mobile']);
    }

    /** @test */
    public function builder_handles_responsive_alignment(): void
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
                                    'title' => 'Test',
                                    'alignment' => 'left',
                                    'alignment_tablet' => 'center',
                                    'alignment_mobile' => 'center'
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
        $this->assertEquals('left', $widget['settings']['alignment']);
        $this->assertEquals('center', $widget['settings']['alignment_tablet']);
        $this->assertEquals('center', $widget['settings']['alignment_mobile']);
    }

    /** @test */
    public function builder_validates_responsive_breakpoints(): void
    {
        $settings = [
            'breakpoints' => [
                'mobile' => 768,
                'tablet' => 1024,
                'desktop' => 1440
            ]
        ];

        $content = [
            ['id' => 'section-1', 'elType' => 'section', 'elements' => []]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => $settings
            ]);

        $response->assertStatus(200);
        $this->page->refresh();

        $this->assertEquals(768, $this->page->settings['breakpoints']['mobile']);
        $this->assertEquals(1024, $this->page->settings['breakpoints']['tablet']);
    }
}
