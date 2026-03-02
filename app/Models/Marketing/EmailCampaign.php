<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    /**
     * Use the marketing database connection.
     */
    protected $connection = 'marketing';

    /**
     * Table name.
     */
    protected $table = 'email_campaigns';

    /**
     * Mass assignable fields.
     */
    protected $fillable = [
        'brand',
        'name',
        'subject',
        'template_view',
        'hero_image',
        'body_json',
        'status',
        'scheduled_at',
        'sent_at',
        'created_by',
    ];

    /**
     * Casts.
     */
    protected $casts = [
        'body_json'    => 'array',
        'scheduled_at' => 'datetime',
        'sent_at'      => 'datetime',
    ];
}