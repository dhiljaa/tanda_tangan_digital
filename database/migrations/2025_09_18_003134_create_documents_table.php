<?php
// database/migrations/2024_01_01_000001_create_documents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('document_number', 100)->unique(); // Field untuk nomor dokumen yang unik
            $table->text('description')->nullable(); // Field untuk deskripsi (nullable)
            $table->string('hash')->unique();
            $table->string('file_path'); // Path untuk file signature
            $table->string('qr_code_path')->nullable(); // Path untuk QR code (nullable karena diupdate setelah dibuat)
            $table->timestamps();
            
            // Index untuk optimasi pencarian
            $table->index(['user_id', 'document_number']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};