{{-- resources/views/documents/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">My Documents</h1>
        <p class="text-gray-600">Manage your digitally signed documents</p>
    </div>
    <a href="{{ route('documents.create') }}" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors shadow-sm">
        <i class="bi bi-plus-circle mr-2"></i>
        Sign New Document
    </a>
</div>

<!-- Search Bar -->
<div class="mb-6">
    <form method="GET" action="{{ route('documents.search') }}" class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <input type="text" 
                   name="q" 
                   value="{{ request('q') }}" 
                   placeholder="Search by title, document number, or description..." 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <button type="submit" 
                class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <i class="bi bi-search mr-2"></i>Search
        </button>
        @if(request('q'))
            <a href="{{ route('documents.index') }}" 
               class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                Clear
            </a>
        @endif
    </form>
</div>

@if($documents->count() > 0)
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hash</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">QR Code</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($documents as $document)
                <tr class="hover:bg-gray-50 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $document->title }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-blue-600">{{ $document->document_number }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-600">
                            @if($document->description)
                                {{ Str::limit($document->description, 50) }}
                            @else
                                <span class="text-gray-400 italic">No description</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <code class="px-2 py-1 text-xs font-mono bg-gray-100 text-gray-800 rounded border">{{ Str::limit($document->hash, 20) }}</code>
                    </td>
                    <td class="px-6 py-4">
                        @if($document->qr_code_path)
                            @php
                                $qrUrl = asset('storage/' . $document->qr_code_path);
                                $qrPath = public_path('storage/' . $document->qr_code_path);
                                $qrExists = file_exists($qrPath);
                            @endphp
                            
                            @if($qrExists)
                                <div class="w-12 h-12 rounded-lg overflow-hidden cursor-pointer transition-transform hover:scale-110 shadow-sm border border-gray-200" 
                                     onclick="showQrModal('{{ $qrUrl }}', '{{ $document->title }}')">
                                    <img src="{{ $qrUrl }}" 
                                         alt="QR Code for {{ $document->title }}" 
                                         class="w-full h-full object-cover"
                                         loading="lazy">
                                </div>
                            @else
                                <div class="w-12 h-12 bg-red-50 border border-red-200 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-exclamation-triangle text-red-400"></i>
                                </div>
                            @endif
                        @else
                            <div class="w-12 h-12 bg-gray-100 border border-gray-200 rounded-lg flex items-center justify-center">
                                <i class="bi bi-question-circle text-gray-400"></i>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">{{ $document->created_at->format('d M Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $document->created_at->format('H:i') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('documents.show', $document) }}" 
                               class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                               title="View Document">
                                <i class="bi bi-eye text-sm"></i>
                            </a>
                            
                            @if($document->qr_code_path && file_exists(public_path('storage/' . $document->qr_code_path)))
                                <a href="{{ asset('storage/' . $document->qr_code_path) }}" 
                                   class="p-2 text-cyan-600 hover:bg-cyan-50 rounded-lg transition-colors"
                                   title="Download QR Code"
                                   download="qr_{{ Str::slug($document->document_number) }}.png">
                                    <i class="bi bi-download text-sm"></i>
                                </a>
                            @endif
                            
                            <button type="button" 
                                    class="p-2 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors"
                                    title="Copy Verification Link"
                                    onclick="copyVerificationLink('{{ route('verify.show', $document->hash) }}')">
                                <i class="bi bi-link text-sm"></i>
                            </button>
                            
                            <form method="POST" action="{{ route('documents.destroy', $document) }}" 
                                  class="inline" 
                                  onsubmit="return confirm('Are you sure you want to delete document {{ $document->document_number }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Delete Document">
                                    <i class="bi bi-trash text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $documents->links() }}
</div>

<!-- QR Code Modal -->
<div id="qrModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="qrModalTitle" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeQrModal()"></div>
        
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="qrModalTitle">QR Code</h3>
                    <div class="mt-2 flex justify-center">
                        <img id="qrModalImage" src="" alt="QR Code" class="max-w-full h-auto rounded-lg shadow-md" style="max-width: 300px;">
                    </div>
                    <p class="text-sm text-gray-500 mt-4 text-center">Scan this QR code to verify the document</p>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors" 
                        onclick="downloadQrFromModal()">
                    <i class="bi bi-download mr-2"></i> Download QR
                </button>
                <button type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors" 
                        onclick="closeQrModal()">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@else
<div class="text-center py-16 bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-6">
        <i class="bi bi-file-earmark-text text-2xl text-gray-400"></i>
    </div>
    @if(request('q'))
        <h3 class="text-lg font-medium text-gray-900 mb-2">No documents found</h3>
        <p class="text-gray-500 mb-8 max-w-md mx-auto">No documents match your search criteria. Try different keywords or clear the search filter.</p>
        <a href="{{ route('documents.index') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm mr-3">
            Clear Search
        </a>
    @else
        <h3 class="text-lg font-medium text-gray-900 mb-2">No documents yet</h3>
        <p class="text-gray-500 mb-8 max-w-md mx-auto">Start by signing your first document to create a secure digital signature with QR code verification.</p>
    @endif
    <a href="{{ route('documents.create') }}" class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors shadow-sm">
        <i class="bi bi-plus-circle mr-2"></i>
        Sign New Document
    </a>
</div>
@endif

<!-- Toast Notifications -->
<div id="toastContainer" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

@endsection

@push('scripts')
<script>
function showQrModal(imageSrc, title) {
    const modalImage = document.getElementById('qrModalImage');
    const modalTitle = document.getElementById('qrModalTitle');
    const modal = document.getElementById('qrModal');
    
    modalImage.src = imageSrc;
    modalTitle.textContent = 'QR Code - ' + title;
    
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeQrModal() {
    const modal = document.getElementById('qrModal');
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function downloadQrFromModal() {
    const img = document.getElementById('qrModalImage');
    const link = document.createElement('a');
    link.href = img.src;
    link.download = 'qr_code.png';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

async function copyVerificationLink(url) {
    try {
        await navigator.clipboard.writeText(url);
        showToast('Link copied to clipboard!', 'success');
    } catch (err) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = url;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showToast('Link copied to clipboard!', 'success');
        } catch (fallbackErr) {
            showToast('Could not copy link. Please copy manually: ' + url, 'error');
        }
        
        document.body.removeChild(textArea);
    }
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

// Handle image loading errors gracefully
document.addEventListener('DOMContentLoaded', function() {
    const qrImages = document.querySelectorAll('img[alt*="QR Code"]');
    qrImages.forEach(img => {
        img.addEventListener('error', function() {
            const placeholder = document.createElement('div');
            placeholder.className = 'w-12 h-12 bg-red-50 border border-red-200 rounded-lg flex items-center justify-center';
            placeholder.innerHTML = '<i class="bi bi-exclamation-triangle text-red-400"></i>';
            placeholder.title = 'QR Code could not be loaded';
            
            this.parentNode.replaceChild(placeholder, this);
        });
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeQrModal();
        }
    });
});
</script>
@endpush

@push('styles')
<style>
/* Custom scrollbar for webkit browsers */
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Print styles */
@media print {
    .fixed, .transition-transform, .hover\:scale-110 {
        display: none !important;
    }
    
    img[alt*="QR Code"] {
        width: 100px !important;
        height: 100px !important;
    }
}

/* Mobile responsiveness */
@media (max-width: 640px) {
    .overflow-x-auto {
        font-size: 0.875rem;
    }
    
    .px-6 {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .py-4 {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }
}

/* Table responsiveness */
@media (max-width: 768px) {
    /* Hide less critical columns on mobile */
    .table-hide-mobile {
        display: none;
    }
}
</style>
@endpush