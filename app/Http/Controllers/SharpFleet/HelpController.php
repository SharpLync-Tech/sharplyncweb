<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function admin(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::isAdminPortal($user)) {
            abort(403);
        }

        return view('sharpfleet.help.admin');
    }

    public function driver(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::isDriver($user)) {
            abort(403, 'Driver access only');
        }

        return view('sharpfleet.help.driver');
    }
}
