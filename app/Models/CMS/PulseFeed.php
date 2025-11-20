<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PulseFeed extends Model
{
    protected $table = 'pulse_feed';

    protected $fillable = [
        'title',
        'message',
        'is_active',
    ];
}
