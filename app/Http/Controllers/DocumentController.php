<?php
// app/Http/Controllers/DocumentController.php - FIXED VERSION WITHOUT AUTHORIZE

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Auth::user()->documents()->latest()->paginate(10);
        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document_number' => 'required|string|max:100|unique:documents,document_number',
            'description' => 'nullable|string|max:1000',
            'signature' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ], [
            'document_number.required' => 'Nomor dokumen wajib diisi.',
            'document_number.unique' => 'Nomor dokumen sudah digunakan.',
            'title.required' => 'Judul dokumen wajib diisi.',
            'signature.required' => 'Tanda tangan wajib diupload.',
            'signature.image' => 'File harus berupa gambar.',
            'signature.mimes' => 'Format file harus PNG, JPG, atau JPEG.',
            'signature.max' => 'Ukuran file maksimal 2MB.',
        ]);

        try {
            // Store signature file
            $signatureFile = $request->file('signature');
            $filename = time() . '_' . Str::random(10) . '.' . $signatureFile->getClientOriginalExtension();
            $filePath = $signatureFile->storeAs('signatures', $filename, 'public');

            // Generate unique hash including document number
            $hash = hash('sha256', Auth::id() . $request->title . $request->document_number . time() . Str::random(20));

            // Save document first to get the hash
            $document = Document::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'document_number' => $request->document_number,
                'description' => $request->description,
                'hash' => $hash,
                'file_path' => $filePath,
                'qr_code_path' => '', // Will be updated after QR generation
            ]);

            // Generate QR Code with HASH-based URL (bukan ID)
            $verifyUrl = route('verify.show', $document->hash); // Menggunakan hash, bukan ID
            
            $options = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel' => QRCode::ECC_M,
                'scale' => 6,
                'imageBase64' => false,
            ]);

            $qrcode = new QRCode($options);
            $qrImageData = $qrcode->render($verifyUrl);

            // Ensure qrcodes directory exists
            $qrCodeDir = public_path('storage/qrcodes');
            if (!file_exists($qrCodeDir)) {
                mkdir($qrCodeDir, 0755, true);
            }
            
            // Save QR code file with document number in filename
            $qrCodeFilename = 'qr_' . $document->id . '_' . Str::slug($request->document_number) . '.png';
            $qrCodePath = 'qrcodes/' . $qrCodeFilename;
            $fullPath = public_path('storage/' . $qrCodePath);
            
            $saved = file_put_contents($fullPath, $qrImageData);
            
            if ($saved === false) {
                throw new \Exception('Failed to save QR code file');
            }

            // Update document with QR code path
            $document->update(['qr_code_path' => $qrCodePath]);

            Log::info('Document created successfully', [
                'document_id' => $document->id,
                'document_number' => $document->document_number,
                'document_hash' => $document->hash,
                'user_id' => Auth::id(),
                'title' => $document->title,
                'verify_url' => $verifyUrl // Log the verification URL
            ]);

            return redirect()->route('documents.index')
                ->with('success', 'Dokumen berhasil ditandatangani! QR code telah dibuat untuk verifikasi menggunakan hash.');

        } catch (\Exception $e) {
            Log::error('Document creation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'title' => $request->title,
                'document_number' => $request->document_number
            ]);

            // Cleanup on error
            if (isset($document)) {
                if (isset($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
                if (isset($fullPath) && file_exists($fullPath)) {
                    unlink($fullPath);
                }
                $document->delete();
            }
            
            return redirect()->back()
                ->withErrors(['signature' => 'Gagal membuat dokumen. Silakan coba lagi.'])
                ->withInput();
        }
    }

    public function show(Document $document)
    {
        // Manual authorization check
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this document.');
        }
        
        return view('documents.show', compact('document'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $documents = Auth::user()->documents()
            ->when($query, function ($q) use ($query) {
                return $q->where('title', 'like', "%{$query}%")
                        ->orWhere('document_number', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
            })
            ->latest()
            ->paginate(10);

        return view('documents.index', compact('documents'));
    }

    public function getByNumber($documentNumber)
    {
        $document = Document::where('document_number', $documentNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        return view('documents.show', compact('document'));
    }

    public function destroy(Document $document)
    {
        // Manual authorization check
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to delete this document.');
        }

        try {
            // Delete signature file
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }
            
            // Delete QR code file
            if ($document->qr_code_path) {
                $fullPath = public_path('storage/' . $document->qr_code_path);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }

            Log::info('Document deleted', [
                'document_id' => $document->id,
                'document_number' => $document->document_number,
                'document_hash' => $document->hash,
                'user_id' => Auth::id()
            ]);

            $document->delete();

            return redirect()->route('documents.index')
                ->with('success', 'Dokumen berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Document deletion failed', [
                'error' => $e->getMessage(),
                'document_id' => $document->id,
                'document_number' => $document->document_number,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('documents.index')
                ->with('error', 'Gagal menghapus dokumen. Silakan coba lagi.');
        }
    }

    public function duplicate(Document $document)
    {
        // Manual authorization check
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to duplicate this document.');
        }
        
        return view('documents.create', [
            'duplicate' => true,
            'originalDocument' => $document
        ]);
    }

    public function generateReport(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $documents = Auth::user()->documents()
            ->when($startDate, function ($q) use ($startDate) {
                return $q->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($q) use ($endDate) {
                return $q->whereDate('created_at', '<=', $endDate);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('documents.report', compact('documents', 'startDate', 'endDate'));
    }
}