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
        Schema::create('doctor_detail_specializations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_detail_id');
            $table->unsignedBigInteger('specialization_id');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('doctor_detail_id')->references('id')->on('doctor_details');
            $table->foreign('specialization_id')->references('id')->on('specializations');
            $table->unique(['doctor_detail_id', 'specialization_id'], 'doctor_detail_specialization_combination_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_detail_specializations');
    }
};
