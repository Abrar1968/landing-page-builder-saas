<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->settings['metaTitle'] ?? $page->title }}</title>
    @if(!empty($page->settings['metaDescription']))
        <meta name="description" content="{{ $page->settings['metaDescription'] }}">
    @endif
    @vite(['resources/css/app.css'])
</head>
<body>
    <div class="min-h-screen">
        @foreach($page->content ?? [] as $element)
            @switch($element['type'] ?? '')
                @case('heading')
                    <x-builder.heading :text="$element['props']['text'] ?? ''" :level="$element['props']['level'] ?? 'h2'" :color="$element['props']['color'] ?? '#000'" :fontSize="$element['props']['fontSize'] ?? '2rem'" />
                    @break

                @case('paragraph')
                    <x-builder.text :text="$element['props']['text'] ?? ''" :color="$element['props']['color'] ?? '#333'" :fontSize="$element['props']['fontSize'] ?? '1rem'" />
                    @break

                @case('image')
                    <x-builder.image :src="$element['props']['src'] ?? ''" :alt="$element['props']['alt'] ?? ''" :width="$element['props']['width'] ?? '100%'" />
                    @break

                @case('button')
                    <x-builder.button :text="$element['props']['text'] ?? 'Button'" :url="$element['props']['url'] ?? '#'" :bgColor="$element['props']['backgroundColor'] ?? '#3B82F6'" :textColor="$element['props']['textColor'] ?? '#fff'" />
                    @break

                @case('divider')
                    <x-builder.divider :color="$element['props']['color'] ?? '#e5e7eb'" :thickness="$element['props']['thickness'] ?? '1px'" />
                    @break

                @case('spacer')
                    <x-builder.spacer :height="$element['props']['height'] ?? '40px'" />
                    @break

                @case('video')
                    <x-builder.video :url="$element['props']['src'] ?? ''" :provider="$element['props']['provider'] ?? 'youtube'" />
                    @break

                @case('hero')
                    <x-builder.hero
                        :title="$element['props']['title'] ?? 'Welcome'"
                        :subtitle="$element['props']['subtitle'] ?? ''"
                        :buttonText="$element['props']['buttonText'] ?? ''"
                        :buttonUrl="$element['props']['buttonUrl'] ?? '#'"
                    />
                    @break

                @case('features')
                    <x-builder.features
                        :title="$element['props']['title'] ?? ''"
                        :subtitle="$element['props']['subtitle'] ?? ''"
                        :columns="$element['props']['columns'] ?? 3"
                        :features="$element['props']['features'] ?? []"
                    />
                    @break

                @case('testimonial')
                    <x-builder.testimonial
                        :quote="$element['props']['quote'] ?? ''"
                        :author="$element['props']['author'] ?? ''"
                        :role="$element['props']['role'] ?? ''"
                    />
                    @break

                @case('pricing')
                    <x-builder.pricing
                        :title="$element['props']['title'] ?? ''"
                        :subtitle="$element['props']['subtitle'] ?? ''"
                        :plans="$element['props']['plans'] ?? []"
                    />
                    @break

                @case('cta')
                    <x-builder.cta
                        :title="$element['props']['title'] ?? ''"
                        :subtitle="$element['props']['subtitle'] ?? ''"
                        :buttonText="$element['props']['buttonText'] ?? ''"
                        :buttonUrl="$element['props']['buttonUrl'] ?? '#'"
                    />
                    @break

                @case('footer')
                    <x-builder.footer
                        :companyName="$element['props']['companyName'] ?? ''"
                        :links="$element['props']['links'] ?? []"
                    />
                    @break

                @case('newsletter')
                    <x-builder.newsletter
                        :title="$element['props']['title'] ?? ''"
                        :subtitle="$element['props']['subtitle'] ?? ''"
                    />
                    @break

                @case('form')
                    <x-builder.form
                        :title="$element['props']['title'] ?? ''"
                        :fields="$element['props']['fields'] ?? []"
                    />
                    @break

                @case('html')
                    <x-builder.html :content="$element['props']['content'] ?? ''" />
                    @break
            @endswitch
        @endforeach
    </div>
</body>
</html>
