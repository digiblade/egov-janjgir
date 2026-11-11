<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_details', function (Blueprint $table): void {
            $table->id();
            $table->string('singleton_key', 32)->default('main')->unique();
            $table->string('department_name_hi')->nullable();
            $table->string('department_name_en')->nullable();
            $table->text('office_address_hi')->nullable();
            $table->text('office_address_en')->nullable();
            $table->string('support_email')->nullable();
            $table->string('contact_number', 40)->nullable();
            $table->string('office_hours_hi')->nullable();
            $table->string('office_hours_en')->nullable();
            $table->string('map_url', 2048)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('support_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_number', 32)->unique();
            $table->string('full_name', 160);
            $table->string('email', 254);
            $table->string('mobile_number', 40)->nullable();
            $table->string('subject', 200);
            $table->text('message');
            $table->string('category', 80)->nullable();
            $table->string('status', 20)->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'submitted_at']);
            $table->index(['assigned_to', 'status']);
            $table->index('email');
        });

        Schema::create('support_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('support_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note');
            $table->timestamps();

            $table->index(['support_request_id', 'created_at']);
        });

        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('setting_key', 120)->unique();
            $table->json('value')->nullable();
            $table->boolean('is_sensitive')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('support_notes');
        Schema::dropIfExists('support_requests');
        Schema::dropIfExists('contact_details');
    }
};
