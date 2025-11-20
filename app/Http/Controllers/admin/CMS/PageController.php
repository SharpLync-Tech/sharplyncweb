<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\CMS\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::orderBy('slug')->get();
        return view('admin.cms.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.cms.pages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'slug' => 'required|string|max:255|unique:pages',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Page::create($request->all());

        return redirect()->route('admin.cms.pages.index')
            ->with('success', 'Page created successfully.');
    }

    public function edit(Page $page)
    {
        return view('admin.cms.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $request->validate([
            'slug' => 'required|string|max:255|unique:pages,slug,' . $page->id,
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $page->update($request->all());

        return redirect()->route('admin.cms.pages.index')
            ->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.cms.pages.index')
            ->with('success', 'Page deleted successfully.');
    }
}
