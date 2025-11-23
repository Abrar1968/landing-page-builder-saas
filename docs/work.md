# Implementation Work Plan

Based on the analysis of Elementor Pro features vs. our current Vue builder, here are the prioritized implementation tasks.

---

## Gap Analysis Summary

### Current State
- ✅ 14 widgets registered
- ✅ Basic control types (text, textarea, number, color, slider, select, choose, switcher, dimensions, media, wysiwyg)
- ✅ Three-tab system (Content/Style/Advanced)
- ✅ Navigator panel
- ✅ History (undo/redo)
- ✅ Keyboard shortcuts
- ✅ Drag and drop
- ✅ Section > Column > Widget hierarchy

### Missing Features

#### High Priority
1. [ ] Missing Group Controls (Typography, Background, Border, Box Shadow)
2. [ ] Right-click context menu
3. [ ] Additional widgets (Tabs, Accordion, Image Box, etc.)
4. [ ] Proper URL control with external link options
5. [ ] Custom CSS field in Advanced tab
6. [ ] CSS Classes/ID in Advanced tab for all elements

#### Medium Priority
7. [ ] Visual history panel
8. [ ] Entrance animations control
9. [ ] Responsive controls per breakpoint
10. [ ] More comprehensive widget styling options

#### Lower Priority
11. [ ] Global widgets
12. [ ] Templates library
13. [ ] Container/Flexbox layout (modern alternative)
14. [ ] Form builder widget

---

## Implementation Tasks

### Phase 1: Essential Control Improvements

#### Task 1.1: Add Typography Group Control ✅
**Status**: TODO → DONE
**Files**: `ControlRenderer.vue`, `registry.js`

Add typography group control with:
- Font family selector
- Font size (with unit)
- Font weight
- Text transform
- Font style
- Line height
- Letter spacing

```javascript
// Control definition
{ name: 'title_typography', type: 'typography', label: 'Typography' }

// Return value
{
  family: 'Inter',
  size: { value: 16, unit: 'px' },
  weight: '600',
  transform: 'none',
  style: 'normal',
  lineHeight: { value: 1.5, unit: 'em' },
  letterSpacing: { value: 0, unit: 'px' }
}
```

#### Task 1.2: Add Background Group Control
**Status**: TODO
**Files**: `ControlRenderer.vue`, `registry.js`

Add background control with:
- Background type (Classic/Gradient)
- Color picker
- Image selector
- Position
- Attachment (scroll/fixed)
- Repeat
- Size (auto/cover/contain)
- Gradient colors and direction

#### Task 1.3: Add Border Group Control
**Status**: TODO
**Files**: `ControlRenderer.vue`, `registry.js`

Add border control with:
- Border type (none/solid/dashed/dotted/double)
- Border width (dimensions for each side)
- Border color
- Border radius (dimensions for each corner)

#### Task 1.4: Add Box Shadow Group Control
**Status**: TODO
**Files**: `ControlRenderer.vue`, `registry.js`

Add box shadow control with:
- Horizontal offset
- Vertical offset
- Blur
- Spread
- Color
- Position (outline/inset)

#### Task 1.5: Add URL Control
**Status**: TODO
**Files**: `ControlRenderer.vue`

Enhance URL input with:
- URL text input
- External link toggle (opens in new tab)
- Nofollow toggle

---

### Phase 2: UI/UX Improvements

#### Task 2.1: Add Right-Click Context Menu
**Status**: TODO
**Files**: `App.vue`

Implement context menu with options:
- Edit [Element Type]
- Copy
- Paste
- Paste Style
- Duplicate
- Delete
- Save as Template (future)

#### Task 2.2: Add Custom CSS Field to Advanced Tab
**Status**: TODO
**Files**: `registry.js`, `stores/builder.js`, `WidgetRenderer.vue`

Add CSS code editor for custom styling:
- Syntax highlighting
- Use `selector` keyword to target element
- Apply styles in renderer

#### Task 2.3: Add CSS Classes/ID Fields
**Status**: TODO
**Files**: `registry.js`

Ensure ALL element types have:
- CSS Classes field (text)
- CSS ID field (text)

#### Task 2.4: Visual History Panel
**Status**: TODO
**Files**: `App.vue`

Add history panel showing:
- List of actions with timestamps
- Click to restore any state
- Visual indication of current position

---

### Phase 3: Additional Widgets

#### Task 3.1: Add Tabs Widget
**Status**: TODO
**Files**: `registry.js`, `WidgetRenderer.vue`

Controls:
- Repeater for tabs (title, content)
- Tab position (top/left/right)
- Tab alignment

#### Task 3.2: Add Accordion Widget
**Status**: TODO
**Files**: `registry.js`, `WidgetRenderer.vue`

Controls:
- Repeater for items (title, content)
- Icon open/close
- First open by default

#### Task 3.3: Add Image Box Widget
**Status**: TODO
**Files**: `registry.js`, `WidgetRenderer.vue`

Controls:
- Image
- Title
- Description
- Link
- Hover animation

#### Task 3.4: Add Star Rating Widget
**Status**: TODO
**Files**: `registry.js`, `WidgetRenderer.vue`

Controls:
- Rating scale (1-5, 1-10)
- Rating value
- Icon (stars, hearts)
- Size, color

#### Task 3.5: Add Icon List Widget
**Status**: TODO
**Files**: `registry.js`, `WidgetRenderer.vue`

Controls:
- Repeater for items (icon, text, link)
- Icon color
- Space between items

#### Task 3.6: Add Flip Box Widget
**Status**: TODO
**Files**: `registry.js`, `WidgetRenderer.vue`

Controls:
- Front content (icon, title, description)
- Back content (icon, title, description, button)
- Flip direction
- Flip trigger (hover/click)

#### Task 3.7: Add Countdown Timer Widget
**Status**: TODO
**Files**: `registry.js`, `WidgetRenderer.vue`

Controls:
- Due date/time
- Show labels
- Days/Hours/Minutes/Seconds visibility
- Styling options

#### Task 3.8: Add Call to Action Widget
**Status**: TODO
**Files**: `registry.js`, `WidgetRenderer.vue`

Controls:
- Image/background
- Title
- Description
- Button text/link
- Ribbon text

#### Task 3.9: Add Google Maps Widget
**Status**: TODO
**Files**: `registry.js`, `WidgetRenderer.vue`

Controls:
- Location address or coordinates
- Zoom level
- Height
- Map type

---

### Phase 4: Section/Column Improvements

#### Task 4.1: Add Section Background Controls
**Status**: TODO
**Files**: `stores/builder.js`

Use the new background group control for sections:
- Classic background
- Gradient background
- Background overlay

#### Task 4.2: Add Column Width Customization
**Status**: TODO
**Files**: `stores/builder.js`, `App.vue`

Allow custom column widths:
- Drag to resize columns
- Exact percentage input
- Preset layouts (25/75, 75/25, etc.)

#### Task 4.3: Add Vertical Alignment
**Status**: TODO
**Files**: `stores/builder.js`, `App.vue`

Add vertical alignment to columns:
- Top
- Middle
- Bottom
- Space between
- Space evenly

---

### Phase 5: Advanced Features (Future)

#### Task 5.1: Responsive Controls
**Status**: TODO

Allow different values per breakpoint:
- Desktop
- Tablet
- Mobile
- Custom breakpoints

#### Task 5.2: Entrance Animations
**Status**: TODO

Add motion effects:
- Animation type (fade, slide, zoom, bounce)
- Duration
- Delay
- Easing

#### Task 5.3: Global Widgets
**Status**: TODO

Save widgets as reusable:
- Save to library
- Edit affects all instances
- Convert to local copy

#### Task 5.4: Templates Library
**Status**: TODO

Pre-built sections/pages:
- Load from library
- Save sections as templates
- Categories and search

---

## Implementation Order

### Sprint 1: Core Controls (Current Focus)
1. ✅ Typography group control
2. ✅ Background group control
3. ✅ Border group control
4. ✅ Box shadow group control
5. ✅ URL control with options
6. ✅ Custom CSS field

### Sprint 2: UI/UX
7. Right-click context menu
8. CSS Classes/ID for all elements
9. Visual history panel

### Sprint 3: Essential Widgets
10. Tabs widget
11. Accordion widget
12. Image Box widget
13. Star Rating widget
14. Icon List widget

### Sprint 4: Marketing Widgets
15. Countdown Timer widget
16. Flip Box widget
17. Call to Action widget
18. Google Maps widget

### Sprint 5: Advanced Features
19. Section background improvements
20. Column width customization
21. Responsive controls
22. Entrance animations

---

## Completion Tracking

| Task | Status | Date Completed |
|------|--------|----------------|
| Typography control | ✅ | 2025-11-23 |
| Background control | ✅ | 2025-11-23 |
| Border control | ✅ | 2025-11-23 |
| Box shadow control | ✅ | 2025-11-23 |
| URL control | ✅ | 2025-11-23 |
| Custom CSS field | ✅ | 2025-11-23 |
| Right-click menu | ⬜ | |
| Context menu | ⬜ | |
| History panel | ⬜ | |
| Tabs widget | ✅ | 2025-11-23 |
| Accordion widget | ✅ | 2025-11-23 |
| Image Box widget | ✅ | 2025-11-23 |
| Star Rating widget | ✅ | 2025-11-23 |
| Icon List widget | ⬜ | |
| Flip Box widget | ✅ | 2025-11-23 |
| Countdown widget | ✅ | 2025-11-23 |
| CTA widget | ✅ | 2025-11-23 |
| Google Maps widget | ✅ | 2025-11-23 |
| Price Table widget | ✅ | 2025-11-23 |

---

## Notes

### Backend Compatibility
All changes must be compatible with:
- Existing Page model JSON structure
- PageService for save/publish
- PageObserver for versioning
- Media model for image uploads

### Database Schema
Current schema supports:
- `pages.content` - JSON storage for all elements
- `pages.settings` - Page-level settings
- `page_versions` - Automatic version history

No schema changes required for these improvements.
