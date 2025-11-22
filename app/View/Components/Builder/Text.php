<?php

namespace App\View\Components\Builder;

use Illuminate\View\Component;

class Text extends Component
{
    public function __construct(
        public string $text = 'Enter your text here...',
        public string $alignment = 'left',
        public string $color = '#333333',
        public string $fontSize = '1rem',
        public string $lineHeight = '1.6'
    ) {}

    public function render()
    {
        return view('components.builder.text');
    }
}
