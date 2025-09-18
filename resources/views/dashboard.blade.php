@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Dasbor</h1>
    <p class="text-gray-600">Selamat datang kembali, {{ Auth::user()->name }}!</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Kartu Total Dokumen -->
    <div class="bg-blue-600 text-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-blue-100">Total Dokumen</h3>
                <p class="text-3xl font-bold">{{ $totalDocuments }}</p>
            </div>
            <div class="p-3 bg-blue-500 rounded-lg">
                <i class="bi bi-file-earmark-text text-2xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Kartu Aksi Cepat -->
    <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Aksi Cepat</h3>
        </div>
        <div class="p-6">
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('documents.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                    <i class="bi bi-plus-circle mr-2"></i>
                    Tanda Tangani Dokumen Baru
                </a>
                <a href="{{ route('documents.index') }}" class="inline-flex items-center justify-center px-4 py-2 border border-blue-600 text-blue-600 font-medium rounded-lg hover:bg-blue-50 transition-colors">
                    <i class="bi bi-list mr-2"></i>
                    Lihat Semua Dokumen
                </a>
            </div>
        </div>
    </div>
</div>

@if($recentDocuments->count() > 0)
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Dokumen Terbaru</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hash</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($recentDocuments as $document)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $document->title }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <code class="px-2 py-1 text-xs font-mono bg-gray-100 text-gray-800 rounded">{{ Str::limit($document->hash, 20) }}</code>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $document->created_at->format('d M Y H:i') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('documents.show', $document) }}" class="inline-flex items-center px-3 py-1 border border-blue-600 text-blue-600 text-sm font-medium rounded-md hover:bg-blue-50 transition-colors">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
        <i class="bi bi-file-earmark-text text-2xl text-gray-400"></i>
    </div>
    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada dokumen</h3>
    <p class="text-gray-500 mb-6">Mulai dengan membuat dokumen tanda tangan digital pertama Anda.</p>
    <a href="{{ route('documents.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
        <i class="bi bi-plus-circle mr-2"></i>
        Buat Dokumen Pertama
    </a>
</div>
@endif
@endsection