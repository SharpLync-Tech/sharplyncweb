<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\CMS\AboutTimelineItem;
use Illuminate\Http\Request;

class AboutTimelineItemController extends Controller
{
    public function index()
    {
        $items = AboutTimelineItem::ordered()->get();
        return view('admin.cms.about.timeline.index', compact('items'));
    }

    public function create()
    {
        return view('admin.cms.about.timeline.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        AboutTimelineItem::create($request->all());

        return redirect()->route('admin.cms.about.timeline.index')
            ->with('success', 'Timeline item created successfully.');
    }

    public function edit(AboutTimelineItem $timelineItem)
    {
        return view('admin.cms.about.timeline.edit', compact('timelineItem'));
    }

    public function update(Request $request, AboutTimelineItem $timelineItem)
    {
        $request->validate([
            'year' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $timelineItem->update($request->all());

        return redirect()->route('admin.cms.about.timeline.index')
            ->with('success', 'Timeline item updated successfully.');
    }

    public function destroy(AboutTimelineItem $timelineItem)
    {
        $timelineItem->delete();

        return redirect()->route('admin.cms.about.timeline.index')
            ->with('success', 'Timeline item deleted successfully.');
    }
}
