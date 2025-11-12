<?php

namespace App\Http\Controllers;

use App\Models\CMS\Testimonial;

class PageController extends Controller
{
    public function about()
    {
        $testimonials = Testimonial::where('is_active', 1)
            ->orderByDesc('created_at')
            ->get();

        return view('about', compact('testimonials'));
    }

    public function testimonials()
    {
        $testimonials = Testimonial::where('is_active', 1)
            ->orderBy('display_order', 'asc')
            ->orderByDesc('created_at')
            ->get();

        return view('testimonials', compact('testimonials'));
    }
}