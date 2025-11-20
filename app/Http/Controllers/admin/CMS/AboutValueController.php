<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\CMS\AboutValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AboutValueController extends Controller
{
    public function index()
    {
        $values = AboutValue::ordered()->get();
        return view('admin.cms.about.values.index', compact('values'));
    }

    public function create()
    {
        return view('admin.cms.about.values.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'icon_path' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('icon_path')) {
            $data['icon_path'] = $request->file('icon_path')->store('cms/about/values/icons', 'public');
        }

        AboutValue::create($data);

        return redirect()->route('admin.cms.about.values.index')
            ->with('success', 'Value created successfully.');
    }

    public function edit(AboutValue $value)
    {
        return view('admin.cms.about.values.edit', compact('value'));
    }

    public function update(Request $request, AboutValue $value)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'icon_path' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('icon_path')) {
            if ($value->icon_path) {
                Storage::disk('public')->delete($value->icon_path);
            }

            $data['icon_path'] = $request->file('icon_path')->store('cms/about/values/icons', 'public');
        }

        $value->update($data);

        return redirect()->route('admin.cms.about.values.index')
            ->with('success', 'Value updated successfully.');
    }

    public function destroy(AboutValue $value)
    {
        if ($value->icon_path) {
            Storage::disk('public')->delete($value->icon_path);
        }

        $value->delete();

        return redirect()->route('admin.cms.about.values.index')
            ->with('success', 'Value deleted successfully.');
    }
}
