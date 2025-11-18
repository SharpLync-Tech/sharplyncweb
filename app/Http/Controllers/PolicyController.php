<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\PdfToText\Pdf;
// We no longer need the Storage facade, as we are using the public_path helper.

class PolicyController extends Controller
{
    // The policy path is now relative to the public directory
    private $policyPath = 'policies/'; 

    /**
     * Renders a policy page, extracting content from a PDF.
     *
     * @param string $policyKey The key (e.g., 'terms', 'privacy').
     * @param string $viewPath The Blade view path (e.g., 'policies.terms').
     * @param string $filename The expected PDF filename (e.g., 'terms_and_conditions.pdf').
     * @return \Illuminate\View\View
     */
    private function renderPolicy(string $policyKey, string $viewPath, string $filename)
    {
        // 1. Define the full absolute file path on the server
        $filePath = public_path($this->policyPath . $filename); 
        
        // 2. Define the public URL for the download link
        $pdfUrl = asset($this->policyPath . $filename); 
        
        $content = '';
        
        // Check if the physical file exists at the given path
        if (file_exists($filePath)) {
            
            try {
                // Instantiate the Pdf class with the absolute path to the PDF
                $plainText = (new Pdf())->setPdf($filePath)->text();
                
                // Convert plain text to simple HTML (paragraphs)
                // nl2br handles new lines, e() escapes for safety
                $htmlContent = nl2br(e($plainText)); 
                $content = '<div class="policy-text-raw">' . $htmlContent . '</div>';

            } catch (\Exception $e) {
                \Log::error("Failed to parse PDF for {$policyKey}: " . $e->getMessage());
                // You can add a specific message for parsing failure
                $content = "<p>Error: Could not process the official document file. Please check server logs for 'pdftotext' path or installation issues.</p>";
            }
        } 
        // If file_exists is false, $content remains '' (empty), triggering the 
        // fallback message in the Blade view.

        return view($viewPath, [
            'content' => $content,
            'pdfUrl' => $pdfUrl,
        ]);
    }

    public function termsAndConditions()
    {
        return $this->renderPolicy(
            'terms',
            'policies.terms',
            'terms_and_conditions.pdf'
        );
    }

    public function privacyPolicy()
    {
        return $this->renderPolicy(
            'privacy',
            'policies.privacy',
            'privacy_policy.pdf'
        );
    }
}