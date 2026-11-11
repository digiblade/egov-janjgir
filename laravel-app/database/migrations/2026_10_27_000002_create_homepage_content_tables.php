<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_information', function (Blueprint $table) {
            $table->id();
            $table->string('singleton_key', 32)->default('department')->unique();
            $table->string('title_hi');
            $table->string('title_en');
            $table->longText('description_hi');
            $table->longText('description_en');
            $table->longText('mission_hi')->nullable();
            $table->longText('mission_en')->nullable();
            $table->longText('objectives_hi')->nullable();
            $table->longText('objectives_en')->nullable();
            $table->foreignId('image_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_hi');
            $table->string('title_en');
            $table->longText('description_hi')->nullable();
            $table->longText('description_en')->nullable();
            $table->string('notice_type', 40)->default('general');
            $table->string('priority', 20)->default('normal');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('attachment_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('external_url', 2048)->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at', 'expires_at']);
            $table->index(['is_pinned', 'display_order']);
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_hi');
            $table->string('title_en');
            $table->longText('description_hi')->nullable();
            $table->longText('description_en')->nullable();
            $table->foreignId('featured_image_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('attachment_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('priority', 20)->default('normal');
            $table->unsignedInteger('display_order')->default(0);
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at', 'expires_at']);
            $table->index(['priority', 'display_order']);
        });

        Schema::create('quick_links', function (Blueprint $table) {
            $table->id();
            $table->string('title_hi');
            $table->string('title_en');
            $table->string('icon', 100)->nullable();
            $table->string('url', 2048);
            $table->boolean('is_external')->default(false);
            $table->boolean('opens_in_new_tab')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('display_order');
        });

        Schema::create('homepage_updates', function (Blueprint $table) {
            $table->id();
            $table->string('title_hi');
            $table->string('title_en');
            $table->text('summary_hi')->nullable();
            $table->text('summary_en')->nullable();
            $table->string('url', 2048)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at', 'expires_at']);
            $table->index('display_order');
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title_hi')->nullable();
            $table->string('title_en')->nullable();
            $table->text('subtitle_hi')->nullable();
            $table->text('subtitle_en')->nullable();
            $table->foreignId('desktop_image_media_id')->constrained('media_assets')->restrictOnDelete();
            $table->foreignId('mobile_image_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('cta_text_hi', 120)->nullable();
            $table->string('cta_text_en', 120)->nullable();
            $table->string('cta_url', 2048)->nullable();
            $table->string('text_alignment', 16)->default('left');
            $table->string('text_color', 7)->default('#FFFFFF');
            $table->string('overlay_color', 7)->default('#000000');
            $table->unsignedTinyInteger('overlay_opacity')->default(35);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'starts_at', 'ends_at']);
            $table->index('display_order');
        });

        Schema::create('kpis', function (Blueprint $table) {
            $table->id();
            $table->string('title_hi');
            $table->string('title_en');
            $table->string('description_hi')->nullable();
            $table->string('description_en')->nullable();
            $table->decimal('value', 18, 2)->default(0);
            $table->string('prefix', 20)->nullable();
            $table->string('suffix', 20)->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('background_color', 7)->nullable();
            $table->string('text_color', 7)->nullable();
            $table->string('icon_color', 7)->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('display_order');
        });

        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_key', 80)->unique();
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->json('settings')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_enabled', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
        Schema::dropIfExists('kpis');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('homepage_updates');
        Schema::dropIfExists('quick_links');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('notices');
        Schema::dropIfExists('department_information');
    }
};
