<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Component;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    public function index()
    {
        $components = Component::orderBy('name')->get();
        return view('admin.components.index', compact('components'));
    }

    public function create()
    {
        return view('admin.components.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Component::create($request->all());

        return redirect()->route('admin.components.index')
            ->with('success', 'Component created.');
    }

    public function edit(Component $component)
    {
        return view('admin.components.edit', compact('component'));
    }

    public function update(Request $request, Component $component)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $component->update($request->all());

        return redirect()->route('admin.components.index')
            ->with('success', 'Component updated.');
    }

    public function destroy(Component $component)
    {
        $component->delete();

        return redirect()->route('admin.components.index')
            ->with('success', 'Component deleted.');
    }
}
