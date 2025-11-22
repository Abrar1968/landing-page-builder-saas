<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'page_id',
        'domain',
        'status',
        'ssl_status',
        'verification_token',
        'verified_at',
        'ssl_provisioned_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'ssl_provisioned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function hasSsl(): bool
    {
        return $this->ssl_status === 'active';
    }
}
