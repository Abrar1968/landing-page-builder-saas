@props([
    'columns' => 2,
    'gap' => '24px',
    'children' => []
])

<div style="display: grid; grid-template-columns: repeat({{ $columns }}, 1fr); gap: {{ $gap }}; padding: 16px;">
    @if(count($children) > 0)
        @foreach($children as $column)
            <div>
                @foreach($column as $element)
                    {{-- Elements within columns would be rendered here --}}
                @endforeach
            </div>
        @endforeach
    @else
        @for($i = 0; $i < $columns; $i++)
            <div style="padding: 16px; background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 4px; min-height: 100px;">
                Column {{ $i + 1 }}
            </div>
        @endfor
    @endif
</div>
