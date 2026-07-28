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
        Schema::create('kms_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_member_id')->constrained()->cascadeOnDelete();
            $table->date('recorded_date');
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('head_circumference', 5, 2)->nullable();
            $table->decimal('lila', 5, 2)->nullable();
            $table->decimal('z_score', 5, 2)->nullable();
            $table->enum('status_gizi', ['hijau', 'kuning', 'merah'])->nullable();
            $table->foreignId('recorder_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kms_records');
    }
};
