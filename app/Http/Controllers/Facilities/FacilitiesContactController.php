<?php
// app/Http/Controllers/Facilities/FacilitiesContactController.php

namespace App\Http\Controllers\Facilities;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FacilitiesContactController extends Controller
{
    public function index()
    {
        return view('facilities.contact');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        // Future: Save to DB or send email, e.g., Mail::to('facilities@sharplync.com.au')->send(new ContactMail($validated));
        // For now, simple redirect with success
        return redirect()->back()->with('success', 'Message sent successfully! We\'ll be in touch soon.');
    }
}