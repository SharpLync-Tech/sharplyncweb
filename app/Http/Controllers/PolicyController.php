<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToText\Pdf;

class PolicyController extends Controller
{
    private $storageDisk = 'public';
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
        $filePath = $this->policyPath . $filename;
        $content = '';
        $pdfUrl = null;

        // Check if the PDF file exists in the public storage
        if (Storage::disk($this->storageDisk)->exists($filePath)) {
            $pdfAbsolutePath = Storage::disk($this->storageDisk)->path($filePath);
            $pdfUrl = Storage::disk($this->storageDisk)->url($filePath);

            try {
                // 1. Extract text from PDF
                $plainText = (new Pdf())->setPdf($pdfAbsolutePath)->text();
                
                // 2. Convert plain text to simple HTML (paragraphs)
                // This is a simple conversion; you may need a more robust library 
                // if your PDF has complex formatting (tables, lists, etc.)
                $htmlContent = nl2br(e($plainText)); // nl2br handles new lines, e() escapes for safety
                $content = '<div class="policy-text-raw">' . $htmlContent . '</div>';

            } catch (\Exception $e) {
                // Log the error and fall back to a message
                \Log::error("Failed to parse PDF for {$policyKey}: " . $e->getMessage());
                $content = "<p>Error: Could not process the official document file.</p>";
            }
        }

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