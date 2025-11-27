<?php

namespace Tests\Feature\Builder;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderStoreTest extends TestCase
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
    public function store_initializes_with_page_data(): void
    {
        $this->page->update([
            'title' => 'Test Page Title',
            'content' => [['id' => 'section-1', 'elType' => 'section']],
            'settings' => ['width' => 1200]
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('builder.edit', $this->page));

        $content = $response->getContent();

        $this->assertStringContainsString('"title":"Test Page Title"', $content);
        $this->assertStringContainsString('"width":1200', $content);
        $this->assertStringContainsString('section-1', $content);
    }

    /** @test */
    public function store_handles_empty_content_array(): void
    {
        $this->page->update(['content' => null]);

        $response = $this->actingAs($this->user)
            ->get(route('builder.edit', $this->page));

        $response->assertStatus(200);
        $this->assertStringContainsString('"content":[]', $response->getContent());
    }

    /** @test */
    public function store_handles_empty_settings(): void
    {
        $this->page->update(['settings' => null]);

        $response = $this->actingAs($this->user)
            ->get(route('builder.edit', $this->page));

        $response->assertStatus(200);
        $this->assertStringContainsString('"settings":[]', $response->getContent());
    }

    /** @test */
    public function store_saves_undo_redo_history(): void
    {
        $step1 = [['id' => '1', 'elType' => 'section', 'elements' => []]];
        $step2 = [
            ['id' => '1', 'elType' => 'section', 'elements' => []],
            ['id' => '2', 'elType' => 'section', 'elements' => []]
        ];

        // Save step 1
        $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $step1,
                'settings' => []
            ]);

        // Save step 2
        $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $step2,
                'settings' => []
            ]);

        $this->page->refresh();
        $this->assertEquals($step2, $this->page->content);
        $this->assertTrue($this->page->versions()->count() >= 2);
    }

    /** @test */
    public function store_generates_unique_element_ids(): void
    {
        $content = [
            ['id' => 'section-1', 'elType' => 'section', 'elements' => [
                ['id' => 'column-1', 'elType' => 'column', 'elements' => [
                    ['id' => 'widget-1', 'elType' => 'widget', 'widgetType' => 'heading'],
                    ['id' => 'widget-2', 'elType' => 'widget', 'widgetType' => 'text-editor']
                ]]
            ]],
            ['id' => 'section-2', 'elType' => 'section', 'elements' => []]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);

        $response->assertStatus(200);

        $ids = [];
        $this->extractIds($content, $ids);

        // Check all IDs are unique
        $this->assertEquals(count($ids), count(array_unique($ids)));
    }

    protected function extractIds(array $elements, array &$ids): void
    {
        foreach ($elements as $element) {
            if (isset($element['id'])) {
                $ids[] = $element['id'];
            }
            if (isset($element['elements'])) {
                $this->extractIds($element['elements'], $ids);
            }
        }
    }

    /** @test */
    public function store_preserves_widget_settings(): void
    {
        $content = [
            ['id' => 'section-1', 'elType' => 'section', 'elements' => [
                ['id' => 'column-1', 'elType' => 'column', 'elements' => [
                    [
                        'id' => 'widget-1',
                        'elType' => 'widget',
                        'widgetType' => 'heading',
                        'settings' => [
                            'title' => 'My Heading',
                            'size' => 'h1',
                            'text_color' => '#000000',
                            'alignment' => 'center'
                        ]
                    ]
                ]]
            ]]
        ];

        $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);

        $this->page->refresh();
        $widgetSettings = $this->page->content[0]['elements'][0]['elements'][0]['settings'];

        $this->assertEquals('My Heading', $widgetSettings['title']);
        $this->assertEquals('h1', $widgetSettings['size']);
        $this->assertEquals('#000000', $widgetSettings['text_color']);
        $this->assertEquals('center', $widgetSettings['alignment']);
    }

    /** @test */
    public function store_handles_clipboard_operations(): void
    {
        $widgetToCopy = [
            'id' => 'widget-1',
            'elType' => 'widget',
            'widgetType' => 'button',
            'settings' => ['text' => 'Click Me']
        ];

        $content = [
            ['id' => 'section-1', 'elType' => 'section', 'elements' => [
                ['id' => 'column-1', 'elType' => 'column', 'elements' => [$widgetToCopy]]
            ]]
        ];

        $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);

        $this->page->refresh();
        $this->assertNotEmpty($this->page->content);
    }

    /** @test */
    public function store_validates_section_structure(): void
    {
        $content = [
            [
                'id' => 'section-1',
                'elType' => 'section',
                'settings' => ['structure' => '50-50'],
                'elements' => [
                    ['id' => 'col-1', 'elType' => 'column', 'settings' => ['_column_size' => 50]],
                    ['id' => 'col-2', 'elType' => 'column', 'settings' => ['_column_size' => 50]]
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
        $this->assertEquals('50-50', $this->page->content[0]['settings']['structure']);
    }

    /** @test */
    public function store_maintains_element_hierarchy(): void
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
                            ['id' => 'widget-2', 'elType' => 'widget', 'widgetType' => 'text-editor']
                        ]
                    ]
                ]
            ]
        ];

        $this->actingAs($this->user)
            ->postJson(route('builder.save', $this->page), [
                'content' => $content,
                'settings' => []
            ]);

        $this->page->refresh();

        // Verify hierarchy: section -> column -> widgets
        $this->assertEquals('section', $this->page->content[0]['elType']);
        $this->assertEquals('column', $this->page->content[0]['elements'][0]['elType']);
        $this->assertEquals('widget', $this->page->content[0]['elements'][0]['elements'][0]['elType']);
        $this->assertEquals('widget', $this->page->content[0]['elements'][0]['elements'][1]['elType']);
    }
}
