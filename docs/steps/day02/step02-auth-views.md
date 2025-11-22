# Day 2 - Step 2: Authentication Blade Views

## Objective
Create TailwindCSS-styled authentication views.

## Tasks

### 2.1 Login View
Create `resources/views/auth/login.blade.php` with:
- Email/password fields
- Remember me checkbox
- Forgot password link
- TailwindCSS styling

### 2.2 Register View
Create `resources/views/auth/register.blade.php` with:
- Name, email, password fields
- Password confirmation
- Terms acceptance checkbox

### 2.3 Password Reset Views
- `forgot-password.blade.php`
- `reset-password.blade.php`

### 2.4 Guest Layout
Create `resources/views/layouts/guest.blade.php` for auth pages.

## Reference Documentation
- `docs/frontend/01-DESIGN-SYSTEM.md` - Form styling, buttons
- `docs/frontend/02-COMPONENTS.md` - Input components
- `docs/04-IMPLEMENTATION-FLOW.md` - Blade examples

## Design Specifications
- Primary button: `bg-indigo-600 hover:bg-indigo-700`
- Input fields: `rounded-md border-gray-300 focus:border-indigo-500`
- Error states: `text-red-600 border-red-500`

## Expected Deliverables
- [ ] Login page styled
- [ ] Register page styled
- [ ] Password reset flow complete
- [ ] Responsive on mobile

## Next Step
→ `step03-email-verification.md`
