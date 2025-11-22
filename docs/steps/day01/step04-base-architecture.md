# Day 1 - Step 4: Base Architecture Setup

## Objective
Implement Service-Repository pattern base classes and directory structure.

## Tasks

### 4.1 Create Directory Structure
```bash
mkdir -p app/Contracts/{Repositories,Services}
mkdir -p app/Repositories
mkdir -p app/Services
mkdir -p app/Observers
```

### 4.2 Create Base Repository Interface
```php
// app/Contracts/Repositories/BaseRepositoryInterface.php
namespace App\Contracts\Repositories;

interface BaseRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
```

### 4.3 Create Base Repository
```php
// app/Repositories/BaseRepository.php
namespace App\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function all()
    {
        return $this->model->all();
    }

    public function find(int $id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $record = $this->find($id);
        $record->update($data);
        return $record;
    }

    public function delete(int $id)
    {
        return $this->model->destroy($id);
    }
}
```

### 4.4 Create Repository Service Provider
```php
// app/Providers/RepositoryServiceProvider.php
```

## Reference Documentation
- `docs/backend/01-ARCHITECTURE.md` - Design patterns explained
- `docs/backend/04-MODELS-REPOSITORIES.md` - Repository implementations

## Architecture Pattern
```
Controller → Service → Repository → Model
```

- **Controllers**: HTTP handling only
- **Services**: Business logic
- **Repositories**: Data access
- **Models**: Relationships only

## Expected Deliverables
- [x] Directory structure created
- [x] Base interfaces defined
- [x] Base repository implemented
- [x] Service provider registered

## Next Step
→ `step05-observer-setup.md`
