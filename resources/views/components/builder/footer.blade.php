@props([
    'companyName' => 'Company Name',
    'copyright' => '',
    'links' => [],
    'socialLinks' => [],
    'bgColor' => '#111827',
    'textColor' => '#9CA3AF',
    'linkColor' => '#FFFFFF'
])

<footer style="padding: 48px 24px; background-color: {{ $bgColor }};">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 24px;">
            <div>
                <div style="font-size: 1.25rem; font-weight: 600; color: {{ $linkColor }}; margin-bottom: 8px;">
                    {{ $companyName }}
                </div>
                <div style="font-size: 0.875rem; color: {{ $textColor }};">
                    {{ $copyright ?: '© ' . date('Y') . ' ' . $companyName . '. All rights reserved.' }}
                </div>
            </div>

            @if(count($links) > 0)
                <nav style="display: flex; gap: 24px;">
                    @foreach($links as $link)
                        <a href="{{ $link['url'] ?? '#' }}"
                           style="font-size: 0.875rem; color: {{ $textColor }}; text-decoration: none;">
                            {{ $link['text'] ?? 'Link' }}
                        </a>
                    @endforeach
                </nav>
            @else
                <nav style="display: flex; gap: 24px;">
                    <a href="#" style="font-size: 0.875rem; color: {{ $textColor }}; text-decoration: none;">Privacy</a>
                    <a href="#" style="font-size: 0.875rem; color: {{ $textColor }}; text-decoration: none;">Terms</a>
                    <a href="#" style="font-size: 0.875rem; color: {{ $textColor }}; text-decoration: none;">Contact</a>
                </nav>
            @endif
        </div>
    </div>
</footer>
