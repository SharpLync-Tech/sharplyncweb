<?php
// app/Http/Controllers/Facilities/FacilitiesServicesController.php

namespace App\Http\Controllers\Facilities;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FacilitiesServicesController extends Controller
{
    public function index()
    {
        // Future: Fetch services data from models like FacilityProject::with('site')->get()
        return view('facilities.services');
    }
}