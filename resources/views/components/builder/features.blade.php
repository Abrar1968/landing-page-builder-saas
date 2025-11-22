@props([
    'title' => 'Our Features',
    'subtitle' => 'Everything you need to succeed',
    'columns' => 3,
    'features' => [],
    'bgColor' => '#FFFFFF',
    'titleColor' => '#111827',
    'subtitleColor' => '#6B7280'
])

<section style="padding: 64px 24px; background-color: {{ $bgColor }};">
    <div style="max-width: 1200px; margin: 0 auto;">
        @if($title)
            <h2 style="font-size: 2rem; font-weight: 700; text-align: center; margin: 0 0 8px 0; color: {{ $titleColor }};">
                {{ $title }}
            </h2>
        @endif
        @if($subtitle)
            <p style="font-size: 1.125rem; text-align: center; margin: 0 0 48px 0; color: {{ $subtitleColor }};">
                {{ $subtitle }}
            </p>
        @endif

        @if(count($features) > 0)
            <div style="display: grid; grid-template-columns: repeat({{ $columns }}, 1fr); gap: 32px;">
                @foreach($features as $feature)
                    <div style="text-align: center; padding: 24px;">
                        @if(!empty($feature['icon']))
                            <div style="font-size: 2.5rem; margin-bottom: 16px;">{{ $feature['icon'] }}</div>
                        @endif
                        <h3 style="font-size: 1.25rem; font-weight: 600; margin: 0 0 8px 0; color: {{ $titleColor }};">
                            {{ $feature['title'] ?? 'Feature Title' }}
                        </h3>
                        <p style="font-size: 1rem; margin: 0; color: {{ $subtitleColor }};">
                            {{ $feature['description'] ?? 'Feature description goes here.' }}
                        </p>
                    </div>
                @endforeach
            </div>
        @else
            <div style="display: grid; grid-template-columns: repeat({{ $columns }}, 1fr); gap: 32px;">
                @for($i = 1; $i <= $columns; $i++)
                    <div style="text-align: center; padding: 24px;">
                        <div style="font-size: 2.5rem; margin-bottom: 16px;">⭐</div>
                        <h3 style="font-size: 1.25rem; font-weight: 600; margin: 0 0 8px 0; color: {{ $titleColor }};">
                            Feature {{ $i }}
                        </h3>
                        <p style="font-size: 1rem; margin: 0; color: {{ $subtitleColor }};">
                            Feature description goes here.
                        </p>
                    </div>
                @endfor
            </div>
        @endif
    </div>
</section>
