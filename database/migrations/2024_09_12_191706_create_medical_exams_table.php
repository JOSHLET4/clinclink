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
        Schema::create('medical_exams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consultation_id');
            $table->unsignedBigInteger('exam_type_id');
            $table->text('results');
            $table->date('date');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('consultation_id')->references('id')->on('consultations');
            $table->foreign('exam_type_id')->references('id')->on('exam_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_exams');
    }
};
