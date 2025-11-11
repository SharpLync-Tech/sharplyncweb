<?php

namespace App\Http\Controllers;

use App\Models\CMS\Testimonial;

class PageController extends Controller
{
    public function about()
    {
        // Only show active testimonials, newest first
        $testimonials = Testimonial::where('is_active', 1)
            ->orderByDesc('created_at')
            ->get();

        return view('about', compact('testimonials'));
    }

    public function testimonials()
    {
        // Same logic, can sort by display_order first
        $testimonials = Testimonial::where('is_active', 1)
            ->orderBy('display_order', 'asc')
            ->orderByDesc('created_at')
            ->get();

        return view('testimonials', compact('testimonials'));
    }
}