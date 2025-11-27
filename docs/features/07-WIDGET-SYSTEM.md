# Widget System Documentation
# Elementor-Inspired Page Builder Widgets

**Document Version:** 1.0
**Date:** November 27, 2025
**Based on:** Elementor Basic Widgets (Free Version)

---

## Table of Contents

1. [Overview](#overview)
2. [Widget Architecture](#widget-architecture)
3. [Complete Widget List](#complete-widget-list)
4. [Widget Properties Structure](#widget-properties-structure)
5. [Implementation Guide](#implementation-guide)

---

## Overview

The Landing Page Builder SaaS implements a comprehensive widget system inspired by Elementor's Basic (free) widgets. The system provides 28 essential widgets covering typography, media, layout, interactive elements, and advanced features.

### Widget Categories

| Category | Widget Count | Purpose |
|----------|--------------|---------|
| **Basic Typography** | 5 | Text elements (Heading, Text Editor, Icon List, Text Path, Alert) |
| **Media Elements** | 5 | Images, videos, audio (Image, Video, Image Box, Image Carousel, SoundCloud) |
| **Interactive** | 6 | User interaction (Button, Star Rating, Social Icons, Tabs, Accordion, Toggle) |
| **Layout** | 6 | Structure elements (Container, Inner Section, Divider, Spacer, Sidebar, Menu Anchor) |
| **Content Display** | 4 | Showcasing content (Icon Box, Basic Gallery, Testimonial, Counter) |
| **Advanced** | 2 | Custom code (HTML, Shortcode) |

---

## Widget Architecture

### Widget Data Structure

Each widget follows this JSON structure in the database:

```json
{
  "id": "el_unique_id_12345",
  "type": "heading",
  "label": "Heading",
  "props": {
    "content": "Your Heading Text",
    "tag": "h2",
    "color": "#000000",
    "typography": {
      "fontSize": "32px",
      "fontWeight": "700",
      "lineHeight": "1.2"
    }
  },
  "styles": {
    "margin": "0 0 16px 0",
    "padding": "0",
    "textAlign": "left"
  },
  "advanced": {
    "cssClasses": "",
    "customCSS": "",
    "responsive": {
      "hideOnMobile": false,
      "hideOnTablet": false
    }
  }
}
```

---

## Complete Widget List

### 1. Heading Widget

**Purpose:** Display H1-H6 headings with full typography control

**Content Tab Properties:**
- Title text (string)
- HTML Tag (H1, H2, H3, H4, H5, H6)
- Link (optional URL)

**Style Tab Properties:**
- Text color
- Typography (font family, size, weight, transform, style, decoration, line-height, letter-spacing)
- Text shadow
- Blend mode

**Advanced Tab Properties:**
- Margin & Padding
- Background (color, gradient, image)
- Border (type, radius, box shadow)
- Custom CSS

**Default Configuration:**
```javascript
{
  type: 'heading',
  props: {
    content: 'New Heading',
    tag: 'h2',
    link: '',
    color: '#000000',
    fontSize: '32px',
    fontWeight: '700',
    fontFamily: 'inherit'
  }
}
```

---

### 2. Image Widget

**Purpose:** Display images with caption, link, and styling options

**Content Tab Properties:**
- Image selection (upload or URL)
- Caption source (attachment/custom/none)
- Link (URL)
- Image size (thumbnail, medium, large, full)
- Open lightbox (yes/no)

**Style Tab Properties:**
- Image width/height
- Max width
- Opacity
- Hover effects (zoom, rotate, blur)
- CSS filters (brightness, contrast, saturation)
- Border radius

**Advanced Tab Properties:**
- Margin & Padding
- Background
- Border & Box Shadow

**Default Configuration:**
```javascript
{
  type: 'image',
  props: {
    src: '',
    alt: '',
    caption: '',
    link: '',
    width: '100%',
    height: 'auto',
    objectFit: 'cover',
    lightbox: false
  }
}
```

---

### 3. Text Editor Widget

**Purpose:** Rich text editing with WYSIWYG interface

**Content Tab Properties:**
- Text content (WYSIWYG editor)
- Drop cap toggle

**Style Tab Properties:**
- Text color
- Typography settings
- Drop cap styling (size, color, spacing)
- Text shadow

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'text-editor',
  props: {
    content: '<p>Enter your text here...</p>',
    dropCap: false
  }
}
```

---

### 4. Video Widget

**Purpose:** Embed YouTube, Vimeo, or self-hosted videos

**Content Tab Properties:**
- Video source (YouTube URL, Vimeo URL, Self-hosted)
- Start time (seconds)
- End time (seconds)
- Player controls options
- Show suggested videos
- Privacy mode
- Lazy load
- Poster image
- Autoplay
- Mute
- Loop

**Style Tab Properties:**
- Play icon styling (size, color, hover color)
- Lightbox options
- Aspect ratio (16:9, 4:3, 3:2, 9:16, 1:1)

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'video',
  props: {
    source: 'youtube',
    url: '',
    startTime: 0,
    endTime: 0,
    autoplay: false,
    mute: false,
    loop: false,
    controls: true,
    aspectRatio: '16:9',
    posterImage: ''
  }
}
```

---

### 5. Button Widget

**Purpose:** Clickable call-to-action button

**Content Tab Properties:**
- Button text
- Link (URL)
- Link target (_self, _blank)
- Icon selection (optional)
- Icon position (left, right)

**Style Tab Properties:**
- Typography
- Text color (normal, hover)
- Background color (normal, hover)
- Border (type, width, color, radius)
- Padding
- Box shadow

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'button',
  props: {
    text: 'Click Me',
    link: '#',
    target: '_self',
    icon: '',
    iconPosition: 'left',
    backgroundColor: '#3B82F6',
    textColor: '#FFFFFF',
    padding: '12px 24px',
    borderRadius: '6px'
  }
}
```

---

### 6. Icon Widget

**Purpose:** Display a single icon from the icon library

**Content Tab Properties:**
- Icon selection (from icon library)
- Link (optional URL)
- View (default, stacked, framed)

**Style Tab Properties:**
- Primary color
- Secondary color (for stacked/framed)
- Size (pixels)
- Padding (for stacked/framed)
- Rotate (degrees)
- Border width (for framed)
- Border radius
- Hover effects

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'icon',
  props: {
    icon: 'fas fa-star',
    link: '',
    view: 'default',
    color: '#3B82F6',
    size: '50px',
    rotate: 0
  }
}
```

---

### 7. Icon Box Widget

**Purpose:** Combined icon, title, and description content block

**Content Tab Properties:**
- Icon selection
- Title text
- Description text
- Title HTML tag (H1-H6)
- Link (URL)
- Link target

**Style Tab Properties:**
- Icon color, size, spacing
- Title typography, color, spacing
- Description typography, color
- Box background, border, padding
- Content alignment
- Hover effects

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'icon-box',
  props: {
    icon: 'fas fa-star',
    title: 'This is the heading',
    description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    titleTag: 'h3',
    link: '',
    iconPosition: 'top',
    alignment: 'center'
  }
}
```

---

### 8. Image Box Widget

**Purpose:** Combined image, title, and description content block

**Content Tab Properties:**
- Image selection
- Title text
- Description text
- Title HTML tag
- Link (URL)
- Link target

**Style Tab Properties:**
- Image position (top, left, right)
- Image size and spacing
- Title typography
- Description typography
- Box styling
- Hover effects

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'image-box',
  props: {
    image: '',
    title: 'This is the heading',
    description: 'Lorem ipsum dolor sit amet.',
    titleTag: 'h3',
    link: '',
    imagePosition: 'top'
  }
}
```

---

### 9. Divider Widget

**Purpose:** Visual separator between content sections

**Content Tab Properties:**
- Style (solid, double, dotted, dashed)
- Weight (thickness in pixels)
- Width (percentage or pixels)
- Gap (spacing above and below)
- Add text/icon in divider
- Text content
- Text position

**Style Tab Properties:**
- Color
- Gradient (for modern browsers)
- Width
- Height
- Text typography
- Icon styling
- Alignment

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'divider',
  props: {
    style: 'solid',
    weight: '1px',
    width: '100%',
    color: '#E5E7EB',
    gap: '15px',
    hasText: false,
    text: ''
  }
}
```

---

### 10. Spacer Widget

**Purpose:** Add adjustable vertical space between elements

**Content Tab Properties:**
- Space size (height in pixels)
- Responsive sizes (desktop, tablet, mobile)

**Style Tab Properties:**
- Minimal styling options

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'spacer',
  props: {
    height: '50px',
    heightTablet: '40px',
    heightMobile: '30px'
  }
}
```

---

### 11. Star Rating Widget

**Purpose:** Display visual star rating (e.g., 4 out of 5 stars)

**Content Tab Properties:**
- Rating value (0-5, 0.5 increments)
- Star count (3, 4, 5, or 10)
- Unmarked style (solid or outline)

**Style Tab Properties:**
- Icon (star or custom)
- Size
- Spacing
- Color (marked)
- Unmarked color
- Alignment

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'star-rating',
  props: {
    rating: 5,
    maxStars: 5,
    icon: 'fas fa-star',
    size: '20px',
    spacing: '5px',
    color: '#FFD700',
    unmarkedColor: '#CCCCCC'
  }
}
```

---

### 12. Accordion Widget

**Purpose:** Vertically stacked, expandable list of items

**Content Tab Properties:**
- Accordion items (array)
  - Title
  - Content
  - Default state (open/closed)
- Allow multiple open items
- FAQ schema toggle

**Style Tab Properties:**
- Border settings
- Title typography and colors (normal, hover, active)
- Content typography and colors
- Icon styling and position
- Spacing

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'accordion',
  props: {
    items: [
      {
        title: 'Accordion Item #1',
        content: 'Lorem ipsum dolor sit amet...',
        open: false
      }
    ],
    multipleOpen: false,
    titleTag: 'div',
    icon: 'fas fa-plus',
    iconActive: 'fas fa-minus'
  }
}
```

---

### 13. Toggle Widget

**Purpose:** Similar to accordion but with different visual style

**Content Tab Properties:**
- Toggle items (array)
  - Title
  - Content
  - Default state
- Title HTML tag

**Style Tab Properties:**
- Border settings
- Title styling (normal, hover, active)
- Content styling
- Icon position and styling

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'toggle',
  props: {
    items: [
      {
        title: 'Toggle Item #1',
        content: 'Lorem ipsum dolor sit amet...',
        open: false
      }
    ],
    titleTag: 'div'
  }
}
```

---

### 14. Tabs Widget

**Purpose:** Horizontal tabbed content sections

**Content Tab Properties:**
- Tab items (array)
  - Title
  - Content
  - Icon (optional)
- Active tab index
- Tabs position (top, bottom, left, right)

**Style Tab Properties:**
- Tab title styling (normal, hover, active)
- Content styling
- Border and spacing
- Icon styling

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'tabs',
  props: {
    items: [
      {
        title: 'Tab #1',
        content: 'Lorem ipsum dolor sit amet...',
        icon: ''
      }
    ],
    activeTab: 0,
    tabsPosition: 'top'
  }
}
```

---

### 15. Social Icons Widget

**Purpose:** Display social media profile links with icons

**Content Tab Properties:**
- Social profiles (array)
  - Platform (Facebook, Twitter, Instagram, etc.)
  - URL
  - Icon
- Layout (inline, grid)
- Alignment

**Style Tab Properties:**
- Icon size
- Icon color (official brand/custom)
- Icon spacing
- Icon shape (circle, square, rounded)
- Border settings
- Hover effects

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'social-icons',
  props: {
    profiles: [
      { platform: 'facebook', url: '#', icon: 'fab fa-facebook' },
      { platform: 'twitter', url: '#', icon: 'fab fa-twitter' }
    ],
    layout: 'inline',
    size: '20px',
    spacing: '10px',
    shape: 'circle'
  }
}
```

---

### 16. Icon List Widget

**Purpose:** Vertical list with icons for each item

**Content Tab Properties:**
- List items (array)
  - Text
  - Icon
  - Link (optional)
- Layout (traditional, inline)

**Style Tab Properties:**
- Icon color and size
- Text typography and color
- Space between items
- Divider styling
- Hover effects

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'icon-list',
  props: {
    items: [
      { text: 'List Item #1', icon: 'fas fa-check', link: '' },
      { text: 'List Item #2', icon: 'fas fa-check', link: '' }
    ],
    layout: 'traditional',
    iconColor: '#3B82F6',
    iconSize: '14px'
  }
}
```

---

### 17. Counter Widget

**Purpose:** Animated number counter for statistics

**Content Tab Properties:**
- Starting number
- Ending number
- Number prefix (e.g., $)
- Number suffix (e.g., +)
- Duration (animation speed)
- Thousand separator
- Title text
- Title position (above, below)

**Style Tab Properties:**
- Number typography and color
- Title typography and color
- Alignment

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'counter',
  props: {
    startNumber: 0,
    endNumber: 100,
    prefix: '',
    suffix: '',
    duration: 2000,
    separator: ',',
    title: 'Counter Title',
    titlePosition: 'below'
  }
}
```

---

### 18. Progress Bar Widget

**Purpose:** Visual progress indicator with percentage

**Content Tab Properties:**
- Title
- Percentage value (0-100)
- Display percentage (yes/no)
- Inner text (optional)

**Style Tab Properties:**
- Bar background color
- Bar color (fill)
- Height
- Border radius
- Title typography
- Percentage typography
- Animation settings

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'progress-bar',
  props: {
    title: 'Progress',
    percentage: 50,
    displayPercentage: true,
    innerText: '',
    barColor: '#3B82F6',
    backgroundColor: '#E5E7EB',
    height: '10px',
    borderRadius: '5px'
  }
}
```

---

### 19. Testimonial Widget

**Purpose:** Display customer testimonial with photo and details

**Content Tab Properties:**
- Content (testimonial text)
- Image (photo)
- Name
- Job title
- Rating (star rating)

**Style Tab Properties:**
- Content typography and color
- Image size, border, spacing
- Name typography and color
- Job title typography and color
- Alignment
- Background and border

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'testimonial',
  props: {
    content: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    image: '',
    name: 'John Doe',
    jobTitle: 'Designer',
    rating: 5,
    alignment: 'center'
  }
}
```

---

### 20. Alert Widget

**Purpose:** Notification box for important messages

**Content Tab Properties:**
- Alert type (info, success, warning, danger)
- Title
- Description
- Dismissible (yes/no)

**Style Tab Properties:**
- Background color
- Border settings
- Title color and typography
- Description color and typography
- Icon styling

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'alert',
  props: {
    type: 'info',
    title: 'Alert Title',
    description: 'Alert description text.',
    dismissible: true,
    icon: 'fas fa-info-circle'
  }
}
```

---

### 21. Basic Gallery Widget

**Purpose:** Grid of image thumbnails with lightbox

**Content Tab Properties:**
- Image selection (multiple)
- Gallery layout (grid, masonry)
- Columns (1-6)
- Image size
- Link (file, attachment, custom, none)
- Lightbox (yes/no)
- Random order

**Style Tab Properties:**
- Image spacing (gap)
- Image border and radius
- Hover effects
- Caption styling

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'basic-gallery',
  props: {
    images: [],
    layout: 'grid',
    columns: 4,
    gap: '10px',
    imageSize: 'medium',
    lightbox: true,
    aspectRatio: '1:1'
  }
}
```

---

### 22. Image Carousel Widget

**Purpose:** Slideshow of multiple images

**Content Tab Properties:**
- Image selection (multiple)
- Slides to show (1-6)
- Slides to scroll
- Autoplay (yes/no)
- Autoplay speed
- Infinite loop
- Pause on hover
- Navigation arrows
- Pagination dots
- Image size
- Link

**Style Tab Properties:**
- Image styling
- Navigation arrow styling
- Pagination dot styling
- Spacing

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'image-carousel',
  props: {
    images: [],
    slidesToShow: 3,
    slidesToScroll: 1,
    autoplay: false,
    autoplaySpeed: 3000,
    infinite: true,
    arrows: true,
    dots: true
  }
}
```

---

### 23. Text Path Widget

**Purpose:** Text along a curved or custom path

**Content Tab Properties:**
- Text content
- Path type (wave, curve, circle, custom SVG)
- Link (optional)
- Start point
- End point

**Style Tab Properties:**
- Text color and typography
- Path color and width
- Text alignment on path

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'text-path',
  props: {
    text: 'Text on a path',
    pathType: 'wave',
    link: '',
    textColor: '#000000',
    fontSize: '20px'
  }
}
```

---

### 24. Menu Anchor Widget

**Purpose:** Invisible anchor for same-page navigation

**Content Tab Properties:**
- Anchor ID (CSS ID attribute)

**Style Tab Properties:**
- None (invisible widget)

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'menu-anchor',
  props: {
    anchorId: 'section-1'
  }
}
```

---

### 25. HTML Widget

**Purpose:** Add custom HTML code

**Content Tab Properties:**
- HTML code input (textarea)

**Style Tab Properties:**
- None

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'html',
  props: {
    content: '<div>Custom HTML here</div>'
  }
}
```

---

### 26. Sidebar Widget

**Purpose:** Display WordPress sidebar widget area

**Content Tab Properties:**
- Select sidebar (from registered sidebars)

**Style Tab Properties:**
- Limited styling options

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'sidebar',
  props: {
    sidebarId: 'primary-sidebar'
  }
}
```

---

### 27. Google Maps Widget

**Purpose:** Embed Google Maps

**Content Tab Properties:**
- Address/location
- Zoom level (1-20)
- Map height
- Map type (roadmap, satellite, hybrid, terrain)

**Style Tab Properties:**
- Limited styling options

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'google-maps',
  props: {
    address: '',
    latitude: 0,
    longitude: 0,
    zoom: 14,
    height: '400px',
    mapType: 'roadmap'
  }
}
```

---

### 28. SoundCloud Widget

**Purpose:** Embed SoundCloud audio

**Content Tab Properties:**
- Track URL
- Visual options (artwork)
- Player settings (auto play, buying, sharing, download)

**Style Tab Properties:**
- Limited styling options

**Advanced Tab Properties:**
- Standard advanced options

**Default Configuration:**
```javascript
{
  type: 'soundcloud',
  props: {
    url: '',
    visual: true,
    autoPlay: false,
    buying: true,
    sharing: true,
    download: true
  }
}
```

---

### 29. Container Widget

**Purpose:** Fundamental layout structure element

**Content Tab Properties:**
- Content width (boxed, full width)
- Minimum height
- HTML tag (div, section, header, footer, etc.)
- Direction (row, column)
- Gap between items

**Style Tab Properties:**
- Background (color, gradient, image, video)
- Border and radius
- Box shadow
- Flex settings (if applicable)

**Advanced Tab Properties:**
- Standard advanced options with enhanced layout controls

**Default Configuration:**
```javascript
{
  type: 'container',
  props: {
    contentWidth: 'boxed',
    minHeight: 'auto',
    tag: 'div',
    direction: 'column',
    gap: '0px',
    children: []
  }
}
```

---

## Widget Properties Structure

### Universal Advanced Tab Features

All widgets share these common Advanced tab settings:

```javascript
{
  advanced: {
    // Layout
    layout: {
      margin: '0px',
      padding: '0px',
      zIndex: 'auto',
      positioning: 'default', // default, absolute, fixed
      width: 'auto',
      maxWidth: 'none'
    },

    // Background
    background: {
      type: 'none', // none, classic, gradient, video, slideshow
      color: '',
      image: '',
      position: 'center center',
      attachment: 'scroll',
      repeat: 'no-repeat',
      size: 'cover'
    },

    // Border
    border: {
      type: 'none', // none, solid, double, dotted, dashed, groove
      width: '1px',
      color: '#000000',
      radius: '0px'
    },

    // Box Shadow
    boxShadow: {
      horizontal: '0px',
      vertical: '0px',
      blur: '10px',
      spread: '0px',
      color: 'rgba(0,0,0,0.5)',
      position: 'outline' // outline, inset
    },

    // Typography (for text widgets)
    typography: {
      fontFamily: 'inherit',
      fontSize: '16px',
      fontWeight: '400',
      textTransform: 'none',
      fontStyle: 'normal',
      textDecoration: 'none',
      lineHeight: '1.5',
      letterSpacing: '0px'
    },

    // Motion Effects
    motionEffects: {
      scrollEffects: {
        verticalScroll: false,
        horizontalScroll: false,
        transparency: false,
        blur: false,
        rotate: false,
        scale: false
      },
      mouseEffects: {
        mouseTrack: false,
        3dTilt: false
      }
    },

    // Responsive
    responsive: {
      hideOnDesktop: false,
      hideOnTablet: false,
      hideOnMobile: false
    },

    // Custom Attributes
    attributes: {
      id: '',
      classes: '',
      dataAttributes: {}
    },

    // Custom CSS
    customCSS: ''
  }
}
```

---

## Implementation Guide

### 1. Widget Registration System

```php
// app/Services/WidgetRegistry.php
namespace App\Services;

class WidgetRegistry
{
    protected array $widgets = [];

    public function register(string $type, array $config): void
    {
        $this->widgets[$type] = $config;
    }

    public function get(string $type): ?array
    {
        return $this->widgets[$type] ?? null;
    }

    public function all(): array
    {
        return $this->widgets;
    }

    public function getByCategory(string $category): array
    {
        return array_filter($this->widgets, fn($w) => $w['category'] === $category);
    }
}
```

### 2. Widget Renderer Service

```php
// app/Services/WidgetRenderer.php
namespace App\Services;

class WidgetRenderer
{
    public function __construct(
        private WidgetRegistry $registry
    ) {}

    public function render(array $widget): string
    {
        $type = $widget['type'] ?? 'unknown';
        $method = 'render' . str_replace('-', '', ucwords($type, '-'));

        if (method_exists($this, $method)) {
            return $this->$method($widget);
        }

        return "<!-- Unknown widget: {$type} -->";
    }

    protected function renderHeading(array $widget): string
    {
        $props = $widget['props'] ?? [];
        $tag = $props['tag'] ?? 'h2';
        $content = e($props['content'] ?? '');
        $styles = $this->buildStyles($widget['styles'] ?? [], $props);

        $html = "<{$tag} style=\"{$styles}\">{$content}</{$tag}>";

        if (!empty($props['link'])) {
            $link = e($props['link']);
            $html = "<a href=\"{$link}\">{$html}</a>";
        }

        return $html;
    }

    protected function renderImage(array $widget): string
    {
        $props = $widget['props'] ?? [];
        $src = e($props['src'] ?? '');
        $alt = e($props['alt'] ?? '');
        $styles = $this->buildStyles($widget['styles'] ?? [], $props);

        $html = "<img src=\"{$src}\" alt=\"{$alt}\" style=\"{$styles}\" loading=\"lazy\">";

        if (!empty($props['link'])) {
            $link = e($props['link']);
            $target = $props['target'] ?? '_self';
            $html = "<a href=\"{$link}\" target=\"{$target}\">{$html}</a>";
        }

        if ($props['lightbox'] ?? false) {
            $html = "<a href=\"{$src}\" data-lightbox=\"image\">{$html}</a>";
        }

        return $html;
    }

    // Add more render methods for each widget type...

    protected function buildStyles(array $styles, array $props): string
    {
        $css = [];

        // Add custom styles
        foreach ($styles as $property => $value) {
            if ($value !== null && $value !== '') {
                $css[] = "{$property}: {$value}";
            }
        }

        // Add typography styles
        if (isset($props['typography'])) {
            foreach ($props['typography'] as $property => $value) {
                $cssProperty = $this->camelToKebab($property);
                $css[] = "{$cssProperty}: {$value}";
            }
        }

        return implode('; ', $css);
    }

    protected function camelToKebab(string $string): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $string));
    }
}
```

### 3. AlpineJS Widget Component

```javascript
// resources/js/components/widget.js
export default function widget() {
    return {
        widgets: [],
        selectedWidget: null,

        addWidget(type) {
            const config = this.getWidgetConfig(type);
            const widget = {
                id: this.generateId(),
                type: type,
                label: config.label,
                props: { ...config.defaultProps },
                styles: {},
                advanced: this.getDefaultAdvanced()
            };

            this.widgets.push(widget);
            this.selectWidget(widget.id);
        },

        getWidgetConfig(type) {
            const configs = {
                'heading': {
                    label: 'Heading',
                    icon: 'H',
                    category: 'basic',
                    defaultProps: {
                        content: 'New Heading',
                        tag: 'h2',
                        color: '#000000',
                        fontSize: '32px',
                        fontWeight: '700'
                    }
                },
                'image': {
                    label: 'Image',
                    icon: '🖼',
                    category: 'media',
                    defaultProps: {
                        src: '',
                        alt: '',
                        width: '100%',
                        objectFit: 'cover'
                    }
                }
                // Add more widget configs...
            };

            return configs[type] || configs['heading'];
        },

        getDefaultAdvanced() {
            return {
                layout: {
                    margin: '0px',
                    padding: '0px'
                },
                background: {
                    type: 'none'
                },
                border: {
                    type: 'none'
                },
                responsive: {
                    hideOnDesktop: false,
                    hideOnTablet: false,
                    hideOnMobile: false
                },
                attributes: {
                    id: '',
                    classes: ''
                }
            };
        },

        generateId() {
            return 'widget_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }
    };
}
```

### 4. Widget Blade Component

```blade
{{-- resources/views/components/widget-renderer.blade.php --}}
@props(['widget'])

@php
    $type = $widget['type'] ?? 'unknown';
    $componentPath = "components.widgets.{$type}";
@endphp

@if(view()->exists($componentPath))
    <x-dynamic-component
        :component="$componentPath"
        :widget="$widget"
    />
@else
    <!-- Widget type "{{ $type }}" not found -->
@endif
```

---

## Widget Database Schema

```php
// database/migrations/xxxx_create_widgets_table.php
Schema::create('widgets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('page_id')->constrained()->onDelete('cascade');
    $table->string('type', 50)->index();
    $table->string('label', 100);
    $table->json('props');
    $table->json('styles')->nullable();
    $table->json('advanced')->nullable();
    $table->integer('sort_order')->default(0);
    $table->foreignId('parent_id')->nullable()->constrained('widgets')->onDelete('cascade');
    $table->boolean('is_visible')->default(true);
    $table->timestamps();
    $table->softDeletes();

    $table->index(['page_id', 'sort_order']);
});
```

---

## Summary

This widget system provides:

- **28 Elementor-inspired widgets** covering all essential page building needs
- **Comprehensive property system** with Content, Style, and Advanced tabs
- **Universal advanced features** for all widgets (margins, padding, backgrounds, borders, etc.)
- **Flexible architecture** supporting custom widget development
- **Laravel service layer** for widget registration and rendering
- **AlpineJS integration** for interactive widget management
- **Database schema** optimized for widget storage and retrieval

The system maintains compatibility with existing Laravel Blade + AlpineJS + TailwindCSS architecture while providing Elementor-level widget functionality.
