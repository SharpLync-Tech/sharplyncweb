<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\CMS\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index()
    {
        $items = MenuItem::ordered()->get();

        return view('admin.cms.menu.index', compact('items'));
    }

    public function create()
    {
        return view('admin.cms.menu.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'open_in_new_tab' => 'boolean',
        ]);

        MenuItem::create($request->all());

        return redirect()->route('admin.cms.menu.index')
            ->with('success', 'Menu item created successfully.');
    }

    public function edit(MenuItem $menuItem)
    {
        return view('admin.cms.menu.edit', compact('menuItem'));
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'open_in_new_tab' => 'boolean',
        ]);

        $menuItem->update($request->all());

        return redirect()->route('admin.cms.menu.index')
            ->with('success', 'Menu item updated successfully.');
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return redirect()->route('admin.cms.menu.index')
            ->with('success', 'Menu item deleted successfully.');
    }
}
