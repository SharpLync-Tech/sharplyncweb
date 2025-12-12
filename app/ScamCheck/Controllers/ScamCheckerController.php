<?php

namespace App\ScamCheck\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ScamCheck\Services\AzureOpenAIClient;
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
     * Analyze message or file
     */
    public function analyze(Request $request, AzureOpenAIClient $client)
    {
        // Validate input
        $request->validate([
            'message' => 'nullable|string',
            'file' => 'nullable|file|max:4096'
        ]);

        $text = $request->message;

        // Handle uploaded file
        if ($request->hasFile('file')) {

            $file = $request->file('file');
            $ext  = strtolower($file->getClientOriginalExtension());

            // Text-based files
            if (in_array($ext, ['txt', 'eml'])) {
                $text = file_get_contents($file->getRealPath());
            }

            // Image screenshots
            elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $imageData = base64_encode(file_get_contents($file->getRealPath()));
                $text = "IMAGE_BASE64:{$imageData}";
            }

            else {
                return back()->with('error', 'Unsupported file type. Allowed: txt, eml, jpg, png.');
            }
        }

        // Ensure text exists
        if (!$text || trim($text) === '') {
            return back()->with('error', 'Please paste a message or upload a file.');
        }

        // 🔍 Run AI analysis
        $result = $client->analyze($text);

        // 📊 Determine verdict + log stats
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

        // 📊 Re-read stats so page updates immediately
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
