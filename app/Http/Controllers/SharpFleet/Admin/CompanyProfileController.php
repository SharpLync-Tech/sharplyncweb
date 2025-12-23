<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyProfileController extends Controller
{
    /**
     * Show company profile form
     */
    public function edit(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || $user['role'] !== 'admin') {
            abort(403, 'Admin access only');
        }

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $user['organisation_id'])
            ->first();

        if (!$organisation) {
            abort(404, 'Organisation not found');
        }

        return view('sharpfleet.admin.company-profile', [
            'organisation' => $organisation,
        ]);
    }

    /**
     * Update company profile
     */
    public function update(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || $user['role'] !== 'admin') {
            abort(403, 'Admin access only');
        }

        $validated = $request->validate([
            'name'         => 'required|string|max:150',
            'company_type' => 'nullable|in:sole_trader,company',
            'industry'     => 'nullable|string|max:150',
        ]);

        DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $user['organisation_id'])
            ->update([
                'name'         => $validated['name'],
                'company_type' => $validated['company_type'] ?? null,
                'industry'     => $validated['industry'] ?? null,
            ]);

        if ($request->has('save_and_return')) {
            return redirect('/app/sharpfleet/admin/company')
                ->with('success', 'Company profile updated.');
        }

        return back()->with('success', 'Company profile updated.');
    }
}
