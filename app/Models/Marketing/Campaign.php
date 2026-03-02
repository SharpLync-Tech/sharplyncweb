<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $connection = 'marketing';

    protected $table = 'email_campaigns';

    protected $fillable = [
        'brand',
        'name',
        'subject',
        'preheader',
        'body_html',
        'template_view',
        'hero_image',
        'body_json',
        'status',
        'scheduled_at',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'body_json' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];
}
