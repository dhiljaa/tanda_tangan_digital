<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document Verification - DigiSign</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-10">
                <div class="text-center mb-4">
                    <h2><i class="bi bi-shield-check"></i> DigiSign Verification</h2>
                    <p class="text-muted">Document Authenticity Check</p>
                </div>

                <div class="card">
                    <div class="card-body">
                        @if($status === 'valid')
                            <div class="alert alert-success text-center" role="alert">
                                <i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i>
                                <h4 class="mt-3">Document Verified</h4>
                                <p>{{ $message }}</p>
                            </div>

                            <div class="row mt-4">
                                <!-- Document Information -->
                                <div class="col-md-8">
                                    <h5 class="mb-3"><i class="bi bi-file-text"></i> Document Information</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th class="bg-light" style="width: 30%;">Document Number:</th>
                                            <td><strong class="text-primary">{{ $document->document_number }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Title:</th>
                                            <td><strong>{{ $document->title }}</strong></td>
                                        </tr>
                                        @if($document->description)
                                        <tr>
                                            <th class="bg-light">Description:</th>
                                            <td>
                                                <div style="word-wrap: break-word; white-space: pre-wrap; max-width: 400px;">
                                                    {{ $document->description }}
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th class="bg-light">Signed By:</th>
                                            <td>{{ $document->user->name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Email:</th>
                                            <td>{{ $document->user->email }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Signature Date:</th>
                                            <td>{{ $document->created_at->format('d M Y H:i:s') }} WIB</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Document Hash:</th>
                                            <td>
                                                <small><code class="text-success">{{ $document->hash }}</code></small>
                                                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $document->hash }}')">
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <!-- Digital Signature -->
                                <div class="col-md-4">
                                    <h5 class="mb-3"><i class="bi bi-pen"></i> Digital Signature</h5>
                                    <div class="text-center">
                                        <div class="border rounded p-3 bg-white">
                                            <img src="{{ Storage::url($document->file_path) }}" 
                                                 alt="Digital Signature" 
                                                 class="img-fluid"
                                                 style="max-height: 200px; max-width: 100%;">
                                        </div>
                                        <small class="text-muted mt-2 d-block">Authentic Digital Signature</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Verification Details -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6><i class="bi bi-info-circle"></i> Verification Details</h6>
                                        <ul class="mb-0">
                                            <li>This document has been digitally signed and verified</li>
                                            <li>The signature is authentic and the document has not been tampered with</li>
                                            <li>Document hash matches the original signature</li>
                                            <li>Verification performed on: <strong>{{ now()->format('d M Y H:i:s') }} WIB</strong></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        @else
                            <div class="alert alert-danger text-center" role="alert">
                                <i class="bi bi-x-circle-fill" style="font-size: 3rem;"></i>
                                <h4 class="mt-3">Verification Failed</h4>
                                <p>{{ $message }}</p>
                                
                                <div class="mt-3">
                                    <small class="text-muted">
                                        Possible reasons:
                                        <ul class="list-unstyled mt-2">
                                            <li>• Document hash is invalid or corrupted</li>
                                            <li>• Document may have been modified after signing</li>
                                            <li>• QR code may be damaged or fake</li>
                                        </ul>
                                    </small>
                                </div>
                            </div>
                        @endif

                        <!-- Footer -->
                        <div class="text-center mt-4 pt-3 border-top">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        Powered by <strong>DigiSign</strong><br>
                                        Digital Signature Verification System
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="bi bi-shield-check"></i> Secure • <i class="bi bi-lock"></i> Encrypted • <i class="bi bi-check-circle"></i> Verified
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Print Button for Valid Documents -->
                @if($status === 'valid')
                <div class="text-center mt-3">
                    <button onclick="window.print()" class="btn btn-outline-primary">
                        <i class="bi bi-printer"></i> Print Verification Report
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Create a temporary toast notification
                const toast = document.createElement('div');
                toast.className = 'toast align-items-center text-white bg-success border-0';
                toast.setAttribute('role', 'alert');
                toast.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">
                            Hash copied to clipboard!
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                `;
                
                // Add to page and show
                document.body.appendChild(toast);
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
                
                // Remove after hiding
                toast.addEventListener('hidden.bs.toast', function() {
                    document.body.removeChild(toast);
                });
            });
        }

        // Print styles
        const printStyles = `
            <style>
                @media print {
                    .btn { display: none !important; }
                    .alert { page-break-inside: avoid; }
                    .card { border: 1px solid #000; }
                    body { background: white !important; }
                }
            </style>
        `;
        document.head.insertAdjacentHTML('beforeend', printStyles);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>