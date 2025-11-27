<?php

namespace App\Models\SupportAdmin;

use Illuminate\Database\Eloquent\Model;

class SupportTicketReply extends Model
{
    protected $connection = 'crm';
    protected $table = 'support_ticket_replies';

    protected $fillable = [
        'ticket_id',
        'user_type',
        'user_id',
        'message',
        'is_internal',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    /**
     * Correct author relationship (customer or admin)
     */
    public function author()
    {
        if ($this->user_type === 'customer') {
            return $this->belongsTo(\App\Models\CRM\User::class, 'user_id');
        }

        if ($this->user_type === 'admin') {
            // Your admin users live here:
            return $this->belongsTo(\App\Models\SupportAdmin\AdminUser::class, 'user_id');
        }

        return null;
    }

    /**
     * Helper flags for views
     */
    public function isCustomer()
    {
        return $this->user_type === 'customer';
    }

    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }
}
