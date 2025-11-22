<?php

return [
    'elements' => [
        'typography' => [
            [
                'type' => 'heading',
                'name' => 'Heading',
                'icon' => 'H',
            ],
            [
                'type' => 'paragraph',
                'name' => 'Paragraph',
                'icon' => '¶',
            ],
        ],
        'media' => [
            [
                'type' => 'image',
                'name' => 'Image',
                'icon' => '🖼',
            ],
            [
                'type' => 'video',
                'name' => 'Video',
                'icon' => '▶',
            ],
        ],
        'layout' => [
            [
                'type' => 'section',
                'name' => 'Section',
                'icon' => '□',
            ],
            [
                'type' => 'columns',
                'name' => 'Columns',
                'icon' => '⊞',
            ],
            [
                'type' => 'divider',
                'name' => 'Divider',
                'icon' => '—',
            ],
            [
                'type' => 'spacer',
                'name' => 'Spacer',
                'icon' => '↕',
            ],
        ],
        'interactive' => [
            [
                'type' => 'button',
                'name' => 'Button',
                'icon' => '▢',
            ],
            [
                'type' => 'form',
                'name' => 'Form',
                'icon' => '📝',
            ],
        ],
        'advanced' => [
            [
                'type' => 'html',
                'name' => 'Custom HTML',
                'icon' => '</>',
            ],
        ],
    ],

    'autosave_interval' => 30000, // 30 seconds

    'history_limit' => 50,

    'default_settings' => [
        'font' => 'Inter',
        'primaryColor' => '#3B82F6',
        'backgroundColor' => '#FFFFFF',
    ],
];
