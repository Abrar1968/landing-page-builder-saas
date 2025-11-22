@props([
    'quote' => 'This product has completely transformed how we work. Highly recommended!',
    'author' => 'John Doe',
    'role' => 'CEO, Company',
    'avatar' => '',
    'bgColor' => '#F9FAFB',
    'quoteColor' => '#374151',
    'authorColor' => '#111827',
    'roleColor' => '#6B7280'
])

<section style="padding: 64px 24px; background-color: {{ $bgColor }};">
    <div style="max-width: 800px; margin: 0 auto; text-align: center;">
        <blockquote style="font-size: 1.5rem; font-style: italic; margin: 0 0 24px 0; color: {{ $quoteColor }}; line-height: 1.6;">
            "{{ $quote }}"
        </blockquote>
        <div style="display: flex; align-items: center; justify-content: center; gap: 16px;">
            @if($avatar)
                <img src="{{ $avatar }}"
                     alt="{{ $author }}"
                     style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
            @else
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #E5E7EB; display: flex; align-items: center; justify-content: center; font-weight: 600; color: #6B7280;">
                    {{ strtoupper(substr($author, 0, 1)) }}
                </div>
            @endif
            <div style="text-align: left;">
                <div style="font-weight: 600; color: {{ $authorColor }};">{{ $author }}</div>
                <div style="font-size: 0.875rem; color: {{ $roleColor }};">{{ $role }}</div>
            </div>
        </div>
    </div>
</section>
