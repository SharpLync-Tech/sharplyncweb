<?php

namespace App\Models\SupportAdmin;

use Illuminate\Database\Eloquent\Model;
use App\Models\Support\SupportTicket;
use App\Models\Support\TicketReply as CustomerReply;

class SupportTicketReply extends Model
{
    protected $connection = 'crm';

    protected $table = 'support_ticket_replies';

    protected $fillable = [
        'ticket_id',
        'user_type',
        'user_id',
        'message',
        'attachment_path',
        'attachment_original_name',
        'attachment_mime',
        'is_internal',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    // FIX: Make admin replies point to the SAME underlying reply model
    public function original()
    {
        return $this->belongsTo(CustomerReply::class, 'id');
    }

    public function isCustomer()
    {
        return $this->user_type === 'customer';
    }

    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }

    public function author()
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }
}
