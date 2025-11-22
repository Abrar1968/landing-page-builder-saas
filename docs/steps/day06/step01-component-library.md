# Day 6 - Step 1: Component Library

## Objective
Create 20+ reusable Blade components with configuration panels.

## Tasks

### 1.1 Blade Components
Create components in `resources/views/components/builder/`:

**Basic Elements:**
- `heading.blade.php` - H1-H6 with size/color
- `text.blade.php` - Paragraph with styling
- `image.blade.php` - Image with alt, sizing
- `button.blade.php` - CTA with colors, URL

**Sections:**
- `hero.blade.php` - Hero with title, subtitle, CTA
- `features.blade.php` - Feature grid
- `testimonial.blade.php` - Quote with avatar
- `pricing.blade.php` - Pricing cards
- `cta.blade.php` - Call-to-action section
- `footer.blade.php` - Footer with links

**Form Elements:**
- `form.blade.php` - Contact form
- `input.blade.php` - Form input
- `newsletter.blade.php` - Email signup

### 1.2 Component Class Example
```php
// app/View/Components/Builder/Heading.php
class Heading extends Component
{
    public function __construct(
        public string $text = '',
        public string $size = 'text-3xl',
        public string $color = '#000000'
    ) {}

    public function render()
    {
        return view('components.builder.heading');
    }
}
```

### 1.3 Properties Panel
Dynamic properties panel based on element type:
- Text inputs, color pickers, selects
- Real-time preview updates

### 1.4 Page Renderer Service
```php
// app/Services/PageRenderer.php
public function render(array $elements): string
{
    $html = '';
    foreach ($elements as $element) {
        $html .= $this->renderElement($element);
    }
    return $html;
}
```

## Reference Documentation
- `docs/frontend/02-COMPONENTS.md` - Component specifications
- `docs/features/02-PAGE-BUILDER.md` - Component list
- `docs/04-IMPLEMENTATION-FLOW.md` - Day 6 components

## Expected Deliverables
- [x] 20+ Blade components created
- [x] Properties panel working
- [x] PageRenderer service
- [x] Responsive preview modes

## Day 6 Complete
→ Proceed to Day 7: Page Management
