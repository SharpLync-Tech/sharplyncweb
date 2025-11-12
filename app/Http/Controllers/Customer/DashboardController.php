<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the main customer portal.
     */
    public function index()
    {
        $user = Auth::user();
        $profile = $user->profile ?? null;

        return view('customers.portal', compact('user', 'profile'));
    }
}
