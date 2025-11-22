<?php

namespace App\View\Components\Builder;

use Illuminate\View\Component;

class Button extends Component
{
    public function __construct(
        public string $text = 'Click Me',
        public string $url = '#',
        public string $target = '_self',
        public string $bgColor = '#3B82F6',
        public string $textColor = '#FFFFFF',
        public string $padding = '12px 24px',
        public string $borderRadius = '6px',
        public string $fontSize = '1rem'
    ) {}

    public function render()
    {
        return view('components.builder.button');
    }
}
