# Builder Interface View

## Overview
The Builder Interface is a full-screen, drag-and-drop page editor that allows users to create and customize landing pages with real-time preview capabilities.

---

## Full-Screen Builder Layout

### Container Structure
```css
.builder-layout {
  display: grid;
  grid-template-columns: 280px 1fr 320px;
  grid-template-rows: 56px 1fr;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
  background-color: #1a1a1a;
}
```

### Layout Regions
- **Top Toolbar**: Spans full width, fixed height 56px
- **Elements Panel**: Left sidebar, 280px width
- **Canvas Area**: Center, flexible width
- **Properties Panel**: Right sidebar, 320px width

---

## Top Toolbar

### Container Styling
```css
.top-toolbar {
  grid-column: 1 / -1;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 16px;
  background-color: #ffffff;
  border-bottom: 1px solid #e5e7eb;
  height: 56px;
  z-index: 100;
}
```

### Left Section - Navigation
```css
.toolbar-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.back-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.back-button:hover {
  background-color: #f3f4f6;
}

.page-name {
  font-size: 14px;
  font-weight: 600;
  color: #111827;
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
```

### Center Section - Device Toggles & History
```css
.toolbar-center {
  display: flex;
  align-items: center;
  gap: 24px;
}

.device-toggles {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px;
  background-color: #f3f4f6;
  border-radius: 8px;
}

.device-toggle-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 32px;
  border-radius: 6px;
  border: none;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.15s ease;
}

.device-toggle-btn.active {
  background-color: #ffffff;
  color: #111827;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.device-toggle-btn:hover:not(.active) {
  color: #374151;
}

.history-controls {
  display: flex;
  align-items: center;
  gap: 4px;
}

.history-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  color: #374151;
  cursor: pointer;
  transition: all 0.15s ease;
}

.history-btn:hover:not(:disabled) {
  background-color: #f9fafb;
  border-color: #d1d5db;
}

.history-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
```

### Right Section - Actions
```css
.toolbar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.preview-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  cursor: pointer;
  transition: all 0.15s ease;
}

.preview-btn:hover {
  background-color: #f9fafb;
  border-color: #d1d5db;
}

.settings-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  color: #374151;
  cursor: pointer;
  transition: all 0.15s ease;
}

.settings-btn:hover {
  background-color: #f9fafb;
}

.publish-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 20px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  color: #ffffff;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border: none;
  cursor: pointer;
  transition: all 0.15s ease;
  box-shadow: 0 1px 2px rgba(99, 102, 241, 0.3);
}

.publish-btn:hover {
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
  transform: translateY(-1px);
}
```

---

## Elements Panel (Left Sidebar)

### Container Styling
```css
.elements-panel {
  display: flex;
  flex-direction: column;
  background-color: #ffffff;
  border-right: 1px solid #e5e7eb;
  overflow: hidden;
}

.elements-panel-header {
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.elements-search {
  width: 100%;
  padding: 10px 12px 10px 36px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  font-size: 14px;
  background-color: #f9fafb;
  background-image: url("data:image/svg+xml,..."); /* Search icon */
  background-repeat: no-repeat;
  background-position: 12px center;
  transition: all 0.15s ease;
}

.elements-search:focus {
  outline: none;
  border-color: #6366f1;
  background-color: #ffffff;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
```

### Element Categories
```css
.elements-content {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
}

.element-category {
  margin-bottom: 24px;
}

.category-title {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #6b7280;
  margin-bottom: 12px;
}

.element-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
}
```

### Element Items
```css
.element-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 16px 12px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  cursor: grab;
  transition: all 0.15s ease;
}

.element-item:hover {
  border-color: #6366f1;
  background-color: #f5f3ff;
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.15);
}

.element-item:active {
  cursor: grabbing;
  transform: scale(0.98);
}

.element-icon {
  width: 24px;
  height: 24px;
  color: #6366f1;
}

.element-name {
  font-size: 12px;
  font-weight: 500;
  color: #374151;
  text-align: center;
}
```

### Element Categories List
- **Basic**: Heading, Text, Button, Image, Divider, Spacer
- **Layout**: Container, Columns, Section, Grid
- **Media**: Video, Icon, Gallery, Carousel
- **Forms**: Input, Textarea, Select, Checkbox, Radio, Form
- **Advanced**: HTML, Code, Map, Social Links

---

## Canvas Area

### Container Styling
```css
.canvas-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  background-color: #f3f4f6;
  overflow: auto;
  padding: 24px;
}

.canvas-wrapper {
  position: relative;
  background-color: #ffffff;
  border-radius: 8px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: width 0.3s ease;
  min-height: 100%;
}

/* Device Widths */
.canvas-wrapper.desktop {
  width: 100%;
  max-width: 1200px;
}

.canvas-wrapper.tablet {
  width: 768px;
}

.canvas-wrapper.mobile {
  width: 375px;
}
```

### Canvas Content
```css
.canvas-content {
  position: relative;
  min-height: 600px;
  padding: 20px;
}

.canvas-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 400px;
  color: #9ca3af;
}

.empty-state-icon {
  width: 64px;
  height: 64px;
  margin-bottom: 16px;
  opacity: 0.5;
}

.empty-state-text {
  font-size: 16px;
  font-weight: 500;
  margin-bottom: 8px;
}

.empty-state-hint {
  font-size: 14px;
  color: #d1d5db;
}
```

### Drop Zone Indicators
```css
.drop-zone {
  position: relative;
  min-height: 40px;
  border: 2px dashed transparent;
  border-radius: 4px;
  transition: all 0.15s ease;
}

.drop-zone.active {
  border-color: #6366f1;
  background-color: rgba(99, 102, 241, 0.05);
}

.drop-indicator {
  position: absolute;
  left: 0;
  right: 0;
  height: 3px;
  background-color: #6366f1;
  border-radius: 2px;
}
```

### Selected Element Overlay
```css
.element-selected {
  position: relative;
  outline: 2px solid #6366f1;
  outline-offset: 2px;
}

.element-controls {
  position: absolute;
  top: -36px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px;
  background: #1f2937;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.element-control-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 4px;
  border: none;
  background: transparent;
  color: #ffffff;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.element-control-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.element-control-btn.delete:hover {
  background-color: rgba(239, 68, 68, 0.8);
}
```

---

## Properties Panel (Right Sidebar)

### Container Styling
```css
.properties-panel {
  display: flex;
  flex-direction: column;
  background-color: #ffffff;
  border-left: 1px solid #e5e7eb;
  overflow: hidden;
}

.properties-panel-header {
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.properties-title {
  font-size: 14px;
  font-weight: 600;
  color: #111827;
}

.properties-element-type {
  font-size: 12px;
  color: #6b7280;
  margin-top: 4px;
}
```

### Tab Navigation
```css
.properties-tabs {
  display: flex;
  border-bottom: 1px solid #e5e7eb;
}

.properties-tab {
  flex: 1;
  padding: 12px 16px;
  font-size: 13px;
  font-weight: 500;
  color: #6b7280;
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  cursor: pointer;
  transition: all 0.15s ease;
}

.properties-tab:hover {
  color: #374151;
}

.properties-tab.active {
  color: #6366f1;
  border-bottom-color: #6366f1;
}
```

### Style Tab Content
```css
.properties-content {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
}

.property-group {
  margin-bottom: 24px;
}

.property-group-title {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #6b7280;
  margin-bottom: 12px;
}

.property-row {
  margin-bottom: 12px;
}

.property-label {
  display: block;
  font-size: 12px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 6px;
}

.property-input {
  width: 100%;
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  font-size: 13px;
  transition: all 0.15s ease;
}

.property-input:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
```

### Style Controls
```css
/* Color Picker */
.color-picker-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
}

.color-swatch {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  cursor: pointer;
}

.color-input {
  flex: 1;
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  font-size: 13px;
  font-family: 'Monaco', monospace;
}

/* Spacing Controls */
.spacing-control {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
}

.spacing-input {
  padding: 8px;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  font-size: 12px;
  text-align: center;
}

/* Typography Controls */
.font-controls {
  display: grid;
  grid-template-columns: 1fr 80px;
  gap: 8px;
}

.font-select {
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  font-size: 13px;
  background: #ffffff;
}

/* Alignment Controls */
.alignment-controls {
  display: flex;
  gap: 4px;
}

.alignment-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.15s ease;
}

.alignment-btn:hover {
  border-color: #d1d5db;
}

.alignment-btn.active {
  background-color: #6366f1;
  border-color: #6366f1;
  color: #ffffff;
}
```

### Settings Tab Content
```css
.settings-content {
  padding: 16px;
}

/* Toggle Switch */
.toggle-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid #f3f4f6;
}

.toggle-label {
  font-size: 13px;
  font-weight: 500;
  color: #374151;
}

.toggle-switch {
  position: relative;
  width: 44px;
  height: 24px;
  background-color: #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.toggle-switch.active {
  background-color: #6366f1;
}

.toggle-switch::after {
  content: '';
  position: absolute;
  top: 2px;
  left: 2px;
  width: 20px;
  height: 20px;
  background-color: #ffffff;
  border-radius: 50%;
  transition: transform 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.toggle-switch.active::after {
  transform: translateX(20px);
}

/* Link Settings */
.link-settings {
  margin-top: 16px;
}

.link-type-select {
  width: 100%;
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  font-size: 13px;
  margin-bottom: 12px;
}
```

---

## Page Settings Modal

### Modal Container
```css
.modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease;
}

.page-settings-modal {
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;
  animation: slideUp 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
```

### Modal Header
```css
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-title {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
}

.modal-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: none;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.15s ease;
}

.modal-close:hover {
  background-color: #f3f4f6;
  color: #374151;
}
```

### Modal Content
```css
.modal-content {
  padding: 24px;
  overflow-y: auto;
  max-height: calc(90vh - 140px);
}

.settings-section {
  margin-bottom: 24px;
}

.settings-section-title {
  font-size: 14px;
  font-weight: 600;
  color: #111827;
  margin-bottom: 16px;
}

.settings-field {
  margin-bottom: 16px;
}

.settings-field-label {
  display: block;
  font-size: 13px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
}

.settings-field-input {
  width: 100%;
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  font-size: 14px;
  transition: all 0.15s ease;
}

.settings-field-input:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.settings-field-textarea {
  min-height: 100px;
  resize: vertical;
}

.settings-field-hint {
  font-size: 12px;
  color: #6b7280;
  margin-top: 6px;
}
```

### Page Settings Sections
- **General**: Page Name, Page Slug/URL
- **SEO**: Meta Title, Meta Description, OG Image
- **Custom Code**: Header Scripts, Footer Scripts
- **Favicon**: Upload/Select Favicon

### Modal Footer
```css
.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 24px;
  border-top: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

.modal-btn-cancel {
  padding: 10px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  cursor: pointer;
  transition: all 0.15s ease;
}

.modal-btn-cancel:hover {
  background-color: #f9fafb;
}

.modal-btn-save {
  padding: 10px 20px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  color: #ffffff;
  background: #6366f1;
  border: none;
  cursor: pointer;
  transition: all 0.15s ease;
}

.modal-btn-save:hover {
  background-color: #4f46e5;
}
```

---

## Preview Modal

### Modal Container
```css
.preview-modal {
  position: fixed;
  inset: 0;
  background-color: #111827;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  animation: fadeIn 0.2s ease;
}
```

### Preview Header
```css
.preview-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  background-color: #1f2937;
  border-bottom: 1px solid #374151;
}

.preview-title {
  font-size: 14px;
  font-weight: 500;
  color: #ffffff;
}

.preview-device-toggles {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px;
  background-color: #374151;
  border-radius: 6px;
}

.preview-device-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 28px;
  border-radius: 4px;
  border: none;
  background: transparent;
  color: #9ca3af;
  cursor: pointer;
  transition: all 0.15s ease;
}

.preview-device-btn.active {
  background-color: #4b5563;
  color: #ffffff;
}

.preview-close {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  color: #ffffff;
  background: transparent;
  border: 1px solid #4b5563;
  cursor: pointer;
  transition: all 0.15s ease;
}

.preview-close:hover {
  background-color: #374151;
}
```

### Preview Content
```css
.preview-content {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  overflow: auto;
}

.preview-frame-wrapper {
  background-color: #ffffff;
  border-radius: 8px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  overflow: hidden;
  transition: all 0.3s ease;
}

.preview-frame-wrapper.desktop {
  width: 100%;
  max-width: 1200px;
  height: calc(100vh - 120px);
}

.preview-frame-wrapper.tablet {
  width: 768px;
  height: calc(100vh - 120px);
}

.preview-frame-wrapper.mobile {
  width: 375px;
  height: 667px;
  border-radius: 24px;
}

.preview-iframe {
  width: 100%;
  height: 100%;
  border: none;
}
```

### Mobile Device Frame
```css
.mobile-frame {
  position: relative;
  padding: 12px;
  background-color: #1f2937;
  border-radius: 32px;
}

.mobile-notch {
  position: absolute;
  top: 8px;
  left: 50%;
  transform: translateX(-50%);
  width: 120px;
  height: 24px;
  background-color: #111827;
  border-radius: 12px;
}

.mobile-home-indicator {
  position: absolute;
  bottom: 8px;
  left: 50%;
  transform: translateX(-50%);
  width: 120px;
  height: 4px;
  background-color: #4b5563;
  border-radius: 2px;
}
```

---

## Responsive Behavior

### Collapsed Panels (< 1200px)
```css
@media (max-width: 1199px) {
  .builder-layout {
    grid-template-columns: 60px 1fr 60px;
  }

  .elements-panel,
  .properties-panel {
    position: fixed;
    top: 56px;
    bottom: 0;
    width: 300px;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    z-index: 50;
  }

  .elements-panel.open {
    transform: translateX(0);
  }

  .properties-panel {
    right: 0;
    left: auto;
    transform: translateX(100%);
  }

  .properties-panel.open {
    transform: translateX(0);
  }

  .panel-toggle {
    display: flex;
  }
}
```

---

## Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl/Cmd + Z` | Undo |
| `Ctrl/Cmd + Shift + Z` | Redo |
| `Ctrl/Cmd + S` | Save |
| `Ctrl/Cmd + P` | Preview |
| `Delete/Backspace` | Delete selected element |
| `Ctrl/Cmd + D` | Duplicate selected element |
| `Ctrl/Cmd + C` | Copy element |
| `Ctrl/Cmd + V` | Paste element |
| `Escape` | Deselect / Close modal |
| `Arrow Keys` | Nudge selected element |

---

## State Management

### Builder State
```typescript
interface BuilderState {
  page: Page;
  selectedElementId: string | null;
  hoveredElementId: string | null;
  deviceMode: 'desktop' | 'tablet' | 'mobile';
  history: HistoryState[];
  historyIndex: number;
  isDirty: boolean;
  isPreviewOpen: boolean;
  isSettingsOpen: boolean;
}
```

### Auto-Save Behavior
- Changes auto-save every 30 seconds
- Visual indicator shows save status
- Manual save available via Ctrl/Cmd + S
