@props([
    'type' => 'text',
    'name' => '',
    'label' => '',
    'placeholder' => '',
    'required' => false,
    'value' => ''
])

<div style="margin-bottom: 16px;">
    @if($label)
        <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 4px; color: #374151;">
            {{ $label }}
            @if($required)
                <span style="color: #EF4444;">*</span>
            @endif
        </label>
    @endif
    <input type="{{ $type }}"
           name="{{ $name }}"
           placeholder="{{ $placeholder }}"
           value="{{ $value }}"
           {{ $required ? 'required' : '' }}
           style="width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 1rem;">
</div>
