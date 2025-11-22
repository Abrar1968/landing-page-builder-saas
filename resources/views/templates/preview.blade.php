<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template Preview</title>
    @vite(['resources/css/app.css'])
    <style>
        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    @foreach($content as $section)
        <div class="section" data-type="{{ $section['type'] ?? 'unknown' }}">
            @switch($section['type'] ?? '')
                @case('hero')
                    <div class="py-20 px-4 text-center bg-gradient-to-b from-indigo-50 to-white">
                        @if(isset($section['components']))
                            @foreach($section['components'] as $component)
                                @if($component['type'] === 'heading')
                                    <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $component['content'] ?? '' }}</h1>
                                @elseif($component['type'] === 'text')
                                    <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">{{ $component['content'] ?? '' }}</p>
                                @elseif($component['type'] === 'button-group')
                                    <div class="flex justify-center gap-4">
                                        @foreach($component['buttons'] ?? [] as $button)
                                            <a href="{{ $button['link'] ?? '#' }}" class="px-6 py-3 rounded-lg {{ $button['variant'] === 'primary' ? 'bg-indigo-600 text-white' : 'border border-gray-300 text-gray-700' }}">
                                                {{ $button['text'] ?? 'Button' }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                    @break

                @case('features')
                    <div class="py-16 px-4 bg-gray-50">
                        <div class="max-w-6xl mx-auto">
                            @if(isset($section['components']))
                                @foreach($section['components'] as $component)
                                    @if($component['type'] === 'section-header')
                                        <div class="text-center mb-12">
                                            <h2 class="text-3xl font-bold text-gray-900">{{ $component['title'] ?? '' }}</h2>
                                            <p class="mt-4 text-lg text-gray-600">{{ $component['subtitle'] ?? '' }}</p>
                                        </div>
                                    @elseif($component['type'] === 'feature-grid')
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                            @foreach($component['features'] ?? [] as $feature)
                                                <div class="bg-white p-6 rounded-lg shadow-sm">
                                                    <h3 class="font-semibold text-gray-900 mb-2">{{ $feature['title'] ?? '' }}</h3>
                                                    <p class="text-gray-600 text-sm">{{ $feature['description'] ?? '' }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @break

                @case('pricing')
                    <div class="py-16 px-4">
                        <div class="max-w-6xl mx-auto text-center">
                            <h2 class="text-3xl font-bold text-gray-900 mb-12">Pricing Plans</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                @if(isset($section['components']))
                                    @foreach($section['components'] as $component)
                                        @if($component['type'] === 'pricing-grid')
                                            @foreach($component['plans'] ?? [] as $plan)
                                                <div class="bg-white border {{ ($plan['featured'] ?? false) ? 'border-indigo-500 shadow-lg' : 'border-gray-200' }} rounded-lg p-6">
                                                    <h3 class="font-semibold text-lg">{{ $plan['name'] ?? '' }}</h3>
                                                    <p class="text-3xl font-bold my-4">${{ $plan['monthlyPrice'] ?? 0 }}<span class="text-sm text-gray-500">/mo</span></p>
                                                    <ul class="text-left text-sm text-gray-600 space-y-2 mb-6">
                                                        @foreach($plan['features'] ?? [] as $feature)
                                                            <li>✓ {{ $feature }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endforeach
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    @break

                @case('cta')
                    <div class="py-16 px-4 bg-indigo-600 text-white text-center">
                        <h2 class="text-3xl font-bold mb-4">Ready to get started?</h2>
                        <p class="text-xl mb-8 opacity-90">Join thousands of users building amazing pages.</p>
                        <a href="#" class="inline-block px-8 py-3 bg-white text-indigo-600 rounded-lg font-semibold">Get Started</a>
                    </div>
                    @break

                @default
                    <div class="py-8 px-4 bg-gray-100 text-center text-gray-500">
                        {{ $section['type'] ?? 'Unknown' }} section
                    </div>
            @endswitch
        </div>
    @endforeach
</body>
</html>
