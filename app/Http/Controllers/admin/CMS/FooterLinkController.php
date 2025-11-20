<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\CMS\FooterLink;
use Illuminate\Http\Request;

class FooterLinkController extends Controller
{
    public function index()
    {
        // Sort by sort_order only
        $links = FooterLink::orderBy('sort_order')->get();

        return view('admin.cms.footer.index', compact('links'));
    }

    public function create()
    {
        return view('admin.cms.footer.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        FooterLink::create($request->all());

        return redirect()->route('admin.cms.footer.index')
            ->with('success', 'Footer link created successfully.');
    }

    public function edit(FooterLink $footerLink)
    {
        return view('admin.cms.footer.edit', compact('footerLink'));
    }

    public function update(Request $request, FooterLink $footerLink)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $footerLink->update($request->all());

        return redirect()->route('admin.cms.footer.index')
            ->with('success', 'Footer link updated successfully.');
    }

    public function destroy(FooterLink $footerLink)
    {
        $footerLink->delete();

        return redirect()->route('admin.cms.footer.index')
            ->with('success', 'Footer link deleted successfully.');
    }
}
