<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;


    protected $connection = 'crm';

    protected $table = 'support_tickets';

    protected $fillable = [
        'customer_id',
        'reference',
        'subject',
        'message',
        'status',
        'priority',
        'created_via',
        'last_reply_at',
        'closed_at',
    ];

    protected $casts = [
        'last_reply_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function replies()
    {
        return $this->hasMany(TicketReply::class, 'ticket_id')
            ->orderBy('created_at', 'asc');
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public static function generateReference(): string
    {
        // Simple human-friendly reference, e.g. SL-2025-000123
        $prefix = 'SL-' . now()->format('Y');
        $random = strtoupper(substr(md5(uniqid((string) microtime(true), true)), 0, 6));

        return $prefix . '-' . $random;
    }
}
