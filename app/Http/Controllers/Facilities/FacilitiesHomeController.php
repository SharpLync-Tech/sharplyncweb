<?php
// app/Http/Controllers/Facilities/FacilitiesHomeController.php

namespace App\Http\Controllers\Facilities;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FacilitiesHomeController extends Controller
{
    public function index()
    {
        // Future: Fetch data from models like FacilitySite::all() if needed
        return view('facilities.home');
    }
}