<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('document_categories')->restrictOnDelete();
            $table->string('name_hi');
            $table->string('name_en');
            $table->text('description_hi')->nullable();
            $table->text('description_en')->nullable();
            $table->string('slug')->unique();
            $table->string('icon', 100)->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parent_id', 'is_active', 'display_order']);
            $table->index(['name_hi', 'name_en']);
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained('document_categories')->restrictOnDelete();
            $table->foreignId('media_asset_id')->constrained('media_assets')->restrictOnDelete();
            $table->string('title_hi');
            $table->string('title_en');
            $table->longText('description_hi')->nullable();
            $table->longText('description_en')->nullable();
            $table->string('version', 50)->default('1.0');
            $table->string('reference_number', 150)->nullable();
            $table->string('issuing_authority', 255)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->string('status', 20)->default('draft');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'status', 'published_at']);
            $table->index(['status', 'published_at', 'expires_at']);
            $table->index('display_order');
            $table->index(['title_hi', 'title_en']);
        });

        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('media_asset_id')->constrained('media_assets')->restrictOnDelete();
            $table->string('version', 50);
            $table->text('change_notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['document_id', 'version']);
            $table->index(['document_id', 'created_at']);
        });

        Schema::create('training_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_hi');
            $table->string('name_en');
            $table->text('description_hi')->nullable();
            $table->text('description_en')->nullable();
            $table->string('slug')->unique();
            $table->string('icon', 100)->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'display_order']);
            $table->index(['name_hi', 'name_en']);
        });

        Schema::create('training_resources', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained('training_categories')->restrictOnDelete();
            $table->string('resource_type', 40);
            $table->string('title_hi');
            $table->string('title_en');
            $table->longText('description_hi')->nullable();
            $table->longText('description_en')->nullable();
            $table->foreignId('media_asset_id')->nullable()->constrained('media_assets')->restrictOnDelete();
            $table->foreignId('thumbnail_media_id')->nullable()->constrained('media_assets')->restrictOnDelete();
            $table->string('external_url', 2048)->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->date('training_date')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->string('status', 20)->default('draft');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'status', 'published_at']);
            $table->index(['status', 'published_at', 'expires_at']);
            $table->index(['resource_type', 'display_order']);
            $table->index(['title_hi', 'title_en']);
        });

        Schema::create('faq_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_hi');
            $table->string('name_en');
            $table->text('description_hi')->nullable();
            $table->text('description_en')->nullable();
            $table->string('slug')->unique();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'display_order']);
            $table->index(['name_hi', 'name_en']);
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('faq_categories')->restrictOnDelete();
            $table->string('question_hi');
            $table->string('question_en');
            $table->longText('answer_hi');
            $table->longText('answer_en');
            $table->unsignedInteger('display_order')->default(0);
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'status', 'display_order']);
            $table->index(['status', 'published_at', 'expires_at']);
            $table->index(['question_hi', 'question_en']);
        });

        Schema::create('report_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_hi');
            $table->string('name_en');
            $table->text('description_hi')->nullable();
            $table->text('description_en')->nullable();
            $table->string('slug')->unique();
            $table->string('icon', 100)->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'display_order']);
            $table->index(['name_hi', 'name_en']);
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained('report_categories')->restrictOnDelete();
            $table->foreignId('media_asset_id')->constrained('media_assets')->restrictOnDelete();
            $table->string('title_hi');
            $table->string('title_en');
            $table->longText('description_hi')->nullable();
            $table->longText('description_en')->nullable();
            $table->string('report_period', 100)->nullable();
            $table->string('issuing_authority', 255)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->string('status', 20)->default('draft');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'status', 'published_at']);
            $table->index(['status', 'published_at', 'expires_at']);
            $table->index('report_period');
            $table->index(['title_hi', 'title_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('report_categories');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('faq_categories');
        Schema::dropIfExists('training_resources');
        Schema::dropIfExists('training_categories');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_categories');
    }
};
