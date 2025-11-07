<?php
/**
 * SharpLync Admin Controller
 * Version: 1.0
 * Description:
 *  - Handles all admin portal pages and actions
 *  - Starting point: dashboard view
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard()
    {
        return view('admin.dashboard');
    }
}