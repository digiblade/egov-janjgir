<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 120);
            $table->string('subject_type', 191)->nullable();
            $table->string('subject_id', 64)->nullable();
            $table->json('changed_fields')->nullable();
            $table->timestamp('occurred_at')->useCurrent();

            $table->index(['actor_id', 'occurred_at']);
            $table->index(['action', 'occurred_at']);
            $table->index(['subject_type', 'subject_id', 'occurred_at'], 'audit_subject_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
