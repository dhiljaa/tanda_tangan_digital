<?php
// app/Http/Controllers/VerificationController.php - HASH ONLY VERSION

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    /**
     * Verify document by hash - SATU-SATUNYA method untuk verifikasi
     */
    public function show($hash)
    {
        $document = Document::where('hash', $hash)->with('user')->first();

        if ($document) {
            $status = 'valid';
            $message = 'Document is genuine and verified.';
            
            // Log verification activity
            Log::info('Document verified via hash', [
                'document_id' => $document->id,
                'document_number' => $document->document_number,
                'hash' => $document->hash,
                'title' => $document->title,
                'verified_at' => now(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        } else {
            $status = 'invalid';
            $message = 'Document verification failed. This may be a fake document or the hash is incorrect.';
            $document = null;
            
            // Log failed verification
            Log::warning('Failed document verification - hash not found', [
                'attempted_hash' => $hash,
                'verified_at' => now(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }

        return view('verify.show', compact('document', 'status', 'message'));
    }

    /**
     * Show verification form (optional)
     */
    public function index()
    {
        return view('verify.index');
    }

    /**
     * Handle manual verification form submission
     */
    public function verify(Request $request)
    {
        $request->validate([
            'hash' => 'required|string|min:3'
        ], [
            'hash.required' => 'Document hash or document number is required.',
            'hash.min' => 'Input must be at least 3 characters long.'
        ]);

        $input = trim($request->input('hash'));
        $document = null;

        // 1. Try to find by full hash first
        $document = Document::where('hash', $input)->first();

        // 2. If not found, try by document number
        if (!$document) {
            $document = Document::where('document_number', $input)->first();
        }

        // 3. If not found, try partial hash (minimum 16 characters for security)
        if (!$document && strlen($input) >= 16 && ctype_xdigit($input)) {
            $document = Document::where('hash', 'like', substr($input, 0, 16) . '%')->first();
        }

        // 4. If not found, try case-insensitive document number search
        if (!$document) {
            $document = Document::whereRaw('LOWER(document_number) = ?', [strtolower($input)])->first();
        }

        if ($document) {
            // Log successful search
            Log::info('Document found via manual search', [
                'document_id' => $document->id,
                'document_number' => $document->document_number,
                'hash' => $document->hash,
                'search_input' => $input,
                'search_method' => $this->getSearchMethod($input, $document),
                'ip' => request()->ip()
            ]);

            // Redirect to the hash verification URL
            return redirect()->route('verify.show', $document->hash);
        }

        // Log failed search
        Log::warning('Failed document search', [
            'search_input' => $input,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return redirect()->back()
            ->withErrors(['hash' => 'Document not found. Please check the hash or document number and try again.'])
            ->withInput();
    }

    /**
     * Determine which search method was used for logging
     */
    private function getSearchMethod($input, $document)
    {
        if ($input === $document->hash) {
            return 'full_hash';
        } elseif ($input === $document->document_number || strtolower($input) === strtolower($document->document_number)) {
            return 'document_number';
        } elseif (strlen($input) >= 16 && strpos($document->hash, $input) === 0) {
            return 'partial_hash';
        }
        
        return 'unknown';
    }

    /**
     * Get verification statistics (optional - for admin/analytics)
     */
    public function getVerificationStats()
    {
        // This could be used for analytics dashboard
        $stats = [
            'total_verifications_today' => Log::whereDate('created_at', today())
                ->where('message', 'like', '%Document verified via hash%')
                ->count(),
            'failed_verifications_today' => Log::whereDate('created_at', today())
                ->where('message', 'like', '%Failed document verification%')
                ->count(),
        ];

        return response()->json($stats);
    }
}