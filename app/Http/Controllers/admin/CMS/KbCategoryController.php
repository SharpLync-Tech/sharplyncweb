<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\CMS\KbCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KbCategoryController extends Controller
{
    public function index()
    {
        $categories = KbCategory::orderBy('name')->get();
        return view('admin.cms.kb.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.cms.kb.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:kb_categories',
            'slug' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        if (! $data['slug']) {
            $data['slug'] = Str::slug($request->name);
        }

        KbCategory::create($data);

        return redirect()->route('admin.cms.kb.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(KbCategory $category)
    {
        return view('admin.cms.kb.categories.edit', compact('category'));
    }

    public function update(Request $request, KbCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:kb_categories,name,' . $category->id,
            'slug' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        if (! $data['slug']) {
            $data['slug'] = Str::slug($request->name);
        }

        $category->update($data);

        return redirect()->route('admin.cms.kb.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(KbCategory $category)
    {
        $category->delete();

        return redirect()->route('admin.cms.kb.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
