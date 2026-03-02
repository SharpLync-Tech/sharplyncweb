<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;

class EmailSubscriber extends Model
{
    /**
     * Use the marketing database connection.
     */
    protected $connection = 'marketing';

    /**
     * Table name.
     */
    protected $table = 'email_subscribers';

    /**
     * Mass assignable fields.
     */
    protected $fillable = [
        'email',
        'brand',
        'status',
        'confirmation_token',
        'unsubscribe_token',
        'confirmed_at',
        'unsubscribed_at',
    ];

    /**
     * Cast timestamps properly.
     */
    protected $casts = [
        'confirmed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];
}