# Day 3 - Step 1: Eloquent Models

## Objective
Create all Eloquent models with relationships and casts.

## Tasks

### 1.1 Page Model
```php
// app/Models/Page.php
class Page extends Model
{
    protected $fillable = [
        'user_id', 'title', 'slug', 'content',
        'settings', 'status', 'template_id', 'published_at'
    ];

    protected $casts = [
        'content' => 'array',
        'settings' => 'array',
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PageVersion::class);
    }
}
```

### 1.2 Create All Models
- Template, Media, Domain, Subscription
- PageView, FormSubmission, PageVersion

## Reference Documentation
- `docs/backend/04-MODELS-REPOSITORIES.md` - Complete model specifications
- `docs/backend/02-DATABASE-SCHEMA.md` - Relationships diagram

## Expected Deliverables
- [ ] All 9 models created
- [ ] Relationships defined
- [ ] JSON casts configured

## Next Step
→ `step02-repositories.md`
