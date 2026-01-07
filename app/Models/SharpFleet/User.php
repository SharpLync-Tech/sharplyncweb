<?php

namespace App\Models\SharpFleet;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'sharpfleet';
    protected $table = 'users';

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    protected $hidden = [
        'password_hash',
        'activation_token',
    ];

    /**
     * Laravel auth expects a "password" field; SharpFleet uses "password_hash".
     */
    public function getAuthPassword(): string
    {
        return (string) ($this->password_hash ?? '');
    }

    /**
     * Convenience accessor: returns the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim(((string) ($this->first_name ?? '')) . ' ' . ((string) ($this->last_name ?? '')));
    }

    /**
     * Conservative profile completeness check for mobile clients.
     */
    public function getIsProfileCompleteAttribute(): bool
    {
        return trim((string) ($this->first_name ?? '')) !== ''
            && trim((string) ($this->last_name ?? '')) !== ''
            && trim((string) ($this->email ?? '')) !== '';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }
}
