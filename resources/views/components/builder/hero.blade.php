@props([
    'title' => 'Welcome to Our Site',
    'subtitle' => 'Build amazing landing pages',
    'buttonText' => 'Get Started',
    'buttonUrl' => '#',
    'bgColor' => '#F3F4F6',
    'titleColor' => '#111827',
    'subtitleColor' => '#6B7280',
    'buttonBgColor' => '#4F46E5',
    'buttonTextColor' => '#FFFFFF',
    'backgroundImage' => ''
])

<section style="padding: 80px 24px; text-align: center; background-color: {{ $bgColor }}; {{ $backgroundImage ? 'background-image: url(' . $backgroundImage . '); background-size: cover; background-position: center;' : '' }}">
    <h1 style="font-size: 3rem; font-weight: 700; margin: 0 0 16px 0; color: {{ $titleColor }};">
        {{ $title }}
    </h1>
    @if($subtitle)
        <p style="font-size: 1.25rem; margin: 0 0 32px 0; color: {{ $subtitleColor }};">
            {{ $subtitle }}
        </p>
    @endif
    @if($buttonText)
        <a href="{{ $buttonUrl }}"
           style="display: inline-block; padding: 16px 32px; background: {{ $buttonBgColor }}; color: {{ $buttonTextColor }}; border-radius: 6px; font-size: 1rem; font-weight: 600; text-decoration: none;">
            {{ $buttonText }}
        </a>
    @endif
</section>
