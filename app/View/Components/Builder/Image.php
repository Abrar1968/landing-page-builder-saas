<?php

namespace App\View\Components\Builder;

use Illuminate\View\Component;

class Image extends Component
{
    public function __construct(
        public string $src = '',
        public string $alt = '',
        public string $width = '100%',
        public string $height = 'auto',
        public string $objectFit = 'cover',
        public string $borderRadius = '0'
    ) {}

    public function render()
    {
        return view('components.builder.image');
    }
}
