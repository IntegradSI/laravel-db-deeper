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
        Schema::create('db_deeper_backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->nullable()->constrained('db_deeper_backup_schedules')->nullOnDelete();
            $table->foreignId('encryption_key_id')->nullable()->constrained('db_deeper_backup_keys')->restrictOnDelete();
            $table->string('requested_by')->nullable();
            $table->string('connection_name', 120);
            $table->string('driver', 32);
            $table->string('disk_name', 120);
            $table->string('destination_path', 500)->nullable();
            $table->string('path', 1000)->nullable();
            $table->string('status', 20)->default('queued')->index();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->char('checksum', 64)->nullable();
            $table->char('encryption_key_fingerprint', 64)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['disk_name', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_deeper_backups');
    }
};
