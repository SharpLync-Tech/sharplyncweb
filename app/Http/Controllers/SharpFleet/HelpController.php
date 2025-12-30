<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function admin(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || ($user['role'] ?? null) !== 'admin') {
            abort(403, 'Admin access only');
        }

        return view('sharpfleet.help.admin');
    }

    public function driver(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        $role = $user['role'] ?? null;
        $isDriver = (bool) ($user['is_driver'] ?? ($role === 'driver'));

        if (!$user || (!$isDriver && $role !== 'driver' && $role !== 'admin')) {
            abort(403, 'Driver access only');
        }

        return view('sharpfleet.help.driver');
    }
}
