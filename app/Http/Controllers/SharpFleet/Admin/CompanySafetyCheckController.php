<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CompanySettingsService;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanySafetyCheckController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canViewSafetyChecks($user)) {
            abort(403);
        }

        $settingsService = new CompanySettingsService($user['organisation_id']);

        return view('sharpfleet.admin.safety-checks', [
            'enabled' => $settingsService->safetyCheckEnabled(),
            'items'   => $settingsService->safetyCheckItems(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canUpdateSafetyChecks($user)) {
            abort(403);
        }

        $settingsService = new CompanySettingsService($user['organisation_id']);
        $settings = $settingsService->all();

        $settings['safety_check']['enabled'] = $request->boolean('enabled');

        $items = [];

        foreach ($request->input('items', []) as $item) {
            if (!empty(trim($item['label'] ?? ''))) {
                $items[] = [
                    'label' => trim($item['label']),
                ];
            }
        }

        $settings['safety_check']['items'] = $items;

        DB::connection('sharpfleet')
            ->table('company_settings')
            ->updateOrInsert(
                ['organisation_id' => $user['organisation_id']],
                ['settings_json' => json_encode($settings)]
            );

        if ($request->has('save_and_return')) {
            return redirect('/app/sharpfleet/admin/company')
                ->with('success', 'Safety checks updated.');
        }

        return back()->with('success', 'Safety checks updated.');
    }
}