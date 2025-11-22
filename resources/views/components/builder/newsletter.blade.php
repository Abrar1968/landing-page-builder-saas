@props([
    'title' => 'Subscribe to our newsletter',
    'subtitle' => 'Get the latest updates and news delivered to your inbox.',
    'placeholder' => 'Enter your email',
    'buttonText' => 'Subscribe',
    'action' => '',
    'bgColor' => '#F3F4F6',
    'titleColor' => '#111827',
    'subtitleColor' => '#6B7280',
    'buttonBgColor' => '#4F46E5',
    'buttonTextColor' => '#FFFFFF'
])

<section style="padding: 64px 24px; background-color: {{ $bgColor }}; text-align: center;">
    <div style="max-width: 500px; margin: 0 auto;">
        @if($title)
            <h2 style="font-size: 1.5rem; font-weight: 700; margin: 0 0 8px 0; color: {{ $titleColor }};">
                {{ $title }}
            </h2>
        @endif
        @if($subtitle)
            <p style="font-size: 1rem; margin: 0 0 24px 0; color: {{ $subtitleColor }};">
                {{ $subtitle }}
            </p>
        @endif

        <form action="{{ $action }}" method="POST" style="display: flex; gap: 8px;">
            <input type="email"
                   name="email"
                   placeholder="{{ $placeholder }}"
                   required
                   style="flex: 1; padding: 12px 16px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 1rem;">
            <button type="submit"
                    style="padding: 12px 24px; background: {{ $buttonBgColor }}; color: {{ $buttonTextColor }}; border: none; border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer; white-space: nowrap;">
                {{ $buttonText }}
            </button>
        </form>
    </div>
</section>
