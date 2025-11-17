<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerProfile extends Model
{
    use HasFactory;

    /**
     * Connection and table configuration
     */
    protected $connection = 'crm';
    protected $table = 'customer_profiles';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'user_id',
        'account_number',
        'business_name',
        'abn',
        'authority_contact',
        'accounts_email',
        'mobile_number',
        'landline_number',
        'preferred_contact_method',
        'marketing_opt_in',
        'preferred_support_window',
        'timezone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postcode',
        'country',
        'notes',
        'documents_path',
        'setup_completed',
    ];

    /**
     * Automatically generate an account number
     * when creating a new profile record.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($profile) {
            if (empty($profile->account_number)) {
                // Example: SL483920 or SL109283
                $profile->account_number = 'SL' . mt_rand(100000, 999999);
            }
        });
    }

    /**
     * Relationship: each profile belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Convenience accessor: get formatted account label
     */
    public function getAccountLabelAttribute()
    {
        return "{$this->account_number} - {$this->business_name}";
    }

    public function contacts()
    {
        return $this->hasMany(\App\Models\CRM\CustomerContact::class, 'customer_profile_id');
    }

}