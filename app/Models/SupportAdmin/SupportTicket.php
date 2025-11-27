<?php

namespace App\Models\SupportAdmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SupportTicket extends Model
{
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

    public function customerUser()
    {
        return $this->belongsTo(\App\Models\CRM\User::class, 'customer_id');
    }

    public function customerProfile()
    {
        return $this->hasOne(\App\Models\CRM\CustomerProfile::class, 'user_id', 'customer_id');
    }

    public function replies()
    {
        return $this->hasMany(SupportTicketReply::class, 'ticket_id');
    }

    public function publicReplies()
    {
        return $this->replies()->where('is_internal', 0);
    }

    public function internalNotes()
    {
        return $this->replies()->where('is_internal', 1);
    }

    public function getLatestMessagePreviewAttribute(): ?string
    {
        $lastPublic = $this->publicReplies()
            ->orderByDesc('created_at')
            ->first();

        $text = $lastPublic?->message ?? $this->message;

        if (!$text) {
            return null;
        }

        $text = strip_tags($text);

        return mb_strlen($text) > 120
            ? mb_substr($text, 0, 117) . '...'
            : $text;
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $query->when($filters['search'] ?? null, function (Builder $q, $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('subject', 'LIKE', "%{$search}%")
                    ->orWhere('reference', 'LIKE', "%{$search}%")
                    ->orWhere('message', 'LIKE', "%{$search}%");
            })->orWhereHas('customerProfile', function (Builder $cp) use ($search) {
                $cp->where('business_name', 'LIKE', "%{$search}%")
                   ->orWhere('authority_contact', 'LIKE', "%{$search}%")
                   ->orWhere('mobile_number', 'LIKE', "%{$search}%");
            })->orWhereHas('customerUser', function (Builder $cu) use ($search) {
                $cu->where('first_name', 'LIKE', "%{$search}%")
                   ->orWhere('last_name', 'LIKE', "%{$search}%")
                   ->orWhere('email', 'LIKE', "%{$search}%");
            });
        });

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        $query->when($filters['sort'] ?? null, function (Builder $q, $sort) {
            switch ($sort) {
                case 'oldest':
                    $q->orderBy('created_at', 'asc');
                    break;
                case 'priority':
                    $q->orderByRaw("FIELD(priority, 'urgent','high','medium','low')");
                    break;
                default:
                    $q->orderBy('created_at', 'desc');
            }
        }, function (Builder $q) {
            $q->orderBy('created_at', 'desc');
        });

        return $query;
    }
}
