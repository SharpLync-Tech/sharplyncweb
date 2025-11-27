<?php

namespace App\Models\SupportAdmin;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    protected $connection = 'crm';
    protected $table = 'users'; // your admin users table
}
