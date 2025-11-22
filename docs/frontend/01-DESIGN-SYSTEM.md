# Design System Documentation

> **Tech Stack**: TailwindCSS v4, AlpineJS, Laravel Blade
> **Aesthetic**: Modern big-tech SaaS inspired by Webflow, Linear, and Vercel

---

## Table of Contents

1. [Tailwind Configuration](#tailwind-configuration)
2. [Color System](#color-system)
3. [Typography](#typography)
4. [Component Classes](#component-classes)
5. [Layout System](#layout-system)
6. [CSS Custom Properties](#css-custom-properties)
7. [Responsive Design Patterns](#responsive-design-patterns)
8. [Animations](#animations)

---

## Tailwind Configuration

### Complete `tailwind.config.js`

```javascript
import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            // Custom Colors - Inspired by Linear/Vercel
            colors: {
                // Primary brand colors
                primary: {
                    50: '#f0f4ff',
                    100: '#e0e9ff',
                    200: '#c7d6fe',
                    300: '#a4b8fc',
                    400: '#8093f8',
                    500: '#5e6ef2',
                    600: '#4f51e6',
                    700: '#4240cb',
                    800: '#3736a4',
                    900: '#313382',
                    950: '#1e1d4c',
                },
                // Neutral grays - Vercel-inspired
                neutral: {
                    50: '#fafafa',
                    100: '#f5f5f5',
                    200: '#e5e5e5',
                    300: '#d4d4d4',
                    400: '#a3a3a3',
                    500: '#737373',
                    600: '#525252',
                    700: '#404040',
                    800: '#262626',
                    850: '#1a1a1a',
                    900: '#171717',
                    950: '#0a0a0a',
                },
                // Accent colors
                accent: {
                    violet: '#8b5cf6',
                    blue: '#3b82f6',
                    cyan: '#06b6d4',
                    emerald: '#10b981',
                    amber: '#f59e0b',
                    rose: '#f43f5e',
                },
                // Semantic colors
                success: {
                    50: '#ecfdf5',
                    100: '#d1fae5',
                    500: '#10b981',
                    600: '#059669',
                    700: '#047857',
                },
                warning: {
                    50: '#fffbeb',
                    100: '#fef3c7',
                    500: '#f59e0b',
                    600: '#d97706',
                    700: '#b45309',
                },
                error: {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    500: '#ef4444',
                    600: '#dc2626',
                    700: '#b91c1c',
                },
                info: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                },
                // Surface colors for cards/modals
                surface: {
                    DEFAULT: '#ffffff',
                    raised: '#fafafa',
                    overlay: '#ffffff',
                    dark: '#171717',
                    'dark-raised': '#262626',
                    'dark-overlay': '#1a1a1a',
                },
            },

            // Typography
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Cal Sans', 'Inter', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', 'Fira Code', ...defaultTheme.fontFamily.mono],
            },

            fontSize: {
                'xs': ['0.75rem', { lineHeight: '1rem', letterSpacing: '0.01em' }],
                'sm': ['0.875rem', { lineHeight: '1.25rem', letterSpacing: '0.01em' }],
                'base': ['1rem', { lineHeight: '1.5rem', letterSpacing: '0' }],
                'lg': ['1.125rem', { lineHeight: '1.75rem', letterSpacing: '-0.01em' }],
                'xl': ['1.25rem', { lineHeight: '1.75rem', letterSpacing: '-0.01em' }],
                '2xl': ['1.5rem', { lineHeight: '2rem', letterSpacing: '-0.02em' }],
                '3xl': ['1.875rem', { lineHeight: '2.25rem', letterSpacing: '-0.02em' }],
                '4xl': ['2.25rem', { lineHeight: '2.5rem', letterSpacing: '-0.03em' }],
                '5xl': ['3rem', { lineHeight: '1.1', letterSpacing: '-0.03em' }],
                '6xl': ['3.75rem', { lineHeight: '1.1', letterSpacing: '-0.03em' }],
                '7xl': ['4.5rem', { lineHeight: '1.05', letterSpacing: '-0.04em' }],
                '8xl': ['6rem', { lineHeight: '1', letterSpacing: '-0.04em' }],
            },

            // Spacing
            spacing: {
                '4.5': '1.125rem',
                '13': '3.25rem',
                '15': '3.75rem',
                '17': '4.25rem',
                '18': '4.5rem',
                '19': '4.75rem',
                '21': '5.25rem',
                '22': '5.5rem',
                '26': '6.5rem',
                '30': '7.5rem',
                '34': '8.5rem',
                '38': '9.5rem',
                '42': '10.5rem',
                '50': '12.5rem',
                '54': '13.5rem',
                '58': '14.5rem',
                '62': '15.5rem',
                '66': '16.5rem',
                '70': '17.5rem',
                '74': '18.5rem',
                '78': '19.5rem',
                '82': '20.5rem',
                '86': '21.5rem',
                '90': '22.5rem',
                '94': '23.5rem',
                '100': '25rem',
                '108': '27rem',
                '116': '29rem',
                '124': '31rem',
                '132': '33rem',
            },

            // Border Radius
            borderRadius: {
                '4xl': '2rem',
                '5xl': '2.5rem',
            },

            // Box Shadow - Linear/Vercel-inspired
            boxShadow: {
                'xs': '0 1px 2px 0 rgb(0 0 0 / 0.05)',
                'sm': '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
                'DEFAULT': '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
                'md': '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
                'lg': '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
                'xl': '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
                '2xl': '0 25px 50px -12px rgb(0 0 0 / 0.25)',
                'glow': '0 0 20px rgb(94 110 242 / 0.3)',
                'glow-lg': '0 0 40px rgb(94 110 242 / 0.4)',
                'inner-glow': 'inset 0 1px 0 0 rgb(255 255 255 / 0.05)',
                'border': '0 0 0 1px rgb(0 0 0 / 0.05)',
                'dark-sm': '0 1px 3px 0 rgb(0 0 0 / 0.3), 0 1px 2px -1px rgb(0 0 0 / 0.3)',
                'dark-md': '0 4px 6px -1px rgb(0 0 0 / 0.4), 0 2px 4px -2px rgb(0 0 0 / 0.4)',
                'dark-lg': '0 10px 15px -3px rgb(0 0 0 / 0.5), 0 4px 6px -4px rgb(0 0 0 / 0.5)',
            },

            // Animations
            animation: {
                'fade-in': 'fade-in 0.3s ease-out',
                'fade-out': 'fade-out 0.3s ease-out',
                'slide-in-up': 'slide-in-up 0.3s ease-out',
                'slide-in-down': 'slide-in-down 0.3s ease-out',
                'slide-in-left': 'slide-in-left 0.3s ease-out',
                'slide-in-right': 'slide-in-right 0.3s ease-out',
                'scale-in': 'scale-in 0.2s ease-out',
                'scale-out': 'scale-out 0.2s ease-out',
                'spin-slow': 'spin 3s linear infinite',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'bounce-soft': 'bounce-soft 1s infinite',
                'shake': 'shake 0.5s ease-in-out',
                'glow-pulse': 'glow-pulse 2s ease-in-out infinite',
                'float': 'float 3s ease-in-out infinite',
                'shimmer': 'shimmer 2s linear infinite',
            },

            keyframes: {
                'fade-in': {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                'fade-out': {
                    '0%': { opacity: '1' },
                    '100%': { opacity: '0' },
                },
                'slide-in-up': {
                    '0%': { transform: 'translateY(10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                'slide-in-down': {
                    '0%': { transform: 'translateY(-10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                'slide-in-left': {
                    '0%': { transform: 'translateX(-10px)', opacity: '0' },
                    '100%': { transform: 'translateX(0)', opacity: '1' },
                },
                'slide-in-right': {
                    '0%': { transform: 'translateX(10px)', opacity: '0' },
                    '100%': { transform: 'translateX(0)', opacity: '1' },
                },
                'scale-in': {
                    '0%': { transform: 'scale(0.95)', opacity: '0' },
                    '100%': { transform: 'scale(1)', opacity: '1' },
                },
                'scale-out': {
                    '0%': { transform: 'scale(1)', opacity: '1' },
                    '100%': { transform: 'scale(0.95)', opacity: '0' },
                },
                'bounce-soft': {
                    '0%, 100%': { transform: 'translateY(-5%)' },
                    '50%': { transform: 'translateY(0)' },
                },
                'shake': {
                    '0%, 100%': { transform: 'translateX(0)' },
                    '25%': { transform: 'translateX(-5px)' },
                    '75%': { transform: 'translateX(5px)' },
                },
                'glow-pulse': {
                    '0%, 100%': { boxShadow: '0 0 20px rgb(94 110 242 / 0.3)' },
                    '50%': { boxShadow: '0 0 40px rgb(94 110 242 / 0.6)' },
                },
                'float': {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
                'shimmer': {
                    '0%': { backgroundPosition: '-200% 0' },
                    '100%': { backgroundPosition: '200% 0' },
                },
            },

            // Transitions
            transitionDuration: {
                '250': '250ms',
                '350': '350ms',
                '400': '400ms',
            },

            transitionTimingFunction: {
                'bounce-in': 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
                'smooth': 'cubic-bezier(0.4, 0, 0.2, 1)',
            },

            // Z-Index
            zIndex: {
                '60': '60',
                '70': '70',
                '80': '80',
                '90': '90',
                '100': '100',
            },

            // Backdrop Blur
            backdropBlur: {
                xs: '2px',
            },
        },
    },

    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
```

---

## Color System

### Light Mode Colors

```css
/* Base application styles */
@layer base {
    :root {
        /* Background colors */
        --color-bg-primary: 255 255 255;
        --color-bg-secondary: 250 250 250;
        --color-bg-tertiary: 245 245 245;

        /* Text colors */
        --color-text-primary: 23 23 23;
        --color-text-secondary: 115 115 115;
        --color-text-tertiary: 163 163 163;
        --color-text-inverse: 255 255 255;

        /* Border colors */
        --color-border-primary: 229 229 229;
        --color-border-secondary: 212 212 212;
        --color-border-focus: 94 110 242;

        /* Brand colors */
        --color-brand-primary: 94 110 242;
        --color-brand-secondary: 79 81 230;
    }
}
```

### Dark Mode Colors

```css
@layer base {
    .dark {
        /* Background colors */
        --color-bg-primary: 10 10 10;
        --color-bg-secondary: 23 23 23;
        --color-bg-tertiary: 38 38 38;

        /* Text colors */
        --color-text-primary: 250 250 250;
        --color-text-secondary: 163 163 163;
        --color-text-tertiary: 115 115 115;
        --color-text-inverse: 23 23 23;

        /* Border colors */
        --color-border-primary: 38 38 38;
        --color-border-secondary: 64 64 64;
        --color-border-focus: 94 110 242;
    }
}
```

### Color Usage Patterns

```html
<!-- Light/Dark adaptive backgrounds -->
<div class="bg-white dark:bg-neutral-950">
    <div class="bg-neutral-50 dark:bg-neutral-900">
        <div class="bg-neutral-100 dark:bg-neutral-800">
            <!-- Nested content -->
        </div>
    </div>
</div>

<!-- Text hierarchy -->
<h1 class="text-neutral-900 dark:text-white">Primary heading</h1>
<p class="text-neutral-600 dark:text-neutral-400">Secondary text</p>
<span class="text-neutral-500 dark:text-neutral-500">Tertiary text</span>

<!-- Borders -->
<div class="border border-neutral-200 dark:border-neutral-800">
    <!-- Content -->
</div>
```

---

## Typography

### Font Setup

Add to your `<head>`:

```html
<!-- Google Fonts: Inter + JetBrains Mono -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<!-- Cal Sans (Display font) - Self-hosted -->
<style>
    @font-face {
        font-family: 'Cal Sans';
        src: url('/fonts/CalSans-SemiBold.woff2') format('woff2');
        font-weight: 600;
        font-style: normal;
        font-display: swap;
    }
</style>
```

### Typography Scale

```html
<!-- Display headings (marketing pages) -->
<h1 class="font-display text-5xl md:text-6xl lg:text-7xl font-semibold tracking-tight text-neutral-900 dark:text-white">
    Build landing pages faster
</h1>

<!-- Page headings -->
<h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-neutral-900 dark:text-white">
    Dashboard
</h1>

<!-- Section headings -->
<h2 class="text-2xl font-semibold tracking-tight text-neutral-900 dark:text-white">
    Recent Projects
</h2>

<!-- Subsection headings -->
<h3 class="text-xl font-semibold text-neutral-900 dark:text-white">
    Settings
</h3>

<!-- Card headings -->
<h4 class="text-lg font-medium text-neutral-900 dark:text-white">
    Card Title
</h4>

<!-- Body text -->
<p class="text-base text-neutral-600 dark:text-neutral-400 leading-relaxed">
    Body text content here.
</p>

<!-- Small text -->
<p class="text-sm text-neutral-500 dark:text-neutral-500">
    Helper text or captions.
</p>

<!-- Code/Mono -->
<code class="font-mono text-sm bg-neutral-100 dark:bg-neutral-800 px-1.5 py-0.5 rounded">
    npm install
</code>
```

### Text Utilities

```css
@layer utilities {
    /* Text balance for headings */
    .text-balance {
        text-wrap: balance;
    }

    /* Gradient text */
    .text-gradient {
        @apply bg-clip-text text-transparent bg-gradient-to-r from-primary-500 to-accent-violet;
    }

    /* Link styles */
    .link {
        @apply text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300
               underline underline-offset-2 decoration-primary-600/30 hover:decoration-primary-600
               transition-colors duration-200;
    }

    /* Prose adjustments for dark mode */
    .prose-dark {
        @apply prose-invert prose-p:text-neutral-400 prose-headings:text-white
               prose-strong:text-white prose-code:text-primary-400;
    }
}
```

---

## Component Classes

### Buttons

```html
<!-- Primary Button -->
<button class="inline-flex items-center justify-center px-4 py-2.5
               bg-primary-600 hover:bg-primary-700 active:bg-primary-800
               text-white text-sm font-medium rounded-lg
               shadow-sm hover:shadow-md
               transition-all duration-200 ease-smooth
               focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
               dark:focus:ring-offset-neutral-900
               disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-primary-600">
    Get Started
</button>

<!-- Secondary Button -->
<button class="inline-flex items-center justify-center px-4 py-2.5
               bg-neutral-100 hover:bg-neutral-200 active:bg-neutral-300
               dark:bg-neutral-800 dark:hover:bg-neutral-700 dark:active:bg-neutral-600
               text-neutral-900 dark:text-white text-sm font-medium rounded-lg
               transition-all duration-200 ease-smooth
               focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:ring-offset-2
               dark:focus:ring-offset-neutral-900
               disabled:opacity-50 disabled:cursor-not-allowed">
    Learn More
</button>

<!-- Ghost Button -->
<button class="inline-flex items-center justify-center px-4 py-2.5
               bg-transparent hover:bg-neutral-100 active:bg-neutral-200
               dark:hover:bg-neutral-800 dark:active:bg-neutral-700
               text-neutral-600 hover:text-neutral-900
               dark:text-neutral-400 dark:hover:text-white
               text-sm font-medium rounded-lg
               transition-all duration-200 ease-smooth
               focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:ring-offset-2
               dark:focus:ring-offset-neutral-900">
    Cancel
</button>

<!-- Outline Button -->
<button class="inline-flex items-center justify-center px-4 py-2.5
               bg-transparent hover:bg-primary-50 active:bg-primary-100
               dark:hover:bg-primary-950 dark:active:bg-primary-900
               border border-primary-600 dark:border-primary-500
               text-primary-600 dark:text-primary-400 text-sm font-medium rounded-lg
               transition-all duration-200 ease-smooth
               focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
               dark:focus:ring-offset-neutral-900">
    Outline
</button>

<!-- Danger Button -->
<button class="inline-flex items-center justify-center px-4 py-2.5
               bg-error-600 hover:bg-error-700 active:bg-error-800
               text-white text-sm font-medium rounded-lg
               shadow-sm hover:shadow-md
               transition-all duration-200 ease-smooth
               focus:outline-none focus:ring-2 focus:ring-error-500 focus:ring-offset-2
               dark:focus:ring-offset-neutral-900">
    Delete
</button>

<!-- Icon Button -->
<button class="inline-flex items-center justify-center w-10 h-10
               bg-neutral-100 hover:bg-neutral-200 active:bg-neutral-300
               dark:bg-neutral-800 dark:hover:bg-neutral-700 dark:active:bg-neutral-600
               text-neutral-600 dark:text-neutral-400 rounded-lg
               transition-all duration-200 ease-smooth
               focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:ring-offset-2
               dark:focus:ring-offset-neutral-900">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
    </svg>
</button>

<!-- Button Sizes -->
<button class="px-3 py-1.5 text-xs rounded-md">XS</button>
<button class="px-3.5 py-2 text-sm rounded-lg">SM</button>
<button class="px-4 py-2.5 text-sm rounded-lg">MD (default)</button>
<button class="px-5 py-3 text-base rounded-lg">LG</button>
<button class="px-6 py-3.5 text-lg rounded-xl">XL</button>
```

### Inputs

```html
<!-- Text Input -->
<div class="space-y-1.5">
    <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
        Email
    </label>
    <input
        type="email"
        id="email"
        class="block w-full px-3.5 py-2.5
               bg-white dark:bg-neutral-900
               border border-neutral-300 dark:border-neutral-700
               rounded-lg text-sm text-neutral-900 dark:text-white
               placeholder:text-neutral-400 dark:placeholder:text-neutral-500
               transition-colors duration-200
               hover:border-neutral-400 dark:hover:border-neutral-600
               focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
               disabled:bg-neutral-50 dark:disabled:bg-neutral-800 disabled:cursor-not-allowed"
        placeholder="you@example.com"
    >
    <p class="text-sm text-neutral-500 dark:text-neutral-400">
        We'll never share your email.
    </p>
</div>

<!-- Input with Error -->
<div class="space-y-1.5">
    <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
        Password
    </label>
    <input
        type="password"
        id="password"
        class="block w-full px-3.5 py-2.5
               bg-white dark:bg-neutral-900
               border border-error-500 dark:border-error-500
               rounded-lg text-sm text-neutral-900 dark:text-white
               focus:outline-none focus:ring-2 focus:ring-error-500 focus:border-transparent"
        aria-invalid="true"
        aria-describedby="password-error"
    >
    <p id="password-error" class="text-sm text-error-600 dark:text-error-400">
        Password must be at least 8 characters.
    </p>
</div>

<!-- Textarea -->
<textarea
    rows="4"
    class="block w-full px-3.5 py-2.5
           bg-white dark:bg-neutral-900
           border border-neutral-300 dark:border-neutral-700
           rounded-lg text-sm text-neutral-900 dark:text-white
           placeholder:text-neutral-400 dark:placeholder:text-neutral-500
           transition-colors duration-200
           hover:border-neutral-400 dark:hover:border-neutral-600
           focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
           resize-none"
    placeholder="Enter your message..."
></textarea>

<!-- Select -->
<select class="block w-full px-3.5 py-2.5
               bg-white dark:bg-neutral-900
               border border-neutral-300 dark:border-neutral-700
               rounded-lg text-sm text-neutral-900 dark:text-white
               transition-colors duration-200
               hover:border-neutral-400 dark:hover:border-neutral-600
               focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
    <option>Select an option</option>
    <option>Option 1</option>
    <option>Option 2</option>
</select>

<!-- Checkbox -->
<label class="flex items-center gap-3 cursor-pointer group">
    <input
        type="checkbox"
        class="w-4 h-4 rounded border-neutral-300 dark:border-neutral-600
               text-primary-600 bg-white dark:bg-neutral-900
               focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
               dark:focus:ring-offset-neutral-900
               transition-colors duration-200"
    >
    <span class="text-sm text-neutral-700 dark:text-neutral-300 group-hover:text-neutral-900 dark:group-hover:text-white">
        Remember me
    </span>
</label>

<!-- Radio -->
<label class="flex items-center gap-3 cursor-pointer group">
    <input
        type="radio"
        name="plan"
        class="w-4 h-4 border-neutral-300 dark:border-neutral-600
               text-primary-600 bg-white dark:bg-neutral-900
               focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
               dark:focus:ring-offset-neutral-900
               transition-colors duration-200"
    >
    <span class="text-sm text-neutral-700 dark:text-neutral-300">
        Monthly
    </span>
</label>

<!-- Toggle Switch with Alpine.js -->
<div x-data="{ enabled: false }">
    <button
        type="button"
        @click="enabled = !enabled"
        :class="enabled ? 'bg-primary-600' : 'bg-neutral-200 dark:bg-neutral-700'"
        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900"
    >
        <span
            :class="enabled ? 'translate-x-6' : 'translate-x-1'"
            class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform duration-200"
        ></span>
    </button>
</div>
```

### Cards

```html
<!-- Basic Card -->
<div class="bg-white dark:bg-neutral-900
            border border-neutral-200 dark:border-neutral-800
            rounded-xl shadow-sm
            overflow-hidden">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
            Card Title
        </h3>
        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
            Card description goes here.
        </p>
    </div>
</div>

<!-- Interactive Card -->
<div class="group bg-white dark:bg-neutral-900
            border border-neutral-200 dark:border-neutral-800
            rounded-xl shadow-sm
            overflow-hidden
            transition-all duration-200
            hover:border-neutral-300 dark:hover:border-neutral-700
            hover:shadow-md dark:hover:shadow-dark-md
            cursor-pointer">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white
                   group-hover:text-primary-600 dark:group-hover:text-primary-400
                   transition-colors duration-200">
            Interactive Card
        </h3>
        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
            Click to interact.
        </p>
    </div>
</div>

<!-- Card with Header -->
<div class="bg-white dark:bg-neutral-900
            border border-neutral-200 dark:border-neutral-800
            rounded-xl shadow-sm
            overflow-hidden">
    <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-800">
        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">
            Card Header
        </h3>
    </div>
    <div class="p-6">
        <p class="text-sm text-neutral-600 dark:text-neutral-400">
            Card content.
        </p>
    </div>
</div>

<!-- Card with Footer -->
<div class="bg-white dark:bg-neutral-900
            border border-neutral-200 dark:border-neutral-800
            rounded-xl shadow-sm
            overflow-hidden">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
            Card with Footer
        </h3>
        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
            Card content here.
        </p>
    </div>
    <div class="px-6 py-4 bg-neutral-50 dark:bg-neutral-800/50
                border-t border-neutral-200 dark:border-neutral-800">
        <button class="text-sm font-medium text-primary-600 dark:text-primary-400
                       hover:text-primary-700 dark:hover:text-primary-300">
            View Details →
        </button>
    </div>
</div>

<!-- Gradient Border Card (Webflow-style) -->
<div class="relative p-[1px] rounded-xl bg-gradient-to-br from-primary-500 via-accent-violet to-accent-cyan">
    <div class="bg-white dark:bg-neutral-900 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
            Premium Card
        </h3>
        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
            With gradient border effect.
        </p>
    </div>
</div>
```

### Modals

```html
<!-- Modal with Alpine.js -->
<div x-data="{ open: false }">
    <!-- Trigger -->
    <button @click="open = true" class="...">
        Open Modal
    </button>

    <!-- Modal -->
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="open = false"
            class="fixed inset-0 bg-black/50 dark:bg-black/70 backdrop-blur-sm"
        ></div>

        <!-- Modal Panel -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click.away="open = false"
                class="relative w-full max-w-md transform overflow-hidden
                       bg-white dark:bg-neutral-900
                       border border-neutral-200 dark:border-neutral-800
                       rounded-2xl shadow-xl dark:shadow-dark-lg
                       transition-all"
            >
                <!-- Header -->
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-800">
                    <h3 id="modal-title" class="text-lg font-semibold text-neutral-900 dark:text-white">
                        Modal Title
                    </h3>
                    <button
                        @click="open = false"
                        class="absolute top-4 right-4 text-neutral-400 hover:text-neutral-500
                               dark:text-neutral-500 dark:hover:text-neutral-400"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="px-6 py-4">
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        Modal content goes here.
                    </p>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-neutral-50 dark:bg-neutral-800/50
                            border-t border-neutral-200 dark:border-neutral-800
                            flex justify-end gap-3">
                    <button @click="open = false" class="px-4 py-2 text-sm font-medium
                                                         text-neutral-700 dark:text-neutral-300
                                                         hover:bg-neutral-100 dark:hover:bg-neutral-800
                                                         rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button class="px-4 py-2 text-sm font-medium text-white
                                   bg-primary-600 hover:bg-primary-700
                                   rounded-lg transition-colors">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
```

### Dropdowns

```html
<!-- Dropdown with Alpine.js -->
<div x-data="{ open: false }" class="relative">
    <!-- Trigger -->
    <button
        @click="open = !open"
        @keydown.escape.window="open = false"
        class="inline-flex items-center gap-2 px-4 py-2.5
               bg-white dark:bg-neutral-900
               border border-neutral-300 dark:border-neutral-700
               rounded-lg text-sm font-medium text-neutral-700 dark:text-neutral-300
               hover:bg-neutral-50 dark:hover:bg-neutral-800
               focus:outline-none focus:ring-2 focus:ring-primary-500"
    >
        Options
        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Dropdown Panel -->
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        @click.away="open = false"
        class="absolute z-50 mt-2 w-56 origin-top-right
               bg-white dark:bg-neutral-900
               border border-neutral-200 dark:border-neutral-800
               rounded-xl shadow-lg dark:shadow-dark-lg
               ring-1 ring-black ring-opacity-5
               divide-y divide-neutral-100 dark:divide-neutral-800
               overflow-hidden"
    >
        <div class="py-1">
            <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300
                              hover:bg-neutral-100 dark:hover:bg-neutral-800
                              transition-colors duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                Edit
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300
                              hover:bg-neutral-100 dark:hover:bg-neutral-800
                              transition-colors duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Duplicate
            </a>
        </div>
        <div class="py-1">
            <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm text-error-600 dark:text-error-400
                              hover:bg-error-50 dark:hover:bg-error-950
                              transition-colors duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete
            </a>
        </div>
    </div>
</div>
```

### Badges

```html
<!-- Default Badge -->
<span class="inline-flex items-center px-2.5 py-0.5
             bg-neutral-100 dark:bg-neutral-800
             text-neutral-700 dark:text-neutral-300
             text-xs font-medium rounded-full">
    Default
</span>

<!-- Primary Badge -->
<span class="inline-flex items-center px-2.5 py-0.5
             bg-primary-100 dark:bg-primary-900/50
             text-primary-700 dark:text-primary-300
             text-xs font-medium rounded-full">
    Primary
</span>

<!-- Success Badge -->
<span class="inline-flex items-center px-2.5 py-0.5
             bg-success-100 dark:bg-success-900/50
             text-success-700 dark:text-success-300
             text-xs font-medium rounded-full">
    Success
</span>

<!-- Warning Badge -->
<span class="inline-flex items-center px-2.5 py-0.5
             bg-warning-100 dark:bg-warning-900/50
             text-warning-700 dark:text-warning-300
             text-xs font-medium rounded-full">
    Warning
</span>

<!-- Error Badge -->
<span class="inline-flex items-center px-2.5 py-0.5
             bg-error-100 dark:bg-error-900/50
             text-error-700 dark:text-error-300
             text-xs font-medium rounded-full">
    Error
</span>

<!-- Badge with Dot -->
<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5
             bg-success-100 dark:bg-success-900/50
             text-success-700 dark:text-success-300
             text-xs font-medium rounded-full">
    <span class="w-1.5 h-1.5 bg-success-500 rounded-full"></span>
    Active
</span>

<!-- Badge with Icon -->
<span class="inline-flex items-center gap-1 px-2 py-0.5
             bg-primary-100 dark:bg-primary-900/50
             text-primary-700 dark:text-primary-300
             text-xs font-medium rounded-md">
    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
    </svg>
    Verified
</span>

<!-- Outline Badge -->
<span class="inline-flex items-center px-2.5 py-0.5
             border border-primary-300 dark:border-primary-700
             text-primary-600 dark:text-primary-400
             text-xs font-medium rounded-full">
    Outline
</span>
```

### Alerts

```html
<!-- Info Alert -->
<div class="flex gap-3 p-4
            bg-info-50 dark:bg-info-950
            border border-info-200 dark:border-info-900
            rounded-lg" role="alert">
    <svg class="w-5 h-5 text-info-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
    </svg>
    <div>
        <h4 class="text-sm font-medium text-info-800 dark:text-info-200">
            Information
        </h4>
        <p class="mt-1 text-sm text-info-700 dark:text-info-300">
            This is an informational message.
        </p>
    </div>
</div>

<!-- Success Alert -->
<div class="flex gap-3 p-4
            bg-success-50 dark:bg-success-950
            border border-success-200 dark:border-success-900
            rounded-lg" role="alert">
    <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
    </svg>
    <div>
        <h4 class="text-sm font-medium text-success-800 dark:text-success-200">
            Success
        </h4>
        <p class="mt-1 text-sm text-success-700 dark:text-success-300">
            Your changes have been saved successfully.
        </p>
    </div>
</div>

<!-- Warning Alert -->
<div class="flex gap-3 p-4
            bg-warning-50 dark:bg-warning-950
            border border-warning-200 dark:border-warning-900
            rounded-lg" role="alert">
    <svg class="w-5 h-5 text-warning-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
    </svg>
    <div>
        <h4 class="text-sm font-medium text-warning-800 dark:text-warning-200">
            Warning
        </h4>
        <p class="mt-1 text-sm text-warning-700 dark:text-warning-300">
            Please review your settings before continuing.
        </p>
    </div>
</div>

<!-- Error Alert -->
<div class="flex gap-3 p-4
            bg-error-50 dark:bg-error-950
            border border-error-200 dark:border-error-900
            rounded-lg" role="alert">
    <svg class="w-5 h-5 text-error-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
    </svg>
    <div>
        <h4 class="text-sm font-medium text-error-800 dark:text-error-200">
            Error
        </h4>
        <p class="mt-1 text-sm text-error-700 dark:text-error-300">
            There was an error processing your request.
        </p>
    </div>
</div>

<!-- Dismissible Alert with Alpine.js -->
<div x-data="{ show: true }" x-show="show" x-transition class="relative flex gap-3 p-4
            bg-info-50 dark:bg-info-950
            border border-info-200 dark:border-info-900
            rounded-lg" role="alert">
    <svg class="w-5 h-5 text-info-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
    </svg>
    <p class="text-sm text-info-700 dark:text-info-300 pr-8">
        This alert can be dismissed.
    </p>
    <button @click="show = false" class="absolute top-4 right-4 text-info-500 hover:text-info-600">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
    </button>
</div>
```

---

## Layout System

### Container

```html
<!-- Standard container -->
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <!-- Content -->
</div>

<!-- Narrow container (for forms, content) -->
<div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
    <!-- Content -->
</div>

<!-- Wide container (for dashboards) -->
<div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
    <!-- Content -->
</div>

<!-- Full-width with max-width prose -->
<div class="mx-auto max-w-prose px-4">
    <!-- Content -->
</div>
```

### Page Layouts

```html
<!-- Standard Page Layout -->
<div class="min-h-screen bg-neutral-50 dark:bg-neutral-950">
    <!-- Navigation -->
    <nav class="sticky top-0 z-40 bg-white/80 dark:bg-neutral-900/80 backdrop-blur-md
                border-b border-neutral-200 dark:border-neutral-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Nav content -->
            </div>
        </div>
    </nav>

    <!-- Main content -->
    <main class="py-8 sm:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Page content -->
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-neutral-200 dark:border-neutral-800
                   bg-white dark:bg-neutral-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
            <!-- Footer content -->
        </div>
    </footer>
</div>

<!-- Dashboard Layout with Sidebar -->
<div class="min-h-screen bg-neutral-50 dark:bg-neutral-950">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64
                      bg-white dark:bg-neutral-900
                      border-r border-neutral-200 dark:border-neutral-800
                      hidden lg:block">
            <div class="flex h-full flex-col">
                <!-- Logo -->
                <div class="flex h-16 items-center px-6
                            border-b border-neutral-200 dark:border-neutral-800">
                    <!-- Logo -->
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-4 px-3">
                    <!-- Nav items -->
                </nav>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 lg:pl-64">
            <!-- Top bar -->
            <div class="sticky top-0 z-40 h-16
                        bg-white/80 dark:bg-neutral-900/80 backdrop-blur-md
                        border-b border-neutral-200 dark:border-neutral-800">
                <!-- Top bar content -->
            </div>

            <!-- Page content -->
            <div class="p-6 lg:p-8">
                <!-- Content -->
            </div>
        </main>
    </div>
</div>
```

### Grid Systems

```html
<!-- Auto-fit grid (responsive cards) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <!-- Cards -->
</div>

<!-- Two-column layout -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Content -->
</div>

<!-- Main + Sidebar -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2">
        <!-- Main content -->
    </div>
    <div>
        <!-- Sidebar -->
    </div>
</div>

<!-- Feature grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <!-- Feature cards -->
</div>
```

### Stack Layouts

```html
<!-- Vertical stack -->
<div class="flex flex-col gap-4">
    <!-- Items -->
</div>

<!-- Horizontal stack -->
<div class="flex flex-row items-center gap-4">
    <!-- Items -->
</div>

<!-- Responsive stack (vertical on mobile, horizontal on desktop) -->
<div class="flex flex-col sm:flex-row gap-4">
    <!-- Items -->
</div>
```

---

## CSS Custom Properties

Add to your main CSS file:

```css
@layer base {
    :root {
        /* Timing */
        --duration-fast: 150ms;
        --duration-normal: 200ms;
        --duration-slow: 300ms;

        /* Easing */
        --ease-smooth: cubic-bezier(0.4, 0, 0.2, 1);
        --ease-bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);

        /* Shadows */
        --shadow-color: 0 0% 0%;
        --shadow-elevation-low:
            0 1px 2px hsl(var(--shadow-color) / 0.05);
        --shadow-elevation-medium:
            0 4px 6px -1px hsl(var(--shadow-color) / 0.1),
            0 2px 4px -1px hsl(var(--shadow-color) / 0.06);
        --shadow-elevation-high:
            0 10px 15px -3px hsl(var(--shadow-color) / 0.1),
            0 4px 6px -2px hsl(var(--shadow-color) / 0.05);

        /* Focus ring */
        --ring-color: theme('colors.primary.500');
        --ring-offset: 2px;
        --ring-width: 2px;

        /* Border radius */
        --radius-sm: 0.375rem;
        --radius-md: 0.5rem;
        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
        --radius-2xl: 1.5rem;

        /* Z-index scale */
        --z-dropdown: 50;
        --z-sticky: 100;
        --z-fixed: 200;
        --z-modal-backdrop: 300;
        --z-modal: 400;
        --z-popover: 500;
        --z-tooltip: 600;
        --z-toast: 700;
    }

    .dark {
        --shadow-color: 0 0% 0%;
        --shadow-elevation-low:
            0 1px 2px hsl(var(--shadow-color) / 0.2);
        --shadow-elevation-medium:
            0 4px 6px -1px hsl(var(--shadow-color) / 0.3),
            0 2px 4px -1px hsl(var(--shadow-color) / 0.2);
        --shadow-elevation-high:
            0 10px 15px -3px hsl(var(--shadow-color) / 0.4),
            0 4px 6px -2px hsl(var(--shadow-color) / 0.3);
    }
}
```

---

## Responsive Design Patterns

### Breakpoints Reference

```
sm: 640px   - Small tablets, large phones
md: 768px   - Tablets
lg: 1024px  - Small laptops, tablets landscape
xl: 1280px  - Desktops
2xl: 1536px - Large desktops
```

### Common Responsive Patterns

```html
<!-- Responsive text -->
<h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl">
    Heading
</h1>

<!-- Responsive spacing -->
<div class="p-4 sm:p-6 md:p-8 lg:p-10">
    <!-- Content -->
</div>

<!-- Responsive visibility -->
<div class="hidden md:block">
    <!-- Only visible on md and up -->
</div>

<div class="md:hidden">
    <!-- Only visible on mobile -->
</div>

<!-- Responsive flex direction -->
<div class="flex flex-col md:flex-row gap-4">
    <!-- Stacks vertically on mobile, horizontally on desktop -->
</div>

<!-- Responsive grid columns -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    <!-- Responsive grid -->
</div>

<!-- Container queries (TailwindCSS v4) -->
<div class="@container">
    <div class="@lg:flex @lg:gap-8">
        <!-- Responds to container width -->
    </div>
</div>
```

### Mobile-First Navigation

```html
<!-- Mobile menu with Alpine.js -->
<div x-data="{ mobileMenuOpen: false }">
    <!-- Mobile menu button -->
    <button
        @click="mobileMenuOpen = !mobileMenuOpen"
        class="lg:hidden p-2 rounded-lg text-neutral-600 dark:text-neutral-400
               hover:bg-neutral-100 dark:hover:bg-neutral-800"
    >
        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <!-- Desktop navigation -->
    <nav class="hidden lg:flex items-center gap-8">
        <!-- Nav items -->
    </nav>

    <!-- Mobile menu panel -->
    <div
        x-show="mobileMenuOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="lg:hidden absolute top-full left-0 right-0
               bg-white dark:bg-neutral-900
               border-b border-neutral-200 dark:border-neutral-800
               shadow-lg"
    >
        <div class="px-4 py-6 space-y-4">
            <!-- Mobile nav items -->
        </div>
    </div>
</div>
```

---

## Animations

### Utility Classes

```css
@layer utilities {
    /* Hover lift effect */
    .hover-lift {
        @apply transition-transform duration-200;
    }
    .hover-lift:hover {
        @apply -translate-y-1;
    }

    /* Hover scale */
    .hover-scale {
        @apply transition-transform duration-200;
    }
    .hover-scale:hover {
        @apply scale-105;
    }

    /* Press effect */
    .press {
        @apply transition-transform duration-100;
    }
    .press:active {
        @apply scale-95;
    }

    /* Shimmer loading effect */
    .shimmer {
        @apply relative overflow-hidden;
    }
    .shimmer::after {
        content: '';
        @apply absolute inset-0;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.4),
            transparent
        );
        @apply animate-shimmer;
        background-size: 200% 100%;
    }

    /* Skeleton loading */
    .skeleton {
        @apply bg-neutral-200 dark:bg-neutral-800 rounded animate-pulse;
    }
}
```

### Page Transitions

```html
<!-- Page fade in on load -->
<main x-data x-init="$el.classList.add('animate-fade-in')">
    <!-- Page content -->
</main>

<!-- Staggered list animation -->
<ul x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 100)">
    <template x-for="(item, index) in items" :key="item.id">
        <li
            x-show="shown"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            :style="{ transitionDelay: `${index * 50}ms` }"
        >
            <!-- Item content -->
        </li>
    </template>
</ul>
```

### Loading States

```html
<!-- Spinner -->
<svg class="animate-spin h-5 w-5 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
</svg>

<!-- Button with loading state -->
<button
    x-data="{ loading: false }"
    @click="loading = true; setTimeout(() => loading = false, 2000)"
    :disabled="loading"
    class="inline-flex items-center gap-2 px-4 py-2.5
           bg-primary-600 text-white text-sm font-medium rounded-lg
           disabled:opacity-70"
>
    <svg x-show="loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <span x-text="loading ? 'Processing...' : 'Submit'"></span>
</button>

<!-- Skeleton card -->
<div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6">
    <div class="skeleton h-4 w-3/4 mb-4"></div>
    <div class="skeleton h-3 w-full mb-2"></div>
    <div class="skeleton h-3 w-5/6 mb-4"></div>
    <div class="skeleton h-8 w-24"></div>
</div>

<!-- Pulse dot indicator -->
<span class="relative flex h-3 w-3">
    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
    <span class="relative inline-flex rounded-full h-3 w-3 bg-primary-500"></span>
</span>
```

### Toast Notifications with Alpine.js

```html
<!-- Toast container -->
<div
    x-data="{
        toasts: [],
        add(message, type = 'info') {
            const id = Date.now()
            this.toasts.push({ id, message, type })
            setTimeout(() => this.remove(id), 5000)
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id)
        }
    }"
    @toast.window="add($event.detail.message, $event.detail.type)"
    class="fixed bottom-4 right-4 z-50 flex flex-col gap-3"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="flex items-center gap-3 px-4 py-3
                   bg-white dark:bg-neutral-800
                   border border-neutral-200 dark:border-neutral-700
                   rounded-lg shadow-lg dark:shadow-dark-lg
                   min-w-[300px]"
        >
            <span x-text="toast.message" class="text-sm text-neutral-900 dark:text-white"></span>
            <button @click="remove(toast.id)" class="ml-auto text-neutral-400 hover:text-neutral-500">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    </template>
</div>

<!-- Trigger toast from anywhere -->
<button @click="$dispatch('toast', { message: 'Changes saved!', type: 'success' })">
    Show Toast
</button>
```

---

## Dark Mode Implementation

### Theme Toggle with Alpine.js

```html
<div
    x-data="{
        theme: localStorage.getItem('theme') || 'system',
        updateTheme() {
            if (this.theme === 'dark' || (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark')
            } else {
                document.documentElement.classList.remove('dark')
            }
            localStorage.setItem('theme', this.theme)
        }
    }"
    x-init="updateTheme(); window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => { if (theme === 'system') updateTheme() })"
    @theme-changed.window="updateTheme()"
>
    <button
        @click="theme = theme === 'light' ? 'dark' : 'light'; updateTheme()"
        class="p-2 rounded-lg text-neutral-600 dark:text-neutral-400
               hover:bg-neutral-100 dark:hover:bg-neutral-800
               transition-colors duration-200"
    >
        <!-- Sun icon (light mode) -->
        <svg x-show="theme === 'light'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <!-- Moon icon (dark mode) -->
        <svg x-show="theme === 'dark'" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
    </button>
</div>
```

### Prevent Flash of Unstyled Content

Add this script to your `<head>` before any stylesheets:

```html
<script>
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }
</script>
```

---

## Blade Component Examples

### Button Component

```php
{{-- resources/views/components/button.blade.php --}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary' => 'bg-primary-600 hover:bg-primary-700 text-white shadow-sm hover:shadow-md focus:ring-primary-500',
        'secondary' => 'bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-neutral-500',
        'ghost' => 'bg-transparent hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400 focus:ring-neutral-500',
        'danger' => 'bg-error-600 hover:bg-error-700 text-white shadow-sm hover:shadow-md focus:ring-error-500',
    ];

    $sizes = [
        'sm' => 'px-3.5 py-2 text-sm',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-5 py-3 text-base',
    ];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size]]) }}
>
    {{ $slot }}
</button>
```

### Usage

```blade
<x-button>Primary Button</x-button>
<x-button variant="secondary">Secondary</x-button>
<x-button variant="ghost" size="sm">Small Ghost</x-button>
<x-button variant="danger" size="lg">Delete</x-button>
```

---

## Design Principles

### Webflow Inspiration
- Clean, spacious layouts with generous whitespace
- Subtle gradients and shadows for depth
- Smooth micro-interactions on hover/click
- Bold typography with tight letter-spacing

### Linear Inspiration
- Monochromatic color schemes with accent pops
- Glass-morphism effects (backdrop-blur)
- Crisp borders and dividers
- Focus on functional beauty

### Vercel Inspiration
- Black and white foundation with minimal color
- Geometric precision in spacing
- Understated elegance
- Performance-focused simplicity

---

## File Structure

```
resources/
├── css/
│   ├── app.css              # Main stylesheet with @layer directives
│   └── components/          # Component-specific styles (if needed)
├── js/
│   └── app.js               # AlpineJS setup
└── views/
    └── components/
        ├── button.blade.php
        ├── input.blade.php
        ├── card.blade.php
        ├── modal.blade.php
        ├── dropdown.blade.php
        ├── badge.blade.php
        └── alert.blade.php
```

---

*This design system provides a solid foundation for building modern, accessible, and visually appealing SaaS applications. Customize colors and spacing to match your brand identity.*
