<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Testimonial;

class PageController extends Controller
{
    public function about()
    {
        try {
            $testimonials = Testimonial::where('is_active', 1)
                ->orderByDesc('created_at')
                ->get();
        } catch (\Throwable $e) {
            // Fail gracefully if DB is unavailable
            $testimonials = collect();
        }

        return view('about', compact('testimonials'));
    }

    public function testimonials()
    {
        try {
            $testimonials = Testimonial::where('is_active', 1)
                ->orderBy('display_order', 'asc')
                ->orderByDesc('created_at')
                ->get();
        } catch (\Throwable $e) {
            $testimonials = collect();
        }

        return view('testimonials', compact('testimonials'));
    }
}
