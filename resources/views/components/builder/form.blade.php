@props([
    'title' => 'Contact Us',
    'fields' => [],
    'submitText' => 'Submit',
    'action' => '',
    'method' => 'POST',
    'bgColor' => '#FFFFFF',
    'titleColor' => '#111827',
    'buttonBgColor' => '#4F46E5',
    'buttonTextColor' => '#FFFFFF'
])

<section style="padding: 48px 24px; background-color: {{ $bgColor }};">
    <div style="max-width: 600px; margin: 0 auto;">
        @if($title)
            <h2 style="font-size: 1.5rem; font-weight: 700; text-align: center; margin: 0 0 32px 0; color: {{ $titleColor }};">
                {{ $title }}
            </h2>
        @endif

        <form action="{{ $action }}" method="{{ $method }}" style="display: flex; flex-direction: column; gap: 16px;">
            @if(count($fields) > 0)
                @foreach($fields as $field)
                    <div>
                        @if(!empty($field['label']))
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 4px; color: #374151;">
                                {{ $field['label'] }}
                            </label>
                        @endif
                        @if(($field['type'] ?? 'text') === 'textarea')
                            <textarea name="{{ $field['name'] ?? '' }}"
                                      placeholder="{{ $field['placeholder'] ?? '' }}"
                                      {{ ($field['required'] ?? false) ? 'required' : '' }}
                                      rows="4"
                                      style="width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 1rem;"></textarea>
                        @else
                            <input type="{{ $field['type'] ?? 'text' }}"
                                   name="{{ $field['name'] ?? '' }}"
                                   placeholder="{{ $field['placeholder'] ?? '' }}"
                                   {{ ($field['required'] ?? false) ? 'required' : '' }}
                                   style="width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 1rem;">
                        @endif
                    </div>
                @endforeach
            @else
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 4px; color: #374151;">Name</label>
                    <input type="text" name="name" placeholder="Your name" required style="width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 1rem;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 4px; color: #374151;">Email</label>
                    <input type="email" name="email" placeholder="Your email" required style="width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 1rem;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 4px; color: #374151;">Message</label>
                    <textarea name="message" placeholder="Your message" rows="4" style="width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 1rem;"></textarea>
                </div>
            @endif

            <button type="submit"
                    style="padding: 12px 24px; background: {{ $buttonBgColor }}; color: {{ $buttonTextColor }}; border: none; border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer;">
                {{ $submitText }}
            </button>
        </form>
    </div>
</section>
