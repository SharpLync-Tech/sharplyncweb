<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;

class EmailSend extends Model
{
    /**
     * Use the marketing database connection.
     */
    protected $connection = 'marketing';

    /**
     * Table name.
     */
    protected $table = 'email_sends';

    /**
     * Mass assignable fields.
     */
    protected $fillable = [
        'campaign_id',
        'subscriber_id',
        'status',
        'message_id',
        'sent_at',
    ];

    /**
     * Casts.
     */
    protected $casts = [
        'sent_at' => 'datetime',
    ];
}