<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('db_deeper_backup_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->boolean('is_enabled')->default(true)->index();
            $table->string('frequency', 20);
            $table->time('run_at')->nullable();
            $table->unsignedTinyInteger('day_of_week')->nullable();
            $table->unsignedTinyInteger('day_of_month')->nullable();
            $table->string('timezone', 64)->default('UTC');
            $table->string('connection_name', 120);
            $table->string('disk_name', 120);
            $table->string('destination_path', 500)->nullable();
            $table->timestamp('next_run_at')->nullable()->index();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();

            $table->index(['is_enabled', 'next_run_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_deeper_backup_schedules');
    }
};
