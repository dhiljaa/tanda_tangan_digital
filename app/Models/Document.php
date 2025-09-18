<?php
// app/Models/Document.php - FIXED VERSION

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'document_number',
        'description',
        'hash',
        'file_path',
        'qr_code_path',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get full URL untuk signature file dengan fallback
     */
    public function getSignatureUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        // Cek apakah file ada di storage
        if (Storage::disk('public')->exists($this->file_path)) {
            return Storage::url($this->file_path);
        }

        // Fallback: cek di public storage
        $publicPath = public_path('storage/' . $this->file_path);
        if (file_exists($publicPath)) {
            return asset('storage/' . $this->file_path);
        }

        Log::warning('Signature file not found', [
            'document_id' => $this->id,
            'file_path' => $this->file_path,
            'storage_exists' => Storage::disk('public')->exists($this->file_path),
            'public_exists' => file_exists($publicPath)
        ]);

        return null;
    }

    /**
     * Get full URL untuk QR code dengan fallback
     */
    public function getQrCodeUrlAttribute(): ?string
    {
        if (!$this->qr_code_path) {
            return null;
        }

        $publicPath = public_path('storage/' . $this->qr_code_path);
        if (file_exists($publicPath)) {
            return asset('storage/' . $this->qr_code_path);
        }

        // Fallback: cek di storage disk
        if (Storage::disk('public')->exists($this->qr_code_path)) {
            return Storage::url($this->qr_code_path);
        }

        Log::warning('QR code file not found', [
            'document_id' => $this->id,
            'qr_code_path' => $this->qr_code_path,
            'public_exists' => file_exists($publicPath),
            'storage_exists' => Storage::disk('public')->exists($this->qr_code_path)
        ]);

        return null;
    }

    /**
     * Get verification URL - FIXED to use hash consistently
     */
    public function getVerificationUrlAttribute(): string
    {
        return route('verify.show', $this->hash); // Konsisten dengan controller
    }

    /**
     * Check if signature file exists
     */
    public function hasValidSignature(): bool
    {
        if (!$this->file_path) {
            return false;
        }

        return Storage::disk('public')->exists($this->file_path) || 
               file_exists(public_path('storage/' . $this->file_path));
    }

    /**
     * Check if QR code file exists
     */
    public function hasValidQrCode(): bool
    {
        if (!$this->qr_code_path) {
            return false;
        }

        return file_exists(public_path('storage/' . $this->qr_code_path)) ||
               Storage::disk('public')->exists($this->qr_code_path);
    }

    /**
     * Get signature file size in bytes
     */
    public function getSignatureFileSizeAttribute(): ?int
    {
        if (!$this->file_path || !$this->hasValidSignature()) {
            return null;
        }

        if (Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->size($this->file_path);
        }

        $publicPath = public_path('storage/' . $this->file_path);
        if (file_exists($publicPath)) {
            return filesize($publicPath);
        }

        return null;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->signature_file_size;
        
        if (!$bytes) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    /**
     * Scope untuk mencari dokumen berdasarkan query
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('document_number', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeDateRange($query, $startDate = null, $endDate = null)
    {
        return $query->when($startDate, function ($q) use ($startDate) {
            return $q->whereDate('created_at', '>=', $startDate);
        })->when($endDate, function ($q) use ($endDate) {
            return $q->whereDate('created_at', '<=', $endDate);
        });
    }

    /**
     * Scope untuk dokumen user tertentu
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Generate download filename for signature
     */
    public function getSignatureDownloadNameAttribute(): string
    {
        return 'signature_' . $this->document_number . '_' . $this->id . '.png';
    }

    /**
     * Generate download filename for QR code
     */
    public function getQrDownloadNameAttribute(): string
    {
        return 'qr_' . $this->document_number . '_' . $this->id . '.png';
    }

    /**
     * Boot method untuk cleanup files saat model dihapus
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($document) {
            Log::info('Deleting document files', [
                'document_id' => $document->id,
                'document_number' => $document->document_number,
                'file_path' => $document->file_path,
                'qr_code_path' => $document->qr_code_path
            ]);

            // Cleanup signature file
            if ($document->file_path) {
                if (Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                    Log::info('Deleted signature file from storage', ['path' => $document->file_path]);
                }
            }

            // Cleanup QR code file
            if ($document->qr_code_path) {
                $fullPath = public_path('storage/' . $document->qr_code_path);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                    Log::info('Deleted QR code file from public', ['path' => $fullPath]);
                }
            }
        });
    }
}