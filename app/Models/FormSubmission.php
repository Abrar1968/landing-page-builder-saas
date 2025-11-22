<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'page_id',
        'form_id',
        'data',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'data' => 'array',
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

    public function scopeByForm($query, string $formId)
    {
        return $query->where('form_id', $formId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
