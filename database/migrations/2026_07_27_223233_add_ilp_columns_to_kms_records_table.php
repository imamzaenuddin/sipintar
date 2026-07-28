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
        Schema::table('kms_records', function (Blueprint $table) {
            $table->string('blood_pressure')->nullable(); // misal '120/80'
            $table->decimal('belly_circumference', 5, 2)->nullable();
            $table->integer('blood_sugar')->nullable();
            $table->decimal('uric_acid', 5, 2)->nullable();
            $table->integer('cholesterol')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kms_records', function (Blueprint $table) {
            $table->dropColumn([
                'blood_pressure', 
                'belly_circumference', 
                'blood_sugar', 
                'uric_acid', 
                'cholesterol'
            ]);
        });
    }
};
