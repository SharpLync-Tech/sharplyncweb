<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\CMS\ContactInfo;
use Illuminate\Http\Request;

class ContactInfoController extends Controller
{
    public function index()
    {
        // Always only one row — create it if missing
        $contact = ContactInfo::firstOrCreate([]);

        return view('admin.cms.contact.index', compact('contact'));
    }

    public function edit(ContactInfo $contact)
    {
        return view('admin.cms.contact.edit', compact('contact'));
    }

    public function update(Request $request, ContactInfo $contact)
    {
        $request->validate([
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'google_map_embed' => 'nullable|string',
            'hours' => 'nullable|string',
            'facebook_url' => 'nullable|string|max:255',
            'instagram_url' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|string|max:255',
            'twitter_url' => 'nullable|string|max:255',
            'youtube_url' => 'nullable|string|max:255',
        ]);

        $contact->update($request->all());

        return redirect()->route('admin.cms.contact.index')
            ->with('success', 'Contact information updated successfully.');
    }
}
