<?php

namespace App\Models\AdminSupport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AdminTicket extends Model
{
    /**
     * Use CRM connection, not default CMS.
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

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Core CRM user record (sharplync_crm.users).
     */
    public function customerUser()
    {
        return $this->belongsTo(\App\Models\CRM\User::class, 'customer_id');
    }

    /**
     * Customer profile record (sharplync_crm.customer_profiles).
     * customer_profiles.user_id -> users.id (customer_id here).
     */
    public function customerProfile()
    {
        return $this->hasOne(\App\Models\CRM\CustomerProfile::class, 'user_id', 'customer_id');
    }

    /**
     * All replies (public + internal).
     */
    public function replies()
    {
        return $this->hasMany(AdminTicketReply::class, 'ticket_id');
    }

    /**
     * Public messages (customer + admin).
     */
    public function publicReplies()
    {
        return $this->replies()->where('is_internal', 0);
    }

    /**
     * Internal notes only visible to admins.
     */
    public function internalNotes()
    {
        return $this->replies()->where('is_internal', 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        // Search by subject, reference, message, customer profile + user name/email
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

        // Status filter
        $query->when($filters['status'] ?? null, function (Builder $q, $status) {
            $q->where('status', $status);
        });

        // Priority filter
        $query->when($filters['priority'] ?? null, function (Builder $q, $priority) {
            $q->where('priority', $priority);
        });

        // Sort
        $query->when($filters['sort'] ?? null, function (Builder $q, $sort) {
            switch ($sort) {
                case 'oldest':
                    $q->orderBy('created_at', 'asc');
                    break;
                case 'priority':
                    // High → Medium → Low
                    $q->orderByRaw("FIELD(priority, 'high', 'medium', 'low')");
                    break;
                default:
                    $q->orderBy('created_at', 'desc');
            }
        }, function (Builder $q) {
            // Default sort
            $q->orderBy('created_at', 'desc');
        });

        return $query;
    }
}
