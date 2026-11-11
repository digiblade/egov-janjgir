<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->string('original_filename');
            $table->string('storage_disk', 32)->default('local');
            $table->string('storage_key')->unique();
            $table->string('mime_type', 191);
            $table->unsignedBigInteger('size_bytes');
            $table->char('checksum_sha256', 64)->nullable()->index();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('visibility', 16)->default('private')->index();
            $table->timestamps();

            $table->index(['mime_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
