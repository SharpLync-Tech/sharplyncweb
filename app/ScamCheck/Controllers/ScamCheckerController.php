<?php

namespace App\ScamCheck\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ScamCheck\Services\AzureOpenAIClient;
use App\ScamCheck\Services\AzureVisionOCR;
use Illuminate\Support\Facades\DB;

class ScamCheckerController extends Controller
{
    /**
     * Display the ThreatCheck page
     */
    public function index()
    {
        $stats = DB::table('threatcheck_stats')
            ->selectRaw('
                COUNT(*) as total_checked,
                SUM(verdict = "safe") as total_safe,
                SUM(verdict = "scam") as total_scam,
                SUM(verdict = "unknown") as total_unknown
            ')
            ->first();

        return view('scamcheck.index', [
            'stats' => $stats,
        ]);
    }

    /**
     * Analyze message or uploaded file
     */
    public function analyze(
        Request $request,
        AzureOpenAIClient $client,
        AzureVisionOCR $ocr
    ) {
        $request->validate([
            'message' => 'nullable|string',
            'file'    => 'nullable|file|max:4096',
        ]);

        $text = $request->message;
        $analysisMeta = [
            'forced_unclear' => false,
            'reason' => null,
        ];

        // 📎 Handle uploaded file
        if ($request->hasFile('file')) {

            $file = $request->file('file');
            $ext  = strtolower($file->getClientOriginalExtension());

            if (in_array($ext, ['txt', 'eml'])) {
                $text = file_get_contents($file->getRealPath());
            }
            elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {

                $ocrText = $ocr->extractText($file->getRealPath());

                if ($ocrText && trim($ocrText) !== '') {
                    $text = $ocrText;
                } else {
                    $analysisMeta['forced_unclear'] = true;
                    $analysisMeta['reason'] = 'OCR could not extract readable text';
                    $text = 'IMAGE_SUBMITTED_WITH_NO_READABLE_TEXT';
                }
            }
            else {
                return back()->with('error', 'Unsupported file type.');
            }
        }

        if (!$text || trim($text) === '') {
            return back()->with('error', 'Please paste a message or upload a file.');
        }

        // 🤖 Run AI analysis
        $rawResult = $client->analyze($text);

        // 🚨 HARD DEFENSIVE PARSING
        $json = json_decode($rawResult, true);
        $validJson = json_last_error() === JSON_ERROR_NONE && is_array($json);

        if (!$validJson) {
            $analysisMeta['forced_unclear'] = true;
            $analysisMeta['reason'] = 'Analysis returned an invalid format';
        }

        // Required fields check
        $required = ['verdict', 'risk_score', 'summary', 'red_flags', 'recommended_action'];

        foreach ($required as $field) {
            if (!$analysisMeta['forced_unclear'] && (!isset($json[$field]) || $json[$field] === '')) {
                $analysisMeta['forced_unclear'] = true;
                $analysisMeta['reason'] = 'Analysis returned incomplete data';
            }
        }

        // Red flags must never be empty for a "safe" result
        if (
            !$analysisMeta['forced_unclear'] &&
            isset($json['red_flags']) &&
            is_array($json['red_flags']) &&
            count($json['red_flags']) === 0
        ) {
            $analysisMeta['forced_unclear'] = true;
            $analysisMeta['reason'] = 'No red flags returned — confidence downgraded';
        }

        // 🚧 FORCE DOWNGRADE IF NEEDED
        if ($analysisMeta['forced_unclear']) {
            $json = [
                'verdict' => 'unclear',
                'risk_score' => 50,
                'summary' => 'The analysis could not be completed with sufficient confidence.',
                'red_flags' => [
                    'Insufficient or degraded analysis output',
                ],
                'recommended_action' =>
                    'Do not rely on this result alone. Independently verify the message and avoid clicking links or making payments.'
            ];
        }

        // 📊 Stats logging (conservative)
        $verdictRaw = strtolower($json['verdict']);

        $verdictForStats =
            str_contains($verdictRaw, 'scam') ? 'scam' :
            (str_contains($verdictRaw, 'unclear') || str_contains($verdictRaw, 'suspicious')
                ? 'unknown'
                : 'unknown'); // NEVER auto-safe

        DB::table('threatcheck_stats')->insert([
            'verdict' => $verdictForStats,
        ]);

        // Refresh stats
        $stats = DB::table('threatcheck_stats')
            ->selectRaw('
                COUNT(*) as total_checked,
                SUM(verdict = "safe") as total_safe,
                SUM(verdict = "scam") as total_scam,
                SUM(verdict = "unknown") as total_unknown
            ')
            ->first();

        return view('scamcheck.index', [
            'input'  => $text,
            'result' => json_encode($json, JSON_PRETTY_PRINT),
            'stats'  => $stats,
            'meta'   => $analysisMeta,
        ]);
    }
}
