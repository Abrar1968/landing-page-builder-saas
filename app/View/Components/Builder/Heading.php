<?php

namespace App\View\Components\Builder;

use Illuminate\View\Component;

class Heading extends Component
{
    public function __construct(
        public string $text = 'New Heading',
        public string $level = 'h2',
        public string $alignment = 'left',
        public string $color = '#000000',
        public string $fontSize = '2rem',
        public string $fontWeight = 'bold'
    ) {}

    public function render()
    {
        return view('components.builder.heading');
    }
}
