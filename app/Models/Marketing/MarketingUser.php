<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;

class MarketingUser extends Model
{
    protected $connection = 'marketing';

    protected $table = 'marketing_users';

    protected $fillable = [
        'email',
        'role',
        'brand_scope',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
