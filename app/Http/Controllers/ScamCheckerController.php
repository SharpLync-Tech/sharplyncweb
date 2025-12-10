<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AzureOpenAIClient;

class ScamCheckerController extends Controller
{
    public function index()
    {
        return view('scam-checker');
    }

    public function analyze(Request $request, AzureOpenAIClient $client)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $result = $client->analyze($request->message);

        return view('scam-checker', [
            'input' => $request->message,
            'result' => $result
        ]);
    }
}
