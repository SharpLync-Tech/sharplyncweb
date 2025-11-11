<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class OnboardingSession extends Model
{
    protected $connection = 'crm';
    protected $table = 'onboarding_sessions';

    protected $fillable = [
        'customer_id',
        'session_token',
        'status',
        'notes',
    ];

    public $timestamps = true;
}