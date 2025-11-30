# Elementor Pro-Style Builder Implementation Plan

## ✅ Already Implemented (Current State)

### Core Infrastructure
- ✅ Vue 3 + Pinia for state management
- ✅ Sections/Columns/Widgets hierarchy
- ✅ Drag & drop widget placement
- ✅ Basic widget library (15+ widgets)
- ✅ Undo/Redo history
- ✅ Media library integration
- ✅ Save/Publish/Preview functionality
- ✅ Responsive preview modes (desktop/tablet/mobile)

### Control Types Available
- ✅ Text, Textarea, Number inputs
- ✅ Select, Choose (button group)
- ✅ Color picker
- ✅ Slider with units
- ✅ Switcher (toggle)
- ✅ Media picker
- ✅ WYSIWYG editor
- ✅ Dimensions (margin/padding with 4 sides)
- ✅ URL with options (new tab, nofollow)
- ✅ Typography group (family, size, weight, transform, style, line-height, letter-spacing)
- ✅ Background group (classic/gradient, color, image, position, size)
- ✅ Border group (style, width, color, radius for 4 corners)
- ✅ Box Shadow group (horizontal, vertical, blur, spread, color, position)
- ✅ Custom CSS/Code editor

### Widgets Available
1. ✅ Heading (H1-H6)
2. ✅ Text Editor (WYSIWYG)
3. ✅ Image
4. ✅ Button
5. ✅ Video (YouTube/Vimeo)
6. ✅ Divider
7. ✅ Spacer
8. ✅ Icon
9. ✅ Icon Box
10. ✅ Counter
11. ✅ Progress Bar
12. ✅ Testimonial
13. ✅ Social Icons
14. ✅ Alert
15. ✅ Image Box
16. ✅ Star Rating
17. ✅ Tabs
18. ✅ Accordion
19. ✅ Countdown
20. ✅ Google Maps
21. ✅ Call to Action
22. ✅ Flip Box
23. ✅ Price Table

## 🚀 Features to Implement (Elementor Pro Enhancements)

### Priority 1: Essential UX Features

#### 1. Right-Click Contextual Menu ⭐⭐⭐
**File**: `resources/js/builder/components/ContextMenu.vue`
**Features**:
- Edit (select and open settings)
- Duplicate
- Copy/Paste
- Delete
- Save as Template/Global
- Navigator (show in hierarchy)
- Add New Section/Column (context-aware)
- Reset Style
- Copy Style / Paste Style

**Implementation**:
```vue
// New component with position tracking
// Listen to contextmenu event on canvas elements
// Show floating menu at cursor position
// Filter options based on element type
```

#### 2. Default Values & Placeholders ⭐⭐⭐
**Enhancement**: Widget registry defaults
**Current Issue**: Some widgets show empty states instead of demo content
**Solution**: 
- Ensure ALL widgets have comprehensive default values
- Add placeholder content for heading: "Add Your Heading Text Here"
- Text editor: "Lorem ipsum dolor sit amet..." sample paragraphs
- Button: "Click Me"
- Image: Placeholder with icon
- Hover to show "Click to edit" hints

#### 3. Enhanced Nested Containers ⭐⭐
**File**: `resources/js/builder/stores/builder.js`
**Features**:
- Inner Section/Inner Container element type
- Unlimited nesting depth
- Navigator panel shows full hierarchy tree
- Drag widgets into nested containers
- Visual indicators for nesting levels

#### 4. Widget-Specific Icon Library ⭐⭐
**New File**: `resources/js/builder/components/IconPicker.vue`
**Features**:
- Modal with searchable icon grid
- Font Awesome, Heroicons, custom SVGs
- Icon picker control type
- Replace emoji placeholders with real icons

### Priority 2: Advanced Styling

#### 5. Hover State Controls ⭐⭐⭐
**Enhancement**: Add "Normal" vs "Hover" tabs in Style panel
**Implementation**:
- Store separate `settings` and `hover_settings`
- Apply `:hover` CSS when rendering
- Available for: Button, Image, Icon, Links
- Hover colors, backgrounds, borders, transforms

#### 6. Motion Effects ⭐⭐
**New Tab**: "Motion Effects" in Advanced tab
**Features**:
- Entrance Animation (fade, slide, zoom, bounce)
- Scrolling Effects:
  - Fade In/Out based on scroll position
  - Vertical/Horizontal movement (parallax)
  - Scale/Rotate on scroll
  - Transparency on scroll
- Mouse Effects:
  - Track (element follows mouse)
  - Tilt (3D tilt based on mouse position)
- Sticky Positioning:
  - Stick to Top/Bottom
  - Sticky offset
  - Effects on sticky

#### 7. Responsive Controls per Device ⭐⭐
**Enhancement**: Device-specific values for controls
**Implementation**:
- Add device toggle (desktop/tablet/mobile icons) in control header
- Store settings as:
  ```json
  {
    "margin": {
      "desktop": { "top": 20, "right": 20, "bottom": 20, "left": 20 },
      "tablet": { "top": 15, "right": 15, "bottom": 15, "left": 15 },
      "mobile": { "top": 10, "right": 10, "bottom": 10, "left": 10 }
    }
  }
  ```
- Apply correct values based on viewport width in preview

#### 8. Custom CSS per Element ⭐
**Already in registry, needs UI enhancement**:
- Better syntax highlighting
- CSS variables support
- Selector auto-complete
- Live preview of CSS changes

### Priority 3: Pro Widgets

#### 9. Form Builder Widget ⭐⭐⭐
**New Widget**: `form-builder`
**Features**:
- Text input, Email, Textarea, Select, Checkbox, Radio
- Submit button
- Form validation
- Integration with form_submissions table
- Success message / Redirect on submit
- Email notifications

#### 10. Slider/Carousel Widget ⭐⭐
**New Widget**: `slider`
**Features**:
- Multiple slides
- Image + Text per slide
- Navigation arrows
- Dots pagination
- Autoplay with timing
- Transition effects (slide/fade)

#### 11. Posts Grid Widget ⭐
**New Widget**: `posts-grid`
**Features**:
- Display published pages as grid
- Filterable by template category
- Card layout with image, title, excerpt
- Pagination
- Link to page URL

#### 12. Pricing Tables (Enhanced) ⭐
**Enhancement**: Make price-table more flexible
- Multiple plans in one widget (3-column layout)
- Featured plan highlight
- Feature comparison checkmarks
- Hover effects
- Currency symbol customization

### Priority 4: Productivity Features

#### 13. Navigator Enhancements ⭐⭐
**Enhancement**: Better hierarchy visualization
- Tree-view with expand/collapse
- Drag-and-drop reordering in navigator
- Right-click in navigator for context menu
- Show/hide eye icon per element
- Lock icon to prevent editing

#### 14. Keyboard Shortcuts ⭐
**Already partially implemented, needs expansion**:
- ✅ Ctrl+S: Save
- ✅ Ctrl+Z: Undo
- ✅ Ctrl+Y: Redo
- ✅ Ctrl+C/V: Copy/Paste
- ✅ Ctrl+D: Duplicate
- ✅ Delete: Delete element
- ❌ Ctrl+G: Group into container
- ❌ Ctrl+Shift+G: Ungroup
- ❌ Arrow keys: Move selection
- ❌ Escape: Deselect
- ❌ Enter: Edit text inline

#### 15. Find & Replace ⭐
**New Feature**: Global text search/replace
- Search across all page content
- Replace text in widgets
- Regex support
- Case-sensitive option

#### 16. Revision History ⭐
**Enhancement**: Use `page_versions` table
- Automatic versioning on save
- Compare revisions side-by-side
- Restore previous version
- Version naming/comments

#### 17. Global Widgets/Templates ⭐⭐
**New Feature**: Reusable components
- Save widget as global
- Update global widget updates all instances
- Template library integration
- Categories: Header, Footer, Hero, CTA, etc.

### Priority 5: Performance & Polish

#### 18. Live Rendering Optimization ⭐⭐
- Debounce style updates
- Virtual scrolling for long pages
- Lazy render off-screen widgets
- Cache computed styles

#### 19. Loading States & Skeletons ⭐
- Show skeleton loader while media loads
- Progress indicator for save/publish
- Optimistic UI updates

#### 20. Error Handling ⭐
- Validation feedback on save
- Undo on error
- Toast notifications for success/error
- Graceful degradation for missing assets

## 📋 Implementation Phases

### Phase 1: Core UX (Week 1)
- ✅ Right-click contextual menu
- ✅ Default values for all widgets
- ✅ Icon library/picker
- ✅ Navigator enhancements

### Phase 2: Advanced Styling (Week 2)
- ✅ Hover state controls
- ✅ Responsive device controls
- ✅ Motion effects (basic)

### Phase 3: Pro Widgets (Week 3)
- ✅ Form builder
- ✅ Slider/Carousel
- ✅ Enhanced pricing tables

### Phase 4: Polish & Performance (Week 4)
- ✅ Keyboard shortcuts expansion
- ✅ Revision history UI
- ✅ Performance optimization
- ✅ Testing & bug fixes

## 🎯 Success Criteria

1. **Visual Parity**: Builder looks and feels like Elementor Pro
2. **Feature Complete**: All listed Elementor features implemented
3. **Performance**: < 100ms response time for interactions
4. **Stability**: No data loss, robust undo/redo
5. **User Delight**: Smooth animations, helpful defaults, intuitive UX

## 📁 File Structure

```
resources/js/builder/
├── App.vue                        # Main builder app
├── main.js                        # Entry point
├── components/
│   ├── ControlRenderer.vue       # ✅ Advanced controls
│   ├── WidgetRenderer.vue        # ✅ Render widgets
│   ├── MediaLibraryModal.vue     # ✅ Media picker
│   ├── ContextMenu.vue           # ❌ NEW: Right-click menu
│   ├── IconPicker.vue            # ❌ NEW: Icon library
│   ├── Navigator.vue             # ❌ NEW: Enhanced tree view
│   ├── RevisionHistory.vue       # ❌ NEW: Version browser
│   ├── FindReplace.vue           # ❌ NEW: Search/replace
│   └── HoverStateToggle.vue      # ❌ NEW: Normal/Hover tabs
├── stores/
│   ├── builder.js                # ✅ Main state management
│   └── templates.js              # ❌ NEW: Global widgets store
├── widgets/
│   ├── registry.js               # ✅ Widget definitions
│   ├── FormBuilder.js            # ❌ NEW
│   ├── Slider.js                 # ❌ NEW
│   └── PostsGrid.js              # ❌ NEW
└── utils/
    ├── animations.js             # ❌ NEW: Motion effects
    ├── responsive.js             # ❌ NEW: Device breakpoints
    └── icons.js                  # ❌ NEW: Icon library data
```

## 🔧 Technical Notes

### Widget Type Field Inconsistency
**Current**: Some widgets use `widget.type`, others use `widget.widgetType`
**Fix**: Standardize on `widgetType` throughout

### Settings Structure
**Current**: Flat `settings` object
**Proposed**: Organize by category
```json
{
  "content": { "title": "Hello", "link": "#" },
  "style": { "color": "#000", "typography": {...} },
  "advanced": { "margin": {...}, "motion": {...} },
  "hover": { "color": "#f00" },
  "responsive": {
    "tablet": { "margin": {...} },
    "mobile": { "margin": {...} }
  }
}
```

### CSS Generation
**Current**: Inline styles only
**Proposed**: Generate `<style>` block for:
- Hover states
- Responsive breakpoints
- Motion effects
- Custom CSS

### Backend API Needs
- ✅ `/api/media` - Already exists
- ❌ `/api/icons` - Icon library endpoint
- ❌ `/api/templates/global` - Global widgets CRUD
- ✅ `/api/pages/{page}/versions` - Already in routes

---

**Last Updated**: November 23, 2025
**Status**: Analysis Complete, Ready for Implementation
