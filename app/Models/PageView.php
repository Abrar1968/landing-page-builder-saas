<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'visitor_id',
        'ip_address',
        'user_agent',
        'referrer',
        'country',
        'device_type',
        'browser',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
