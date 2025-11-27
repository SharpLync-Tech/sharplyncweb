<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    /**
     * Tickets live in the CRM database, not the CMS.
     */
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
        'closed_at'     => 'datetime',
    ];

    // Status constants (for future admin UI as well)
    public const STATUS_OPEN                = 'open';
    public const STATUS_PENDING             = 'pending';
    public const STATUS_WAITING_CUSTOMER    = 'waiting_customer';
    public const STATUS_WAITING_THIRD_PARTY = 'waiting_third_party';
    public const STATUS_WAITING_STOCK       = 'waiting_stock';
    public const STATUS_RESOLVED            = 'resolved';
    public const STATUS_CLOSED              = 'closed';

    // Priority constants
    public const PRIORITY_LOW    = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH   = 'high';

    public function replies()
    {
        return $this->hasMany(TicketReply::class, 'ticket_id');
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Simple human-friendly reference, e.g. SL-2025-39D2B7
     */
    public static function generateReference(): string
    {
        $year = now('Australia/Brisbane')->format('Y');

        do {
            $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $reference = 'SL-' . $year . '-' . $random;
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }
}
