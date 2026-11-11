<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table): void {
            $table->date('report_date')->nullable()->after('report_period');
            $table->string('office_block', 120)->nullable()->after('issuing_authority');
            $table->index(['report_date', 'office_block'], 'reports_date_office_idx');
            $table->index('office_block');
        });

        Schema::table('team_members', function (Blueprint $table): void {
            $table->boolean('email_public')->default(false)->after('email');
            $table->boolean('contact_number_public')->default(false)->after('contact_number');
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table): void {
            $table->dropColumn(['email_public', 'contact_number_public']);
        });

        Schema::table('reports', function (Blueprint $table): void {
            $table->dropIndex('reports_date_office_idx');
            $table->dropIndex(['office_block']);
            $table->dropColumn(['report_date', 'office_block']);
        });
    }
};
