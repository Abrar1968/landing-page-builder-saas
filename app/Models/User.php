<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'timezone',
        'last_login_at',
        'stripe_customer_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get user's pages.
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    /**
     * Get user's media files.
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Get user's domains.
     */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    /**
     * Get user's subscription.
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    /**
     * Get user's templates.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }

    /**
     * Get user's payments.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
