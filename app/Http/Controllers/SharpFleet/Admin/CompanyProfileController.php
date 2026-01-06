<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CompanySettingsService;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::isCompanyAdmin($user)) {
            abort(403);
        }

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $user['organisation_id'])
            ->first();

        if (!$organisation) {
            abort(404, 'Organisation not found');
        }

        $settingsService = new CompanySettingsService($user['organisation_id']);

        return view('sharpfleet.admin.company-profile', [
            'organisation' => $organisation,
            'timezone'     => $settingsService->timezone(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::isCompanyAdmin($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'name'         => 'required|string|max:150',
            'industry'     => 'nullable|string|max:150',
            'timezone'     => 'required|string|max:100',
        ]);

        DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $user['organisation_id'])
            ->update([
                'name'         => $validated['name'],
                'industry'     => $validated['industry'] ?? null,
            ]);

        // Update timezone via settings service
        $settingsService = new CompanySettingsService($user['organisation_id']);
        $settings = $settingsService->all();
        $settings['timezone'] = $validated['timezone'];

        DB::connection('sharpfleet')
            ->table('company_settings')
            ->updateOrInsert(
                ['organisation_id' => $user['organisation_id']],
                ['settings_json' => json_encode($settings)]
            );

        if ($request->has('save_and_return')) {
            return redirect('/app/sharpfleet/admin/company')
                ->with('success', 'Company profile updated.');
        }

        return back()->with('success', 'Company profile updated.');
    }
}
