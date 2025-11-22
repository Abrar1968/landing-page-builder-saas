<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'page_id',
        'content',
        'version_number',
        'created_at',
    ];

    protected $casts = [
        'content' => 'array',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    // Scopes
    public function scopeByPage($query, int $pageId)
    {
        return $query->where('page_id', $pageId);
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('version_number');
    }
}
