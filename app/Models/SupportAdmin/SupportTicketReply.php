<?php

namespace App\Models\SupportAdmin;

use Illuminate\Database\Eloquent\Model;

class SupportTicketReply extends Model
{
    protected $connection = 'crm';
    protected $table = 'support_ticket_replies';

    protected $fillable = [
        'ticket_id',
        'customer_id',
        'admin_id',
        'message',
        'is_internal',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\CRM\User::class, 'customer_id');
    }

    public function admin()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'admin_id');
    }
}
