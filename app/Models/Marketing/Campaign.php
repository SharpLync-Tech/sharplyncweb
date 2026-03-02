<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $connection = 'marketing';

    protected $table = 'email_campaigns';

    protected $fillable = [
        'name',
        'subject',
        'body_html',
        'status',
        'scheduled_at',
    ];
}