<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderTest extends TestCase
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
    public function user_can_access_builder_edit_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('builder.edit', $this->page));

        $response->assertStatus(200);
        $response->assertViewIs('builder.edit');
        $response->assertViewHas('page', $this->page);
    }

    /** @test */
    public function builder_edit_page_contains_required_data(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('builder.edit', $this->page));

        $response->assertSee('id="builder-app"', false);
        $response->assertSee('id="page-data"', false);
        $response->assertSee('window.builderRoutes', false);
    }

    /** @test */
    public function builder_page_data_is_properly_encoded(): void
    {
        $this->page->update([
            'content' => [
                ['id' => '1', 'elType' => 'section', 'elements' => []]
            ],
            'settings' => ['width' => 1200]
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('builder.edit', $this->page));

        $content = $response->getContent();

        $this->assertStringContainsString('"id":' . $this->page->id, $content);
        $this->assertStringContainsString('"title":"' . $this->page->title . '"', $content);
        $this->assertStringContainsString('"slug":"' . $this->page->slug . '"', $content);
    }

    /** @test */
    public function builder_routes_are_available_in_javascript(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('builder.edit', $this->page));

        $content = $response->getContent();

        $this->assertStringContainsString('window.builderRoutes', $content);
        $this->assertStringContainsString('save:', $content);
        $this->assertStringContainsString('publish:', $content);
        $this->assertStringContainsString('preview:', $content);
        $this->assertStringContainsString('media:', $content);
    }

    /** @test */
    public function user_can_save_builder_content(): void
    {
        $content = [
            [
                'id' => 'test-section-1',
                'elType' => 'section',
                'settings' => ['background_color' => '#ffffff'],
                'elements' => [
                    [
                        'id' => 'test-column-1',
                        'elType' => 'column',
                        'settings' => ['_column_size' => 100],
                        'elements' => [
                            [
                                'id' => 'test-widget-1',
                                'elType' => 'widget',
                                'widgetType' => 'heading',
                                'settings' => ['title' => 'Test Heading']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => ['width' => 1200]
            ]);

        $response->assertStatus(200);

        $this->page->refresh();
        $this->assertEquals($content, $this->page->content);
        $this->assertEquals(['width' => 1200], $this->page->settings);
    }

    /** @test */
    public function user_can_publish_page_from_builder(): void
    {
        $this->page->update(['status' => 'draft']);

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.publish', $this->page));

        $response->assertStatus(200);

        $this->page->refresh();
        $this->assertEquals('published', $this->page->status);
        $this->assertNotNull($this->page->published_at);
    }

    /** @test */
    public function user_can_preview_page_from_builder(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('builder.preview', $this->page));

        $response->assertStatus(200);
        $response->assertViewIs('builder.preview');
    }

    /** @test */
    public function user_cannot_edit_others_page_in_builder(): void
    {
        $otherUser = User::factory()->create();
        $otherPage = Page::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get(route('builder.edit', $otherPage));

        $response->assertForbidden();
    }

    /** @test */
    public function user_cannot_save_others_page_content(): void
    {
        $otherUser = User::factory()->create();
        $otherPage = Page::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $otherPage), [
                'content' => [],
                'settings' => []
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function guest_cannot_access_builder(): void
    {
        $response = $this->get(route('builder.edit', $this->page));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function builder_save_validates_content_structure(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => 'invalid',
                'settings' => []
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function builder_creates_page_version_on_save(): void
    {
        $initialVersionCount = $this->page->versions()->count();

        $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => [['id' => '1', 'elType' => 'section']],
                'settings' => []
            ]);

        $this->assertEquals($initialVersionCount + 1, $this->page->versions()->count());
    }

    /** @test */
    public function builder_updates_page_updated_at_timestamp(): void
    {
        // Save initial state
        $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => [['id' => 'section-1', 'elType' => 'section', 'elements' => []]],
                'settings' => []
            ]);

        $this->page->refresh();
        $oldTimestamp = $this->page->updated_at->timestamp;

        // Wait a moment to ensure timestamp difference
        sleep(1);

        // Save with different content
        $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => [['id' => 'section-2', 'elType' => 'section', 'elements' => []]],
                'settings' => []
            ]);

        $this->page->refresh();
        $this->assertGreaterThan($oldTimestamp, $this->page->updated_at->timestamp);
    }    /** @test */
    public function builder_handles_complex_nested_content(): void
    {
        $complexContent = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'settings' => [
                    'background_color' => '#f0f0f0',
                    'padding' => ['top' => 50, 'right' => 20, 'bottom' => 50, 'left' => 20]
                ],
                'elements' => [
                    [
                        'id' => 'column-1',
                        'elType' => 'column',
                        'settings' => ['_column_size' => 50],
                        'elements' => [
                            [
                                'id' => 'widget-1',
                                'elType' => 'widget',
                                'widgetType' => 'heading',
                                'settings' => ['title' => 'Column 1 Heading', 'size' => 'h2'],
                                'hover_settings' => ['text_color' => '#ff0000']
                            ],
                            [
                                'id' => 'widget-2',
                                'elType' => 'widget',
                                'widgetType' => 'text-editor',
                                'settings' => ['editor' => '<p>Test content</p>']
                            ]
                        ]
                    ],
                    [
                        'id' => 'column-2',
                        'elType' => 'column',
                        'settings' => ['_column_size' => 50],
                        'elements' => [
                            [
                                'id' => 'widget-3',
                                'elType' => 'widget',
                                'widgetType' => 'button',
                                'settings' => [
                                    'text' => 'Click Me',
                                    'link' => 'https://example.com',
                                    'background_color' => '#4f46e5'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $complexContent,
                'settings' => []
            ]);

        $response->assertStatus(200);

        $this->page->refresh();
        $this->assertEquals($complexContent, $this->page->content);
    }

    /** @test */
    public function builder_preserves_motion_effects_in_settings(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'settings' => [
                    'motion_effects' => [
                        'entrance_animation' => 'fadeInUp',
                        'animation_duration' => 1000,
                        'animation_delay' => 200,
                        'sticky' => true,
                        'sticky_position' => 'top',
                        'parallax' => true,
                        'parallax_speed' => 0.5
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
        $motionEffects = $this->page->content[0]['settings']['motion_effects'];

        $this->assertEquals('fadeInUp', $motionEffects['entrance_animation']);
        $this->assertEquals(1000, $motionEffects['animation_duration']);
        $this->assertTrue($motionEffects['sticky']);
    }

    /** @test */
    public function builder_saves_responsive_settings(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'settings' => [
                    'padding' => ['top' => 50, 'right' => 20, 'bottom' => 50, 'left' => 20],
                    'padding_tablet' => ['top' => 30, 'right' => 15, 'bottom' => 30, 'left' => 15],
                    'padding_mobile' => ['top' => 20, 'right' => 10, 'bottom' => 20, 'left' => 10]
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

        $this->assertArrayHasKey('padding_tablet', $settings);
        $this->assertArrayHasKey('padding_mobile', $settings);
        $this->assertEquals(30, $settings['padding_tablet']['top']);
        $this->assertEquals(20, $settings['padding_mobile']['top']);
    }
}
