<?php

namespace App\Models\CRM;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * Connection and table.
     * Uses the CRM database connection defined in config/database.php
     */
    protected $connection = 'crm';
    protected $table = 'users';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'profile_photo',
        'email',
        'password',
        'account_status',
        'email_verified_at',
        'last_login_at',
        'auth_provider',
        'verification_token',
        'verification_expires_at',
    ];

    /**
     * Hidden attributes.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'email_verified_at'       => 'datetime',
        'last_login_at'           => 'datetime',
        'verification_expires_at' => 'datetime',
    ];

    /**
     * Relationship: each CRM User has one linked CustomerProfile record.
     * Allows access via $user->profile
     */
    public function profile(): HasOne
    {
        return $this->hasOne(\App\Models\CRM\CustomerProfile::class, 'user_id');
    }

    /**
     * Convenience accessor: returns the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Automatically hash passwords on set,
     * ensuring consistent security across registration methods.
     */
    public function setPasswordAttribute($value): void
    {
        if (!empty($value) && Hash::needsRehash($value)) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    /**
     * Check if profile setup is complete.
     * Useful for onboarding redirects.
     */
    public function getIsProfileCompleteAttribute(): bool
    {
        return (bool) ($this->profile && $this->profile->setup_completed);
    }
}
