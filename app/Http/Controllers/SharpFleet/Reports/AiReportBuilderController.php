<?php

namespace App\Http\Controllers\SharpFleet\Reports;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\ReportAiClient;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiReportBuilderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canViewReports($user)) {
            abort(403);
        }

        return view('sharpfleet.admin.reports.ai-report-builder', [
            'prompt' => old('prompt', ''),
            'result' => null,
        ]);
    }

    public function generate(Request $request, ReportAiClient $client): View
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canViewReports($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:2000'],
        ]);

        $organisationId = (int) ($user['organisation_id'] ?? 0);
        $settings = new CompanySettingsService($organisationId);

        $result = $client->generateReport(trim($validated['prompt']), [
            'timezone' => $settings->timezone(),
            'date_format' => $settings->dateFormat(),
            'distance_unit' => $settings->distanceUnit(),
        ]);

        if ($result === null) {
            return back()
                ->withErrors(['prompt' => 'Unable to generate the report right now. Please try again.'])
                ->withInput();
        }

        return view('sharpfleet.admin.reports.ai-report-builder', [
            'prompt' => trim($validated['prompt']),
            'result' => $result,
        ]);
    }
}
