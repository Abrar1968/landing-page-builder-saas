# Day 1 - Step 3: TailwindCSS v4 & AlpineJS Setup

> **📝 NOTE**: This guide sets up AlpineJS for **dashboard and marketing pages**. For the page builder, **Vue.js 3 + Pinia** is used instead. See [docs/03-SETUP.md](../../03-SETUP.md) for complete frontend setup including Vue.js.

## Objective
Configure TailwindCSS v4 with Vite plugin and initialize AlpineJS for dashboard/marketing pages.

## Tasks

### 3.1 Configure Vite
```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

### 3.2 Setup TailwindCSS
```css
/* resources/css/app.css */
@import "tailwindcss";
```

### 3.3 Initialize AlpineJS
```javascript
// resources/js/app.js
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
```

### 3.4 Configure Tailwind Theme
Create `tailwind.config.js` with custom colors from design system.

## Reference Documentation
- `docs/frontend/01-DESIGN-SYSTEM.md` - Color palette, typography
- `docs/03-SETUP.md` - Frontend configuration section

## Design System Colors
```javascript
// Primary: Indigo (#4F46E5)
// Secondary: Slate (#64748B)
// Accent: Emerald (#10B981)
// Danger: Rose (#F43F5E)
```

## Expected Deliverables
- [x] Vite configured with TailwindCSS v4 plugin
- [x] AlpineJS initialized and working
- [x] Custom color palette configured
- [x] Hot reload working with `npm run dev`

## Next Step
→ `step04-base-architecture.md`
