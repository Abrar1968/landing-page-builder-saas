@props([
    'title' => 'Ready to get started?',
    'subtitle' => 'Join thousands of satisfied customers today.',
    'buttonText' => 'Start Free Trial',
    'buttonUrl' => '#',
    'bgColor' => '#4F46E5',
    'titleColor' => '#FFFFFF',
    'subtitleColor' => '#E0E7FF',
    'buttonBgColor' => '#FFFFFF',
    'buttonTextColor' => '#4F46E5'
])

<section style="padding: 64px 24px; background-color: {{ $bgColor }}; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 2rem; font-weight: 700; margin: 0 0 16px 0; color: {{ $titleColor }};">
            {{ $title }}
        </h2>
        @if($subtitle)
            <p style="font-size: 1.125rem; margin: 0 0 32px 0; color: {{ $subtitleColor }};">
                {{ $subtitle }}
            </p>
        @endif
        <a href="{{ $buttonUrl }}"
           style="display: inline-block; padding: 16px 32px; background: {{ $buttonBgColor }}; color: {{ $buttonTextColor }}; border-radius: 6px; font-size: 1rem; font-weight: 600; text-decoration: none;">
            {{ $buttonText }}
        </a>
    </div>
</section>
