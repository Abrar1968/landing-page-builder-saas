<?php

namespace App\View\Components\Builder;

use Illuminate\View\Component;

class Hero extends Component
{
    public function __construct(
        public string $title = 'Welcome to Our Site',
        public string $subtitle = 'Build amazing landing pages',
        public string $buttonText = 'Get Started',
        public string $buttonUrl = '#',
        public string $bgColor = '#F3F4F6',
        public string $titleColor = '#111827',
        public string $subtitleColor = '#6B7280',
        public string $buttonBgColor = '#4F46E5',
        public string $buttonTextColor = '#FFFFFF',
        public string $backgroundImage = ''
    ) {}

    public function render()
    {
        return view('components.builder.hero');
    }
}
