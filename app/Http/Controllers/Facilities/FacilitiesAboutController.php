<?php
// app/Http/Controllers/Facilities/FacilitiesAboutController.php

namespace App\Http\Controllers\Facilities;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FacilitiesAboutController extends Controller
{
    public function index()
    {
        // Future: Fetch testimonials from DB if integrated
        $testimonials = []; // Placeholder; e.g., Testimonial::where('category', 'facilities')->get()
        return view('facilities.about', compact('testimonials'));
    }
}