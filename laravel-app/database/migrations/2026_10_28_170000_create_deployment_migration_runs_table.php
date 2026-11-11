<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployment_migration_runs', function (Blueprint $table): void {
            $table->id();
            $table->char('key_hash', 64)->unique();
            $table->timestamp('ran_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_migration_runs');
    }
};
