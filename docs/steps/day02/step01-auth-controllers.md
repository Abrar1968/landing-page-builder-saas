# Day 2 - Step 1: Authentication Controllers

## Objective
Implement user authentication with Laravel Breeze and custom user management.

## Tasks

### 1.1 Create User Repository
```php
// app/Repositories/UserRepository.php
namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }
}
```

### 1.2 Create User Service
```php
// app/Services/UserService.php
namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(protected UserRepository $repository)
    {
    }

    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return $this->repository->create($data);
    }
}
```

### 1.3 Customize Registration Controller
Extend Breeze's RegisteredUserController to use UserService.

## Reference Documentation
- `docs/features/01-AUTHENTICATION.md` - Complete auth specification
- `docs/04-IMPLEMENTATION-FLOW.md` - Day 2 section
- `docs/backend/05-SERVICES.md` - Service layer patterns

## Expected Deliverables
- [ ] UserRepository created
- [ ] UserService implemented
- [ ] Registration uses service layer
- [ ] Login/logout working

## Next Step
→ `step02-auth-views.md`
