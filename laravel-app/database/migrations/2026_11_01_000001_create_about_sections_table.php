<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('title_en', 255);
            $table->string('title_hi', 255);
            $table->longText('content_en');
            $table->longText('content_hi');
            $table->unsignedInteger('display_order')->default(0);
            $table->string('status', 24)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'display_order']);
        });

        $this->migrateLegacyAboutContent();
    }

    public function down(): void
    {
        Schema::dropIfExists('about_sections');
    }

    private function migrateLegacyAboutContent(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        $legacy = DB::table('site_settings')->where('setting_key', 'site.about')->first();
        if (! $legacy) {
            return;
        }

        $values = is_array($legacy->value) ? $legacy->value : json_decode((string) $legacy->value, true);
        if (! is_array($values)) {
            return;
        }

        $sections = [
            ['key' => 'introduction', 'title_en' => 'Introduction', 'title_hi' => 'परिचय'],
            ['key' => 'about', 'title_en' => 'About Us', 'title_hi' => 'हमारे बारे में'],
            ['key' => 'mission', 'title_en' => 'Our Mission', 'title_hi' => 'हमारा मिशन'],
            ['key' => 'vision', 'title_en' => 'Our Vision', 'title_hi' => 'हमारा दृष्टिकोण'],
            ['key' => 'objectives', 'title_en' => 'Objectives', 'title_hi' => 'उद्देश्य'],
        ];

        $order = 10;
        foreach ($sections as $section) {
            $english = trim((string) ($values[$section['key'].'_en'] ?? ''));
            $hindi = trim((string) ($values[$section['key'].'_hi'] ?? ''));
            if ($section['key'] === 'introduction') {
                $englishIntro = trim((string) ($values['page_intro_en'] ?? ''));
                $hindiIntro = trim((string) ($values['page_intro_hi'] ?? ''));
                $english = trim($englishIntro.($englishIntro !== '' && $english !== '' ? "\n\n" : '').$english);
                $hindi = trim($hindiIntro.($hindiIntro !== '' && $hindi !== '' ? "\n\n" : '').$hindi);
            }
            if ($english === '' && $hindi === '') {
                continue;
            }

            DB::table('about_sections')->insert([
                'title_en' => $section['title_en'],
                'title_hi' => $section['title_hi'],
                'content_en' => $english,
                'content_hi' => $hindi,
                'display_order' => $order,
                'status' => 'published',
                'published_at' => now(),
                'created_by' => $legacy->updated_by,
                'updated_by' => $legacy->updated_by,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $order += 10;
        }
    }
};
