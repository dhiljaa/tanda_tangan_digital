@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Document Details</h1>
            <p class="text-gray-600">View and verify document information</p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-2">
            <a href="{{ route('documents.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                <i class="bi bi-arrow-left mr-2"></i>
                Back to List
            </a>
            @if(isset($duplicate) && $duplicate)
                <a href="{{ route('documents.duplicate', $document) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="bi bi-copy mr-2"></i>
                    Duplicate
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Document Information -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Document Information</h2>
                </div>
                <div class="p-6">
                    <dl class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Title</dt>
                            <dd class="sm:col-span-2 text-sm text-gray-900 font-medium">{{ $document->title }}</dd>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Document Number</dt>
                            <dd class="sm:col-span-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <i class="bi bi-hash mr-1"></i>
                                    {{ $document->document_number }}
                                </span>
                                <button onclick="copyToClipboard('{{ $document->document_number }}')" 
                                        class="ml-2 p-1 text-gray-400 hover:text-gray-600 transition-colors" 
                                        title="Copy document number">
                                    <i class="bi bi-clipboard text-sm"></i>
                                </button>
                            </dd>
                        </div>

                       @if($document->description)
<div class="space-y-2">
    <dt class="text-sm font-medium text-gray-500">Description</dt>
    <dd class="text-sm text-gray-900">
        <div class="prose prose-sm max-w-none p-4 bg-gray-50 rounded-lg border" 
             style="word-wrap: break-word; white-space: pre-wrap; max-width: 100%;">
            {{ $document->description }}
        </div>
    </dd>
</div>
@endif
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Document Hash</dt>
                            <dd class="sm:col-span-2">
                                <code class="px-3 py-1 text-xs font-mono bg-gray-100 text-gray-800 rounded border break-all">{{ $document->hash }}</code>
                                <button onclick="copyToClipboard('{{ $document->hash }}')" 
                                        class="ml-2 p-1 text-gray-400 hover:text-gray-600 transition-colors" 
                                        title="Copy hash">
                                    <i class="bi bi-clipboard text-sm"></i>
                                </button>
                            </dd>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Signed By</dt>
                            <dd class="sm:col-span-2 text-sm text-gray-900">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs font-medium mr-2">
                                        {{ substr($document->user->name, 0, 1) }}
                                    </div>
                                    {{ $document->user->name }}
                                </div>
                            </dd>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="sm:col-span-2 text-sm text-gray-900">
                                <div>{{ $document->created_at->format('d M Y H:i:s') }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $document->created_at->diffForHumans() }}</div>
                            </dd>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Verification</dt>
                            <dd class="sm:col-span-2">
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <a href="{{ route('verify.show', $document->hash) }}" 
                                       target="_blank" 
                                       class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                        <i class="bi bi-shield-check mr-2"></i>
                                        Verify Document
                                    </a>
                                    <button onclick="copyVerificationLink()" 
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                        <i class="bi bi-link mr-2"></i>
                                        Copy Link
                                    </button>
                                </div>
                                <div class="mt-2 text-xs text-gray-500 break-all" id="verificationUrl">
                                    {{ route('verify.show', $document->hash) }}
                                </div>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- QR Code Section -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 sticky top-4">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">QR Code</h3>
                </div>
                <div class="p-6 text-center">
                    @if($document->qr_code_path && file_exists(public_path('storage/' . $document->qr_code_path)))
                        <div class="inline-block p-4 bg-white border-2 border-gray-200 rounded-lg shadow-sm">
                            <img src="{{ asset('storage/' . $document->qr_code_path) }}" 
                                 alt="QR Code for {{ $document->title }}" 
                                 class="w-48 h-48 object-contain mx-auto"
                                 onclick="showQrModal()"
                                 style="cursor: pointer;"
                                 title="Click to enlarge">
                        </div>
                        <p class="text-sm text-gray-500 mt-4 mb-4">Scan to verify document authenticity</p>
                        <div class="flex flex-col gap-2">
                            <a href="{{ asset('storage/' . $document->qr_code_path) }}" 
                               download="qr_{{ Str::slug($document->document_number) }}.png"
                               class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                <i class="bi bi-download mr-2"></i>
                                Download QR Code
                            </a>
                           
                        </div>
                    @else
                        <div class="inline-block p-4 bg-red-50 border-2 border-red-200 rounded-lg">
                            <div class="w-48 h-48 flex items-center justify-center">
                                <div class="text-center">
                                    <i class="bi bi-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                                    <p class="text-sm text-red-600">QR Code not found</p>
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-red-500 mt-4">QR code file is missing or corrupted</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Digital Signature Section -->
    <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Digital Signature</h3>
        </div>
        <div class="p-6">
            @if($document->file_path && Storage::disk('public')->exists($document->file_path))
                <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                    <img src="{{ Storage::url($document->file_path) }}" 
                         alt="Digital Signature for {{ $document->title }}" 
                         class="max-h-48 mx-auto object-contain cursor-pointer hover:scale-105 transition-transform"
                         onclick="showSignatureModal()"
                         title="Click to view full size">
                    <p class="text-sm text-gray-500 mt-4">Digital signature image - Click to view full size</p>
                </div>
            @else
                <div class="bg-red-50 border-2 border-dashed border-red-300 rounded-lg p-8 text-center">
                    <i class="bi bi-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                    <p class="text-sm text-red-600">Signature file not found</p>
                    <p class="text-xs text-red-500 mt-2">The signature file may have been moved or deleted</p>
                </div>
            @endif
        </div>
    </div>

   

<!-- QR Code Modal -->
<div id="qrModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="qrModalTitle" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModals()"></div>
        
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="qrModalTitle">QR Code - {{ $document->title }}</h3>
                    <div class="mt-2 flex justify-center">
                        <img src="{{ asset('storage/' . $document->qr_code_path) }}" alt="QR Code" class="max-w-full h-auto">
                    </div>
                    <p class="text-sm text-gray-500 mt-4 text-center">Document: {{ $document->document_number }}</p>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors" 
                        onclick="downloadQrFromModal()">
                    <i class="bi bi-download mr-2"></i> Download
                </button>
                <button type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors" 
                        onclick="closeModals()">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Signature Modal -->
<div id="signatureModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="signatureModalTitle" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModals()"></div>
        
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="signatureModalTitle">Digital Signature - {{ $document->title }}</h3>
                    <div class="mt-2 flex justify-center bg-gray-50 p-4 rounded-lg">
                        <img src="{{ Storage::url($document->file_path) }}" alt="Digital Signature" class="max-w-full h-auto max-h-96 object-contain">
                    </div>
                    <p class="text-sm text-gray-500 mt-4 text-center">Document: {{ $document->document_number }}</p>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors" 
                        onclick="closeModals()">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toastContainer" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

@endsection

@push('scripts')
<script>
function showQrModal() {
    document.getElementById('qrModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function showSignatureModal() {
    document.getElementById('signatureModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeModals() {
    document.getElementById('qrModal').classList.add('hidden');
    document.getElementById('signatureModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function downloadQrFromModal() {
    const link = document.createElement('a');
    link.href = '{{ asset('storage/' . $document->qr_code_path) }}';
    link.download = 'qr_{{ Str::slug($document->document_number) }}.png';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function printQr() {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>QR Code - {{ $document->document_number }}</title>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; margin: 20px; }
                    img { max-width: 300px; height: auto; }
                    h1 { font-size: 18px; margin-bottom: 20px; }
                    .info { margin-top: 20px; font-size: 14px; color: #666; }
                    .document-number { font-weight: bold; color: #2563eb; }
                </style>
            </head>
            <body>
                <h1>{{ $document->title }}</h1>
                <p class="document-number">Document Number: {{ $document->document_number }}</p>
                <img src="{{ asset('storage/' . $document->qr_code_path) }}" alt="QR Code">
                <div class="info">
                    @if($document->description)
                    <p><strong>Description:</strong> {{ $document->description }}</p>
                    @endif
                    <p><strong>Document Hash:</strong> {{ $document->hash }}</p>
                    <p><strong>Created:</strong> {{ $document->created_at->format('d M Y H:i:s') }}</p>
                    <p><strong>Signed by:</strong> {{ $document->user->name }}</p>
                    <p><strong>Verification URL:</strong> {{ route('verify.show', $document->hash) }}</p>
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showToast('Copied to clipboard!', 'success');
    } catch (err) {
        fallbackCopy(text);
    }
}

async function copyVerificationLink() {
    const url = document.getElementById('verificationUrl').textContent.trim();
    try {
        await navigator.clipboard.writeText(url);
        showToast('Verification link copied to clipboard!', 'success');
    } catch (err) {
        fallbackCopy(url);
    }
}

function fallbackCopy(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showToast('Copied to clipboard!', 'success');
    } catch (fallbackErr) {
        showToast('Could not copy to clipboard', 'error');
    }
    
    document.body.removeChild(textArea);
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toastId = 'toast_' + Date.now();
    
    const bgColor = type === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
    const textColor = type === 'success' ? 'text-green-800' : 'text-red-800';
    const iconColor = type === 'success' ? 'text-green-400' : 'text-red-400';
    const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
    
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `${bgColor} border rounded-lg p-4 shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out max-w-sm`;
    
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="bi ${icon} ${iconColor} mr-3"></i>
            <div class="flex-1">
                <p class="text-sm font-medium ${textColor}">${type === 'success' ? 'Success' : 'Error'}</p>
                <p class="text-sm ${textColor} opacity-90">${message}</p>
            </div>
            <button onclick="removeToast('${toastId}')" class="${textColor} hover:opacity-75 ml-3">
                <i class="bi bi-x"></i>
            </button>
        </div>
    `;
    
    container.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
        toast.classList.add('translate-x-0');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        removeToast(toastId);
    }, 5000);
}

function removeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModals();
    }
    
    // Copy shortcuts
    if (e.ctrlKey || e.metaKey) {
        if (e.key === 'h') {
            e.preventDefault();
            copyToClipboard('{{ $document->hash }}');
        } else if (e.key === 'n') {
            e.preventDefault();
            copyToClipboard('{{ $document->document_number }}');
        } else if (e.key === 'l') {
            e.preventDefault();
            copyVerificationLink();
        }
    }
});

// Error handling for images
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('error', function() {
            this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTgiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmaWxsPSIjOTk5Ij5JbWFnZSBub3QgZm91bmQ8L3RleHQ+PC9zdmc+';
            this.alt = 'Image not found';
            this.classList.add('opacity-50');
        });
    });

    // Add keyboard shortcut tooltips
    const tooltips = {
        'Ctrl+H': 'Copy hash',
        'Ctrl+N': 'Copy document number', 
        'Ctrl+L': 'Copy verification link',
        'Esc': 'Close modals'
    };

    // Create help tooltip
    const helpButton = document.createElement('button');
    helpButton.className = 'fixed bottom-4 left-4 p-3 bg-gray-600 text-white rounded-full hover:bg-gray-700 transition-colors z-40';
    helpButton.innerHTML = '<i class="bi bi-question"></i>';
    helpButton.title = 'Keyboard shortcuts';
    helpButton.onclick = showKeyboardShortcuts;
    document.body.appendChild(helpButton);
});

function showKeyboardShortcuts() {
    const shortcuts = [
        { key: 'Ctrl+H', action: 'Copy document hash' },
        { key: 'Ctrl+N', action: 'Copy document number' },
        { key: 'Ctrl+L', action: 'Copy verification link' },
        { key: 'Esc', action: 'Close modals' }
    ];
    
    const shortcutList = shortcuts.map(s => `${s.key}: ${s.action}`).join('\n');
    alert('Keyboard Shortcuts:\n\n' + shortcutList);
}
</script>
@endpush

@push('styles')
<style>
/* Custom styles for better visual hierarchy */
.break-all {
    word-break: break-all;
}

.prose {
    line-height: 1.6;
}

/* Badge styling */
.bg-blue-100 {
    background-color: #dbeafe;
}

.text-blue-800 {
    color: #1e40af;
}

/* Print styles */
@media print {
    .fixed, .sticky, button, .transition-transform {
        display: none !important;
    }
    
    .grid {
        display: block !important;
    }
    
    .lg\:col-span-2, .lg\:col-span-1 {
        width: 100% !important;
    }
    
    img {
        max-width: 200px !important;
        height: auto !important;
    }
    
    .shadow-sm, .shadow-lg, .border {
        box-shadow: none !important;
        border: 1px solid #e5e7eb !important;
    }
}

/* Responsive improvements */
@media (max-width: 640px) {
    .max-w-6xl {
        margin-left: 1rem;
        margin-right: 1rem;
    }
    
    .grid-cols-1.sm\:grid-cols-3 {
        gap: 1rem;
    }
    
    .sm\:col-span-2 {
        margin-top: 0.5rem;
    }
    
    .sticky {
        position: static !important;
    }
}

/* Smooth transitions */
.transition-transform {
    transition: transform 0.2s ease-in-out;
}

.hover\:scale-105:hover {
    transform: scale(1.05);
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Focus styles for accessibility */
button:focus, a:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Loading states */
.loading {
    opacity: 0.5;
    pointer-events: none;
}
</style>
@endpush