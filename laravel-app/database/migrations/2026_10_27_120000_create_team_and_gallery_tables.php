<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_hi');
            $table->string('name_en');
            $table->text('description_hi')->nullable();
            $table->text('description_en')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'display_order']);
            $table->index('name_en');
            $table->index('name_hi');
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('full_name_hi');
            $table->string('full_name_en');
            $table->foreignId('photograph_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('designation_hi');
            $table->string('designation_en');
            $table->foreignId('team_category_id')->constrained('team_categories');
            $table->string('office_block')->nullable();
            $table->string('contact_number', 32)->nullable();
            $table->text('responsibilities_hi')->nullable();
            $table->text('responsibilities_en')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at', 'expires_at']);
            $table->index(['team_category_id', 'is_active', 'display_order']);
            $table->index('full_name_en');
            $table->index('full_name_hi');
            $table->index('designation_en');
            $table->index('designation_hi');
            $table->index('office_block');
        });

        Schema::create('gallery_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_hi');
            $table->string('name_en');
            $table->text('description_hi')->nullable();
            $table->text('description_en')->nullable();
            $table->foreignId('cover_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'display_order']);
            $table->index('name_en');
            $table->index('name_hi');
        });

        Schema::create('gallery_albums', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_hi');
            $table->string('title_en');
            $table->text('description_hi')->nullable();
            $table->text('description_en')->nullable();
            $table->date('event_date')->nullable();
            $table->foreignId('cover_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('gallery_category_id')->constrained('gallery_categories');
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at', 'expires_at']);
            $table->index(['gallery_category_id', 'is_active', 'display_order']);
            $table->index('event_date');
            $table->index('title_en');
            $table->index('title_hi');
        });

        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_album_id')->constrained('gallery_albums');
            $table->foreignId('media_asset_id')->constrained('media_assets')->restrictOnDelete();
            $table->string('caption_hi')->nullable();
            $table->string('caption_en')->nullable();
            $table->string('alt_text_hi')->nullable();
            $table->string('alt_text_en')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['gallery_album_id', 'media_asset_id']);
            $table->index(['gallery_album_id', 'is_active', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_images');
        Schema::dropIfExists('gallery_albums');
        Schema::dropIfExists('gallery_categories');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('team_categories');
    }
};
