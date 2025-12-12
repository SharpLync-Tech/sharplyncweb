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
        // 📊 Read stats for display
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
        // ✅ Validate input
        $request->validate([
            'message' => 'nullable|string',
            'file'    => 'nullable|file|max:4096',
        ]);

        $text = $request->message;

        // 📎 Handle uploaded file
        if ($request->hasFile('file')) {

            $file = $request->file('file');
            $ext  = strtolower($file->getClientOriginalExtension());

            /**
             * TEXT FILES (.txt, .eml)
             */
            if (in_array($ext, ['txt', 'eml'])) {
                $text = file_get_contents($file->getRealPath());
            }

            /**
             * IMAGE FILES (OCR)
             */
            elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {

                $ocrText = $ocr->extractText($file->getRealPath());

                if ($ocrText && trim($ocrText) !== '') {
                    $text = $ocrText;
                } else {
                    // Explicit signal to AI that OCR found nothing
                    $text = 'IMAGE_SUBMITTED_WITH_NO_READABLE_TEXT';
                }
            }

            /**
             * Unsupported files
             */
            else {
                return back()->with('error', 'Unsupported file type. Allowed: txt, eml, jpg, png.');
            }
        }

        // 🚫 Ensure something exists to analyze
        if (!$text || trim($text) === '') {
            return back()->with('error', 'Please paste a message or upload a file.');
        }

        // 🤖 Run AI analysis
        $result = $client->analyze($text);

        // 📊 Parse verdict + log stats
        $json = json_decode($result, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($json['verdict'])) {

            $verdictRaw = strtolower($json['verdict']);

            $verdictForStats =
                str_contains($verdictRaw, 'scam') ? 'scam' :
                (str_contains($verdictRaw, 'suspicious') || str_contains($verdictRaw, 'unclear')
                    ? 'unknown'
                    : 'safe');

            DB::table('threatcheck_stats')->insert([
                'verdict' => $verdictForStats,
            ]);
        }

        // 📊 Re-read stats for live update
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
            'result' => $result,
            'stats'  => $stats,
        ]);
    }
}
