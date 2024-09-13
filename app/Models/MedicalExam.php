<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalExam extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }
}
