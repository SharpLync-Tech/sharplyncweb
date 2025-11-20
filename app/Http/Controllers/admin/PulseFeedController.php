<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PulseFeed;
use Illuminate\Http\Request;

class PulseFeedController extends Controller
{
    public function index()
    {
        $items = PulseFeed::orderByDesc('created_at')->get();
        return view('admin.pulse.index', compact('items'));
    }

    public function create()
    {
        return view('admin.pulse.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'is_active' => 'boolean',
        ]);

        PulseFeed::create($request->all());

        return redirect()->route('admin.pulse.index')
            ->with('success', 'Pulse Feed item created.');
    }

    public function edit(PulseFeed $pulse)
    {
        return view('admin.pulse.edit', compact('pulse'));
    }

    public function update(Request $request, PulseFeed $pulse)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $pulse->update($request->all());

        return redirect()->route('admin.pulse.index')
            ->with('success', 'Pulse Feed updated.');
    }

    public function destroy(PulseFeed $pulse)
    {
        $pulse->delete();

        return redirect()->route('admin.pulse.index')
            ->with('success', 'Pulse Feed deleted.');
    }
}
