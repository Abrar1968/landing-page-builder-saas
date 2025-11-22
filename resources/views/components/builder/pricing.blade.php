@props([
    'title' => 'Pricing Plans',
    'subtitle' => 'Choose the plan that works for you',
    'plans' => [],
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

        @if(count($plans) > 0)
            <div style="display: grid; grid-template-columns: repeat({{ count($plans) }}, 1fr); gap: 24px;">
                @foreach($plans as $plan)
                    <div style="border: 1px solid #E5E7EB; border-radius: 8px; padding: 32px; text-align: center; {{ ($plan['featured'] ?? false) ? 'border-color: #4F46E5; box-shadow: 0 4px 6px rgba(79, 70, 229, 0.1);' : '' }}">
                        <h3 style="font-size: 1.5rem; font-weight: 600; margin: 0 0 8px 0; color: {{ $titleColor }};">
                            {{ $plan['name'] ?? 'Plan' }}
                        </h3>
                        <div style="font-size: 3rem; font-weight: 700; color: {{ $titleColor }}; margin: 16px 0;">
                            ${{ $plan['price'] ?? '0' }}<span style="font-size: 1rem; font-weight: 400; color: {{ $subtitleColor }};">/mo</span>
                        </div>
                        @if(!empty($plan['features']))
                            <ul style="list-style: none; padding: 0; margin: 24px 0; text-align: left;">
                                @foreach($plan['features'] as $feature)
                                    <li style="padding: 8px 0; color: {{ $subtitleColor }};">✓ {{ $feature }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <a href="{{ $plan['buttonUrl'] ?? '#' }}"
                           style="display: block; padding: 12px 24px; background: {{ ($plan['featured'] ?? false) ? '#4F46E5' : '#F3F4F6' }}; color: {{ ($plan['featured'] ?? false) ? '#FFFFFF' : '#374151' }}; border-radius: 6px; text-decoration: none; font-weight: 600;">
                            {{ $plan['buttonText'] ?? 'Get Started' }}
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
                @foreach(['Basic' => 9, 'Pro' => 29, 'Enterprise' => 99] as $name => $price)
                    <div style="border: 1px solid #E5E7EB; border-radius: 8px; padding: 32px; text-align: center; {{ $name === 'Pro' ? 'border-color: #4F46E5;' : '' }}">
                        <h3 style="font-size: 1.5rem; font-weight: 600; margin: 0 0 8px 0;">{{ $name }}</h3>
                        <div style="font-size: 3rem; font-weight: 700; margin: 16px 0;">
                            ${{ $price }}<span style="font-size: 1rem; font-weight: 400; color: #6B7280;">/mo</span>
                        </div>
                        <a href="#" style="display: block; padding: 12px 24px; background: {{ $name === 'Pro' ? '#4F46E5' : '#F3F4F6' }}; color: {{ $name === 'Pro' ? '#FFFFFF' : '#374151' }}; border-radius: 6px; text-decoration: none; font-weight: 600;">
                            Get Started
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
