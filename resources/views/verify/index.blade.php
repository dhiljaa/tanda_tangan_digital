@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-center">
        <div class="w-full max-w-2xl">
            <div class="bg-white rounded-lg shadow-md border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="bi bi-shield-check text-blue-600 mr-2"></i>
                        {{ __('Verify Document') }}
                    </h3>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('verify.post') }}" class="space-y-6">
                        @csrf

                        <div class="space-y-2">
                            <label for="hash" class="block text-sm font-medium text-gray-700">
                                {{ __('Document Hash or Document Number') }}
                            </label>
                            <div class="relative">
                                <input id="hash" 
                                       type="text" 
                                       name="hash" 
                                       value="{{ old('hash') }}" 
                                       required 
                                       autofocus
                                       placeholder="Enter document hash or document number..."
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('hash') border-red-500 ring-2 ring-red-200 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="bi bi-key text-gray-400"></i>
                                </div>
                            </div>
                            
                            @error('hash')
                                <div class="flex items-center mt-2 text-red-600">
                                    <i class="bi bi-exclamation-circle mr-1 text-sm"></i>
                                    <span class="text-sm">{{ $message }}</span>
                                </div>
                            @enderror
                            
                            <p class="text-sm text-gray-500 mt-2">
                                <i class="bi bi-info-circle mr-1"></i>
                                Enter the full document hash, partial hash (minimum 16 characters), or document number (e.g., DOC-2024-001)
                            </p>
                        </div>

                        <div class="pt-4">
                            <button type="submit" 
                                    class="w-full flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <i class="bi bi-search mr-2"></i>
                                {{ __('Verify Document') }}
                            </button>
                        </div>
                    </form>
                    
                    <!-- Additional Information -->
                    <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <h4 class="text-sm font-semibold text-blue-900 flex items-center mb-3">
                            <i class="bi bi-lightbulb mr-2"></i>
                            How to verify documents
                        </h4>
                        <ul class="text-sm text-blue-800 space-y-2">
                            <li class="flex items-start">
                                <i class="bi bi-check-circle text-blue-600 mr-2 mt-0.5 flex-shrink-0"></i>
                                <span><strong>QR Code:</strong> Scan the QR code on your document to verify automatically</span>
                            </li>
                            <li class="flex items-start">
                                <i class="bi bi-check-circle text-blue-600 mr-2 mt-0.5 flex-shrink-0"></i>
                                <span><strong>Document Hash:</strong> Enter the full document hash for complete verification</span>
                            </li>
                            <li class="flex items-start">
                                <i class="bi bi-check-circle text-blue-600 mr-2 mt-0.5 flex-shrink-0"></i>
                                <span><strong>Partial Hash:</strong> Use partial hash (minimum 16 characters) for quick lookup</span>
                            </li>
                            <li class="flex items-start">
                                <i class="bi bi-check-circle text-blue-600 mr-2 mt-0.5 flex-shrink-0"></i>
                                <span><strong>Document Number:</strong> Enter the document number (e.g., DOC-2024-001, INV-001, etc.)</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Example formats -->
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-900 flex items-center mb-3">
                            <i class="bi bi-clipboard-data mr-2"></i>
                            Example formats
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="font-medium text-gray-700 mb-2">Document Hash:</p>
                                <code class="px-2 py-1 bg-white rounded border text-xs text-gray-600">
                                    a1b2c3d4e5f6g7h8...
                                </code>
                            </div>
                            <div>
                                <p class="font-medium text-gray-700 mb-2">Document Number:</p>
                                <code class="px-2 py-1 bg-white rounded border text-xs text-gray-600">
                                    DOC-2024-001
                                </code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const hashInput = document.getElementById('hash');
    const form = document.querySelector('form');
    
    // Auto-detect input type and provide visual feedback
    hashInput.addEventListener('input', function() {
        const value = this.value.trim();
        const icon = this.parentElement.querySelector('.bi-key');
        
        if (value.length === 0) {
            icon.className = 'bi bi-key text-gray-400';
        } else if (value.match(/^[A-Z]+-\d{4}-\d+$/i)) {
            // Document number format detected
            icon.className = 'bi bi-hash text-blue-500';
        } else if (value.length >= 16 && value.match(/^[a-f0-9]+$/i)) {
            // Hash format detected
            icon.className = 'bi bi-shield-check text-green-500';
        } else if (value.length < 16) {
            // Too short for reliable verification
            icon.className = 'bi bi-exclamation-triangle text-yellow-500';
        } else {
            // Unknown format
            icon.className = 'bi bi-question-circle text-gray-500';
        }
    });

    // Form validation
    form.addEventListener('submit', function(e) {
        const value = hashInput.value.trim();
        
        if (value.length < 3) {
            e.preventDefault();
            alert('Please enter at least 3 characters for verification.');
            hashInput.focus();
            return;
        }

        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split animate-spin mr-2"></i>Verifying...';
        
        // Restore button if form submission fails (shouldn't happen with proper validation)
        setTimeout(() => {
            if (submitBtn.disabled) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }, 10000);
    });

    // Auto-focus and clear selection
    hashInput.focus();
    hashInput.select();
});
</script>
@endpush

@push('styles')
<style>
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Enhanced input styling */
input:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Icon color transitions */
.bi {
    transition: color 0.2s ease-in-out;
}

/* Code block styling */
code {
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
}

/* Responsive improvements */
@media (max-width: 640px) {
    .max-w-4xl {
        margin-left: 1rem;
        margin-right: 1rem;
    }
    
    .px-6 {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}
</style>
@endpush

@endsection