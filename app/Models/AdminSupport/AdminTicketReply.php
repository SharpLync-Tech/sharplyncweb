<?php

namespace App\Models\AdminSupport;

use Illuminate\Database\Eloquent\Model;

class AdminTicketReply extends Model
{
    /**
     * Use CRM connection, not default CMS.
     */
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
        return $this->belongsTo(AdminTicket::class, 'ticket_id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\CRM\User::class, 'customer_id');
    }

    public function admin()
    {
        // You can swap this to your actual admin user model if different.
        return $this->belongsTo(\App\Models\Admin::class, 'admin_id');
    }
}
