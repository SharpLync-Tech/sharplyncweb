<?php

namespace App\ScamCheck\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ScamCheck\Services\AzureOpenAIClient;

class ScamCheckerController extends Controller
{
    public function index()
    {
        return view('scamcheck.index');
    }

    public function analyze(Request $request, AzureOpenAIClient $client)
    {
        // Validate input
        $request->validate([
            'message' => 'nullable|string',
            'file' => 'nullable|file|max:4096'
        ]);

        $text = $request->message;

        // If a file is uploaded, extract text
        if ($request->hasFile('file')) {

            $file = $request->file('file');
            $ext  = strtolower($file->getClientOriginalExtension());

            // Text-based files
            if (in_array($ext, ['txt', 'eml'])) {
                $text = file_get_contents($file->getRealPath());
            }

            // Image screenshots (jpg, png)
            elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {

                $imageData = base64_encode(file_get_contents($file->getRealPath()));

                // We send an indicator that this is an image to the AI service
                $text = "IMAGE_BASE64:{$imageData}";
            }

            else {
                return back()->with('error', 'Unsupported file type. Allowed: txt, eml, jpg, png.');
            }
        }

        // Ensure text exists after processing
        if (!$text || trim($text) === '') {
            return back()->with('error', 'Please paste a message or upload a file.');
        }

        // Run AI analysis
        $result = $client->analyze($text);

        return view('scamcheck.index', [
            'input'  => $text,
            'result' => $result
        ]);
    }
}
