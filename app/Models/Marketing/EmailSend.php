<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailSend extends Model
{
    use HasFactory;

    protected $connection = 'marketing';

    protected $table = 'email_sends';

    protected $fillable = [
        'campaign_id',
        'subscriber_id',
        'status',
        'message_id',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function getSentAtAuAttribute()
    {
        if (!$this->sent_at) {
            return null;
        }

        return $this->sent_at
            ->timezone('Australia/Sydney')
            ->format('d/m/Y H:i');
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function subscriber()
    {
        return $this->belongsTo(EmailSubscriber::class, 'subscriber_id');
    }
}