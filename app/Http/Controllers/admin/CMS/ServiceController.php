<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\CMS\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::ordered()->get();
        return view('admin.cms.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.cms.services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'icon_path' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'image_path' => 'nullable|file|mimes:png,jpg,jpeg,webp|max:4096',
        ]);

        $data = $request->all();

        if ($request->hasFile('icon_path')) {
            $data['icon_path'] = $request->file('icon_path')->store('cms/services/icons', 'public');
        }

        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('cms/services/images', 'public');
        }

        Service::create($data);

        return redirect()->route('admin.cms.services.index')
            ->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        return view('admin.cms.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'icon_path' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'image_path' => 'nullable|file|mimes:png,jpg,jpeg,webp|max:4096',
        ]);

        $data = $request->all();

        // Replace icon if new one uploaded
        if ($request->hasFile('icon_path')) {
            if ($service->icon_path) {
                Storage::disk('public')->delete($service->icon_path);
            }
            $data['icon_path'] = $request->file('icon_path')->store('cms/services/icons', 'public');
        }

        // Replace image if new one uploaded
        if ($request->hasFile('image_path')) {
            if ($service->image_path) {
                Storage::disk('public')->delete($service->image_path);
            }
            $data['image_path'] = $request->file('image_path')->store('cms/services/images', 'public');
        }

        $service->update($data);

        return redirect()->route('admin.cms.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        if ($service->icon_path) {
            Storage::disk('public')->delete($service->icon_path);
        }

        if ($service->image_path) {
            Storage::disk('public')->delete($service->image_path);
        }

        $service->delete();

        return redirect()->route('admin.cms.services.index')
            ->with('success', 'Service deleted successfully.');
    }
}
