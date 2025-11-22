# Day 2 - Step 3: Email Verification & User Observer

## Objective
Implement email verification and user activity logging.

## Tasks

### 3.1 Enable Email Verification
```php
// app/Models/User.php
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    // ...
}
```

### 3.2 Create UserObserver
```php
// app/Observers/UserObserver.php
namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        // Log user creation, queue welcome email
    }

    public function updated(User $user): void
    {
        if ($user->isDirty('email')) {
            // Re-verify email if changed
        }
    }
}
```

### 3.3 Register Observer
```php
// app/Providers/AppServiceProvider.php
User::observe(UserObserver::class);
```

## Reference Documentation
- `docs/features/01-AUTHENTICATION.md` - Email verification, activity tracking
- `docs/06-SECURITY.md` - Security considerations

## Expected Deliverables
- [ ] Email verification working
- [ ] UserObserver implemented
- [ ] Activity logging ready
- [ ] Last login tracking

## Day 2 Complete
→ Proceed to Day 3: Core Models & Database
