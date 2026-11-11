<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_sections', function (Blueprint $table): void {
            $table->string('label_en', 120)->nullable();
            $table->string('label_hi', 120)->nullable();
            $table->string('title_en', 180)->nullable();
            $table->string('title_hi', 180)->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_hi')->nullable();
        });

        Schema::table('quick_links', function (Blueprint $table): void {
            $table->text('description_en')->nullable();
            $table->text('description_hi')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('quick_links', function (Blueprint $table): void {
            $table->dropColumn(['description_en', 'description_hi']);
        });

        Schema::table('homepage_sections', function (Blueprint $table): void {
            $table->dropColumn(['label_en', 'label_hi', 'title_en', 'title_hi', 'description_en', 'description_hi']);
        });
    }
};
