<?php
/**
 * SharpLync Admin - Testimonial Controller
 * Version: 1.2 (CRUD complete; GUID-safe created_by; strict casting)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = DB::connection('mysql')
            ->table('testimonials')
            ->orderBy('display_order', 'asc')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        return view('admin.testimonials.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'      => 'required|string|max:255',
            'customer_position'  => 'nullable|string|max:255',
            'customer_company'   => 'nullable|string|max:255',
            'testimonial_text'   => 'required|string',
            'rating'             => 'nullable|integer|min:1|max:5',
            'display_order'      => 'nullable|integer|min:0',
            'is_featured'        => 'nullable|boolean',
            'is_active'          => 'nullable|boolean',
        ]);

        $isFeatured = $request->boolean('is_featured') ? 1 : 0;
        $isActive   = $request->boolean('is_active', true) ? 1 : 0;

        $createdBy = session('admin_user')['id'] ?? null;
        if ($createdBy === null || !ctype_digit((string) $createdBy)) {
            $createdBy = null; // BIGINT only
        }

        DB::connection('mysql')->table('testimonials')->insert([
            'customer_name'     => $validated['customer_name'],
            'customer_position' => $validated['customer_position'] ?? null,
            'customer_company'  => $validated['customer_company'] ?? null,
            'testimonial_text'  => $validated['testimonial_text'],
            'rating'            => $validated['rating'] ?? null,
            'display_order'     => $validated['display_order'] ?? 0,
            'is_featured'       => $isFeatured,
            'is_active'         => $isActive,
            'created_by'        => $createdBy,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial added successfully!');
    }

    public function edit($id)
    {
        $t = DB::connection('mysql')->table('testimonials')->where('id', $id)->first();
        abort_if(!$t, 404);
        return view('admin.testimonials.edit', compact('t'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'customer_name'      => 'required|string|max:255',
            'customer_position'  => 'nullable|string|max:255',
            'customer_company'   => 'nullable|string|max:255',
            'testimonial_text'   => 'required|string',
            'rating'             => 'nullable|integer|min:1|max:5',
            'display_order'      => 'nullable|integer|min:0',
            'is_featured'        => 'nullable|boolean',
            'is_active'          => 'nullable|boolean',
        ]);

        $isFeatured = $request->boolean('is_featured') ? 1 : 0;
        $isActive   = $request->boolean('is_active', true) ? 1 : 0;

        DB::connection('mysql')->table('testimonials')->where('id', $id)->update([
            'customer_name'     => $validated['customer_name'],
            'customer_position' => $validated['customer_position'] ?? null,
            'customer_company'  => $validated['customer_company'] ?? null,
            'testimonial_text'  => $validated['testimonial_text'],
            'rating'            => $validated['rating'] ?? null,
            'display_order'     => $validated['display_order'] ?? 0,
            'is_featured'       => $isFeatured,
            'is_active'         => $isActive,
            'updated_at'        => now(),
        ]);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial updated.');
    }

    public function destroy($id)
    {
        DB::connection('mysql')->table('testimonials')->where('id', $id)->delete();
        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial deleted.');
    }
}