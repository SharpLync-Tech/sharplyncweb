<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Model;

class TicketReply extends Model
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
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    // Correct author relationship
    public function author()
    {
        if ($this->user_type === 'customer') {
            return $this->belongsTo(\App\Models\CRM\User::class, 'user_id');
        }

        if ($this->user_type === 'admin') {
            return $this->belongsTo(\App\Models\SupportAdmin\AdminUser::class, 'user_id');
        }

        return null;
    }

    public function isCustomer()
    {
        return $this->user_type === 'customer';
    }

    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }
}
