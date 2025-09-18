@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Sign New Document</h1>
        <p class="text-gray-600">Create a digitally signed document with QR code verification</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Document Information</h2>
        </div>
        
        <div class="p-6">
            <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <!-- Document Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Document Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('title') border-red-300 focus:ring-red-500 @enderror" 
                           id="title" 
                           name="title" 
                           value="{{ old('title', $duplicate ?? false ? $originalDocument->title : '') }}" 
                           placeholder="Enter document title"
                           required>
                    @error('title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Document Number -->
                <div>
                    <label for="document_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Document Number <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('document_number') border-red-300 focus:ring-red-500 @enderror" 
                           id="document_number" 
                           name="document_number" 
                           value="{{ old('document_number') }}" 
                           placeholder="Enter unique document number (e.g., DOC-2024-001)"
                           required>
                    @error('document_number')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">Unique identifier for this document. This will be used for verification and cannot be changed.</p>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors resize-vertical @error('description') border-red-300 focus:ring-red-500 @enderror" 
                              id="description" 
                              name="description" 
                              rows="3" 
                              placeholder="Optional description of the document content or purpose"
                              maxlength="1000">{{ old('description', $duplicate ?? false ? $originalDocument->description : '') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">Optional description to help identify this document (max 1000 characters)</p>
                </div>

                <!-- Digital Signature Upload -->
                <div>
                    <label for="signature" class="block text-sm font-medium text-gray-700 mb-2">
                        Digital Signature <span class="text-red-500">*</span>
                    </label>
                    
                    <!-- File Upload Area -->
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors @error('signature') border-red-300 @enderror" 
                         id="dropZone">
                        <div class="space-y-1 text-center">
                            <div class="mx-auto h-12 w-12 text-gray-400" id="uploadIcon">
                                <i class="bi bi-cloud-upload text-3xl"></i>
                            </div>
                            <div class="flex text-sm text-gray-600">
                                <label for="signature" 
                                       class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Upload a file</span>
                                    <input id="signature" 
                                           name="signature" 
                                           type="file" 
                                           class="sr-only" 
                                           accept="image/png,image/jpeg,image/jpg" 
                                           required>
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG up to 2MB</p>
                        </div>
                    </div>
                    
                    <!-- File Preview -->
                    <div id="filePreview" class="hidden mt-4">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <img id="previewImage" src="" alt="Signature preview" class="h-16 w-16 object-cover rounded-lg border border-gray-300">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900" id="fileName"></p>
                                    <p class="text-sm text-gray-500" id="fileSize"></p>
                                </div>
                                <button type="button" 
                                        class="flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 transition-colors"
                                        onclick="removeFile()">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    @error('signature')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">Upload your signature image in PNG or JPG format, maximum 2MB</p>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row gap-3 sm:justify-end pt-6 border-t border-gray-200">
                    <a href="{{ route('documents.index') }}" 
                       class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex justify-center items-center px-6 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            id="submitBtn">
                        <i class="bi bi-shield-check mr-2"></i>
                        Sign Document
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Info Section -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="bi bi-info-circle text-blue-400 mr-3 mt-0.5"></i>
            <div>
                <h3 class="text-sm font-medium text-blue-800">How it works</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Provide a unique document number and title for identification</li>
                        <li>Add an optional description to explain the document purpose</li>
                        <li>Upload your signature image (PNG/JPG format)</li>
                        <li>The system generates a unique hash and QR code for verification</li>
                        <li>Your signed document is securely stored and can be verified by anyone</li>
                        <li>Use the QR code or verification link to prove document authenticity</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('signature');
    const filePreview = document.getElementById('filePreview');
    const previewImage = document.getElementById('previewImage');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const uploadIcon = document.getElementById('uploadIcon');
    const submitBtn = document.getElementById('submitBtn');
    const documentNumberInput = document.getElementById('document_number');

    // Auto-generate document number suggestion
    function generateDocumentNumber() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const time = String(now.getHours()).padStart(2, '0') + String(now.getMinutes()).padStart(2, '0');
        return `DOC-${year}${month}${day}-${time}`;
    }

    // Set placeholder with suggested document number
    if (documentNumberInput && !documentNumberInput.value) {
        documentNumberInput.placeholder = `e.g., ${generateDocumentNumber()}`;
    }

    // Character counter for description
    const descriptionTextarea = document.getElementById('description');
    if (descriptionTextarea) {
        const maxLength = 1000;
        const counterDiv = document.createElement('div');
        counterDiv.className = 'text-xs text-gray-500 mt-1 text-right';
        counterDiv.innerHTML = `<span id="descriptionCounter">0</span>/${maxLength} characters`;
        descriptionTextarea.parentNode.insertBefore(counterDiv, descriptionTextarea.nextSibling.nextSibling);

        const counter = document.getElementById('descriptionCounter');
        
        descriptionTextarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            counter.textContent = currentLength;
            
            if (currentLength > maxLength * 0.9) {
                counter.parentElement.classList.add('text-yellow-600');
            } else {
                counter.parentElement.classList.remove('text-yellow-600');
            }
            
            if (currentLength >= maxLength) {
                counter.parentElement.classList.add('text-red-600');
                counter.parentElement.classList.remove('text-yellow-600');
            } else {
                counter.parentElement.classList.remove('text-red-600');
            }
        });

        // Initial count
        const initialLength = descriptionTextarea.value.length;
        counter.textContent = initialLength;
    }

    // Drag and drop functionality
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    dropZone.addEventListener('drop', handleDrop, false);

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight(e) {
        dropZone.classList.add('border-blue-400', 'bg-blue-50');
    }

    function unhighlight(e) {
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }

    // File input change event
    fileInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });

    function handleFiles(files) {
        if (files.length > 0) {
            const file = files[0];
            
            // Validate file type
            if (!file.type.match('image/(png|jpeg|jpg)')) {
                alert('Please select a PNG or JPG image file.');
                return;
            }
            
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB.');
                return;
            }
            
            showFilePreview(file);
        }
    }

    function showFilePreview(file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            
            filePreview.classList.remove('hidden');
            dropZone.classList.add('hidden');
        };
        
        reader.readAsDataURL(file);
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    window.removeFile = function() {
        fileInput.value = '';
        filePreview.classList.add('hidden');
        dropZone.classList.remove('hidden');
        previewImage.src = '';
        fileName.textContent = '';
        fileSize.textContent = '';
    }

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const titleInput = document.getElementById('title');
        const documentNumberInput = document.getElementById('document_number');
        const signatureInput = document.getElementById('signature');

        // Validate required fields
        if (!titleInput.value.trim()) {
            e.preventDefault();
            titleInput.focus();
            alert('Please enter a document title.');
            return;
        }

        if (!documentNumberInput.value.trim()) {
            e.preventDefault();
            documentNumberInput.focus();
            alert('Please enter a document number.');
            return;
        }

        if (!signatureInput.files.length) {
            e.preventDefault();
            alert('Please upload a signature file.');
            return;
        }

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split mr-2 animate-spin"></i>Processing...';
    });

    // Auto-format document number (optional)
    documentNumberInput.addEventListener('blur', function() {
        let value = this.value.trim().toUpperCase();
        if (value && !value.match(/^[A-Z]+-\d{4,}-\d+$/)) {
            // Suggest a format if it doesn't match the expected pattern
            if (!value.includes('-')) {
                const now = new Date();
                const year = now.getFullYear();
                value = `DOC-${year}-${value}`;
                this.value = value;
            }
        }
    });
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

/* Custom focus styles for better accessibility */
input:focus, textarea:focus, button:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
}

/* File input hover effects */
#dropZone:hover {
    border-color: #9CA3AF;
}

#dropZone.border-blue-400 {
    border-color: #60A5FA !important;
    background-color: #EFF6FF !important;
}

/* Character counter styling */
.text-yellow-600 {
    color: #D97706 !important;
}

.text-red-600 {
    color: #DC2626 !important;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .max-w-2xl {
        margin-left: 1rem;
        margin-right: 1rem;
    }
    
    .px-6 {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

/* Form field spacing improvements */
.space-y-6 > * + * {
    margin-top: 1.5rem;
}

/* Textarea resize handle */
.resize-vertical {
    resize: vertical;
    min-height: 80px;
}
</style>
@endpush