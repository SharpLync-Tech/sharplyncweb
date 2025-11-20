<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\CMS\AboutSection;
use Illuminate\Http\Request;

class AboutSectionController extends Controller
{
    public function index()
    {
        $sections = AboutSection::ordered()->get();
        return view('admin.cms.about.sections.index', compact('sections'));
    }

    public function create()
    {
        return view('admin.cms.about.sections.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        AboutSection::create($request->all());

        return redirect()->route('admin.cms.about.sections.index')
            ->with('success', 'About section created successfully.');
    }

    public function edit(AboutSection $section)
    {
        return view('admin.cms.about.sections.edit', compact('section'));
    }

    public function update(Request $request, AboutSection $section)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $section->update($request->all());

        return redirect()->route('admin.cms.about.sections.index')
            ->with('success', 'About section updated successfully.');
    }

    public function destroy(AboutSection $section)
    {
        $section->delete();

        return redirect()->route('admin.cms.about.sections.index')
            ->with('success', 'About section deleted successfully.');
    }
}
