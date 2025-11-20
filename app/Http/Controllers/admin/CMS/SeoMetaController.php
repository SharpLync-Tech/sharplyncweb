<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\CMS\SeoMeta;
use Illuminate\Http\Request;

class SeoMetaController extends Controller
{
    public function index()
    {
        $meta = SeoMeta::orderBy('page_slug')->get();
        return view('admin.cms.seo.index', compact('meta'));
    }

    public function create()
    {
        return view('admin.cms.seo.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'page_slug' => 'required|string|max:255|unique:seo_meta',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        SeoMeta::create($request->all());

        return redirect()->route('admin.cms.seo.index')
            ->with('success', 'SEO meta created successfully.');
    }

    public function edit(SeoMeta $seoMeta)
    {
        return view('admin.cms.seo.edit', compact('seoMeta'));
    }

    public function update(Request $request, SeoMeta $seoMeta)
    {
        $request->validate([
            'page_slug' => 'required|string|max:255|unique:seo_meta,page_slug,' . $seoMeta->id,
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $seoMeta->update($request->all());

        return redirect()->route('admin.cms.seo.index')
            ->with('success', 'SEO meta updated successfully.');
    }

    public function destroy(SeoMeta $seoMeta)
    {
        $seoMeta->delete();

        return redirect()->route('admin.cms.seo.index')
            ->with('success', 'SEO meta deleted successfully.');
    }
}
