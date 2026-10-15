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
        Schema::create('db_deeper_backup_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->text('secret');
            $table->char('fingerprint', 64)->unique();
            $table->boolean('is_active')->default(false)->index();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_deeper_backup_keys');
    }
};
